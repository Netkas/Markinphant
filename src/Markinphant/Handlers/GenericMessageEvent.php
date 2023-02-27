<?php

    namespace Markinphant\Handlers;

    use Markinphant\Classes\SessionManager;
    use Markinphant\Exceptions\MarkovGenerateException;
    use ncc\Exceptions\InvalidPackageNameException;
    use ncc\Exceptions\InvalidScopeException;
    use ncc\Exceptions\PackageLockException;
    use ncc\Exceptions\PackageNotFoundException;
    use RedisException;
    use TgBotLib\Abstracts\ChatType;
    use TgBotLib\Bot;
    use TgBotLib\Exceptions\TelegramException;
    use TgBotLib\Interfaces\EventInterface;
    use TgBotLib\Objects\Telegram\Update;

    class GenericMessageEvent implements EventInterface
    {
        /**
         * Handles a generic message event
         *
         * @param Bot $bot
         * @param Update $update
         * @return void
         * @throws MarkovGenerateException
         * @throws RedisException
         * @throws TelegramException
         * @throws InvalidPackageNameException
         * @throws InvalidScopeException
         * @throws PackageLockException
         * @throws PackageNotFoundException
         */
        public function handle(Bot $bot, Update $update): void
        {
            if($update->getMessage() == null)
                return;
            if($update->getMessage()->getChat() == null)
                return;

            $text = $update->getMessage()->getText() ?? $update->getMessage()->getCaption() ?? null;
            if($text == null)
                return;

            if(
                $update->getMessage()->getChat()->getType() == ChatType::Group ||
                $update->getMessage()->getChat()->getType() == ChatType::Supergroup
            )
            {
                $session = SessionManager::getInstance()->getEntry($update->getMessage()->getChat()->getId());
                $session->setLastSeen(time());

                $model = SessionManager::getInstance()->getModel($update->getMessage()->getChat()->getId());

                if($session->getConfiguration()->isEnabled())
                {
                    // Ignore commands
                    if(str_starts_with($text, '/'))
                        return;

                    $model->addSample($text);
                    SessionManager::getInstance()->updateModel($update->getMessage()->getChat()->getId(), $model);
                    $session->incrementCollectedSamples();
                }

                if($update->getMessage()->getReplyToMessage() !== null)
                {
                    if($update->getMessage()->getReplyToMessage()->getFrom()->getUsername() == 'PublicServerchatBot')
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
                // Respond only if bot has collected at least 5 samples
                elseif($session->getConfiguration()->getResponseProbability() > 0 && $session->getCollectedSamples() > 5)
                {
                    $rand = rand(0, 100);

                    if($rand <= $session->getConfiguration()->getResponseProbability())
                    {
                        $bot->sendMessage(
                            chat_id: $update->getMessage()->getChat()->getId(),
                            text: $model->generate(),
                            options: [
                                'reply_to_message_id' => $update->getMessage()->getMessageId()
                            ]
                        );
                    }

                    $session->setLastMessage(time());
                }


                SessionManager::getInstance()->updateEntry($session);
            }

            if($update->getMessage()->getChat()->getType() == ChatType::Private)
            {
                $bot->sendMessage(
                    chat_id: $update->getMessage()->getChat()->getId(),
                    text: 'This bot is not meant to be used in private chats. Please add it to a group instead.',
                    options: [
                        'reply_to_message_id' => $update->getMessage()->getMessageId()
                    ]
                );
            }
        }
    }