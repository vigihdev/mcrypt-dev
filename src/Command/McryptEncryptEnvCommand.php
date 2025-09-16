<?php

namespace McryptDev\Command;

use McryptDev\Key;
use McryptDev\Mcrypt;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * McryptEncryptEnvCommand
 *
 * Console command untuk mengenkripsi environment variables tertentu dalam file .env
 */
final class McryptEncryptEnvCommand extends CommandAbstract
{
    protected static $defaultName = 'mcrypt:encrypt:env';

    /**
     * configure
     *
     * Konfigurasi command dengan arguments dan options
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Encrypt specific environment variables in .env file')
            ->addArgument('keyPath', InputArgument::REQUIRED, 'Path ke file key')
            ->addArgument('envFile', InputArgument::REQUIRED, 'Path ke file .env')
            ->addArgument('cwd', InputArgument::OPTIONAL, 'Curent Work Directory')
            ->addOption('key', null, InputOption::VALUE_REQUIRED, 'Key dari envFile')
        ;
    }

    /**
     * execute
     *
     * Eksekusi command untuk mengenkripsi environment variables tertentu
     *
     * @param InputInterface $input Input interface
     * @param OutputInterface $output Output interface
     * @return int Status code (SUCCESS/FAILURE)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $keyPath = $input->getArgument('keyPath');
        $envFile = $input->getArgument('envFile');
        $cwd = $input->getArgument('cwd');
        $key = $input->getOption('key') ?? [];
        $keyEnv = array_map('trim', explode(',', $key));

        if ($cwd) {
            $envFile = getcwd() . DIRECTORY_SEPARATOR . $envFile;
        }

        try {

            $keyLoad = Key::load($keyPath);
            $mcrypt = new Mcrypt($keyLoad);

            if ($mcrypt->encryptEnv($envFile, $keyEnv)) {
                $output->writeln('<info>Environment variables berhasil dienkripsi</info>');
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
