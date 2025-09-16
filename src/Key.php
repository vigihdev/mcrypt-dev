<?php

declare(strict_types=1);

namespace McryptDev;

use Exception;
use InvalidArgumentException;

/**
 * Key
 *
 * Class untuk mengelola loading Defuse Crypto Key dari file
 */
final class Key
{
    /**
     * load
     *
     * Memuat Defuse Crypto Key dari file dengan support tilde expansion
     *
     * @param string $keyFilepath Path ke file key (support ~ untuk home directory)
     * @return \Defuse\Crypto\Key Instance Defuse Crypto Key
     * @throws InvalidArgumentException Jika file key tidak ditemukan
     */
    public static function load(string $keyFilepath): \Defuse\Crypto\Key
    {

        if (strpos($keyFilepath, '~') === 0) {
            $home = getenv('HOME') ?: ($_SERVER['HOME'] ?? null);
            if ($home) {
                $keyFilepath = $home . substr($keyFilepath, 1);
            }
        }

        if (!is_file($keyFilepath)) {
            throw new InvalidArgumentException("Error File {$keyFilepath} tidak tersedia", 1);
        }

        return \Defuse\Crypto\Key::loadFromAsciiSafeString(file_get_contents($keyFilepath));
    }
}
