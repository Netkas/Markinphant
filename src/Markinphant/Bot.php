<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Markinphant;

    use Exception;
    use LogLib\Log;
    use Markinphant\Classes\Utilities;
    use Markinphant\Commands\StartCommand;
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
         * Public Constructor
         *
         * @throws Exception
         */
        public function __construct()
        {
            $this->configuration = Utilities::getConfiguration();

            // Initialize the bot
            $this->bot = new \TgBotLib\Bot($this->configuration['bot']['token']);
            $this->bot->setHost($this->configuration['bot']['host']);
            $this->bot->setSsl((bool)$this->configuration['bot']['use_ssl'] ?? true);

            // Initialize Tamer (if enabled)
            /** @noinspection PhpUnnecessaryBoolCastInspection */
            if((bool)$this->configuration['tamer']['enabled'])
            {
                Log::info('com.netkas.markinphant', 'Tamer is enabled, initializing...');
                $tamer_config = $this->configuration['tamer'];
                \TamerLib\Tamer::init(
                    (string)$tamer_config['protocol'],
                    $tamer_config['servers'],
                    $tamer_config['username'],
                    $tamer_config['password']
                );

                Tamer::addWorker(__DIR__ . DIRECTORY_SEPARATOR . 'worker', (int)$tamer_config['workers']);
            }
            
            // Register the commands
            $this->bot->setCommandHandler('start', new StartCommand());
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
        public function main()
        {
            Log::info('com.netkas.markinphant', 'Starting Markinphant Bot...');

            while(true)
            {
                try
                {
                    $this->bot->handleGetUpdates(true);
                }
                catch(Exception $e)
                {
                    Log::error('com.netkas.markinphant', 'hiccup', $e);
                }
            }
        }
    }