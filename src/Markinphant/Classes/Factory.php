<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Markinphant\Classes;

    use ConfigLib\Configuration;
    use Exception;
    use Markinphant\Handlers\DeleteCommand;
    use Markinphant\Handlers\ExportCommand;
    use Markinphant\Handlers\GenericMessageEvent;
    use Markinphant\Handlers\HelpCommand;
    use Markinphant\Handlers\StartCommand;
    use Markinphant\Handlers\ThinkCommand;
    use TgBotLib\Abstracts\EventType;
    use TgBotLib\Bot;

    class Factory
    {
        /**
         * The cache for the configuration file
         *
         * @var array|null
         */
        private static $configuration_cache = null;

        /**
         * Returns the configuration for the program
         *
         * @return array
         * @throws Exception
         */
        public static function getConfiguration(): array
        {
            if (self::$configuration_cache !== null)
                return self::$configuration_cache;

            $configuration = new Configuration('markinphant');

            // Bot Configuration
            $configuration->setDefault('bot.token', '<token>');
            $configuration->setDefault('bot.username', 'MarkinphantBot');
            $configuration->setDefault('bot.host', 'api.telegram.org');
            $configuration->setDefault('bot.use_ssl', true);
            $configuration->setDefault('bot.default_locale', 'en');
            $configuration->setDefault('bot.max_model_size', 1000000);

            // Tamer Configuration
            $configuration->setDefault('tamer.enabled', false);
            $configuration->setDefault('tamer.workers', 4);
            $configuration->setDefault('tamer.protocol', 'gearman');
            $configuration->setDefault('tamer.username', 'guest');
            $configuration->setDefault('tamer.password', 'guest');
            $configuration->setDefault('tamer.servers', [
                '127.0.0.1:4730'
            ]);

            // Redis connection
            $configuration->setDefault('redis.host', '127.0.0.1');
            $configuration->setDefault('redis.port', 6379);
            $configuration->setDefault('redis.password', null);
            $configuration->setDefault('redis.timeout', 0);

            // Save the configuration
            $configuration->save();

            // Load the configuration
            self::$configuration_cache = $configuration->getConfiguration();
            return self::$configuration_cache;
        }

        /**
         * Initializes a new bot instance
         *
         * @param string $token
         * @param string $host
         * @param bool $use_ssl
         * @return Bot
         */
        public static function initializeBot(string $token, string $host, bool $use_ssl): Bot
        {
            // Initialize the bot
            $bot = new Bot($token);
            $bot->setHost($host);
            $bot->setSsl($use_ssl);

            // Initialize commands & events
            $bot->setCommandHandlers([
                'deleted' => new DeleteCommand(),
                'export' => new ExportCommand(),
                'help' => new HelpCommand(),
                'start' => new StartCommand(),
                'think' => new ThinkCommand()
            ]);

            $bot->setEventHandler(EventType::GenericUpdate, new GenericMessageEvent());

            return $bot;
        }
    }