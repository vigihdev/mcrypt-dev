<?php

declare(strict_types=1);

namespace Tests\Unit\Command;

use Tests\TestCase;
use McryptDev\Command\McryptEncryptEnvCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class McryptEncryptEnvCommandTest extends TestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();
        
        $application = new Application();
        $application->add(new McryptEncryptEnvCommand());
        
        $command = $application->find('mcrypt:encrypt:env');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteSuccess(): void
    {
        $envFile = $this->createTestEnvFile([
            'SECRET_KEY' => 'secret_value',
            'PUBLIC_VAR' => 'public_value',
            'ANOTHER_SECRET' => 'another_secret'
        ]);

        $this->commandTester->execute([
            'keyPath' => $this->testKeyPath,
            'envFile' => $envFile,
            '--key' => 'SECRET_KEY,ANOTHER_SECRET'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('berhasil dienkripsi', $this->commandTester->getDisplay());

        // Verify encryption
        $content = file_get_contents($envFile);
        $this->assertStringContainsString('SECRET_KEY=def502', $content);
        $this->assertStringContainsString('ANOTHER_SECRET=def502', $content);
        $this->assertStringContainsString('PUBLIC_VAR=public_value', $content); // Should remain plain

        unlink($envFile);
    }

    public function testExecuteWithCwdArgument(): void
    {
        // Create temp env in current directory for relative path testing
        $tempEnv = tempnam(getcwd(), 'test_env_');
        $relativePath = basename($tempEnv);
        
        file_put_contents($tempEnv, "TEST_VAR=test_value\n");

        $this->commandTester->execute([
            'keyPath' => $this->testKeyPath,
            'envFile' => $relativePath,
            'cwd' => getcwd(),
            '--key' => 'TEST_VAR'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());

        unlink($tempEnv);
    }

    public function testExecuteWithInvalidKeyPath(): void
    {
        $envFile = $this->createTestEnvFile(['TEST' => 'value']);

        $this->commandTester->execute([
            'keyPath' => '/invalid/key',
            'envFile' => $envFile,
            '--key' => 'TEST'
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('tidak tersedia', $this->commandTester->getDisplay());

        unlink($envFile);
    }

    public function testExecuteWithInvalidEnvFile(): void
    {
        $this->commandTester->execute([
            'keyPath' => $this->testKeyPath,
            'envFile' => '/invalid/env/file',
            '--key' => 'TEST'
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }
}
