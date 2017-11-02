# Web UI

On a fresh **Ubuntu/Debian x64** machine login as 'root' and run this:

```bash
curl -sS -o /tmp/server-install.sh https://raw.githubusercontent.com/theodorosploumis/faaast/master/scripts/server-install.sh | DOMAIN="mydomain.com" && sh /tmp/server-install.sh

```

This will install the Web UI on a server with main domain.

 - Tested with Debian 9.2 x64
 - Needs at least 2GB of RAM
 - Docker images need at least 2GB space

Local development.

```
php -S localhost:8899 -t html/
```
