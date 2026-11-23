# Procédure d'installation de Mercator (Red Hat)

Cette procédure s'applique à Red Hat Enterprise Linux 9 et aux distributions compatibles (Rocky Linux 9, AlmaLinux 9).

## Configuration recommandée

- OS : Red Hat Enterprise Linux 9 - Server
- RAM : 2G
- Disque : 10G
- VCPU : 2

## Installation

Mettre à jour la distribution linux

    sudo dnf update && sudo dnf upgrade

Installer Httpd, GIT, Graphviz et Vim

    sudo dnf install vim httpd git graphviz

Mercator nécessite PHP **8.4.x** (>= 8.4.1, PHP 8.5 n'est pas encore supporté) avec l'extension `sodium`. Le module
PHP 8.4 des dépôts AppStream de RHEL 9 ne fournit pas `php-sodium` : activer les dépôts EPEL et Remi

    sudo dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-9.noarch.rpm
    sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-9.rpm

Vérifier que le module `remi-8.4` est disponible

    sudo dnf module list php

Installer PHP 8.4

    sudo dnf module reset php
    sudo dnf module enable php:remi-8.4
    sudo dnf install php

Vérifier la version de PHP (doit être 8.4.x)

    php --version

Installer les extensions PHP

    sudo dnf install php-json php-dbg php-mysqlnd php-gd php-zip php-curl php-mbstring php-xml php-ldap php-soap php-sodium php-intl

> Sur Red Hat, le paquet `php` exécute PHP via **PHP-FPM** : il installe `php-fpm` et le fichier
> `/etc/httpd/conf.d/php.conf`, qui transmet les fichiers PHP au socket `/run/php-fpm/www.sock`. Aucun module
> `mod_php` n'est nécessaire.

Installer Composer

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

Vérifier que Composer est installé

    composer --version

## Projet

Créer le répertoire du projet

    sudo mkdir /var/www/mercator
    sudo chown $USER: /var/www/mercator

Cloner le projet depuis Github

    git clone https://github.com/sourcentis/mercator /var/www/mercator

## Composer

Installer les packages avec composer

    cd /var/www/mercator
    composer install

## MySQL

Installer MySQL

    sudo dnf install mysql-server

Vérifier que vous utilisez MySQL

    mysql --version

Démarrer le service MySQL et l'activer au démarrage

    sudo systemctl enable --now mysqld.service

Lancer MySQL avec les droits root

    sudo mysql

Créer la base de données _mercator_ et l'utilisateur _mercator_user_

    CREATE DATABASE mercator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
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

Remarque : le seed est important (--seed), car il crée le premier utilisateur administrateur.

Générer la clé de l'application

    php artisan key:generate

Vider le cache

    php artisan config:clear

Pour importer la base de données de test (facultatif)

    sudo mysql mercator < database/data/mercator_data_fr.sql

## Base de données CPE (facultatif)

La commande `mercator:cpe-sync` synchronise les CPE (Common Platform Enumeration) depuis la NVD (National
Vulnerability Database). Elle est planifiée chaque jour à 00h30 par le planificateur Laravel (voir
[Planificateur](#planificateur)).

Ajouter les paramètres suivants dans le fichier `.env` :

    NVD_API_URL=https://services.nvd.nist.gov/rest/json/cpes/2.0
    # Facultatif mais recommandé : clé d'API NVD pour bénéficier de quotas plus élevés
    NVD_API_KEY=

La première synchronisation télécharge tout le dictionnaire CPE (1 à 2 heures sans clé d'API). Elle peut être lancée
manuellement :

    php artisan mercator:cpe-sync --now

## Démarrage

Démarrer l'application avec php pour vérifier l'installation

    php artisan serve

ou pour accéder à l'application depuis un autre serveur

    php artisan serve --host 0.0.0.0 --port 8000

L'application est accessible à l'URL [http://127.0.0.1:8000]

    utilisateur : admin@admin.com
    mot de passe : password

## Configurer Passport

Pour l'API JSON, installer Laravel Passport. Cette commande génère aussi les clés de chiffrement de l'API
(`storage/oauth-private.key` et `storage/oauth-public.key`)

    php artisan passport:install

Les permissions de ces clés sont réglées dans la section [Apache](#apache).

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

## Configuration de la connexion LDAP

Si vous souhaitez connecter Mercator avec un Active Directory ou un serveur LDAP, renseignez les paramètres de connexion
dans le fichier `.env` :

    LDAP_ENABLED=true
    LDAP_FALLBACK_LOCAL=true
    LDAP_AUTO_PROVISION=false
    LDAP_AUTO_PROVISION_ROLE=User
    LDAP_HOST=ldap.example.org
    # Compte de service utilisé pour rechercher les utilisateurs
    LDAP_USERNAME="cn=user,dc=example,dc=org"
    LDAP_PASSWORD=secret
    LDAP_PORT=389
    LDAP_BASE_DN="dc=example,dc=org"
    LDAP_USERS_BASE_DN="ou=people,dc=example,dc=org"
    LDAP_TIMEOUT=5
    LDAP_SSL=false
    LDAP_TLS=true
    # Attribut(s) correspondant à l'identifiant saisi sur la page de connexion (sAMAccountName sur Active Directory)
    LDAP_LOGIN_ATTRIBUTES=uid
    # Optionnel : DN complet du groupe dont l'utilisateur doit être membre
    LDAP_GROUP="cn=mercator,ou=groups,dc=example,dc=org"

Avec TLS ou LDAPS, déclarez l'autorité de certification de l'annuaire avec `TLS_CACERT` dans `/etc/openldap/ldap.conf`.

Toutes les options LDAP et un tableau de dépannage sont décrits dans la
[documentation de configuration](https://sourcentis.github.io/mercator/fr/config/#configuration-ldap).

## Apache

Donner le projet à l'utilisateur `apache` et l'autoriser à écrire dans les répertoires `storage` et `bootstrap/cache`

    sudo chown -R apache:apache /var/www/mercator
    sudo chmod -R 775 /var/www/mercator/storage /var/www/mercator/bootstrap/cache

Restreindre ensuite à nouveau les clés de Passport : la commande précédente s'est aussi appliquée à elles, et Passport
refuse les clés lisibles par tous (l'API répond alors par une erreur 500)

    sudo chmod 600 /var/www/mercator/storage/oauth-*.key

> **Ne lancez pas `php artisan` en `root`.** Les fichiers de cache créés dans `storage/` (par exemple `storage/app/purifier`) appartiendraient alors à `root` et le serveur web ne pourrait plus y écrire (erreur *"Directory … storage/app/purifier not writable"*). Utilisez `sudo -u apache php artisan …`. Si le problème est déjà survenu, relancez les trois commandes ci-dessus.

Créer un fichier de configuration d'hôte virtuel Apache pour servir l'application Mercator :

    sudo vi /etc/httpd/conf.d/mercator.conf

Ajouter les lignes suivantes :

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

Les fichiers PHP sont exécutés par PHP-FPM via `/etc/httpd/conf.d/php.conf`. Démarrer PHP-FPM et Apache, et les
activer au démarrage :

    sudo systemctl enable --now php-fpm httpd

Ouvrir le port HTTP (et HTTPS) dans le pare-feu :

    sudo firewall-cmd --permanent --add-service=http --add-service=https
    sudo firewall-cmd --reload

### SELinux

SELinux est activé par défaut sur Red Hat. Plutôt que de le désactiver, autoriser Apache à écrire dans les répertoires
`storage` et `bootstrap/cache` de l'application :

    sudo dnf install policycoreutils-python-utils
    sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/mercator/storage(/.*)?"
    sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/mercator/bootstrap/cache(/.*)?"
    sudo restorecon -Rv /var/www/mercator

Autoriser ensuite les connexions sortantes dont Mercator a besoin :

    # Base de données (MySQL / PostgreSQL)
    sudo setsebool -P httpd_can_network_connect_db on
    # LDAP / Active Directory
    sudo setsebool -P httpd_can_connect_ldap on
    # Serveur SMTP, API NVD, Keycloak / Entra ID
    sudo setsebool -P httpd_can_network_connect on

Si l'accès est encore refusé, rechercher les opérations bloquées avec `sudo ausearch -m avc -ts recent`.

### HTTPS

Installer le module SSL d'Apache

    sudo dnf install mod_ssl

Voici le fichier de configuration pour HTTPS

```apache
<VirtualHost *:443>
    ServerName carto.exemple.com
    ServerAdmin admin@exemple.com
    DocumentRoot /var/www/mercator/public

    SSLEngine on
    SSLProtocol all -SSLv2 -SSLv3
    SSLCipherSuite HIGH:!aNULL:!MD5

    SSLCertificateFile /etc/pki/tls/certs/carto.exemple.com.crt
    SSLCertificateKeyFile /etc/pki/tls/private/carto.exemple.com.key
    SSLCertificateChainFile /etc/pki/tls/certs/CA.crt

    <Directory /var/www/mercator/public>
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog /var/log/httpd/mercator_error.log
    CustomLog /var/log/httpd/mercator_access.log combined
</VirtualHost>
```

Redémarrer Apache

    sudo systemctl restart httpd

Pour forcer la redirection en HTTPS, mettre ce paramètre dans le fichier .env (`APP_ENV=production` force aussi HTTPS
quand `APP_FORCE_HTTPS` n'est pas défini) :

    APP_FORCE_HTTPS=true

### Gestionnaire PHP-FPM explicite (facultatif)

Pour déclarer explicitement le socket PHP-FPM dans l'hôte virtuel (par exemple après avoir modifié la configuration du
pool dans `/etc/php-fpm.d/www.conf`), ajouter ce bloc dans `<VirtualHost>` :

```apache
<FilesMatch "\.php$">
    SetHandler "proxy:unix:/run/php-fpm/www.sock|fcgi://localhost/"
</FilesMatch>
```

> Le chemin du socket doit correspondre au paramètre `listen` de `/etc/php-fpm.d/www.conf` (`/run/php-fpm/www.sock`
> par défaut sur Red Hat). Un mauvais chemin fait répondre Apache par une erreur 503.

## Planificateur

Installer et démarrer cron si nécessaire

    sudo dnf install cronie
    sudo systemctl enable --now crond

Modifier la crontab de l'utilisateur `apache` (pas celle de root, voir l'avertissement de la section [Apache](#apache))

    sudo crontab -u apache -e

ajouter cette ligne dans la crontab

    * * * * * cd /var/www/mercator && php artisan schedule:run >> /dev/null 2>&1

## Problèmes

### Restaurer le mot de passe administrateur

    sudo mysql mercator -e "update users set password='$(php -r "echo password_hash('n3w-p4sSw0rD.', PASSWORD_BCRYPT, ['cost' => 10]);")' where id=1;"

### Mémoire PHP

Si vous générez de gros rapports, vous devrez augmenter la mémoire allouée à PHP dans `/etc/php.ini`

    memory_limit = 512M

puis redémarrer PHP-FPM

    sudo systemctl restart php-fpm

## Mise à jour

Avant de mettre à jour l'application, faire une sauvegarde de la base de données et du projet.

    sudo mysqldump mercator > mercator_backup.sql

ou (Postgres)

    sudo -u postgres pg_dump mercator > mercator_backup.sql

Récupérer les sources depuis GIT

    cd /var/www/mercator
    sudo -u apache git pull

Mettre à jour les librairies (`sudo` ne cherche pas dans `/usr/local/bin`, d'où le chemin complet vers Composer)

    sudo -u apache /usr/local/bin/composer install

Migrer la base de données

    sudo -u apache php artisan migrate

Vider les caches

    sudo -u apache php artisan config:clear
    sudo -u apache php artisan view:clear

## Tests de non-régression

Mercator utilise [Pest](https://pestphp.com) pour les tests de non-régression. Les tests utilisent le fichier
`.env.testing` fourni avec le projet, qui pointe vers une base dédiée `mercator_test` (les tests vident la base sur
laquelle ils s'exécutent : ne jamais utiliser la base de production). La créer avec `sudo mysql` :

    CREATE DATABASE mercator_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    GRANT ALL PRIVILEGES ON mercator_test.* TO 'mercator_user'@'localhost';

Lancer les tests

    cd /var/www/mercator
    sudo -u apache ./vendor/bin/pest

ou seulement un groupe de tests

    sudo -u apache ./vendor/bin/pest --group=api
