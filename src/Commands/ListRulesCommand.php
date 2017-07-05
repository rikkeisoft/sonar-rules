<?php

namespace App\Commands;

use Lemon\Cli\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ListRulesCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'rules:list';

    /**
     * @var string
     */
    protected $apiEndpoint = '/api/rules/search';

    /**
     * @var string
     */
    protected $password = '';

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
     * Interacts with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('password')) {
            $question = new Question('Please type password for user ' . $input->getOption('user') . ' ?');
            $question->setHidden(true);
            $question->setHiddenFallback(false);

            QuestionHelper::disableStty();
            $helper = $this->getHelper('question');

            $this->password = $helper->ask($input, $output, $question);
        }
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
        $language = $input->getArgument('language');
        $baseUri = $input->getOption('uri');

        $query = [
            'format' => 'json',
            'f' => implode(',', ['repo', 'name', 'htmlDesc', 'htmlNote', 'status', 'tags']),
            'ps' => 500,
            's' => 'key',
            'asc' => 'true',
            'tags' => 'rank,rank1,rank2,rank3,rank4,rank5',
            'languages' => $language,
        ];

        $user = $input->getOption('user');
        $password = $this->password;

        $response = $client->get($baseUri . $this->apiEndpoint . '?' . http_build_query($query), [
            'auth' => [$user, $password],
        ]);

        if ($response->getStatusCode() != 200) {
            if ($output->isDebug()) {
                $output->writeln("<error>Response status code: {$response->getStatusCode()}</error>");
            }

            return 1;
        }

        if (strpos((string)$response->getHeaderLine('Content-Type'), 'json') === false) {
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

        $format = strtolower($input->getOption('format')) === 'csv' ? 'csv' : 'html';
        $file = str_replace(['{lang}', '{format}'], [$language, $format], $input->getOption('outfile'));
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file));
        }

        if (strtolower($input->getOption('format')) === 'csv') {
            $doc = $this->createCsv($data);
        } else {
            $doc = $this->createHtml($data);
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

            new InputOption('outfile', 'o', InputOption::VALUE_REQUIRED, 'Save file', getcwd() . '/build/{lang}.html'),
            new InputOption('format', 'f', InputOption::VALUE_REQUIRED, 'Format', 'html'),
            new InputOption('user', 'u', InputOption::VALUE_REQUIRED, 'Username', 'admin'),
            new InputOption('password', 'p', InputOption::VALUE_NONE, 'Password'),
            new InputOption('uri', null, InputOption::VALUE_REQUIRED, 'BaseUrl of Sonar service', 'http://sonar.rikkei.org'),
            new InputArgument('language', InputArgument::REQUIRED, 'Filter by language'),
        ]);
    }

    /**
     * @param array $data
     * @return string
     */
    private function createHtml($data)
    {
        /** @var \Twig_Environment $renderer */
        $renderer = $this->getContainer()['renderer'];
        return $renderer->render('list_rules.html.twig', compact('data'));
    }

    /**
     * @param array $data
     * @return string
     */
    private function createCsv($data)
    {
        ob_start();

        $resource = fopen('php://output', 'w');
        fputcsv($resource, array_keys(reset($data['rules'])));

        foreach ($data['rules'] as $row) {
            foreach ($row as $index => $field) {
                if (is_array($field)) {
                    $row[$index] = implode(',',$field);
                }
            }
            fputcsv($resource, $row);
        }

        fclose($resource);

        return ob_get_clean();
    }
}
