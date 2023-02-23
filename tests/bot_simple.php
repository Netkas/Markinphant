<?php

    require 'ncc';
    import('com.netkas.markinphant');
    import('net.nosial.tgbotlib');

    $generator = new \Markinphant\Classes\MarkovChains();

    if(file_exists('model.bin'))
    {
        $generator = \Markinphant\Classes\MarkovChains::import(json_decode(file_get_contents('model.bin'), true));
    }

    $bot = new \TgBotLib\Bot('<bot token>');
    $last_save = 0;

    while(true)
    {
        try
        {
            foreach($bot->getUpdates() as $update)
            {
                if($update->getMessage() !== null && $update->getMessage()->getText() !== null)
                {
                    $generator->addSample($update->getMessage()->getText());

                    $output = $generator->generate();
                    $msg = null;

                    // Handle mentions of @MarkinphantBot
                    if(strpos($update->getMessage()->getText(), '@MarkinphantBot') !== false)
                    {
                        $msg = $bot->sendMessage($update->getMessage()->getChat()->getId(), $output,[
                                'reply_to_message_id' => $update->getMessage()->getMessageId()]
                        );
                    }
                    // Handle replies to @MarkinphantBot
                    elseif($update->getMessage()->getReplyToMessage() !== null && $update->getMessage()->getReplyToMessage()->getFrom()->getUsername() === 'MarkinphantBot')
                    {
                        $msg = $bot->sendMessage($update->getMessage()->getChat()->getId(), $output,[
                                'reply_to_message_id' => $update->getMessage()->getMessageId()]
                        );
                    }
                    // Handle /think command
                    elseif($update->getMessage()->getText() === '/think')
                    {
                        $msg = $bot->sendMessage($update->getMessage()->getChat()->getId(), $output,[
                                'reply_to_message_id' => $update->getMessage()->getMessageId()]
                        );
                    }
                    // 10% to reply with a generated sentence
                    elseif(rand(0, 100) < 5)
                    {
                        $msg = $bot->sendMessage($update->getMessage()->getChat()->getId(), $output,[
                                'reply_to_message_id' => $update->getMessage()->getMessageId()]
                        );
                    }

                    if($msg !== null)
                    {
                        if (($msg->getText() == '/think') || ($msg->getText() == '/think@MarkinphantBot')) {
                            $bot->sendMessage($update->getMessage()->getChat()->getId(), $generator->generate(), [
                                    'reply_to_message_id' => $msg->getMessageId()]
                            );
                        }

                        if ($msg->getText() == 'luner') {
                            $bot->sendMessage($update->getMessage()->getChat()->getId(), 'HAHAHHAHAHAHA luner', [
                                    'reply_to_message_id' => $update->getMessage()->getMessageId()]
                            );
                        }

                        if ($msg->getText() == '/q') {
                            $response = [
                                'i did that because ur a clown',
                                'clown is a word that describes you',
                                'you are a clown',
                                $generator->generate()
                            ];

                            $bot->sendMessage($update->getMessage()->getChat()->getId(), $response[array_rand($response)], [
                                    'reply_to_message_id' => $update->getMessage()->getMessageId()]
                            );
                        }
                    }
                }

                if($update->getMessage() !== null && $update->getMessage()->getCaption() !== null)
                {
                    $generator->addSample(explode(PHP_EOL, $update->getMessage()->getCaption())[0]);
                }
            }
        }
        catch(Exception $e)
        {
            var_dump($e);
        }

        var_dump(time() - $last_save);
        if(time() - $last_save > 60)
        {
            $last_save = time();
            file_put_contents('model.bin', json_encode($generator->export()));
        }
    }
