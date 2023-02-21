<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Markinphant\Classes;

    use Markinphant\Abstracts\Constants;
    use Markinphant\Exceptions\MarkovGenerateException;

    class MarkovChains
    {
        /**
         * @var array
         */
        private $model;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            $this->model = [];
        }

        /**
         * Adds a sample to the model
         *
         * @param string $sample The sample to add
         * @param bool $as_lower Whether to convert the sample to lowercase
         * @return void
         */
        public function addSample(string $sample, bool $as_lower=true): void
        {
            $frames = Utilities::sampleToFrames($sample, $as_lower);

            for ($i = 0; $i < count($frames) - 1; $i++)
            {
                $current_frame = $frames[$i];
                $next_frame = $frames[$i + 1];

                if (!array_key_exists($current_frame, $this->model))
                {
                    $this->model[$current_frame] = [];
                }

                if (!array_key_exists($next_frame, $this->model[$current_frame]))
                {
                    $this->model[$current_frame][$next_frame] = 0;
                }

                $this->model[$current_frame][$next_frame]++;
            }
        }

        /**
         * Add multiple samples to the model at once
         *
         * @param array $samples
         * @param bool $as_lower
         * @return void
         */
        public function addSamples(array $samples, bool $as_lower=true): void
        {
            foreach ($samples as $sample)
            {
                $this->addSample($sample, $as_lower);
            }
        }

        /**
         * Generates a sentence from the model using Markov chains and returns it
         *
         * @return string
         * @throws MarkovGenerateException
         */
        public function generate(): string
        {
            $generated = [];
            $current_frame = Constants::MrkvStart;

            while ($current_frame != Constants::MrkvEnd)
            {
                $next_frame = $this->getNextFrame($current_frame);
                if ($next_frame == Constants::MrkvEnd)
                {
                    break;
                }
                $generated[] = $next_frame;
                $current_frame = $next_frame;
            }

            return implode(' ', $generated);
        }

        /**
         * Returns the next frame in the chain
         *
         * @param string $current_frame
         * @return string
         * @throws MarkovGenerateException
         */
        private function getNextFrame(string $current_frame): string
        {
            if (!array_key_exists($current_frame, $this->model))
            {
                throw new MarkovGenerateException('No model for frame: ' . $current_frame);
            }

            $next_frames = $this->model[$current_frame];
            $next_frame = null;
            $total = 0;

            /** @noinspection PhpUnusedLocalVariableInspection */
            foreach ($next_frames as $frame => $weight)
            {
                $total += $weight;
            }

            $rand = rand(0, $total);
            $current = 0;

            foreach ($next_frames as $frame => $weight)
            {
                $current += $weight;

                if ($current >= $rand)
                {
                    $next_frame = $frame;
                    break;
                }
            }

            return (string)$next_frame;
        }

        /**
         * Clears the model
         *
         * @return void
         */
        public function clear(): void
        {
            $this->model = [];
        }

        /**
         * Exports the model
         *
         * @return array
         * @noinspection PhpUnused
         */
        public function export(): array
        {
            return $this->model;
        }

        /**
         * Returns a new Generator instance from the given data
         *
         * @param array $data
         * @return MarkovChains
         */
        public static function import(array $data): MarkovChains
        {
            $generator = new MarkovChains();
            $generator->model = $data;
            return $generator;
        }

        /**
         * Returns the model
         *
         * @return array
         * @noinspection PhpUnused
         */
        public function getModel(): array
        {
            return $this->model;
        }

    }