<?php

    namespace Markinphant\Handlers;

    use TgBotLib\Abstracts\ChatType;
    use TgBotLib\Bot;
    use TgBotLib\Exceptions\TelegramException;
    use TgBotLib\Interfaces\CommandInterface;
    use TgBotLib\Objects\Telegram\Update;

    class StartCommand implements CommandInterface
    {
        /**
         * Handles the /start command
         *
         * @param Bot $bot
         * @param Update $update
         * @return void
         * @throws TelegramException
         */
        public function handle(Bot $bot, Update $update): void
        {
            // Handle private chats
            if($update->getMessage()->getChat()->getType() == ChatType::Private)
            {
                $bot->sendMessage(
                    chat_id: $update->getMessage()->getChat()->getId(),
                    text: 'Hello, ' . $update->getMessage()->getFrom()->getFirstName() . '!' . PHP_EOL . PHP_EOL .
                    'I am Markinphant, a bot that can learn from messages and reply to them.' . PHP_EOL .
                    'You can use me in groups, but I will only learn from messages that I can see.' . PHP_EOL .
                    'If you want to learn more about me, use /help.',
                    options: [
                        'reply_to_message_id' => $update->getMessage()->getMessageId()
                    ]
                );

                return;
            }

            // Handle group chats
            if($update->getMessage()->getChat()->getType() == ChatType::Group || $update->getMessage()->getChat()->getType() == ChatType::Supergroup)
            {
                // Group chat
                $bot->sendMessage(
                    chat_id: $update->getMessage()->getChat()->getId(),
                    text: 'Hello, ' . $update->getMessage()->getFrom()->getFirstName() . '!',
                    options: [
                        'reply_to_message_id' => $update->getMessage()->getMessageId(),
                    ]
                );
            }
        }
    }