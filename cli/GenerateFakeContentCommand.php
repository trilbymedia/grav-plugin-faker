<?php

/**
 * @package    Grav\Plugin\Login
 *
 * @copyright  Copyright (C) 2014 - 2017 RocketTheme, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Plugin\Console;

use Faker\Factory;
use Grav\Common\Filesystem\Folder;
use Grav\Common\Grav;
use Grav\Common\Inflector;
use Grav\Console\ConsoleCommand;
use Grav\Plugin\Faker\FakerMarkdownProvider;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
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
    protected $faker;
    protected $total_pages;

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this
            ->setName('generate')
            ->addOption(
                'nested-levels',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of nested levels - default 1'
            )
            ->addOption(
                'visible-levels',
                null,
                InputOption::VALUE_OPTIONAL,
                'Visible Levels - default is 1'
            )
            ->addOption(
                'items-per-level',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of items per level - default 100'
            )
            ->addOption(
                'max-items',
                null,
                InputOption::VALUE_OPTIONAL,
                'Maximum number of items - default 10000'
            )
            ->addOption(
                'min-parts',
                null,
                InputOption::VALUE_OPTIONAL,
                'Minimum number of parts - default 5 parts'
            )
            ->addOption(
                'max-parts',
                null,
                InputOption::VALUE_OPTIONAL,
                'Maximum number of parts - default 20 parts'
            )
            ->addOption(
                'min-images',
                null,
                InputOption::VALUE_OPTIONAL,
                'Minimum number of images - default 0'
            )
            ->addOption(
                'max-images',
                null,
                InputOption::VALUE_OPTIONAL,
                'Maximum number of images - default 3'
            )
            ->addOption(
                'location',
                null,
                InputOption::VALUE_OPTIONAL,
                'Location - default is `page://'
            )
            ->addOption(
                'template',
                null,
                InputOption::VALUE_OPTIONAL,
                'Template - default is `default'
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
            'max_items'         => 1000,
            'min_parts'         => 5,
            'max_parts'         => 20,
            'min_images'        => 0,
            'max_images'        => 3,
            'location'          => 'page://',
            'template'          => 'default',
        ];

        $this->helper = $this->getHelper('question');

        // Ask questions if option not provided
        foreach (array_keys($this->data) as $key) {
            $options_key = Inflector::hyphenize($key);
            $this->options[$key] = $this->input->getOption($options_key);
            $this->askQuestion($key);
        }

        $location = $grav['locator']->findResource($this->data['location']);
        $this->total_pages = 0;


        if ($location) {
            $io->warning(sprintf('Creating content in %s', $location));

            $this->faker = Factory::create();
            $this->faker->addProvider(new FakerMarkdownProvider($this->faker));

            $this->createContent($location);

            $io->success($this->total_pages . ' pages created');

        } else {
            $io->error(sprintf('Sorry, location: %s does not exists', $location));
        }
    }

    protected function createContent($location, $level = 1) {


        for ($i = 0; $i < $this->data['items_per_level']; $i++) {

            if ($this->total_pages >= $this->data['max_items']) {
                break;
            }

            $item_number = $i;
            $title = Inflector::titleize($this->faker->words(4, true));
            $slug = Inflector::hyphenize($title);
            $folder_name = $slug;

            $template = $this->options['template'];

            // Handle visibility
            if ($this->data['visible_levels'] > 0 &&  $level <= $this->data['visible_levels']) {
                $folder_name = str_pad($i+1, 2, '0', STR_PAD_LEFT) . '.' . $folder_name;
            }

            $folder = $location . '/' . $folder_name;

            Folder::create($folder);

            $images = [];
            $image_count = $this->faker->numberBetween($this->data['min_images'], $this->data['max_images']);

            for ($j = 0; $j < $image_count; $j++) {
                $images[] = $this->createImage($folder);
            }

            $content = $this->faker->markdown($this->data, $images);
            $markdown = "---\ntitle: $title\n---\n\n" . $content;

            file_put_contents($folder . '/' . $template . '.md', $markdown);

            $this->output->writeln('<white>' . ($this->total_pages + 1) . '</white> ' . $folder . ' <green>✔</green>');
            $this->total_pages++;

            // check if children need to be created
            if ($this->data['nested_levels'] > 0 &&  $level < $this->data['nested_levels']) {
                $this->createContent($folder, $level + 1);
            }
        }
    }

    protected function createImage($path)
    {
        $filename = Inflector::hyphenize($this->faker->words(2, true)) . '.jpg';
        $path = $path . '/' . $filename;
        $source = __DIR__ . '/../media/sample.jpg';

        if (file_exists($source)) {
            copy($source, $path);
            return $filename;
        }

        return 'filenotfound.jpg';
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
