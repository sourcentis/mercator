# Procédure d'installation de Mercator

## Configuration recommandée

- OS : RedHat 9.3 - Server
- RAM : 2G
- Disque : 10G
- VCPU 2

## Installation

Mettre à jour la distribution linux

	sudo dnf update && sudo dnf upgrade

Installer Httpd, GIT, Graphviz, Vim et PHP

	sudo dnf install vim httpd git graphviz php

Vérifier que le module php 8.2 est disponible

	sudo dnf module list php

Installer php 8.1

	sudo dnf module reset php
	sudo dnf module enable php:8.2
	sudo dnf install php

Vérifier la version php

	php --version

Installer PHP et les librairies

	sudo dnf install php-json php-dbg php-mysqlnd php-gd php-zip php-curl php-mbstring php-xml php-ldap php-soap

Installer Composer

	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

Vérifier que Composer est installé

	composer --version

## Project

Créer le répertoire du projet

	cd /var/www
	sudo mkdir mercator
	sudo chown $USER:$GROUP mercator

Cloner le projet depuis Github

	git clone https://www.github.com/dbarzin/mercator


## Composer

Installer les packages avec composer

    cd /var/www/mercator
	composer install

## MySQL

Installer MySQL

	sudo dnf install mysql-server

Vérifier que vous utilisez MySQL

	sudo mysql --version

Démarrer le service MySQL

	systemctl start mysqld.service
	systemctl enable mysqld.service

Lancer MySQL avec les droits root

	sudo mysql

Créer la base de données _mercator_ et l'utilisateur _mercator_user_

	CREATE DATABASE mercator CHARACTER SET utf8 COLLATE utf8_general_ci;
	CREATE USER 'mercator_user'@'localhost' IDENTIFIED BY 's3cr3t';
	GRANT ALL PRIVILEGES ON mercator.* TO 'mercator_user'@'localhost';
	GRANT PROCESS ON *.* TO 'mercator_user'@'localhost';

	FLUSH PRIVILEGES;
	EXIT;

## Configuration

Créer un fichier .env dans le répertoire racine du projet :

	cd /var/www/mercator
	cp .env.example .env

Modifier les paramètres de connexion à la base de données dans le fichier .env :

	## .env file
	DB_CONNECTION=mysql
	# DB_CONNECTION=pgsql
	DB_HOST=127.0.0.1
	DB_PORT=3306
	# Commenter DB_PORT pour pgsql
	DB_DATABASE=mercator
	DB_USERNAME=mercator_user
	DB_PASSWORD=s3cr3t

## Créer la base de données

Exécuter les migrations

	php artisan migrate --seed

Remarque: la graine est importante (--seed), car elle créera le premier utilisateur administrateur pour vous.

Générer la clé de l'application

	php artisan key:generate

Vider la cache

	php artisan config:clear

Pour importer la base de données de test (facultatif)

	sudo mysql mercator < data/mercator_data_fr.sql

## Importer la base de données des CPE

Décompresse la base de données

    gzip -d mercator_cpe.sql.gz

Importer la base de données

    sudo mysql mercator < mercator_cpe.sql

## Démarrage

Démarrer l'application avec php

    php artisan serve

ou pour y accéder à l'application depuis un autre serveur

    php artisan serve --host 0.0.0.0 --port 8000

L'application est accessible à l'URL [http://127.0.0.1:8000]

    utilisateur : admin@admin.com
    mot de passe : password

## Configurer Passport

Pour l'API JSON, il faut installer Laravel Passport

    php artisan passport:install

Générer les clés de l'API

    php artisan passport:keys

Changer les permission d'accès à la clé

    sudo chown apache:apache storage/oauth-*.key
    sudo chmod 600 storage/oauth-*.key

## Configuration du mail

Si vous souhaitez envoyer des e-mails de notification depuis Mercator.
Vous devez configurer l'accès au serveur SMTP dans .env

    MAIL_HOST='smtp.localhost'
    MAIL_PORT=587
    MAIL_AUTH=true
    MAIL_SMTP_SECURE='tls'
    MAIL_USERNAME=
    MAIL_PASSWORD=

MAIL_SMTP_SECURE accepte 'tls' (STARTTLS obligatoire, port 587), 'ssl' (TLS implicite, port 465) ou une valeur vide (STARTTLS si le serveur le propose). Le certificat du serveur SMTP est toujours vérifié.

Vous pouvez également configurer DKIM :

    MAIL_DKIM_DOMAIN = 'admin.local';
    MAIL_DKIM_PRIVATE = '/path/to/private/key';
    MAIL_DKIM_SELECTOR = 'default'; // Match your DKIM DNS selector
    MAIL_DKIM_PASSPHRASE = '';      // Only if your key has a passphrase

N'oubliez pas de [configurer](https://dbarzin.github.io/deming/config.fr/#notifications) le contenu et la fréquence d'envoi des mails.

## Sheduler

Modifier le crontab

    sudo crontab -e

ajouter cette ligne dans le crontab

    * * * * * cd /var/www/mercator && php artisan schedule:run >> /dev/null 2>&1

## Configuration de la connexion LDAP

Si vous souhaitez connecter Mercator avec un Active Diretory ou un serveur LDAP,
dans le fichier .env, mettez les paramètres de connexion en décommentant les lignes :

    # Plusieurs types possibles : AD, OpenLDAP, FreeIPA, DirectoryServer
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

Retrouvez une documentation plus complète sur la configuration de [LdapRecord](https://ldaprecord.com/docs/laravel/v2/configuration/#using-an-environment-file-env).

## Apache

Pour configurer Apache, modifiez les propriétés du répertoire mercator et accordez les autorisations appropriées au répertoire de stockage avec la commande suivante

    sudo chown -R apache:apache /var/www/mercator
    sudo chmod -R 775 /var/www/mercator/storage

> **Ne lancez pas `php artisan` en `root`.** Les fichiers de cache créés dans `storage/` (par exemple `storage/app/purifier`) appartiendraient alors à `root` et le serveur web ne pourrait plus y écrire (erreur *"Directory … storage/app/purifier not writable"*). Utilisez `sudo -u apache php artisan …`. Si le problème est déjà survenu, relancez les deux commandes ci-dessus.

Vérifier si le module SELinux est activé (Pour que l'application soit accessible il faut que SELinux soit désactivé)

	sestatus

Si le module est activé, procéder ainsi :

	sudo vi /etc/selinux/config

Trouvez la ligne qui commence par SELINUX= dans le fichier. Elle devrait ressembler à ceci :

	SELINUX=enforcing

Changez enforcing en disabled. Votre ligne devrait maintenant ressembler à ceci :

	SELINUX=disabled

Redémarrer le système

	sudo reboot

Ensuite, créez un nouveau fichier de configuration d'hôte virtuel Apache pour servir l'application Mercator :

    sudo vi /etc/httpd/conf.d/mercator.conf

Ajouter les lignes suivantes :

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

Enfin, redémarrez le service Apache pour activer les modifications :

    sudo systemctl restart httpd

### HTTPS

Voici le fichier de configuration pour HTTPS

```xml
<VirtualHost *:443>
    ServerName carto.XXXXXXXX
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

Pour forcer le redirection en HTTPS, il faut mettre ce paramètre dans le fichier .env :

    APP_ENV=production


### Variante PHP-FPM (systèmes Red Hat)

Cette configuration permet d’utiliser PHP-FPM avec Apache via un socket UNIX, offrant de meilleures performances et une compatibilité accrue avec le module `mpm_event`.

#### 1. Installer PHP-FPM

```bash
sudo dnf install php-fpm
```

Activer et démarrer le service PHP-FPM :

```bash
sudo systemctl enable --now php-fpm
```

> N'oubliez pas d'installer également les modules PHP nécessaires à votre application (`php-mbstring`, `php-mysqlnd`, `php-xml`, etc.).

---

#### 2. Exemple de configuration Apache

##### VirtualHost HTTP

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

##### VirtualHost HTTPS

```apache
<VirtualHost *:443>
    ServerName carto.exemple.com
    ServerAdmin admin@example.com

    DocumentRoot /var/www/mercator/public

    SSLEngine on
    SSLProtocol all -SSLv2 -SSLv3
    SSLCipherSuite HIGH:!aNULL:!MD5

    SSLCertificateFile /etc/httpd/certs/carto.exemple.com.crt
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

> **Remarques :**
> - Adaptez les chemins vers les certificats SSL selon votre environnement.
> - Vérifiez le chemin du socket PHP-FPM : il peut être `/run/php-fpm/www.sock` selon votre version/configuration.

---

### Astuce : gérer les permissions avec SELinux

Sur les systèmes avec SELinux activé (cas par défaut sur RHEL et dérivés), vous pourriez rencontrer des erreurs de type *"Permission denied"* lors de l'utilisation du socket UNIX.

Voici comment autoriser Apache à accéder au socket PHP-FPM :

```bash
sudo setsebool -P httpd_can_network_connect on
sudo chcon -u system_u -t httpd_sys_rw_content_t /run/php/php-fpm.sock
```

Et si vous hébergez Mercator dans un répertoire personnalisé :

```bash
sudo semanage fcontext -a -t httpd_sys_content_t "/var/www/mercator(/.*)?"
sudo restorecon -Rv /var/www/mercator
```

> Pour utiliser `semanage`, installez le paquet suivant si besoin :  
> ```bash
> sudo dnf install policycoreutils-python-utils
> ```


## Problèmes

### Restaurer le mot de passe administrateur

    mysql mercator -e "update users set password=$(php -r "echo password_hash('n3w-p4sSw0rD.', PASSWORD_BCRYPT, ['cost' => 10]);") where id=1;"

### PHP Memory

Si vous générez de gros rapports, vous devrez mettre augmenter la mémoire allouée à PHP dans /etc/php/8.x/apache2/php.ini

    memory_limit = 512M

## Mise à jour

Avant de mettre à jour l'application prenez un backup de la base de données et du projet.

    mysqldump mercator > mercator_backup.sql

ou (Postgres)

    pg_dump mercator > mercator_backup.sql

Récupérer les sources de GIT

    cd /var/www/mercator
    sudo -u apache git pull

Migrer la base de données

    sudo -u apache php artisan migrate

Installer les librairies PHP

    sudo -u apache composer install

Vider les caches

    sudo -u apache php artisan config:clear &&  php artisan view:clear

## Tests de non-régression

Pour exécuter les tests de non-régression de Mercator, vous devez d'abord instaler Chromium :

    sudo apt install chromium-browser

Installer le pluggin dusk

    sudo -u apache php artisan dusk:chrome-driver

Configurer l'environement

    sudo -u apache cp .env .env.dusk.local

Lancer l'application

    sudo -u apache php artisan serve

Dans un autre terminal, lancer les tests

    sudo -u apache php artisan dusk
