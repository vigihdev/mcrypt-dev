<?php

namespace McryptDev\Command;

use McryptDev\Key;
use McryptDev\Mcrypt;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class McryptEncryptEnvCommand extends Command
{
    protected static $defaultName = 'mcrypt:encrypt:env';

    protected function configure(): void
    {
        $this
            ->setDescription('Encrypt environment variables')
            ->addArgument('keyPath', InputArgument::REQUIRED, 'Path ke file key')
            ->addArgument('envFile', InputArgument::REQUIRED, 'Path ke file .env')
            ->addOption('keys', null, InputOption::VALUE_REQUIRED, 'Keys yang akan dienkripsi, pisahkan dengan koma');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keyPath = $input->getArgument('keyPath');
        $envFile = $input->getArgument('envFile');
        $keys = $input->getOption('keys');

        if (!$keys) {
            $output->writeln('<error>Option --keys harus diisi</error>');
            return Command::FAILURE;
        }

        $keysArray = array_map('trim', explode(',', $keys));

        $key = Key::load($keyPath);
        $mcrypt = new Mcrypt($key);

        if ($mcrypt->encryptEnv($envFile, $keysArray)) {
            $output->writeln('<info>Environment variables berhasil dienkripsi</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<error>Gagal mengenkripsi environment variables</error>');
        return Command::FAILURE;
    }
}
