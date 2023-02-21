<?php

    require 'ncc';
    import('com.netkas.markinphant');

    $generator = new \Markinphant\Classes\MarkovChains();
    $data = file_get_contents('data.txt');
    $generator->addSamples(explode(PHP_EOL, $data));
    var_dump($generator);

    // Generate 10 sentences
    for ($i = 0; $i < 10; $i++)
    {
        echo $generator->generate() . PHP_EOL;
    }