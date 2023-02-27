<?php

    namespace Markinphant\Handlers;

    use Markinphant\Classes\SessionManager;
    use Markinphant\Exceptions\MarkovGenerateException;
    use ncc\Exceptions\InvalidPackageNameException;
    use ncc\Exceptions\InvalidScopeException;
    use ncc\Exceptions\PackageLockException;
    use ncc\Exceptions\PackageNotFoundException;
    use TgBotLib\Abstracts\ChatType;
    use TgBotLib\Bot;
    use TgBotLib\Exceptions\TelegramException;
    use TgBotLib\Interfaces\CommandInterface;
    use TgBotLib\Objects\Telegram\Update;

    class ThinkCommand implements CommandInterface
    {
        /**
         * Handles the /start command
         *
         * @param Bot $bot
         * @param Update $update
         * @return void
         * @throws TelegramException
         * @throws MarkovGenerateException
         * @throws \RedisException
         * @throws InvalidPackageNameException
         * @throws InvalidScopeException
         * @throws PackageLockException
         * @throws PackageNotFoundException
         */
        public function handle(Bot $bot, Update $update): void
        {
            // Handle private chats
            if($update->getMessage()->getChat()->getType() == ChatType::Private)
            {
                $bot->sendMessage(
                    chat_id: $update->getMessage()->getChat()->getId(),
                    text: 'This command is not available in private chats.',
                    options: [
                        'reply_to_message_id' => $update->getMessage()->getMessageId()
                    ]
                );

                return;
            }

            // Handle group chats
            if($update->getMessage()->getChat()->getType() == ChatType::Group || $update->getMessage()->getChat()->getType() == ChatType::Supergroup)
            {
                $session = SessionManager::getInstance()->getEntry($update->getMessage()->getChat()->getId());
                $session->setLastSeen(time());

                $model = SessionManager::getInstance()->getModel($update->getMessage()->getChat()->getId());

                if($session->getCollectedSamples() < 5)
                {
                    $bot->sendMessage(
                        chat_id: $update->getMessage()->getChat()->getId(),
                        text: 'Please wait until I have collected enough samples to think.',
                        options: [
                            'reply_to_message_id' => $update->getMessage()->getMessageId()
                        ]
                    );
                }
                else
                {
                    $bot->sendMessage(
                        chat_id: $update->getMessage()->getChat()->getId(),
                        text: $model->generate(),
                        options: [
                            'reply_to_message_id' => $update->getMessage()->getMessageId()
                        ]
                    );
                }
            }
        }
    }