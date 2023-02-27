<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Markinphant\Objects;

    class IndexEntry
    {
        /**
         * @var string|null
         */
        private $chat_id;

        /**
         * @var int
         */
        private $last_seen;

        /**
         * @var int
         */
        private $last_message;

        /**
         * @var int
         */
        private $collected_samples;

        /**
         * @var Configuration
         */
        private $configuration;

        /**
         * Public Constructor
         *
         * @param string $chat_id
         */
        public function __construct(string $chat_id)
        {
            $this->chat_id = $chat_id;
            $this->last_seen = time();
            $this->last_message = 0;
            $this->collected_samples = 0;
            $this->configuration = new Configuration();
        }

        /**
         * @return string|null
         */
        public function getChatId(): ?string
        {
            return $this->chat_id;
        }

        /**
         * @param string|null $chat_id
         */
        public function setChatId(?string $chat_id): void
        {
            $this->chat_id = $chat_id;
        }

        /**
         * @return int
         */
        public function getLastSeen(): int
        {
            return $this->last_seen;
        }

        /**
         * @param int $last_seen
         */
        public function setLastSeen(int $last_seen): void
        {
            $this->last_seen = $last_seen;
        }

        /**
         * @return int
         */
        public function getLastMessage(): int
        {
            return $this->last_message;
        }

        /**
         * @param int $last_message
         */
        public function setLastMessage(int $last_message): void
        {
            $this->last_message = $last_message;
        }

        /**
         * @return int
         */
        public function getCollectedSamples(): int
        {
            return $this->collected_samples;
        }

        /**
         * @param int $collected_samples
         */
        public function setCollectedSamples(int $collected_samples): void
        {
            $this->collected_samples = $collected_samples;
        }

        /**
         * @return void
         */
        public function incrementCollectedSamples(): void
        {
            $this->collected_samples++;
        }

        /**
         * @return Configuration
         */
        public function getConfiguration(): Configuration
        {
            return $this->configuration;
        }

        /**
         * @param Configuration $configuration
         */
        public function setConfiguration(Configuration $configuration): void
        {
            $this->configuration = $configuration;
        }

        /**
         * Returns the index entry as an array
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'chat_id' => $this->chat_id,
                'last_seen' => $this->last_seen,
                'last_message' => $this->last_message,
                'collected_samples' => $this->collected_samples,
                'configuration' => $this->configuration->toArray()
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @param string|null $chat_id
         * @return IndexEntry
         */
        public static function fromArray(array $data, ?string $chat_id=null): IndexEntry
        {
            $entry = new IndexEntry($chat_id ?? $data['chat_id']);
            $entry->last_seen = $data['last_seen'] ?? 0;
            $entry->last_message = $data['last_message'] ?? 0;
            $entry->collected_samples = $data['collected_samples'] ?? 0;
            $entry->configuration = Configuration::fromArray($data['configuration'] ?? []);


            return $entry;
        }
    }