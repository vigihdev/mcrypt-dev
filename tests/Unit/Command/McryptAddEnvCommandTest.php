<?php

declare(strict_types=1);

namespace Tests\Unit\Command;

use Tests\TestCase;
use McryptDev\Command\McryptAddEnvCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class McryptAddEnvCommandTest extends TestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();
        
        $application = new Application();
        $application->add(new McryptAddEnvCommand());
        
        $command = $application->find('mcrypt:add:env');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteSuccess(): void
    {
        $envFile = $this->createTestEnvFile([
            'EXISTING_VAR' => 'existing_value'
        ]);

        $this->commandTester->execute([
            'keyPath' => $this->testKeyPath,
            'envFile' => $envFile,
            '--env' => 'NEW_VAR1=value1,NEW_VAR2=value2'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('successfully', $this->commandTester->getDisplay());

        // Verify env file was updated
        $content = file_get_contents($envFile);
        $this->assertStringContainsString('NEW_VAR1=def502', $content);
        $this->assertStringContainsString('NEW_VAR2=def502', $content);

        unlink($envFile);
    }

    public function testExecuteWithInvalidKeyPath(): void
    {
        $envFile = $this->createTestEnvFile();

        $this->commandTester->execute([
            'keyPath' => '/invalid/key/path',
            'envFile' => $envFile,
            '--env' => 'TEST=value'
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
            '--env' => 'TEST=value'
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('tidak tersedia', $this->commandTester->getDisplay());
    }

    public function testExecuteWithoutEnvOption(): void
    {
        $envFile = $this->createTestEnvFile();

        $this->commandTester->execute([
            'keyPath' => $this->testKeyPath,
            'envFile' => $envFile
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());

        unlink($envFile);
    }
}
