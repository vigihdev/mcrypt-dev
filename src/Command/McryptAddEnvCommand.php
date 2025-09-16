<?php

declare(strict_types=1);

namespace McryptDev\Command;

use McryptDev\Key;
use McryptDev\Mcrypt;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * McryptAddEnvCommand
 *
 * Console command untuk menambahkan environment variables baru ke file .env dengan enkripsi otomatis
 */
final class McryptAddEnvCommand extends CommandAbstract
{
    protected static $defaultName = 'mcrypt:add:env';


    /**
     * configure
     *
     * Konfigurasi command dengan arguments dan options
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Add new environment variables to .env file with automatic encryption')
            ->addArgument('keyPath', InputArgument::REQUIRED, 'Path ke file key')
            ->addArgument('envFile', InputArgument::REQUIRED, 'Path ke file env')
            ->addOption('env', null, InputOption::VALUE_REQUIRED, 'List env yang akan dienkripsi, pisahkan dengan koma')
        ;
    }

    /**
     * execute
     *
     * Eksekusi command untuk menambahkan environment variables dengan enkripsi otomatis
     *
     * @param InputInterface $input Input interface
     * @param OutputInterface $output Output interface
     * @return int Status code (SUCCESS/FAILURE)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $keyPath = $input->getArgument('keyPath');
        $envFile = $input->getArgument('envFile');
        $env = $input->getOption('env') ? array_map('trim', explode(',', $input->getOption('env'))) : [];

        $envs = [];
        array_map(function ($value) use (&$envs) {
            $values = explode('=', $value, 2);
            $envs[$values[0]] = $values[1];
        }, $env);


        try {
            $key = Key::load($keyPath);
            $mcrypt = new Mcrypt($key);

            if ($mcrypt->addEnv($envFile, $envs)) {
                $output->writeln('<info>Environment variables encrypted successfully</info>');
            } else {
                $output->writeln('<error>Failed to encrypt environment variables</error>');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
