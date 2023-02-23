<?php

    namespace Markinphant\Classes;

    use ConfigLib\Configuration;
    use Exception;
    use Markinphant\Abstracts\Constants;

    class Utilities
    {
        /**
         * The cache for locales
         *
         * @var array
         */
        private static $locale_cache = [];

        /**
         * Converts a sample to frames for the model with start and stop frames
         *
         * @param string $sample The sample to convert
         * @param bool $as_lower Whether to convert the sample to lowercase
         * @return array
         */
        public static function sampleToFrames(string $sample, bool $as_lower): array
        {
            $frames = [
                Constants::MrkvStart
            ];

            foreach (explode(' ', $as_lower ? strtolower($sample) : $sample) as $word)
            {
                $frames[] = $word;
            }

            $frames[] = Constants::MrkvEnd;
            return $frames;
        }

        /**
         * Gets the locale for the program
         *
         * @param string $locale
         * @param string $value
         * @return string
         */
        public static function getLocale(string $locale, string $value): string
        {
            $locale = strtolower($locale);
            if(strlen($locale) > 2)
                $locale = substr($locale, 0, 2);

            if(isset(self::$locale_cache[$locale]))
                return self::$locale_cache[$locale][$value] ?? sprintf('?%s?', $value);

            $file = __DIR__ . DIRECTORY_SEPARATOR . 'Locales' . DIRECTORY_SEPARATOR . $locale . '.json';

            if (!file_exists($file))
            {
                // Load the default locale
                $file = __DIR__ . DIRECTORY_SEPARATOR . 'Locales' . DIRECTORY_SEPARATOR . 'en.json';
            }

            self::$locale_cache[$locale] = json_decode(file_get_contents($file), true);
            return self::$locale_cache[$locale][$value] ?? sprintf('?%s?', $value);
        }

        /**
         * Returns the configuration for the program
         *
         * @return array
         * @throws Exception
         */
        public static function getConfiguration(): array
        {
            $configuration = new Configuration('markinphant');

            // Bot Configuration
            $configuration->setDefault('bot.token', '<token>');
            $configuration->setDefault('bot.username', 'MarkinphantBot');
            $configuration->setDefault('bot.host', 'api.telegram.org');
            $configuration->setDefault('bot.use_ssl', true);
            $configuration->setDefault('bot.default_locale', 'en');

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

            // Return the configuration
            return $configuration->getConfiguration();
        }
    }