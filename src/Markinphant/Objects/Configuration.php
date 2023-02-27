<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Markinphant\Objects;

    class Configuration
    {
        /**
         * @var bool
         */
        private $enabled;

        /**
         * @var int
         */
        private $response_probability;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            $this->enabled = true;
            $this->response_probability = 15;
        }

        /**
         * Returns true if Learning is enabled for the chat
         *
         * @return bool
         */
        public function isEnabled(): bool
        {
            return $this->enabled;
        }

        /**
         * Sets the enabled state of Learning for the chat
         * True = Learning is enabled
         * False = Learning is disabled
         *
         * @param bool $enabled
         * @return void
         */
        public function setEnabled(bool $enabled): void
        {
            $this->enabled = $enabled;
        }

        /**
         * Returns the probability of a response (A number between 0 and 100)
         *
         * @return int
         */
        public function getResponseProbability(): int
        {
            return $this->response_probability;
        }

        /**
         * Sets the probability of a response (A number between 0 and 100)
         *
         * @param int $response_probability
         * @return void
         */
        public function setResponseProbability(int $response_probability): void
        {
            if($response_probability < 0)
            {
                $response_probability = 0;
            }
            else if($response_probability > 100)
            {
                $response_probability = 100;
            }

            $this->response_probability = $response_probability;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'enabled' => $this->enabled,
                'response_probability' => $this->response_probability
            ];
        }

        /**
         * Constructs a Configuration object from an array representation
         *
         * @param mixed $param
         * @return Configuration
         */
        public static function fromArray(mixed $param)
        {
            $configuration = new Configuration();

            if(is_array($param))
            {
                if(isset($param['enabled']))
                {
                    $configuration->setEnabled((bool)$param['enabled']);
                }

                if(isset($param['response_probability']))
                {
                    $configuration->setResponseProbability((int)$param['response_probability']);
                }
            }

            return $configuration;
        }
    }