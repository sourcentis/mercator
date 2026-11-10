# Procédure d'installation de Mercator

## Configuration recommandée

- OS : Ubuntu 24.04 LTS - Server - Small user footprint
- RAM : 2G
- Disque : 10G
- VCPU 2

## Installation

Mettre à jour la distribution linux

    sudo apt update && sudo apt upgrade

Installer Apache2, GIT, Graphviz et Composer

    sudo apt install vim apache2 git graphviz composer

Installer PHP et les librairies

> **Prérequis** : Mercator nécessite PHP **8.4.x** (>= 8.4.1). **PHP 8.5 n'est pas encore supporté** : la
> dépendance `phpoffice/phpspreadsheet` (utilisée pour les exports Excel) restreint son propre `composer.json` à
> `php >=7.4.0 <8.5.0`, ce qui fait échouer `composer install` sous PHP 8.5. Les dépôts officiels d'Ubuntu 24.04 ne
> fournissent que PHP **8.3**, ce qui est également insuffisant. Il faut donc ajouter le dépôt PPA `ondrej/php`, qui
> fournit PHP 8.4 :
>
>     sudo apt install software-properties-common
>     sudo add-apt-repository ppa:ondrej/php
>     sudo apt update

Installez ensuite PHP 8.4 et les extensions requises, **en utilisant systématiquement le numéro de version dans le
nom des paquets** (`php8.4-xxx`), et non les paquets non versionnés (`php-xxx`), qui pointent vers la version par
défaut d'Ubuntu (8.3) et non vers celle installée depuis le PPA :

    sudo apt install php8.4 php8.4-zip php8.4-curl php8.4-mbstring php8.4-xml php8.4-ldap php8.4-soap php8.4-xdebug php8.4-mysql php8.4-gd php8.4-intl libapache2-mod-php8.4

> Utilisez bien la version **8.4** ci-dessus, pas 8.5 (voir l'encadré précédent). Attention à bien installer *tous*
> les paquets d'extensions versionnés correspondants : une installation "nue" du seul paquet `php8.4` (sans
> `php8.4-curl`, `php8.4-xml` qui fournit `ext-dom`/`ext-simplexml`, `php8.4-ldap`, `php8.4-zip`, etc.) fait échouer
> `composer install` avec des erreurs *"... requires PHP extension ext-curl / ext-dom / ext-ldap / ... but it is
> missing from your system"*.

Si plusieurs versions de PHP sont installées côte à côte, sélectionnez la version par défaut utilisée en ligne de
commande (CLI) :

    sudo update-alternatives --config php

Cette commande n'a **aucun effet sur la version utilisée par Apache** — voir la section [Apache](#apache) ci-dessous
en cas d'erreur du type *"Composer detected issues in your platform: Your Composer dependencies require a PHP
version >= 8.4.1"* affichée par le serveur web.

## Project

Créer le répertoire du projet

    cd /var/www
    sudo mkdir mercator
    sudo chown $USER:$GROUP mercator

Cloner le projet depuis Github

    git clone https://www.github.com/dbarzin/mercator

## Composer

Installer les packages avec composer :

    cd /var/www/mercator
    composer install

## MySQL

Installer MySQL

    sudo apt install mysql-server

Lancer MySQL avec les droits root

    sudo mysql

Créer la base de données _mercator_ et l'utilisateur _mercator_user_

    CREATE DATABASE mercator CHARACTER SET utf8 COLLATE utf8_general_ci;
    CREATE USER 'mercator_user'@'localhost' IDENTIFIED BY 's3cr3t';
    GRANT ALL PRIVILEGES ON mercator.* TO 'mercator_user'@'localhost';
    GRANT PROCESS ON *.* TO 'mercator_user'@'localhost';

    FLUSH PRIVILEGES;

Le chargement initial du schéma (`php artisan migrate --seed`, voir plus bas) crée quelques fonctions SQL
(`rand_person`, `rand_short`...). Si la journalisation binaire (`log_bin`) est activée sur votre serveur MySQL —
c'est le cas par défaut sur le paquet `mysql-server` d'Ubuntu — leur création échoue avec l'erreur *"You do not
have the SUPER privilege and binary logging is enabled"*, car `mercator_user` n'a pas le privilège SUPER. Plutôt
que d'accorder ce privilège élevé à l'utilisateur applicatif, autorisez la création de fonctions depuis la même
session `mysql` :

    SET GLOBAL log_bin_trust_function_creators = 1;
    EXIT;

> Ce réglage n'est pas persistant après un redémarrage de MySQL. Si vous devez recharger le schéma plus tard
> (réinstallation, `migrate:fresh`...), ajoutez `log_bin_trust_function_creators=1` sous `[mysqld]` dans
> `/etc/mysql/mysql.conf.d/mysqld.cnf` puis redémarrez MySQL (`sudo systemctl restart mysql`) pour le rendre
> permanent.

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

    sudo mysql mercator < database/data/mercator_data_fr.sql

## Base de données CPE (facultatif)

Une commande Artisan **`mercator:cpe-sync`** **synchronise les CPE (Common Platform Enumeration)** depuis la NVD (
National
Vulnerability Database) et alimente les tables
`cpe_vendors`, `cpe_products` et `cpe_versions`.

### Planification

La commande est **planifiée quotidiennement à 3h30** via le planificateur de Laravel.
➡️ Si votre **planificateur Laravel est déjà configuré** (exécution de `php artisan schedule:run` par cron), **aucune
action n'est requise**. La synchronisation se fera automatiquement.

### Configuration

Ajoutez les paramètres suivants à votre fichier `.env`:

```dotenv
# Point de terminaison de l'API NVD CPE 2.0
CPE_API_URL=https://services.nvd.nist.gov/rest/json/cpes/2.0

# (Facultatif mais recommandé) Clé API NVD pour bénéficier de quotas de requêtes plus élevés
NVD_API_KEY=
```

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

    sudo chown www-data:www-data storage/oauth-*.key
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

N'oubliez pas de [configurer](https://dbarzin.github.io/deming/config.fr/#notifications) le contenu et la fréquence
d'envoi des mails.

## Scheduler

Modifier le crontab

    sudo crontab -e

ajouter cette ligne dans le crontab

    * * * * * cd /var/www/mercator && php artisan schedule:run >> /dev/null 2>&1

---

## Configuration LDAP / LDAPRecord (optionnelle)

Cette section permet d’activer l’authentification LDAP dans **Deming** en utilisant **LDAPRecord v2**.
Elle fonctionne avec **Active Directory** *et* **OpenLDAP**, et peut coexister avec l’authentification locale (base de
données).

### Prérequis

L’extension PHP LDAP doit être installée et activée.

```bash
sudo apt-get install php-ldap
sudo systemctl restart apache2
```

### Variables d’environnement

Ajoutez ou ajustez les variables suivantes :

```dotenv
# Activer LDAP dans Deming (mode hybride)
LDAP_ENABLED=true                 # Active l’authentification LDAP
LDAP_FALLBACK_LOCAL=true          # Si LDAP échoue, on tente l’auth DB locale
LDAP_AUTO_PROVISION=false         # Création automatique de l’utilisateur local après un bind LDAP réussi

# Connexion au serveur LDAP
LDAP_HOST=ldap.example.org
LDAP_PORT=389                     # 389 (StartTLS) ou 636 (LDAPS)
LDAP_BASE_DN=dc=example,dc=org
LDAP_USERNAME=cn=admin,dc=example,dc=org   # Compte de service utilisé pour les recherches
LDAP_PASSWORD=********
LDAP_USERS_BASE_DN=ou=people,dc=example,dc=org 
LDAP_TLS=true                     # StartTLS (recommandé avec le port 389)
LDAP_SSL=false                    # true si vous utilisez ldaps:// sur le port 636
LDAP_TIMEOUT=5                    # (optionnel)
LDAP_GROUP=mercator               # LDAP group to which the user must belong to access the application

# Attributs candidats pour identifier l’utilisateur saisi dans le formulaire
# L’ordre est important : le premier qui correspond est utilisé.
# OpenLDAP : uid, cn, mail
# Active Directory : sAMAccountName, userPrincipalName, mail
LDAP_LOGIN_ATTRIBUTES=uid,cn,mail,sAMAccountName,userPrincipalName
```

# L’utilisateur doit être membre de ce groupe LDAP

LDAP_GROUP=Mercator

---

### Exemples

* **OpenLDAP** (DN typique d’un utilisateur : `uid=jdupont,ou=people,dc=example,dc=org`) :

  ```dotenv
  LDAP_TLS=true
  LDAP_SSL=false
  LDAP_LOGIN_ATTRIBUTES=uid,cn,mail
  LDAP_GROUP=Mercator
  ```

* **Active Directory** (UPN : `jdupont@example.org`, sAMAccountName : `jdupont`) :

  ```dotenv
  LDAP_TLS=true
  LDAP_SSL=false
  LDAP_LOGIN_ATTRIBUTES=sAMAccountName,userPrincipalName,mail,cn
  LDAP_USERNAME=EXAMPLE\svc_ldap   # ou le DN complet du compte de service
  ```

---

Après modification du fichier `.env` :

```bash
php artisan config:clear
php artisan optimize:clear
```

---

## Configuration de Keycloak (optionel)

Pour configurer Keycloak, suivez ces étapes :

- Ouvrez votre fichier .env.
- Décommentez et modifiez les paramètres de configuration de Keycloak comme suit :

```
KEYCLOAK = enable
KEYCLOAK_CLIENT_ID= # Client Name (on Keycloak)
KEYCLOAK_CLIENT_SECRET=  # Client Secret
KEYCLOAK_REDIRECT_URI=<Mercator IP Address>/login/keycloak/callback
KEYCLOAK_BASE_URL=<KeyCloak IP Address>
KEYCLOAK_REALM=   # RealM Name
KEYCLOAK_AUTO_PROVISIONNING= # active/désactive la création automatique d'utilisateurs (valeurs possibles : true/false. 'true' par défaut)
KEYCLOAK_BTN_LABEL= # défini le libellé du bouton de connexion Keycloak ('Keycloak' par défaut)
```

Après avoir défini KEYCLOAK sur enable, un bouton apparaîtra sur la page de connexion, permettant aux utilisateurs de se
connecter via Keycloak.

Pour une documentation plus complète sur la configuration de Keycloak, consultez la documentation officielle de
Keycloak.

## Apache

> **Attention aux versions multiples de PHP** : le module `libapache2-mod-php` installé plus haut se lie à une seule
> version de PHP à la fois, indépendamment de la version sélectionnée en CLI avec `update-alternatives`. Si plusieurs
> versions de PHP cohabitent sur le serveur (par exemple parce que la distribution en installe une par défaut en plus
> de celle que vous avez ajoutée), Apache peut continuer à utiliser l'ancien module et afficher une erreur du type
> *"Composer detected issues in your platform: Your Composer dependencies require a PHP version >= 8.4.1"*, suivie
> d'une erreur HTTP 500 sur l'application. Vérifiez le module actif avec `apache2ctl -M | grep php`, puis désactivez
> le mauvais module et activez le bon avec `sudo a2dismod phpX.Y` / `sudo a2enmod phpX.Z` avant de redémarrer Apache.
> Pour éviter ce type d'ambiguïté, préférez la [variante PHP-FPM](#variante-php-fpm-avec-apache) décrite plus bas.

Pour configurer Apache, modifiez les propriétés du répertoire mercator et accordez les autorisations appropriées au
répertoire de stockage avec la commande suivante

    sudo chown -R www-data:www-data /var/www/mercator
    sudo chmod -R 775 /var/www/mercator/storage

> **Ne lancez pas `php artisan` en `root`.** Les fichiers de cache créés dans `storage/` (par exemple `storage/app/purifier`) appartiendraient alors à `root` et le serveur web ne pourrait plus y écrire (erreur *"Directory … storage/app/purifier not writable"*). Utilisez `sudo -u www-data php artisan …`. Si le problème est déjà survenu, relancez les deux commandes ci-dessus.

Ensuite, créez un nouveau fichier de configuration d'hôte virtuel Apache pour servir l'application Mercator :

    sudo vi /etc/apache2/sites-available/mercator.conf

Ajouter les lignes suivantes :

```xml

<VirtualHost *:80>
        ServerName mercator.local
        ServerAdmin admin@example.com
        DocumentRoot /var/www/mercator/public
<Directory /var/www/mercator>
        AllowOverride All
        </Directory>
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
        </VirtualHost>
```

Enregistrez et fermez le fichier lorsque vous avez terminé. Ensuite, activez l'hôte virtuel Apache et le module de
réécriture avec la commande suivante:

    sudo a2enmod rewrite
    sudo a2dissite 000-default.conf
    sudo a2ensite mercator.conf

Enfin, redémarrez le service Apache pour activer les modifications:

    sudo systemctl restart apache2

### HTTPS

Activer le module SSL d'Apache

    sudo a2enmod ssl

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
        ErrorLog ${APACHE_LOG_DIR}/mercator_error.log
        CustomLog ${APACHE_LOG_DIR}/mercator_access.log combined
        </VirtualHost>
```

Pour forcer le redirection en HTTPS, il faut mettre ce paramètre dans le fichier .env :

    APP_ENV=production

### Variante PHP-FPM avec Apache

Cette méthode permet de faire tourner Mercator avec PHP-FPM (FastCGI Process Manager) et le module `mpm_event` d’Apache,
pour de meilleures performances que l’utilisation classique avec `mod_php`.

#### 1. Installer PHP-FPM

```bash
sudo apt install php-fpm
```

#### 2. Configurer Apache

Désactiver les modules incompatibles (notamment `mod_php` et `mpm_prefork`) :

```bash
sudo a2dismod mpm_prefork phpX.Y
```

> Remplace `phpX.Y` par la version installée (ex. `php8.2`).

Activer les modules nécessaires pour le fonctionnement avec PHP-FPM :

```bash
sudo a2enmod proxy_fcgi mpm_event
```

#### 3. Vérifier ou ajuster la configuration du module `mpm_event`

Édite le fichier `/etc/apache2/mods-enabled/mpm_event.conf` pour ajuster les paramètres selon la charge de ton serveur.
Consulte la documentation officielle pour les détails :  
<https://httpd.apache.org/docs/current/fr/mod/event.html>

#### 4. Exemple de VirtualHost HTTP

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

    ErrorLog ${APACHE_LOG_DIR}/mercator_error.log
    CustomLog ${APACHE_LOG_DIR}/mercator_access.log combined
</VirtualHost>
```

#### 5. Exemple de VirtualHost HTTPS

```apache
<VirtualHost *:443>
    ServerName carto.example.com
    ServerAdmin admin@example.com

    DocumentRoot /var/www/mercator/public

    SSLEngine on
    SSLProtocol all -SSLv2 -SSLv3
    SSLCipherSuite HIGH:!aNULL:!MD5

    SSLCertificateFile /etc/apache2/certs/carto.example.com.crt
    SSLCertificateKeyFile /etc/apache2/certs/private.key
    SSLCertificateChainFile /etc/apache2/certs/CA.crt

    <Directory /var/www/mercator/public>
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php-fpm.sock|fcgi://localhost/"
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/mercator_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/mercator_ssl_access.log combined
</VirtualHost>
```

> **Remarques :**
> - Adapte les chemins des certificats SSL à ton environnement.
> - Vérifie que le socket PHP-FPM correspond bien au chemin `/run/php/php-fpm.sock` (à adapter selon ta version de PHP,
    ex. `/run/php/php8.2-fpm.sock`).

## Problèmes

### Restaurer le mot de passe administrateur

    mysql mercator -e "update users set password=\"$(php -r "echo password_hash('n3w-p4sSw0rD.', PASSWORD_BCRYPT, ['cost' => 10]);")\" where id=1;"

### PHP Memory

Si vous générez de gros rapports, vous devrez mettre augmenter la mémoire allouée à PHP dans
/etc/php/8.x/apache2/php.ini

    memory_limit = 512M

## Mise à jour

Avant de mettre à jour l'application prenez un backup de la base de données et du projet.

    mysqldump mercator > mercator_backup.sql

ou (Postgres)

    pg_dump mercator > mercator_backup.sql

Récupérer les sources de GIT

    cd /var/www/mercator
    sudo -u www-data git pull

Installer les librairies PHP

    sudo -u www-data composer install

Migrer la base de données

    sudo -u www-data php artisan migrate

Vider les caches

    sudo -u www-data php artisan config:clear &&  php artisan view:clear

## 🧪 Tests de non-régression

Mercator utilise **[Pest](https://pestphp.com)** pour les tests de non-régression.

Ces tests garantissent que les fonctionnalités de l'application continuent de fonctionner comme prévu après les mises à
jour ou les refactorisations.

### Configurer l'environnement

Créer un fichier d'environnement de test dédié :

```bash
cp .env .env.testing
```

Mettre à jour la configuration de la base de données dans `.env.testing`, par exemple :

```env
APP_ENV=testing
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### Exécuter la suite de tests

Exécuter tous les tests non régressifs avec Pest :

```bash
php artisan test
```

ou directement :

```bash
./vendor/bin/pest
```

### Arrêter à la première erreur

Pour arrêter l'exécution après la première erreur ou le premier échec :

```bash
./vendor/bin/pest --stop-on-failure
```

### Exécuter un fichier de test ou un groupe de tests spécifique

Vous pouvez cibler un test ou un répertoire spécifique :

```bash
./vendor/bin/pest tests/Feature/Api
```

ou utiliser des groupes/étiquettes :

```bash
./vendor/bin/pest --group=api
```

---

✅ **Remarques**

* Chromium et Laravel Dusk ne sont **plus nécessaires**.

* Tous les tests sont désormais gérés par **Pest** avec les utilitaires de test intégrés de Laravel.

* Les rapports de test et la couverture peuvent être générés avec :

```bash
XDEBUG_MODE=coverage ./vendor/bin/pest --coverage
```
