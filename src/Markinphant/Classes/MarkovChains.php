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
         * @var int
         */
        private $max_size;

        /**
         * @var bool
         */
        private $remove_less_frequent;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            $this->model = [];
            $this->max_size = 0;
            $this->remove_less_frequent = false;
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

            if($this->max_size > 0)
            {
                $this->resizeModel();
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
         * Resizes the model and removes entries according to $this->remove_less_frequent
         * If $this->remove_less_frequent is true, the least frequently used entries are removed
         * If $this->remove_less_frequent is false, the most frequently used entries are removed
         *
         * @return void
         */
        private function resizeModel(): void
        {
            if ($this->max_size == 0)
            {
                return;
            }

            $size = count($this->model);

            if($size < $this->max_size)
            {
                return;
            }

            $entries = [];
            foreach ($this->model as $current_frame => $next_frames)
            {
                foreach ($next_frames as $next_frame => $weight)
                {
                    $entries[] = [$current_frame, $next_frame, $weight];
                }
            }

            if($this->remove_less_frequent)
            {
                usort($entries, function($a, $b)
                {
                    return $b[2] <=> $a[2];
                });
            }
            else
            {
                usort($entries, function($a, $b)
                {
                    return $a[2] <=> $b[2];
                });
            }

            while($size > $this->max_size)
            {
                $entry = array_shift($entries);
                $current_frame = $entry[0];
                $next_frame = $entry[1];
                $weight = $entry[2];

                if($weight == 0)
                    continue;

                unset($this->model[$current_frame][$next_frame]);
                $size--;

                if (count($this->model[$current_frame]) == 0)
                {
                    unset($this->model[$current_frame]);
                }

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
            return [
                'model' => $this->model,
                'max_size' => $this->max_size,
                'remove_less_frequent' => $this->remove_less_frequent
            ];
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
            $generator->model = $data['model'] ?? [];
            $generator->max_size = $data['max_size'] ?? 0;
            $generator->remove_less_frequent = $data['remove_less_frequent'] ?? false;
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

        /**
         * Returns the maximum size of the model (0 = no limit) default: 0
         *
         * @return int
         * @noinspection PhpUnused
         */
        public function getMaxSize(): int
        {
            return $this->max_size;
        }

        /**
         * Sets the maximum size of the model (0 = no limit) default: 0
         *
         * @param int $max_size
         * @noinspection PhpUnused
         */
        public function setMaxSize(int $max_size): void
        {
            $this->max_size = $max_size;
        }

        /**
         * Tells whether to remove the least or most frequently used entries when the model is full
         * If true, the least frequently used entries are removed when the model is full
         * If false, the most frequently used entries are removed when the model is full (default)
         *
         * @return bool
         * @noinspection PhpUnused
         */
        public function isRemoveLessFrequent(): bool
        {
            return $this->remove_less_frequent;
        }

        /**
         * Set whether to remove the least or most frequently used entries when the model is full
         * If true, the least frequently used entries are removed when the model is full
         * If false, the most frequently used entries are removed when the model is full (default)
         *
         * @param bool $remove_less_frequent
         * @noinspection PhpUnused
         */
        public function setRemoveLessFrequent(bool $remove_less_frequent): void
        {
            $this->remove_less_frequent = $remove_less_frequent;
        }

    }