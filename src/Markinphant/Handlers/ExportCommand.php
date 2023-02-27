<?php

    namespace Markinphant\Handlers;

    use LogLib\Log;
    use Markinphant\Classes\SessionManager;
    use ncc\Exceptions\InvalidPackageNameException;
    use ncc\Exceptions\InvalidScopeException;
    use ncc\Exceptions\PackageLockException;
    use ncc\Exceptions\PackageNotFoundException;
    use RedisException;
    use TgBotLib\Abstracts\ChatActionType;
    use TgBotLib\Abstracts\ChatType;
    use TgBotLib\Bot;
    use TgBotLib\Exceptions\TelegramException;
    use TgBotLib\Interfaces\CommandInterface;
    use TgBotLib\Objects\Telegram\Update;

    class ExportCommand implements CommandInterface
    {
        /**
         * Handles the /start command
         *
         * @param Bot $bot
         * @param Update $update
         * @return void
         * @throws TelegramException
         * @throws RedisException
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
                if(!SessionManager::getInstance()->isAdmin(
                    $bot, $update->getMessage()->getChat()->getId(), $update->getMessage()->getFrom()->getId())
                )
                {
                    $bot->sendMessage(
                        chat_id: $update->getMessage()->getChat()->getId(),
                        text: 'Only administrators can use this command.',
                        options: [
                            'reply_to_message_id' => $update->getMessage()->getMessageId()
                        ]
                    );

                    return;
                }

                // Get the session and update it
                $session = SessionManager::getInstance()->getEntry($update->getMessage()->getChat()->getId());
                $session->setLastSeen(time());
                SessionManager::getInstance()->updateEntry($session);

                // Check if the model has been trained
                if($session->getCollectedSamples() == 0)
                {
                    $bot->sendMessage(
                        chat_id: $update->getMessage()->getChat()->getId(),
                        text: 'No model has been trained yet to export',
                        options: [
                            'reply_to_message_id' => $update->getMessage()->getMessageId()
                        ]
                    );

                    return;
                }

                // Check if the model exists
                if(file_exists(SessionManager::getInstance()->getModelPath($update->getMessage()->getChat()->getId())))
                {
                    Log::info('com.netkas.markinphant', 'Exporting model for chat ' . $update->getMessage()->getChat()->getId());

                    $bot->sendChatAction(
                        chat_id: $update->getMessage()->getChat()->getId(),
                        action: ChatActionType::UploadDocument
                    );

                    $bot->sendDocument(
                        chat_id: $update->getMessage()->getChat()->getId(),
                        document: SessionManager::getInstance()->getModelPath($update->getMessage()->getChat()->getId()),
                        options: [
                            'reply_to_message_id' => $update->getMessage()->getMessageId()
                        ]
                    );

                    return;
                }

                // No model found
                $bot->sendMessage($update->getMessage()->getChat()->getId(), 'No model found to export');
            }
        }
    }