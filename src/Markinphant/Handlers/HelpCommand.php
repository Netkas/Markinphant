<?php

    namespace Markinphant\Handlers;

    use TgBotLib\Abstracts\ChatType;
    use TgBotLib\Bot;
    use TgBotLib\Exceptions\TelegramException;
    use TgBotLib\Interfaces\CommandInterface;
    use TgBotLib\Objects\Telegram\Update;

    class HelpCommand implements CommandInterface
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
            $bot->sendMessage(
                chat_id: $update->getMessage()->getChat()->getId(),
                text: 'These are the available commands:' . PHP_EOL . PHP_EOL .
                '<code>/start</code> - Start the bot' . PHP_EOL .
                '<code>/help</code> - Show this message' . PHP_EOL .
                '<code>/think</code> - What am I thinking about?' . PHP_EOL .
                '<code>/export</code> - Export the model to a file (Only available to admins)' . PHP_EOL .
                `<code>/delete</code> - Delete all the data I have (Only available to admins)` . PHP_EOL . PHP_EOL .
                'Source code: https://git.n64.cc/netkas/markinphant',
                options: [
                    'reply_to_message_id' => $update->getMessage()->getMessageId(),
                    'parse_mode' => 'HTML'
                ]
            );
        }
    }