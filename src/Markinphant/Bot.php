<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Markinphant;

    use Exception;
    use LogLib\Log;
    use Markinphant\Classes\SessionManager;
    use Redis;
    use TamerLib\Objects\Task;
    use TamerLib\Tamer;

    class Bot
    {
        /**
         * @var array
         */
        private $configuration;

        /**
         * @var \TgBotLib\Bot
         */
        private $bot;

        /**
         * @var SessionManager
         */
        private $session_manager;

        /**
         * Public Constructor
         *
         * @throws Exception
         */
        public function __construct()
        {
            $this->configuration = Classes\Factory::getConfiguration();

            // Initialize the bot
            $this->bot = Classes\Factory::initializeBot(
                (string)$this->configuration['bot']['token'],
                (string)$this->configuration['bot']['host'],
                (bool)$this->configuration['bot']['use_ssl'],
            );

            // Initialize Tamer (if enabled)
            /** @noinspection PhpUnnecessaryBoolCastInspection */
            if((bool)$this->configuration['tamer']['enabled'])
            {
                Log::info('com.netkas.markinphant', 'Tamer is enabled, initializing...');
                $tamer_config = $this->configuration['tamer'];

                try
                {
                    Tamer::initWorker();
                }
                catch(Exception $e)
                {
                    Tamer::init(
                        (string)$tamer_config['protocol'],
                        $tamer_config['servers'],
                        $tamer_config['username'],
                        $tamer_config['password']
                    );

                    Tamer::addWorker(__DIR__ . DIRECTORY_SEPARATOR . 'worker', (int)$tamer_config['workers']);
                }
            }

            // Initialize Session Manager
            Log::info('com.netkas.markinphant', 'Initializing Session Manager...');
            $redis = new Redis();
            $redis->connect(
                $this->configuration['redis']['host'],
                $this->configuration['redis']['port'],
                $this->configuration['redis']['timeout']
            );

            if($this->configuration['redis']['password'] !== null)
                $redis->auth($this->configuration['redis']['password']);

            $this->session_manager = new SessionManager($redis);
            $this->session_manager->load();
        }

        /**
         * @return array
         */
        public function getConfiguration(): array
        {
            return $this->configuration;
        }

        /**
         * @return \TgBotLib\Bot
         */
        public function getBot(): \TgBotLib\Bot
        {
            return $this->bot;
        }

        /**
         * Runs the bot in a loop
         *
         * @return void
         */
        public function main(): void
        {
            Log::info('com.netkas.markinphant', 'Starting Markinphant Bot...');
            $last_session_save = time();

            /** @noinspection PhpUnnecessaryBoolCastInspection */
            if((bool)$this->configuration['tamer']['enabled'])
            {
                try
                {
                    Tamer::startWorkers();
                }
                catch(Exception $e)
                {
                    Log::error('com.netkas.markinphant', 'Cannot initialize workers with TamerLib', $e);
                    exit(1);
                }
            }

            while(true)
            {
                try
                {
                    /** @noinspection PhpUnnecessaryBoolCastInspection */
                    if((bool)$this->configuration['tamer']['enabled'])
                    {
                        // Handle updates with Tamer if it's enabled
                        foreach($this->bot->getUpdates() as $update)
                        {
                            Tamer::do(Task::create('handle_update', json_encode($update->toArray())));
                        }
                    }
                    else
                    {
                        // Otherwise handle it traditionally
                        $this->bot->handleGetUpdates();
                    }

                    if(time() - $last_session_save >= 15)
                    {
                        // Occasionally save the session from memory to disk
                        Log::verbose('com.netkas.markinphant', 'Saving session...');
                        $this->session_manager->save();
                        $last_session_save = time();
                    }

                    Tamer::monitor();
                }
                catch(Exception $e)
                {
                    Log::error('com.netkas.markinphant', $e->getMessage(), $e);
                }
            }
        }
    }