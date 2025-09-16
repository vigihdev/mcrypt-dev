<?php

declare(strict_types=1);

namespace McryptDev;

use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
use InvalidArgumentException;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Mcrypt
 *
 * Class untuk mengelola enkripsi dan dekripsi environment variables menggunakan Defuse Crypto
 */
final class Mcrypt
{
    /**
     * @var Key Defuse crypto key untuk enkripsi/dekripsi
     */
    public function __construct(
        private readonly Key $key
    ) {}

    /**
     * addEnv
     *
     * Menambahkan atau mengupdate environment variables ke file .env dengan enkripsi otomatis
     *
     * @param string $envFile Path ke file .env
     * @param array $newEnvs Array key-value environment variables baru
     * @return bool Status berhasil atau gagal
     * @throws InvalidArgumentException Jika file .env tidak ditemukan
     */
    public function addEnv(string $envFile, array $newEnvs): bool
    {
        if (!is_file($envFile)) {
            throw new InvalidArgumentException("Error File {$envFile} tidak tersedia", 1);
        }

        if (empty($newEnvs)) {
            return false;
        }

        $dotenvs = (new Dotenv())->parse(
            file_get_contents($envFile)
        );

        $newEnvDot = [];
        foreach ($dotenvs as $key => $value) {

            foreach ($newEnvs as $newKey => $newValue) {
                if (!$this->isDefuseEncrypted($newValue)) {
                    $newValue = $this->encrypt($newValue);
                }

                if ($key === $newKey) {
                    $value = $newValue;
                }
            }

            $newEnvDot[$key] = $value;
        }

        $newEnvEncrypts = [];
        foreach ($newEnvs as $newKey => $newValue) {
            if (!in_array($newKey, array_keys($newEnvDot))) {
                if (!$this->isDefuseEncrypted($newValue)) {
                    $newValue = $this->encrypt($newValue);
                    $newEnvEncrypts[$newKey] = $newValue;
                }
            }
        }

        // merge
        $envData = array_merge($newEnvDot, $newEnvEncrypts);

        // Save env
        $content = array_map(fn($value, $key) => "{$key}={$value}", $envData, array_keys($envData));
        $content = implode(PHP_EOL, $content);
        return (bool)file_put_contents($envFile, $content);
    }

    /**
     * encryptEnv
     *
     * Mengenkripsi environment variables tertentu dalam file .env
     *
     * @param string $envFile Path ke file .env
     * @param array $keys Array nama key yang akan dienkripsi
     * @return bool Status berhasil atau gagal
     * @throws InvalidArgumentException Jika file .env tidak ditemukan
     */
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

    /**
     * isDefuseEncrypted
     *
     * Mengecek apakah string sudah terenkripsi dengan Defuse Crypto
     *
     * @param string $ciphertext String yang akan dicek
     * @return bool True jika sudah terenkripsi, false jika belum
     */
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

    /**
     * encrypt
     *
     * Mengenkripsi plaintext menggunakan Defuse Crypto
     *
     * @param string $plaintext Text yang akan dienkripsi
     * @return string Encrypted text
     */
    public function encrypt(string $plaintext): string
    {
        return Crypto::encrypt($plaintext, $this->key);
    }

    /**
     * decrypt
     *
     * Mendekripsi ciphertext menggunakan Defuse Crypto
     *
     * @param string $ciphertext Text yang akan didekripsi
     * @return string Decrypted text
     */
    public function decrypt(string $ciphertext): string
    {
        return Crypto::decrypt($ciphertext, $this->key);
    }
}
