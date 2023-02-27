<?php

    namespace Markinphant\Handlers;

    use Exception;
    use LogLib\Log;
    use Markinphant\Classes\SessionManager;
    use RedisException;
    use TgBotLib\Abstracts\ChatType;
    use TgBotLib\Bot;
    use TgBotLib\Exceptions\TelegramException;
    use TgBotLib\Interfaces\CommandInterface;
    use TgBotLib\Objects\Telegram\Update;

    class DeleteCommand implements CommandInterface
    {
        /**
         * Handles the /start command
         *
         * @param Bot $bot
         * @param Update $update
         * @return void
         * @throws TelegramException
         * @throws RedisException
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

                try
                {
                    SessionManager::getInstance()->purge($update->getMessage()->getChat()->getId());
                }
                catch(Exception $e)
                {
                    Log::error('com.netkas.markinphant', 'Failed to purge session: ' . $e->getMessage(), $e);
                    $bot->sendMessage(
                        chat_id: $update->getMessage()->getChat()->getId(),
                        text: 'Failed to delete session: ' . $e->getMessage(),
                        options: [
                            'reply_to_message_id' => $update->getMessage()->getMessageId()
                        ]
                    );
                    return;
                }

                $bot->sendMessage(
                    chat_id: $update->getMessage()->getChat()->getId(),
                    text: 'Session deleted successfully.',
                    options: [
                        'reply_to_message_id' => $update->getMessage()->getMessageId()
                    ]
                );
            }
        }
    }