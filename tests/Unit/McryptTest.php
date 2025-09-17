<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use McryptDev\Mcrypt;
use McryptDev\Key;
use InvalidArgumentException;

class McryptTest extends TestCase
{
    public function testEncryptDecrypt(): void
    {
        $plaintext = 'Hello World!';
        
        $encrypted = $this->mcrypt->encrypt($plaintext);
        $decrypted = $this->mcrypt->decrypt($encrypted);
        
        $this->assertNotEquals($plaintext, $encrypted);
        $this->assertEquals($plaintext, $decrypted);
    }

    public function testEncryptedStringFormat(): void
    {
        $plaintext = 'test data';
        $encrypted = $this->mcrypt->encrypt($plaintext);
        
        // Should start with def502
        $this->assertStringStartsWith('def502', $encrypted);
        
        // Should be hex format
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/i', $encrypted);
        
        // Should be at least 50 characters
        $this->assertGreaterThan(50, strlen($encrypted));
    }

    public function testIsDefuseEncrypted(): void
    {
        $plaintext = 'not encrypted';
        $encrypted = $this->mcrypt->encrypt('test data');
        
        $this->assertFalse($this->mcrypt->isDefuseEncrypted($plaintext));
        $this->assertTrue($this->mcrypt->isDefuseEncrypted($encrypted));
        
        // Test edge cases
        $this->assertFalse($this->mcrypt->isDefuseEncrypted(''));
        $this->assertFalse($this->mcrypt->isDefuseEncrypted('short'));
        $this->assertFalse($this->mcrypt->isDefuseEncrypted('def502')); // Too short
    }

    public function testAddEnvToNewFile(): void
    {
        $envFile = $this->createTestEnvFile([
            'EXISTING_VAR' => 'existing_value',
            'PLAIN_VAR' => 'plain_value'
        ]);

        $newEnvs = [
            'NEW_VAR1' => 'new_value1',
            'NEW_VAR2' => 'new_value2'
        ];

        $result = $this->mcrypt->addEnv($envFile, $newEnvs);
        $this->assertTrue($result);

        // Verify file content
        $content = file_get_contents($envFile);
        $this->assertStringContainsString('EXISTING_VAR=existing_value', $content);
        $this->assertStringContainsString('NEW_VAR1=def502', $content); // Should be encrypted
        $this->assertStringContainsString('NEW_VAR2=def502', $content); // Should be encrypted

        unlink($envFile);
    }

    public function testAddEnvUpdateExisting(): void
    {
        $envFile = $this->createTestEnvFile([
            'DB_HOST' => 'localhost',
            'DB_PORT' => '3306'
        ]);

        $newEnvs = [
            'DB_HOST' => 'new_host', // Update existing
            'DB_USER' => 'new_user'  // Add new
        ];

        $result = $this->mcrypt->addEnv($envFile, $newEnvs);
        $this->assertTrue($result);

        // Parse the file
        $content = file_get_contents($envFile);
        $lines = explode(PHP_EOL, $content);
        
        $envData = [];
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $envData[$key] = $value;
            }
        }

        // DB_HOST should be updated and encrypted
        $this->assertArrayHasKey('DB_HOST', $envData);
        $this->assertTrue($this->mcrypt->isDefuseEncrypted($envData['DB_HOST']));
        
        // DB_PORT should remain unchanged
        $this->assertEquals('3306', $envData['DB_PORT']);
        
        // DB_USER should be added and encrypted
        $this->assertArrayHasKey('DB_USER', $envData);
        $this->assertTrue($this->mcrypt->isDefuseEncrypted($envData['DB_USER']));

        unlink($envFile);
    }

    public function testAddEnvFileNotExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('tidak tersedia');
        
        $this->mcrypt->addEnv('/non/existent/file.env', ['TEST' => 'value']);
    }

    public function testAddEnvEmptyData(): void
    {
        $envFile = $this->createTestEnvFile(['EXISTING' => 'value']);
        
        $result = $this->mcrypt->addEnv($envFile, []);
        $this->assertFalse($result);
        
        unlink($envFile);
    }

    public function testEncryptEnv(): void
    {
        $envFile = $this->createTestEnvFile([
            'ENCRYPT_ME' => 'secret_value',
            'LEAVE_ME' => 'plain_value',
            'ENCRYPT_ME_TOO' => 'another_secret'
        ]);

        $keysToEncrypt = ['ENCRYPT_ME', 'ENCRYPT_ME_TOO'];
        
        $result = $this->mcrypt->encryptEnv($envFile, $keysToEncrypt);
        $this->assertTrue($result);

        // Parse updated file
        $content = file_get_contents($envFile);
        $lines = explode(PHP_EOL, $content);
        
        $envData = [];
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $envData[$key] = $value;
            }
        }

        // These should be encrypted
        $this->assertTrue($this->mcrypt->isDefuseEncrypted($envData['ENCRYPT_ME']));
        $this->assertTrue($this->mcrypt->isDefuseEncrypted($envData['ENCRYPT_ME_TOO']));
        
        // This should remain plain
        $this->assertEquals('plain_value', $envData['LEAVE_ME']);

        unlink($envFile);
    }

    public function testEncryptEnvAlreadyEncrypted(): void
    {
        $alreadyEncrypted = $this->mcrypt->encrypt('already_secret');
        
        $envFile = $this->createTestEnvFile([
            'ALREADY_ENCRYPTED' => $alreadyEncrypted,
            'PLAIN_VALUE' => 'plain'
        ]);

        $result = $this->mcrypt->encryptEnv($envFile, ['ALREADY_ENCRYPTED']);
        $this->assertTrue($result);

        // Verify it wasn't double-encrypted
        $content = file_get_contents($envFile);
        $this->assertStringContainsString($alreadyEncrypted, $content);

        unlink($envFile);
    }

    public function testEncryptEnvFileNotExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('tidak tersedia');
        
        $this->mcrypt->encryptEnv('/non/existent/file.env', ['TEST']);
    }

    public function testEncryptEnvEmptyKeys(): void
    {
        $envFile = $this->createTestEnvFile(['TEST' => 'value']);
        
        $result = $this->mcrypt->encryptEnv($envFile, []);
        $this->assertFalse($result);
        
        unlink($envFile);
    }

    public function testEncryptEnvNonExistentKeys(): void
    {
        $envFile = $this->createTestEnvFile(['EXISTING' => 'value']);
        
        $result = $this->mcrypt->encryptEnv($envFile, ['NON_EXISTENT']);
        $this->assertTrue($result); // Should still return true, just no changes
        
        unlink($envFile);
    }
}
