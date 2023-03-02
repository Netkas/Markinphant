<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Markinphant\Classes;

    use Exception;
    use LogLib\Log;
    use Markinphant\Objects\IndexEntry;
    use ncc\Exceptions\InvalidPackageNameException;
    use ncc\Exceptions\InvalidScopeException;
    use ncc\Exceptions\PackageLockException;
    use ncc\Exceptions\PackageNotFoundException;
    use Redis;
    use RedisException;
    use TgBotLib\Bot;
    use TgBotLib\Exceptions\TelegramException;
    use TgBotLib\Objects\Telegram\ChatMember;

    class SessionManager
    {
        /**
         * @var SessionManager
         */
        private static $session_manager;

        /**
         * @var string
         */
        private $index_path;

        /**
         * @var IndexEntry[]
         */
        private $entries;

        /**
         * @var Redis
         */
        private $redis;

        /**
         * Public Constructor`
         *
         * @throws InvalidPackageNameException
         * @throws InvalidScopeException
         * @throws PackageLockException
         * @throws PackageNotFoundException
         */
        public function __construct(Redis $redis)
        {
            $this->redis = $redis;
            $this->index_path = Utilities::getStoragePath() . DIRECTORY_SEPARATOR . 'index.json';

            try
            {
                $this->load();
            }
            catch(Exception $e)
            {
                unset($e);
            }

            self::$session_manager = $this;
        }

        /**
         * Returns the index entry for the given chat ID
         *
         * @param string|int $chat_id
         * @return IndexEntry|null
         * @throws RedisException
         */
        public function getEntry(string|int $chat_id): IndexEntry
        {
            if(!$this->redis->exists(sprintf('chat_session:%s', $chat_id)))
            {
                $index = new IndexEntry($chat_id);
                $this->redis->set(sprintf('chat_session:%s', $chat_id), json_encode($index->toArray()));
                return $index;
            }

            return IndexEntry::fromArray(json_decode($this->redis->get(sprintf('chat_session:%s', $chat_id)), true), $chat_id);
        }

        /**
         * Gets the model for the given chat ID
         *
         * @param string|int $chat_id
         * @return MarkovChains
         * @throws InvalidPackageNameException
         * @throws InvalidScopeException
         * @throws PackageLockException
         * @throws PackageNotFoundException
         * @throws RedisException
         */
        public function getModel(string|int $chat_id): MarkovChains
        {
            if($this->redis->exists(sprintf('chat_model:%s', $chat_id)))
            {
                MarkovChains::import(json_decode($this->redis->get(sprintf('chat_model:%s', $chat_id)), true));
            }

            $model_path = Utilities::getStoragePath() . DIRECTORY_SEPARATOR . sprintf('model_%s.json', $chat_id);

            // If the model isn't loaded, create or load it from the file system
            if(file_exists($model_path))
            {
                $model = MarkovChains::import(json_decode(file_get_contents($model_path), true));
            }
            else
            {
                $model = new MarkovChains();
                $model->setMaxSize(50000);
            }

            $this->redis->set(sprintf('chat_model:%s', $chat_id), json_encode($model->export()));
            return $model;
        }

        /**
         * Returns the path to the model for the given chat ID
         *
         * @param string|int $chat_id
         * @return string
         * @throws InvalidPackageNameException
         * @throws InvalidScopeException
         * @throws PackageLockException
         * @throws PackageNotFoundException
         */
        public function getModelPath(string|int $chat_id): string
        {
            return Utilities::getStoragePath() . DIRECTORY_SEPARATOR . sprintf('model_%s.json', $chat_id);
        }

        /**
         * Updates the index entry for the given chat ID
         *
         * @param IndexEntry $entry
         * @return void
         * @throws RedisException
         */
        public function updateEntry(IndexEntry $entry): void
        {
            $this->redis->set(sprintf('chat_session:%s', $entry->getChatId()), json_encode($entry->toArray()));
        }

        /**
         * Updates the model for the given chat ID
         *
         * @param string|int $chat_id
         * @param MarkovChains $model
         * @return void
         * @throws RedisException
         */
        public function updateModel(string|int $chat_id, MarkovChains $model): void
        {
            $this->redis->set(sprintf('chat_model:%s', $chat_id), json_encode($model->export()));
        }

        /**
         * Checks if the given user is an admin in the given chat
         *
         * @param Bot $bot
         * @param string|int $chat_id
         * @param string|int $user_id
         * @return bool
         * @throws RedisException
         * @throws TelegramException
         */
        public function isAdmin(Bot $bot, string|int $chat_id, string|int $user_id): bool
        {
            // Redis cache for admins
            if($this->redis->exists(sprintf('chat_admins:%s', $chat_id)))
            {
                $admins = array_map(
                    fn($admin) => ChatMember::fromArray($admin),
                    json_decode($this->redis->get(sprintf('chat_admins:%s', $chat_id)), true)
                );
            }
            else
            {
                // Set for 3 minutes
                $admins = $bot->getChatAdministrators($chat_id);
                $this->redis->set(sprintf('chat_admins:%s', $chat_id), json_encode(array_map(fn($admin) => $admin->toArray(), $admins)));
                $this->redis->expire(sprintf('chat_admins:%s', $chat_id), 180);
            }

            /** @var ChatMember $admin */
            foreach($admins as $admin)
            {
                if($admin->getUser()->getId() === $user_id)
                {
                    return true;
                }
            }

            return false;
        }

        /**
         * Purges the index entry and model for the given chat ID
         *
         * @param string|int $chat_id
         * @return void
         * @throws InvalidPackageNameException
         * @throws InvalidScopeException
         * @throws PackageLockException
         * @throws PackageNotFoundException
         * @throws RedisException
         */
        public function purge(string|int $chat_id): void
        {
            $this->redis->del(sprintf('chat_session:%s', $chat_id));
            $this->redis->del(sprintf('chat_model:%s', $chat_id));
            $this->redis->del(sprintf('chat_admins:%s', $chat_id));

            if(file_exists($this->getModelPath($chat_id)))
            {
                unlink($this->getModelPath($chat_id));
            }

            unset($this->entries[$chat_id]);
        }

        /**
         * Saves the index to the file system
         *
         * @return void
         * @throws InvalidPackageNameException
         * @throws InvalidScopeException
         * @throws PackageLockException
         * @throws PackageNotFoundException
         * @throws RedisException
         */
        public function save(): void
        {
            // First pull all the entries from Redis
            foreach($this->redis->keys('chat_session:*') as $key)
            {
                $chat_id = str_replace('chat_session:', '', $key);
                $index_entry = json_decode($this->redis->get($key), true);

                if($index_entry == null)
                {
                    Log::warning('com.netkas.markinphant', sprintf('Index entry for chat ID %s is null', $chat_id));
                    continue;
                }

                $this->entries[$chat_id] = IndexEntry::fromArray($index_entry, $chat_id);
            }

            // Save all the models to the file system
            foreach($this->redis->keys('chat_model:*') as $key)
            {
                $model_path = Utilities::getStoragePath() . DIRECTORY_SEPARATOR . sprintf('model_%s.json', str_replace('chat_model:', '', $key));
                file_put_contents($model_path, $this->redis->get($key));
                chmod($model_path, 0777);
            }

            // Then save them to the file system
            $results = [];
            foreach($this->entries as $entry)
            {
                $results[$entry->getChatId()] = $entry->toArray();
            }

            file_put_contents($this->index_path, json_encode($results));
            chmod($this->index_path, 0777);
        }

        /**
         * Loads the index from the file system
         *
         * @return void
         * @throws RedisException
         */
        public function load(): void
        {
            $this->entries = [];

            if(file_exists($this->index_path))
            {
                $results = json_decode(file_get_contents($this->index_path), true);

                foreach($results as $chat_id => $data)
                {
                    $this->entries[$chat_id] = IndexEntry::fromArray($data, $chat_id);
                }

                chmod($this->index_path, 0777);
            }

            foreach($this->entries as $chat_id => $entry)
            {
                if($this->redis->exists(sprintf('chat_session:%s', $chat_id)))
                    $this->redis->del(sprintf('chat_session:%s', $chat_id));

                $this->redis->set(sprintf('chat_session:%s', $chat_id), json_encode($entry->toArray()));
            }
        }

        /**
         * @return string
         */
        public function getIndexPath(): string
        {
            return $this->index_path;
        }

        /**
         * Public Destructor
         */
        public function __destruct()
        {
            try
            {
                $this->save();
            }
            catch(Exception $e)
            {
                unset($e);
            }
        }

        /**
         * Returns the instance of the SessionManager
         *
         * @return SessionManager
         */
        public static function getInstance(): SessionManager
        {
            return self::$session_manager;
        }
    }