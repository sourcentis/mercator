# Mercator installation procedure

## Recommended configuration

- OS: RedHat 9.3 - Server
- RAM: 2G
- Disk: 10G
- VCPU2

## Facility

Update linux distribution

    sudo dnf update && sudo dnf upgrade

Install Httpd, GIT, Graphviz, Vim and PHP

    sudo dnf install vim httpd git graphviz php

Check that the php 8.2 module is available

    sudo dnf module list php

Install php 8.2

    sudo dnf module reset php
    sudo dnf module enable php:8.2
    sudo dnf install php

Check php version

    php --version

Install PHP and libraries

    sudo dnf install php-json php-dbg php-mysqlnd php-gd php-zip php-curl php-mbstring php-xml php-ldap php-soap

Install Composer

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

Verify that Composer is installed

    composer --version

## Project

Create the project directory

    cd /var/www
    sudo mkdir mercator
    sudo chown $USER:$GROUP mercator

Clone the project from Github

    git clone https://www.github.com/dbarzin/mercator

## Composer

Install packages with composer

    composer install

## MySQL

Install MySQL

    sudo dnf install mysql-server

Verify that you are using MySQL

    sudo mysql --version

Start the MySQL service

    systemctl start mysqld.service
    systemctl enable mysqld.service

Launch MySQL with root rights

    sudo mysql

Create the _mercator_ database and the _mercator_user_ user

    CREATE DATABASE mercator CHARACTER SET utf8 COLLATE utf8_general_ci;
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

    sudo mysql mercator < data/mercator_data_en.sql

## Import the CPE database

Unpack the database

     gzip -d mercator_cpe.sql.gz

Import database

     sudo mysql mercator < mercator_cpe.sql

## Startup

Start application with php

     php artisan serve

or to access the application from another server

     php artisan serve --host 0.0.0.0 --port 8000

The application is accessible at URL [http://127.0.0.1:8000]

     user: admin@admin.com
     password: password

## Configure Passport

For the JSON API, you need to install Laravel Passport

     php artisan passport:install

Generate API keys

     php artisan passport:keys

Change key access permissions

     sudo chown apache:apache storage/oauth-*.key
     sudo chmod 600 storage/oauth-*.key

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

Don't forget to [configure](https://dbarzin.github.io/deming/config/#notifications) the content and frequency of your emails.

## Sheduler

Edit crontab

     sudo crontab -e

add this line to the crontab

     * * * * * cd /var/www/mercator && php artisan schedule:run >> /dev/null 2>&1

 ## Configuring the LDAP connection

 If you want to connect Mercator with an Active Directory or an LDAP server,
 in the .env file, put the connection parameters by uncommenting the lines:

      # Several possible types: AD, OpenLDAP, FreeIPA, DirectoryServer
      LDAP_TYPE="AD"
      # If true, LDAP actions will be written to the application's default log file
      LDAP_LOGGING=true
      LDAP_CONNECTION=default
      LDAP_HOST=127.0.0.1
      # Identifiers of the user who will connect to the LDAP in order to perform queries
      LDAP_USERNAME="cn=user,dc=local,dc=com"
      LDAP_PASSWORD=secret
      LDAP_PORT=389
      LDAP_BASE_DN="dc=local,dc=com"
      LDAP_TIMEOUT=5
      LDAP_SSL=false
      LDAP_TLS=false
      # Allows you to restrict access to a tree structure
      LDAP_SCOPE="ou=Accounting,ou=Groups,dc=planetexpress,dc=com"
      # Allows you to restrict access to groups
      LDAP_GROUPS="Delivering,Help Desk"

 Find more complete documentation on configuring [LdapRecord](https://ldaprecord.com/docs/laravel/v2/configuration/#using-an-environment-file-env).

 ##Apache

 To configure Apache, modify the properties of the mercator directory and grant the appropriate permissions to the storage directory with the following command

      sudo chown -R apache:apache /var/www/mercator
      sudo chmod -R 775 /var/www/mercator/storage

 > **Do not run `php artisan` as `root`.** Cache files created in `storage/` (for example `storage/app/purifier`) would be owned by `root` and the web server could no longer write to them (error *"Directory … storage/app/purifier not writable"*). Use `sudo -u apache php artisan …`. If the problem has already occurred, run the two commands above again.

 Check if the SELinux module is activated (For the application to be accessible, SELinux must be deactivated)

 status

 If the module is activated, proceed as follows:

 sudo vi /etc/selinux/config

 Find the line that starts with SELINUX= in the file. It should look like this:

 SELINUX=enforcing

 Change enforcing to disabled. Your line should now look like this:

 SELINUX=disabled

 Restart the system

 sudo reboot

 Next, create a new Apache virtual host configuration file to serve the Mercator application:

      sudo vi /etc/httpd/conf.d/mercator.conf

 Add the following lines:

 ```xml
 <VirtualHost *:80>
      ServerName mercator.local
      ServerAdmin admin@example.com
      DocumentRoot /var/www/mercator/public
      <Directory /var/www/mercator>
          AllowOverride All
      </Directory>
      ErrorLog /var/log/httpd/mercator_error.log
      CustomLog /var/log/httpd/mercator_access.log combined
 </VirtualHost>
 ```

 Finally, restart the Apache service to activate the changes:

      sudo systemctl restart httpd

 ### HTTPS

 Here is the configuration file for HTTPS

 ```xml
 <VirtualHost *:443>
      ServerName map.XXXXXXXX
      ServerAdmin
      DocumentRoot /var/www/mercator/public
      SSLEngine on
      SSLProtocol all -SSLv2 -SSLv3
      SSLCipherSuite HIGH:3DES:!aNULL:!MD5:!SEED:!IDEA
      SSLCertificateFile /etc/apache2/certs/certs/carto.XXXXX.crt
      SSLCertificateKeyFile /etc/apache2/certs/private/private.key
      SSLCertificateChainFile /etc/apache2/certs/certs/XXXXXCA.crt
      <Directory /var/www/mercator/public>
          AllowOverride All
      </Directory>
      ErrorLog /var/log/httpd/mercator_error.log
      CustomLog /var/log/httpd/mercator_access.log combined
 </VirtualHost>
 ```

 To force HTTPS redirection, you must put this parameter in the .env file:

      APP_ENV=production


### PHP-FPM Variant

This setup configures Apache to use PHP-FPM via a UNIX socket, improving performance and compatibility with `mpm_event`.

#### 1. Install PHP-FPM

```bash
sudo dnf install php-fpm
```

Ensure that `php-fpm` is enabled and started:

```bash
sudo systemctl enable --now php-fpm
```

> You may need to install additional PHP modules depending on your application needs (e.g. `php-mbstring`, `php-mysqlnd`, `php-xml`, etc.).

---

#### 2. Apache VirtualHost Configuration

##### Example: HTTP VirtualHost

```apache
<VirtualHost *:80>
    ServerName mercator.local
    ServerAdmin admin@example.com

    DocumentRoot /var/www/mercator/public

    <Directory /var/www/mercator>
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php-fpm.sock|fcgi://localhost/"
    </FilesMatch>

    ErrorLog /var/log/httpd/mercator_error.log
    CustomLog /var/log/httpd/mercator_access.log combined
</VirtualHost>
```

##### Example: HTTPS VirtualHost

```apache
<VirtualHost *:443>
    ServerName carto.example.com
    ServerAdmin admin@example.com

    DocumentRoot /var/www/mercator/public

    SSLEngine on
    SSLProtocol all -SSLv2 -SSLv3
    SSLCipherSuite HIGH:!aNULL:!MD5

    SSLCertificateFile /etc/httpd/certs/carto.example.com.crt
    SSLCertificateKeyFile /etc/httpd/certs/private.key
    SSLCertificateChainFile /etc/httpd/certs/CA.crt

    <Directory /var/www/mercator/public>
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php-fpm.sock|fcgi://localhost/"
    </FilesMatch>

    ErrorLog /var/log/httpd/mercator_error.log
    CustomLog /var/log/httpd/mercator_access.log combined
</VirtualHost>
```

> **Notes:**
> - Adjust paths and file names to match your certificate setup.
> - Verify the PHP-FPM socket path; on some systems it may be `/run/php-fpm/www.sock`.


 ## Problems

 ### Restore administrator password

      mysql mercator -e "update users set password=$(php -r "echo password_hash('n3w-p4sSw0rD.', PASSWORD_BCRYPT, ['cost' => 10]);") where id=1;"

 ### PHP Memory

 If you are generating large reports, you will need to increase the memory allocated to PHP in /etc/php/8.x/apache2/php.ini

      memory_limit = 512M

 ## Update

 Before updating the application, take a backup of the database and the project.

      mysqldump mercator > mercator_backup.sql

 or (Postgres)

      pg_dump mercator > mercator_backup.sql

 Retrieve GIT sources

      cd /var/www/mercator
      sudo -u apache git pull

 Migrate the database

      sudo -u apache php artisan migrate

 Update libraries

      sudo -u apache composer install

 Clear caches

      sudo -u apache php artisan config:clear && php artisan view:clear

 ## Non regression tests

 To run Mercator regression tests, you must first install Chromium:

      sudo apt install chromium-browser

 Install the dusk plugin

      sudo -u apache php artisan dusk:chrome-driver

 Configure the environment

      sudo -u apache cp .env .env.dusk.local

 Launch the application

      sudo -u apache php artisan serve

 In another terminal, run the tests

      sudo -u apache php artisan dusk
