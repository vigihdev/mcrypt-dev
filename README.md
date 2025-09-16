## Usage

### Contoh Services

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

```
php bin/console mcrypt:add:env ~/.config-dev/.defuse.key --env=DB_HOST=localhost,DB_NAME=db_name
```
