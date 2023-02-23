<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Markinphant\Objects;

    class ChatSettings
    {
        /**
         * @var string|int
         */
        private $chat_id;

        /**
         * @var int
         */
        private $response_chance;

        /**
         * @param $chat_id
         */
        public function __construct($chat_id)
        {
            $this->chat_id = $chat_id;
            $this->response_chance = 15;
        }

        /**
         * Returns the current chat id
         *
         * @return int|string
         */
        public function getChatId(): int|string
        {
            return $this->chat_id;
        }

        /**
         * Returns the current response chance
         *
         * @return int
         */
        public function getResponseChance(): int
        {
            return $this->response_chance;
        }

        /**
         * Sets the chance of the bot to randomly respond to a message
         *
         * @param int $response_chance
         */
        public function setResponseChance(int $response_chance): void
        {
            $this->response_chance = $response_chance;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'chat_id' => $this->chat_id,
                'response_chance' => $this->response_chance
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return static
         */
        public static function fromArray(array $data): self
        {
            $chat_settings = new self($data['chat_id']);
            $chat_settings->setResponseChance($data['response_chance']);
            return $chat_settings;
        }
    }