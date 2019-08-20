<?php

/**
 * @package    Grav\Plugin\Login
 *
 * @copyright  Copyright (C) 2014 - 2017 RocketTheme, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Plugin\Console;

use Grav\Common\Grav;
use Grav\Common\Inflector;
use Grav\Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class CleanCommand
 *
 * @package Grav\Console\Cli
 */
class GenerateFakeContentCommand extends ConsoleCommand
{
    protected $options;
    protected $data;
    protected $helper;

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this
            ->setName('generate')
            ->addOption(
                'nested-levels',
                'nl',
                InputOption::VALUE_OPTIONAL,
                'Number of nested levels - default 1'
            )
            ->addOption(
                'visible-levels',
                'vl',
                InputOption::VALUE_OPTIONAL,
                'Visible Levels - default is 1'
            )
            ->addOption(
                'items-per-level',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Number of items per level - default 100'
            )
            ->addOption(
                'min-words',
                'min-wrds',
                InputOption::VALUE_OPTIONAL,
                'Minimum length of content in words - default 100 words'
            )
            ->addOption(
                'max-words',
                'max-wrds',
                InputOption::VALUE_OPTIONAL,
                'Maximum length of content in words - default 1000 words'
            )
            ->addOption(
                'min-images',
                'min-img',
                InputOption::VALUE_OPTIONAL,
                'Minimum number of images - default 0'
            )
            ->addOption(
                'max-images',
                'max-img',
                InputOption::VALUE_OPTIONAL,
                'Maximum number of images - default 3'
            )
            ->addOption(
                'location',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Location - default is `user/pages/'
            )

            ->setDescription('Generates fake content')
            ->setHelp('The <info>lookup-user</info> finds a user based on some data query.')
        ;
    }

    /**
     * @return int|null|void
     */
    protected function serve()
    {
        include __DIR__ . '/../vendor/autoload.php';

        $io = new SymfonyStyle($this->input, $this->output);

        $grav = Grav::instance();
        $grav->setup();

        $io->title('Generate Faker Content');

        $this->data = [
            'nested_levels'     => 1,
            'visible_levels'    => 1,
            'items_per_level'   => 100,
            'min_words'         => 100,
            'max_words'         => 1000,
            'min_images'        => 0,
            'max_images'        => 3,
            'location'          => 'page://',
        ];

        $this->helper = $this->getHelper('question');

        // Ask questions if option not provided
        foreach (array_keys($this->data) as $key) {
            $options_key = Inflector::hyphenize($key);
            $this->options[$key] = $this->input->getOption($options_key);
            $this->askQuestion($key);
        }

        $location = $grav['locator']->findResource($this->data['location']);


        if ($location) {
            $io->comment(sprintf('Creating content in %s', $location));


        } else {
            $io->error(sprintf('Sorry, location: %s does not exists', $location));
        }
    }

    protected function createContent($dir) {

    }


    protected function askQuestion($key)
    {
        $type = gettype($this->data[$key]);


        if (!$this->options[$key]) {
            // Get username and validate
            $question = new Question("Number of <yellow>$key</yellow> <green>[{$this->data[$key]}]</green>: ", $this->data[$key]);
            if (in_array($type, ['integer','double'])) {
                $method = 'validate' . ucFirst($type);
            } else {
                $method = 'normalize' . ucFirst($type);
            }

            $this->$method($question);
            $this->data[$key] = $this->helper->ask($this->input, $this->output, $question);
        } else {
            $this->data[$key] = $this->options[$key];
        }
    }

    /**
     * @param $question  Question
     */
    protected function validateInteger($question)
    {
        $question->setValidator(function ($answer) {
            if (!is_numeric($answer)) {
                throw new \RuntimeException(
                    'This value must be an integer'
                );
            }

            return intval($answer);
        });
    }

    /**
     * @param $question  Question
     */
    protected function normalizeString($question)
    {
        $question->setNormalizer(function ($value) {
            // $value can be null here
            return $value ? trim($value) : '';
        });
    }

    /**
     * @param $question  Question
     */
    protected function validateString($question)
    {
        $question->setValidator(function ($answer) {
            return $answer;
        });
    }
}
