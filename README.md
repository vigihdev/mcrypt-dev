# SSH Helpers

Simple SSH and SFTP connection helpers using phpseclib3.

## Features

- SSH connection with key authentication
- SFTP file operations (upload/download)
- Command execution via SSH and SFTP
- Secure configuration management

## Usage

### Contoh Services

```yaml
parameters:
  ssh.host: "1.1.1.1"
  ssh.port: 22
  ssh.user: "user"

services:
  public.key:
    public: true
    class: 'Ssh\Connection\PublicKeyLoaderConnection'
    factory: ['Ssh\Connection\PublicKeyLoaderConnection', "load"]
    arguments: ["~/.ssh/id_rsa"]

  sftp:
    public: true
    class: 'phpseclib3\Net\SFTP'
    arguments:
      $host: "%ssh.host%"
      $port: "%ssh.port%"
      $timeout: 10
    calls:
      - method: login
        arguments:
          - "%ssh.user%"
          - "@public.key"

  ssh:
    public: true
    class: 'phpseclib3\Net\SSH2'
    arguments:
      $host: "%ssh.host%"
      $port: "%ssh.port%"
      $timeout: 10
    calls:
      - method: login
        arguments:
          - "%ssh.user%"
          - "@public.key"
```

### SSH Connection

```php
/** @var phpseclib3\Net\SSH2 $ssh */
$ssh = $container->get('sirent.ssh');
echo $ssh->exec('pwd');
```

### SFTP Operations

```php
/** @var Ssh\Service\SftpNet $sftp */
$sftp = new SftpNet(sftp: $container->get('sirent.sftp'));

// List files
$files = $sftp->list();

// Change directory
$sftp->chdir('public_html');

// Download file
$sftp->downloadFile('remote.txt', '/local/path.txt');

// Execute command
$result = $sftp->exec('wp cli info');
```

## Configuration

### Container Services

```php
// config/services.php
return [
    'sirent.ssh' => function() {
        $ssh = new \phpseclib3\Net\SSH2('1.1.1.1', 22);
        $key = \phpseclib3\Crypt\PublicKeyLoader::load(file_get_contents('/path/to/private.key'));
        $ssh->login('username', $key);
        return $ssh;
    },

    'sirent.sftp' => function($container) {
        return new \phpseclib3\Net\SFTP('1.1.1.1', 22);
    }
];
```

### Database Configuration

```ini
# .mycnf/database.my.cnf (chmod 600)
[client]
host=localhost
user=database_user
password='secure_password'

[mysql]
host = localhost
user = database_user
password = 'secure_password'
database = database_name
```

## Security

- Private keys should be stored securely
- Database config files use 600 permissions
- Use strong passwords and key-based authentication
