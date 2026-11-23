# Mercator installation procedure (Red Hat)

This procedure applies to Red Hat Enterprise Linux 9 and its compatible distributions (Rocky Linux 9, AlmaLinux 9).

## Recommended configuration

- OS: Red Hat Enterprise Linux 9 - Server
- RAM: 2G
- Disk: 10G
- VCPU: 2

## Installation

Update the Linux distribution

    sudo dnf update && sudo dnf upgrade

Install Httpd, GIT, Graphviz and Vim

    sudo dnf install vim httpd git graphviz

Mercator requires PHP **8.4.x** (>= 8.4.1, PHP 8.5 is not supported yet) with the `sodium` extension. The PHP 8.4
module of the RHEL 9 AppStream repositories does not provide `php-sodium`, so enable the EPEL and Remi repositories

    sudo dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-9.noarch.rpm
    sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-9.rpm

Check that the `remi-8.4` module is available

    sudo dnf module list php

Install PHP 8.4

    sudo dnf module reset php
    sudo dnf module enable php:remi-8.4
    sudo dnf install php

Check the PHP version (must be 8.4.x)

    php --version

Install the PHP extensions

    sudo dnf install php-json php-dbg php-mysqlnd php-gd php-zip php-curl php-mbstring php-xml php-ldap php-soap php-sodium php-intl

> On Red Hat, the `php` package runs PHP through **PHP-FPM**: it installs `php-fpm` and the
> `/etc/httpd/conf.d/php.conf` file, which sends PHP files to the `/run/php-fpm/www.sock` socket. No `mod_php` module
> is needed.

Install Composer

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

Verify that Composer is installed

    composer --version

## Project

Create the project directory

    sudo mkdir /var/www/mercator
    sudo chown $USER: /var/www/mercator

Clone the project from Github

    git clone https://github.com/sourcentis/mercator /var/www/mercator

## Composer

Install packages with composer

    cd /var/www/mercator
    composer install

## MySQL

Install MySQL

    sudo dnf install mysql-server

Verify that you are using MySQL

    mysql --version

Start the MySQL service and enable it at boot

    sudo systemctl enable --now mysqld.service

Launch MySQL with root rights

    sudo mysql

Create the _mercator_ database and the _mercator_user_ user

    CREATE DATABASE mercator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE USER 'mercator_user'@'localhost' IDENTIFIED BY 's3cr3t';
    GRANT ALL PRIVILEGES ON mercator.* TO 'mercator_user'@'localhost';
    GRANT PROCESS ON *.* TO 'mercator_user'@'localhost';

    FLUSH PRIVILEGES;
    EXIT;

## Configuration

Create an .env file in the project root directory:

    cd /var/www/mercator
    cp .env.example .env

Edit database connection settings in the .env file:

    ## .env file
    DB_CONNECTION=mysql
    #DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    # Comment DB_PORT for pgsql
    DB_DATABASE=mercator
    DB_USERNAME=mercator_user
    DB_PASSWORD=s3cr3t

## Create the database

Run migrations

    php artisan migrate --seed

Note: The seed is important (--seed), because it will create the first admin user for you.

Generate app key

    php artisan key:generate

Clear cache

    php artisan config:clear

To import the test database (optional)

    sudo mysql mercator < database/data/mercator_data_en.sql

## CPE database (optional)

The `mercator:cpe-sync` command synchronizes CPEs (Common Platform Enumeration) from the NVD (National Vulnerability
Database). It is scheduled daily at 00:30 by the Laravel scheduler (see [Scheduler](#scheduler)).

Add the following parameters to the `.env` file:

    NVD_API_URL=https://services.nvd.nist.gov/rest/json/cpes/2.0
    # Optional but recommended: NVD API key for higher request quotas
    NVD_API_KEY=

The first synchronization downloads the whole CPE dictionary (1 to 2 hours without API key). It can be started
manually:

    php artisan mercator:cpe-sync --now

## Startup

Start the application with php to check the installation

    php artisan serve

or to access the application from another server

    php artisan serve --host 0.0.0.0 --port 8000

The application is accessible at URL [http://127.0.0.1:8000]

    user: admin@admin.com
    password: password

## Configure Passport

For the JSON API, install Laravel Passport. This command also generates the API encryption keys
(`storage/oauth-private.key` and `storage/oauth-public.key`)

    php artisan passport:install

The permissions of these keys are set in the [Apache](#apache) section.

## Email configuration

If you wish to send notification e-mails from Mercator.
You have to configure the SMTP server access in .env

    MAIL_HOST='smtp.localhost'
    MAIL_PORT=587
    MAIL_AUTH=true
    MAIL_SMTP_SECURE='tls'
    MAIL_USERNAME=
    MAIL_PASSWORD=

MAIL_SMTP_SECURE accepts 'tls' (mandatory STARTTLS, port 587), 'ssl' (implicit TLS, port 465) or an empty value (STARTTLS when the server offers it). The SMTP server certificate is always verified.

You may also configure DKIM :

    MAIL_DKIM_DOMAIN = 'admin.local';
    MAIL_DKIM_PRIVATE = '/path/to/private/key';
    MAIL_DKIM_SELECTOR = 'default'; // Match your DKIM DNS selector
    MAIL_DKIM_PASSPHRASE = '';      // Only if your key has a passphrase

## Configuring the LDAP connection

If you want to connect Mercator with an Active Directory or an LDAP server, set the connection parameters in the
`.env` file:

    LDAP_ENABLED=true
    LDAP_FALLBACK_LOCAL=true
    LDAP_AUTO_PROVISION=false
    LDAP_AUTO_PROVISION_ROLE=User
    LDAP_HOST=ldap.example.org
    # Service account used to search for users
    LDAP_USERNAME="cn=user,dc=example,dc=org"
    LDAP_PASSWORD=secret
    LDAP_PORT=389
    LDAP_BASE_DN="dc=example,dc=org"
    LDAP_USERS_BASE_DN="ou=people,dc=example,dc=org"
    LDAP_TIMEOUT=5
    LDAP_SSL=false
    LDAP_TLS=true
    # Attribute(s) matching the identifier typed on the login page (sAMAccountName on Active Directory)
    LDAP_LOGIN_ATTRIBUTES=uid
    # Optional: full DN of the group the user must belong to
    LDAP_GROUP="cn=mercator,ou=groups,dc=example,dc=org"

With TLS or LDAPS, declare the directory's CA with `TLS_CACERT` in `/etc/openldap/ldap.conf`.

All LDAP options and a troubleshooting table are described in the
[configuration documentation](https://sourcentis.github.io/mercator/config/#ldap-configuration).

## Apache

Give the project to the `apache` user and allow it to write to the `storage` and `bootstrap/cache` directories

    sudo chown -R apache:apache /var/www/mercator
    sudo chmod -R 775 /var/www/mercator/storage /var/www/mercator/bootstrap/cache

Then restrict the Passport keys again: the previous command also applied to them, and Passport refuses keys that are
readable by everyone (the API then answers with an error 500)

    sudo chmod 600 /var/www/mercator/storage/oauth-*.key

> **Do not run `php artisan` as `root`.** Cache files created in `storage/` (for example `storage/app/purifier`) would be owned by `root` and the web server could no longer write to them (error *"Directory … storage/app/purifier not writable"*). Use `sudo -u apache php artisan …`. If the problem has already occurred, run the three commands above again.

Create a new Apache virtual host configuration file to serve the Mercator application:

    sudo vi /etc/httpd/conf.d/mercator.conf

Add the following lines:

```apache
<VirtualHost *:80>
    ServerName mercator.local
    ServerAdmin admin@example.com
    DocumentRoot /var/www/mercator/public
    <Directory /var/www/mercator/public>
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog /var/log/httpd/mercator_error.log
    CustomLog /var/log/httpd/mercator_access.log combined
</VirtualHost>
```

PHP files are executed by PHP-FPM through `/etc/httpd/conf.d/php.conf`. Start PHP-FPM and Apache, and enable them at
boot:

    sudo systemctl enable --now php-fpm httpd

Open the HTTP (and HTTPS) port in the firewall:

    sudo firewall-cmd --permanent --add-service=http --add-service=https
    sudo firewall-cmd --reload

### SELinux

SELinux is enabled by default on Red Hat. Rather than disabling it, allow Apache to write to the application's
`storage` and `bootstrap/cache` directories:

    sudo dnf install policycoreutils-python-utils
    sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/mercator/storage(/.*)?"
    sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/mercator/bootstrap/cache(/.*)?"
    sudo restorecon -Rv /var/www/mercator

Then allow the outgoing connections Mercator needs:

    # Database (MySQL / PostgreSQL)
    sudo setsebool -P httpd_can_network_connect_db on
    # LDAP / Active Directory
    sudo setsebool -P httpd_can_connect_ldap on
    # SMTP server, NVD API, Keycloak / Entra ID
    sudo setsebool -P httpd_can_network_connect on

If access is still denied, look for the blocked operations with `sudo ausearch -m avc -ts recent`.

### HTTPS

Install the Apache SSL module

    sudo dnf install mod_ssl

Here is the configuration file for HTTPS

```apache
<VirtualHost *:443>
    ServerName carto.example.com
    ServerAdmin admin@example.com
    DocumentRoot /var/www/mercator/public

    SSLEngine on
    SSLProtocol all -SSLv2 -SSLv3
    SSLCipherSuite HIGH:!aNULL:!MD5

    SSLCertificateFile /etc/pki/tls/certs/carto.example.com.crt
    SSLCertificateKeyFile /etc/pki/tls/private/carto.example.com.key
    SSLCertificateChainFile /etc/pki/tls/certs/CA.crt

    <Directory /var/www/mercator/public>
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog /var/log/httpd/mercator_error.log
    CustomLog /var/log/httpd/mercator_access.log combined
</VirtualHost>
```

Restart Apache

    sudo systemctl restart httpd

To force HTTPS redirection, set this parameter in the .env file (`APP_ENV=production` also forces HTTPS when
`APP_FORCE_HTTPS` is not set):

    APP_FORCE_HTTPS=true

### Dedicated PHP-FPM handler (optional)

To declare the PHP-FPM socket explicitly in the virtual host (for example after changing the pool configuration in
`/etc/php-fpm.d/www.conf`), add this block inside `<VirtualHost>`:

```apache
<FilesMatch "\.php$">
    SetHandler "proxy:unix:/run/php-fpm/www.sock|fcgi://localhost/"
</FilesMatch>
```

> The socket path must match the `listen` parameter of `/etc/php-fpm.d/www.conf` (`/run/php-fpm/www.sock` by default
> on Red Hat). A wrong path makes Apache answer with an error 503.

## Scheduler

Install and start cron if needed

    sudo dnf install cronie
    sudo systemctl enable --now crond

Edit the crontab of the `apache` user (not root, see the warning in the [Apache](#apache) section)

    sudo crontab -u apache -e

add this line to the crontab

    * * * * * cd /var/www/mercator && php artisan schedule:run >> /dev/null 2>&1

## Problems

### Restore administrator password

    sudo mysql mercator -e "update users set password='$(php -r "echo password_hash('n3w-p4sSw0rD.', PASSWORD_BCRYPT, ['cost' => 10]);")' where id=1;"

### PHP Memory

If you are generating large reports, you will need to increase the memory allocated to PHP in `/etc/php.ini`

    memory_limit = 512M

then restart PHP-FPM

    sudo systemctl restart php-fpm

## Update

Before updating the application, take a backup of the database and the project.

    sudo mysqldump mercator > mercator_backup.sql

or (Postgres)

    sudo -u postgres pg_dump mercator > mercator_backup.sql

Retrieve GIT sources

    cd /var/www/mercator
    sudo -u apache git pull

Update libraries (`sudo` does not search `/usr/local/bin`, hence the full path to Composer)

    sudo -u apache /usr/local/bin/composer install

Migrate the database

    sudo -u apache php artisan migrate

Clear caches

    sudo -u apache php artisan config:clear
    sudo -u apache php artisan view:clear

## Non-regression tests

Mercator uses [Pest](https://pestphp.com) for non-regression testing. The tests use the `.env.testing` file provided
with the project, which points to a dedicated `mercator_test` database (the tests empty the database they run on, never
use the production database). Create it with `sudo mysql`:

    CREATE DATABASE mercator_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    GRANT ALL PRIVILEGES ON mercator_test.* TO 'mercator_user'@'localhost';

Run the tests

    cd /var/www/mercator
    sudo -u apache ./vendor/bin/pest

or only one group of tests

    sudo -u apache ./vendor/bin/pest --group=api
