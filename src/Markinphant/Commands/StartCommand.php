<?php

    namespace Markinphant\Commands;

    use Markinphant\Classes\Utilities;
    use TgBotLib\Abstracts\ChatType;
    use TgBotLib\Bot;
    use TgBotLib\Interfaces\CommandInterface;
    use TgBotLib\Objects\Telegram\Update;

    class StartCommand implements CommandInterface
    {
        /**
         * @inheritDoc
         */
        public function handle(Bot $bot, Update $update): void
        {
            // Handle private chats
            if($update->getMessage()->getChat()->getType() == ChatType::Private)
            {
                $bot->sendMessage(
                    chat_id: $update->getMessage()->getChat()->getId(),
                    text: Utilities::getLocale('en', 'start_command_private'),
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
                    text: Utilities::getLocale('en', 'start_command_group'),
                    options: [
                        'reply_to_message_id' => $update->getMessage()->getMessageId(),
                    ]
                );
            }
        }
    }