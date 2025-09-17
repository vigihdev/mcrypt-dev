<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use McryptDev\Key;
use InvalidArgumentException;

class KeyTest extends TestCase
{
    public function testLoadExistingKey(): void
    {
        $key = Key::load($this->testKeyPath);
        
        $this->assertInstanceOf(\Defuse\Crypto\Key::class, $key);
    }

    public function testLoadKeyFileNotExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('tidak tersedia');
        
        Key::load('/non/existent/key/file');
    }

    public function testLoadKeyWithTildeExpansion(): void
    {
        // Skip if no HOME environment variable
        if (!getenv('HOME') && !isset($_SERVER['HOME'])) {
            $this->markTestSkipped('HOME environment variable not set');
        }

        // Create a temporary key in home directory
        $home = getenv('HOME') ?: $_SERVER['HOME'];
        $tempKeyPath = $home . '/.temp_test_key';
        
        // Copy our test key
        copy($this->testKeyPath, $tempKeyPath);
        
        try {
            $key = Key::load('~/.temp_test_key');
            $this->assertInstanceOf(\Defuse\Crypto\Key::class, $key);
        } finally {
            // Cleanup
            if (file_exists($tempKeyPath)) {
                unlink($tempKeyPath);
            }
        }
    }

    public function testLoadKeyWorksWithEncryption(): void
    {
        $key = Key::load($this->testKeyPath);
        
        $plaintext = 'test encryption';
        $encrypted = \Defuse\Crypto\Crypto::encrypt($plaintext, $key);
        $decrypted = \Defuse\Crypto\Crypto::decrypt($encrypted, $key);
        
        $this->assertEquals($plaintext, $decrypted);
    }
}
