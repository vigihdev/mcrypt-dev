<?php

declare(strict_types=1);

namespace McryptDev;

use Exception;
use InvalidArgumentException;


final class Key
{

    /**
     *
     * @param string $keyFilepath
     * @return \Defuse\Crypto\Key
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

    public function __construct(
        private readonly string $keyFilepath
    ) {
        if (!is_file($keyFilepath)) {
            throw new InvalidArgumentException("Error File {$keyFilepath} tidak tersedia", 1);
        }
    }
}
