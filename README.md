# McryptDev

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.0-blue.svg)](https://php.net)

Sebuah library PHP untuk enkripsi dan dekripsi data menggunakan Defuse PHP Encryption dengan integrasi Symfony Console untuk mengelola environment variables yang terenkripsi.

## Fitur

- 🔐 Enkripsi/dekripsi data menggunakan Defuse PHP Encryption
- 🔑 Manajemen kunci enkripsi yang aman
- 📝 Command line tools untuk mengelola environment variables
- 🛠️ Integrasi dengan Symfony DI Container
- ⚡ Mudah digunakan dan dikonfigurasi

## Instalasi

```bash
composer require vigihdev/mcrypt-dev
```

## Persyaratan

- PHP ^8.0
- Defuse PHP Encryption ^2.4
- Symfony Components ^6.4

## Penggunaan

### 1. Konfigurasi Services (Symfony DI)

```yaml
services:
  key:
    public: false
    class: 'McryptDev\Key'
    factory: ['McryptDev\Key', "load"]
    arguments: ["~/.config-dev/.defuse.key"]

  mcrypt:
    public: true
    class: 'McryptDev\Mcrypt'
    arguments:
      $key: "@key"
```

### 2. Penggunaan Dasar

```php
<?php

use McryptDev\Key;
use McryptDev\Mcrypt;

// Load kunci enkripsi
$key = Key::load('path/to/your/key/file');

// Inisialisasi Mcrypt
$mcrypt = new Mcrypt($key);

// Enkripsi data
$encrypted = $mcrypt->encrypt('data rahasia');

// Dekripsi data
$decrypted = $mcrypt->decrypt($encrypted);
```

### 3. Console Commands

#### Menambah Environment Variables

```bash
php bin/console mcrypt:add:env ~/.config-dev/.defuse.key --env=DB_HOST=localhost,DB_NAME=db_name
```

#### Enkripsi Environment File

```bash
php bin/console mcrypt:encrypt:env ~/.config-dev/.defuse.key .env --key=DB_HOST,DB_NAME
```

### 4. Generate Kunci Enkripsi

```php
<?php

use McryptDev\Key;

// Generate kunci baru
$key = Key::createNewRandomKey();

// Simpan kunci ke file
Key::saveToAsciiSafeString($key, 'path/to/save/key');
```

## Struktur Proyek

```
mcrypt-dev/
├── src/
│   ├── Command/
│   │   ├── CommandAbstract.php
│   │   ├── McryptAddEnvCommand.php
│   │   └── McryptEncryptEnvCommand.php
│   ├── Key.php
│   └── Mcrypt.php
├── bin/
├── config/
├── composer.json
└── README.md
```

## Development Server

Untuk menjalankan development server:

```bash
composer run server
```

Server akan berjalan di `http://localhost:2232`

## Kontribusi

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b feature/amazing-feature`)
3. Commit perubahan Anda (`git commit -m 'Add some amazing feature'`)
4. Push ke branch (`git push origin feature/amazing-feature`)
5. Buat Pull Request

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

## Author

**Vigih Dev**
- Email: vigihdev@gmail.com
- GitHub: [@vigihdev](https://github.com/vigihdev)

## Keamanan

Jika Anda menemukan kerentanan keamanan, silakan kirim email ke vigihdev@gmail.com alih-alih menggunakan issue tracker publik.

---

⭐ Jangan lupa berikan star jika proyek ini membantu Anda!
