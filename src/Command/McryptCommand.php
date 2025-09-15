<?php


namespace McryptDev\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


final class McryptCommand extends Command
{

    protected static $defaultName = 'mcrypt';

    protected function configure(): void
    {
        $this
            ->addArgument('key', InputArgument::REQUIRED, 'Description of the key argument')
            ->addArgument('add')
            ->addArgument('encrypt')
            ->addOption('key', null, InputOption::VALUE_REQUIRED, 'description env')
            ->addOption('env', null, InputOption::VALUE_OPTIONAL, 'description env')
            ->setDescription('Mcrypt');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        $key = $input->getArgument('key');
        if (empty(trim($key) || !$key)) {
            $output->writeln('<error>Key cannot be empty!</error>');
            return Command::FAILURE;
        }

        $add = $input->getArgument('add');
        $env = $input->getOption('env');
        if ($env) {
            $env = array_map(fn($v) => trim($v), explode(',', $env, 2));
            $envs = [];
            array_map(function ($value) use (&$envs) {
                $values = explode('=', $value, 2);
                $envs[$values[0]] = $values[1];
            }, $env);
        }

        $output->writeln('Hello Word');

        return parent::SUCCESS;
    }
}
