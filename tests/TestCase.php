<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use McryptDev\Key;
use McryptDev\Mcrypt;

abstract class TestCase extends PHPUnitTestCase
{
    protected string $testKeyPath;
    protected string $testEnvPath;
    protected Mcrypt $mcrypt;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testKeyPath = __DIR__ . '/Fixtures/test.key';
        $this->testEnvPath = __DIR__ . '/Fixtures/test.env';
        
        // Create test key if not exists
        if (!file_exists($this->testKeyPath)) {
            $key = \Defuse\Crypto\Key::createNewRandomKey();
            file_put_contents($this->testKeyPath, $key->saveToAsciiSafeString());
        }
        
        $key = Key::load($this->testKeyPath);
        $this->mcrypt = new Mcrypt($key);
    }

    protected function createTestEnvFile(array $data = []): string
    {
        $content = [];
        foreach ($data as $key => $value) {
            $content[] = "{$key}={$value}";
        }
        
        $filePath = tempnam(sys_get_temp_dir(), 'test_env_');
        file_put_contents($filePath, implode(PHP_EOL, $content));
        
        return $filePath;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
