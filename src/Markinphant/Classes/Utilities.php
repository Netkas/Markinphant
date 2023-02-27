<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Markinphant\Classes;

    use Markinphant\Abstracts\Constants;
    use ncc\Exceptions\InvalidPackageNameException;
    use ncc\Exceptions\InvalidScopeException;
    use ncc\Exceptions\PackageLockException;
    use ncc\Exceptions\PackageNotFoundException;
    use ncc\Runtime;

    class Utilities
    {
        /**
         * Converts a sample to frames for the model with start and stop frames
         *
         * @param string $sample The sample to convert
         * @param bool $as_lower Whether to convert the sample to lowercase
         * @return array
         */
        public static function sampleToFrames(string $sample, bool $as_lower): array
        {
            $frames = [
                Constants::MrkvStart
            ];

            foreach (explode(' ', $as_lower ? strtolower($sample) : $sample) as $word)
            {
                $frames[] = $word;
            }

            $frames[] = Constants::MrkvEnd;
            return $frames;
        }

        /**
         * @return string
         * @throws InvalidPackageNameException
         * @throws InvalidScopeException
         * @throws PackageLockException
         * @throws PackageNotFoundException
         */
        public static function getStoragePath(): string
        {
            return Runtime::getDataPath('com.netkas.markinphant');
        }
    }