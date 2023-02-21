<?php

    namespace Markinphant\Exceptions;

    use Exception;
    use Throwable;

    class MarkovGenerateException extends Exception
    {
        /**
         * @param string $message
         * @param int $code
         * @param Throwable|null $previous
         */
        public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }