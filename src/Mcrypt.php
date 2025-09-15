<?php

declare(strict_types=1);

namespace McryptDev;

use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
use InvalidArgumentException;
use Symfony\Component\Dotenv\Dotenv;


final class Mcrypt
{

    public function __construct(
        private readonly Key $key
    ) {}

    public function encryptEnv(string $envFile, array $keys): bool
    {

        if (!is_file($envFile)) {
            throw new InvalidArgumentException("Error File {$envFile} tidak tersedia", 1);
        }

        if (empty($keys)) {
            return false;
        }

        $dotenvs = (new Dotenv())->parse(
            file_get_contents($envFile)
        );

        $newEnv = [];
        foreach ($dotenvs as $key => $value) {
            if (in_array($key, $keys)) {
                if (!$this->isDefuseEncrypted($value)) {
                    $value = $this->encrypt($value);
                }
            }

            $newEnv[$key] = $value;
        }

        if (!empty($newEnv)) {
            // Save env
            $content = array_map(fn($value, $key) => "{$key}={$value}", $newEnv, array_keys($newEnv));
            $content = implode(PHP_EOL, $content);
            return (bool)file_put_contents($envFile, $content);
        }

        return false;
    }

    public function isDefuseEncrypted(string $ciphertext): bool
    {

        // 1. Check minimal length (def50200 + minimal encrypted data)
        if (strlen($ciphertext) < 50) {
            return false;
        }

        // 2. Check prefix signature Defuse Crypto
        $isDefusePrefix = (strpos($ciphertext, 'def502') === 0);

        // 3. Check hex format (optional)
        $isHexFormat = preg_match('/^[0-9a-f]+$/i', $ciphertext);

        return $isDefusePrefix && $isHexFormat;
    }

    public function encrypt(string $plaintext): string
    {
        return Crypto::encrypt($plaintext, $this->key);
    }

    /**
     *
     * @param string $ciphertext
     * @return string
     */
    public function decrypt(string $ciphertext): string
    {
        return Crypto::decrypt($ciphertext, $this->key);
    }
}
