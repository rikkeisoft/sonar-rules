<?php

namespace App\Commands;

use Lemon\Cli\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListRulesCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'rules:list';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName($this->name)
            ->setDefinition($this->createDefinition())
            ->setDescription('Make documents for Sonar rules')
            ->setHelp('TODO');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|int null or 0 if everything went fine, or an error code
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \GuzzleHttp\Client $client */
        $client = $this->getContainer()['http-client'];
        $baseUri = $input->getArgument('base-uri');
        $query = [
            'format' => 'json',
            'f' => implode(',', ['repo', 'name', 'htmlDesc', 'htmlNote', 'status']),
            'ps' => 500,
        ];

        if ($input->hasOption('languages')) {
            $query['languages'] = $input->getOption('languages');
        }

        $user = $input->getOption('user');
        $password = $input->getOption('password');

        $response = $client->request('GET', $baseUri . '/api/rules/search?' . http_build_query($query), [
            'auth' => [$user, $password],
        ]);

        if ($response->getStatusCode() != 200) {
            if ($output->isDebug()) {
                $output->writeln("<error>Response status code: {$response->getStatusCode()}</error>");
            }

            return 1;
        }

        if (strpos((string) $response->getHeaderLine('Content-Type'), 'json') === false) {
            if ($output->isDebug()) {
                $output->writeln("<error>Response content type: {$response->getHeaderLine('Content-Type')}</error>");
            }

            return 2;
        }

        $data = json_decode($response->getBody(), true);

        if (null === $data && json_last_error()) {
            if ($output->isDebug()) {
                $output->writeln("<error>Parse error: {json_last_error_msg()}</error>");
            }

            return 3;
        }

        /** @var \Twig_Environment $renderer */
        $renderer = $this->getContainer()['renderer'];
        $doc = $renderer->render('list_rules.html.twig', compact('data'));

        $file = $input->getOption('outfile');

        if (!file_exists(dirname($file))) {
            mkdir(dirname($file));
        }

        if (!file_put_contents($file, $doc)) {
            if ($output->isDebug()) {
                $output->writeln("<error>Write to file '{$file}' error</error>");
            }

            return 4;
        }

        if ($output->isDebug()) {
            $output->writeln("<info>Generate success</info>");
        }

        return 0;
    }

    /**
     * @return InputDefinition
     */
    private function createDefinition()
    {
        return new InputDefinition([
            new InputOption('languages', 'l', InputOption::VALUE_OPTIONAL, 'Filter by languages'),
            new InputOption('outfile', 'o', InputOption::VALUE_REQUIRED, 'Save file', getcwd() . '/build/file.html'),
            new InputOption('user', 'u', InputOption::VALUE_REQUIRED, 'Username', 'user'),
            new InputOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password', 'password'),
            new InputArgument('base-uri', InputArgument::REQUIRED, 'BaseUrl of Sonar service'),
        ]);
    }
}
