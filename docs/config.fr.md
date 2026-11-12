# Configuration

Ce document décrit toutes les options de configuration disponibles dans Mercator, notamment l'intégration LDAP, la prise en charge des groupes imbriqués Active Directory, la configuration de la messagerie, la mise en cache et les fonctionnalités optionnelles.

Mercator s'appuie sur des variables d'environnement (`.env`) pour configurer les fonctionnalités principales telles que l'authentification, LDAP, l'auto-provisionnement, la messagerie et la sécurité.

---

## Configuration LDAP

Mercator prend en charge à la fois l'**authentification locale** et l'**authentification LDAP**.
Les comptes locaux restent toujours disponibles comme solution de repli si cela est configuré.

### Activer / Désactiver l'authentification LDAP

```
LDAP_ENABLED=true
```

### Autoriser la connexion locale en cas d'échec de l'authentification LDAP

```
LDAP_FALLBACK_LOCAL=true
```

Valeur par défaut : `true` (s'applique si la variable est absente du `.env`).

Lorsque cette option est active, Mercator essaie le **mot de passe local** stocké dans sa base dès que l'authentification LDAP échoue, **quelle qu'en soit la raison** :

* serveur LDAP injoignable, échec du compte de service, erreur TLS
* utilisateur introuvable dans la base de recherche, ou non membre de `LDAP_GROUP`
* plusieurs entrées LDAP correspondent à l'identifiant
* **mauvais mot de passe LDAP** (l'annuaire a refusé l'authentification)

Un mot de passe local valide reste donc toujours une autre façon de se connecter, même lorsque l'annuaire rejette le mot de passe. Positionnez `LDAP_FALLBACK_LOCAL=false` si les comptes LDAP ne doivent s'authentifier qu'auprès de l'annuaire (les comptes purement locaux, comme `admin`, ne peuvent alors plus se connecter tant que LDAP est activé).

Seul cas où le mot de passe local n'est **pas** essayé : l'annuaire accepte le mot de passe, mais aucun utilisateur Mercator n'a ce `login` et l'auto-provisionnement est désactivé. La connexion est alors refusée.

#### Déroulement de la connexion

1. `LDAP_ENABLED=false` → authentification locale uniquement.
2. Recherche LDAP de l'identifiant, puis authentification avec le DN et le mot de passe de l'utilisateur.
3. Succès LDAP → connexion de l'utilisateur Mercator ayant le même `login` (créé si `LDAP_AUTO_PROVISION=true`, refusé sinon).
4. Échec LDAP → authentification locale si `LDAP_FALLBACK_LOCAL=true`, refus sinon.

### Créer automatiquement les utilisateurs Mercator depuis LDAP

Si l'utilisateur LDAP existe mais qu'aucun utilisateur Mercator correspondant n'est trouvé, Mercator peut créer automatiquement le compte local correspondant.
La correspondance se fait **uniquement** sur le champ `login` de Mercator, qui doit être identique à l'identifiant saisi sur la page de connexion.

```
LDAP_AUTO_PROVISION=true
```

Le compte local sera créé avec le rôle suivant :

```
LDAP_AUTO_PROVISION_ROLE=user
```

La valeur doit correspondre exactement au titre du rôle (sensible à la casse). Si le rôle n'existe pas, l'utilisateur est créé sans rôle et un avertissement est journalisé.
Les comptes auto-provisionnés reçoivent un mot de passe local aléatoire : ils ne peuvent pas utiliser le repli local.

---

### Paramètres de connexion LDAP

```
LDAP_HOST=ldap.example.com
LDAP_USERNAME="CN=ldap-reader,OU=Service Accounts,DC=example,DC=com"
LDAP_PASSWORD="secret"
LDAP_PORT=389
LDAP_BASE_DN="DC=example,DC=com"
LDAP_TIMEOUT=5
LDAP_SSL=false
LDAP_TLS=false
```

Ces valeurs sont transmises directement à la couche de connexion LDAPRecord de Laravel.

Les mots de passe sont vérifiés auprès du seul serveur défini dans `LDAP_HOST`. Avec Active Directory, si ce contrôleur de domaine n'a pas encore reçu un changement de mot de passe (réplication en retard ou en échec), il n'accepte que l'**ancien** mot de passe de l'utilisateur.

---

### Base de recherche des utilisateurs LDAP

Définit l'emplacement où les utilisateurs doivent être recherchés :

```
LDAP_USERS_BASE_DN="OU=Users,DC=example,DC=com"
```

Si vide, Mercator recherche à partir de `LDAP_BASE_DN`.

---

### Attributs de connexion LDAP

Définit les attributs LDAP pouvant être utilisés comme identifiant de connexion :

```
LDAP_LOGIN_ATTRIBUTES=sAMAccountName,uid,mail
```

Valeur par défaut : `uid,cn,mail,sAMAccountName,userPrincipalName`.

Mercator essaiera ces attributs avec un filtre OR. Une **seule** entrée LDAP doit correspondre : si plusieurs entrées correspondent (par exemple un utilisateur et un contact partageant le même `mail` ou `cn`), la connexion est refusée. Gardez cette liste aussi courte que possible (par exemple `sAMAccountName` sur Active Directory).

---

### Restriction par groupe LDAP

Il est possible de restreindre l'accès à Mercator aux membres d'un groupe LDAP spécifique.

```
LDAP_GROUP="CN=Mercator-Users,OU=Groups,DC=example,DC=com"
```

Si vide, tous les utilisateurs authentifiés par LDAP peuvent se connecter.

---

## Prise en charge des groupes imbriqués (Active Directory uniquement)

Mercator peut vérifier l'appartenance récursive (groupes imbriqués) lors de l'utilisation de **Microsoft Active Directory**.

Cette fonctionnalité est désactivée par défaut.

### Activer la recherche de groupes imbriqués

```
LDAP_NESTED_GROUPS=true
```

### Fonctionnement

Lorsque cette option est activée, Mercator utilise la règle de correspondance spécifique à AD :

```
1.2.840.113556.1.4.1941   (LDAP_MATCHING_RULE_IN_CHAIN)
```

Exemple de filtre LDAP généré :

```
(memberOf:1.2.840.113556.1.4.1941:=CN=Mercator-Users,OU=Groups,DC=example,DC=com)
```

Cela permet de reconnaître :

* Les membres directs
* Les membres indirects
* Les groupes profondément imbriqués (multi-niveaux)

### Limitations importantes

* **Pris en charge uniquement sur Microsoft Active Directory**
* Ne fonctionnera **pas** sur OpenLDAP ou d'autres serveurs LDAP
* Si activé sur des systèmes non-AD, l'authentification LDAP échouera

---

## Configuration de la messagerie

Mercator envoie des notifications, des réinitialisations de mot de passe (pour les comptes locaux) et des e-mails système.

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=mailer@example.com
MAIL_PASSWORD=secret
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Mercator"
```

---

## Paramètres applicatifs

### Nom de l'application

Ce nom est affiché dans le coin supérieur gauche de chaque page de l'application.

```
APP_NAME=Mercator
```

### Environnement de l'instance Mercator

Permet de préciser le type de l'instance Mercator : Production, Développement, Intégration, Pré-production, Prototype, Maquette…

```
APP_ENV=Production
```

📢 *Remarque : `APP_ENV=Production` est obligatoire pour que le HTTPS fonctionne.*

### Limite du taux d'appels API

Limite les requêtes API pour protéger les ressources du serveur.
Format : `API_RATE_LIMIT` requêtes par `API_RATE_LIMIT_DECAY` minute(s).

```
    60,1       = 60 req/min    (défaut - usage normal)
    120,1      = 120 req/min   (développement/tests)
    1000,60    = 1000 req/h    (API publique)
    10000,1440 = 10000 req/j   (intégrations tierces)
```

Retourne HTTP 429 (Too Many Requests) en cas de dépassement.

```
API_RATE_LIMIT=60
API_RATE_LIMIT_DECAY=1
```

### URL de l'application

```
APP_URL=https://mercator.example.com
```

Utilisée dans les liens, les notifications, les URL d'export, etc.

### Mode débogage

```
APP_DEBUG=false
```

Lorsqu'il est activé, les erreurs sont affichées à l'écran. **Ne pas activer en production.**

### Durée de session

```
SESSION_LIFETIME=120
```

### Content Security Policy

```
CSP_MODE=enforce
```

Mercator envoie un en-tête Content Security Policy (CSP) qui n'autorise que les scripts de l'application, porteurs d'un nonce renouvelé à chaque requête. Un script injecté dans une page (attaque XSS) n'est donc pas exécuté par le navigateur.

| Valeur | Effet |
|--------|-------|
| `enforce` (défaut) | La politique est appliquée (`Content-Security-Policy`) : tout ce qui n'est pas autorisé est bloqué |
| `report-only` | La politique est seulement observée (`Content-Security-Policy-Report-Only`) : rien n'est bloqué, les violations sont journalisées dans `storage/logs/security-AAAA-MM-JJ.log` |
| `off` | Aucun en-tête CSP |

Toute autre valeur désactive la CSP, comme `off`. Les en-têtes `X-Content-Type-Options`, `X-Frame-Options` et `Referrer-Policy` sont envoyés dans tous les cas.

Si une page ne fonctionne plus correctement (bouton sans effet, graphique absent), passer temporairement en `report-only` : l'application refonctionne immédiatement et le journal `security` indique la ressource bloquée, à signaler pour correction.

Le mode en vigueur est affiché dans la [page d'information](#page-dinformation).

---

## Journalisation

Mercator utilise le système de journalisation de Laravel. Les journaux de l'application sont écrits dans :

```
storage/logs/laravel.log
```

Tous les niveaux (y compris `debug`) sont déjà écrits dans ce fichier : il n'y a pas de niveau à augmenter.

### Diagnostic des connexions

Chaque tentative de connexion est tracée dans `laravel.log` avec des messages préfixés par `[auth]`, l'identifiant saisi et l'adresse IP du client (jamais le mot de passe) :

| Message | Signification |
|---------|---------------|
| `Login succeeded.` | Connexion acceptée (rôles et nombre de permissions) |
| `Login succeeded but user has no role.` | Connexion acceptée, mais toutes les pages répondront 403 |
| `LDAP user not found (or not member of LDAP_GROUP).` | Aucune entrée trouvée : vérifier la base de recherche, le filtre et le groupe |
| `LDAP identifier collision: multiple entries match.` | Plusieurs entrées correspondent : les DN sont listés |
| `LDAP bind refused for user.` | L'annuaire a refusé le mot de passe (voir `ad_reason` et `host`) |
| `LDAP service bind / connection failed.` | Serveur injoignable, erreur TLS ou compte de service (`LDAP_USERNAME`) refusé |
| `LDAP OK but no local user with this login.` | Annuaire OK mais aucun utilisateur Mercator avec ce `login` |
| `Login refused: local authentication failed.` | Échec du repli local (`unknown login` ou `wrong local password`) |
| `Login locked out: too many attempts.` | Trop de tentatives pour cet identifiant depuis cette IP |

Pour Active Directory, `ad_reason` décode le sous-code du message de diagnostic : `52e` identifiants invalides, `525` utilisateur introuvable, `530`/`531` connexion non autorisée (horaire/poste), `532` mot de passe expiré, `533` compte désactivé, `701` compte expiré, `773` changement de mot de passe obligatoire, `775` compte verrouillé.

### Journalisation LDAPRecord

Pour tracer également toutes les opérations LDAP (recherche, authentification) :

```
LDAP_LOGGING=true
```

Les journaux apparaîtront dans :

```
storage/logs/ldap.log
```

Si aucun fichier n'apparaît, vérifiez les permissions du répertoire.

📢 *Note : si la configuration est en cache (`php artisan config:cache`), les modifications du `.env` ne s'appliquent qu'après `php artisan config:clear` (ou un nouveau `config:cache`).*

## Page d'information

La page d'information rassemble la configuration de l'instance et la situation de l'utilisateur connecté. Elle est accessible à tout utilisateur via :

> **Aide → Informations** (`/admin/doc/info`)

Elle permet de vérifier qu'un réglage du `.env` est bien pris en compte, de comprendre pourquoi un utilisateur ne voit pas un objet ou n'a pas un droit, et de transmettre ces éléments au support.

### Mercator

| Ligne | Contenu |
|-------|---------|
| Version | Version de Mercator (`version.txt`) |
| Environnement | Valeur de `APP_ENV` |
| Fuseau horaire / Langue | Fuseau et langue par défaut de l'application |
| Date / heure | Heure du serveur, utile pour vérifier le fuseau |
| Base de données / Version du serveur BD | Pilote utilisé et version du serveur de base de données |
| PHP / Laravel | Versions de PHP et du framework |
| Méthodes d'authentification | `Local`, `LDAP` et/ou `Keycloak`, selon la configuration |
| Pilotes | Pilotes de cache, de file d'attente et d'envoi de courriels |
| Mémoire PHP | Mémoire utilisée par la requête, limite `memory_limit` et mémoire disponible |
| Content Security Policy (CSP_MODE) | Mode CSP en vigueur (voir [Content Security Policy](#content-security-policy)) |

La ligne Content Security Policy affiche un badge de couleur :

| Badge | Signification |
|-------|---------------|
| 🟢 `enforce` | Politique appliquée |
| 🟠 `report-only` | Observation : violations journalisées sans blocage |
| 🔴 `off` ou autre valeur | CSP désactivée |

Une valeur mal saisie dans le `.env` (par exemple `CSP_MODE=enforced`) apparaît en rouge : elle désactive réellement la CSP.

### Utilisateur

Identifiant, nom, adresse électronique, langue et granularité de l'utilisateur connecté, ainsi que ses rôles et s'il est [cartographe](cartographers.md) (avec les types d'objets qui lui sont attribués). Lorsque les [périmètres](perimeters.md) sont activés, la carte indique aussi ses périmètres et le périmètre actif.

### Périmètres

Affiché uniquement lorsque les périmètres sont activés. Pour chaque périmètre de l'utilisateur, le tableau donne ses rôles dans ce périmètre, le nombre d'objets qu'il peut y consulter et, pour chaque droit (Lecture, Création, Modification, Suppression), le nombre de types d'objets concernés. La ligne du périmètre actif est mise en évidence.

### Objets cartographiés

Nombre d'objets visibles depuis le périmètre actif, par vue de la cartographie. Le bouton **Droits par type d'objet** déplie le détail : pour chaque type d'objet, le nombre d'objets visibles et les droits Lecture / Création / Modification / Suppression, par périmètre. Un administrateur dispose de tous les droits dans tous les périmètres.

### Copier pour le helpdesk

Le bouton **Copier pour le helpdesk** place dans le presse-papiers un résumé texte de la page (configuration, utilisateur, périmètres, nombre d'objets visibles), prêt à coller dans une demande de support.

📢 *Remarque : ce résumé contient l'identifiant et l'adresse électronique de l'utilisateur. Le relire avant de l'envoyer à l'extérieur de l'organisation.*

📢 *Remarque : la page affiche la configuration effectivement chargée. Si la configuration est en cache, une modification du `.env` n'y apparaît qu'après `php artisan config:clear`.*

---

## Export / Import de fichiers

Mercator prend en charge l'export Excel et PDF via :

* `maatwebsite/excel`
* `phpoffice/phpword`

Assurez-vous que `storage/` et `bootstrap/cache/` sont accessibles en écriture.

---

## Configuration Docker

### Surcharger les variables d'environnement dans `docker-compose.yml`

```yaml
environment:
  - LDAP_ENABLED=true
  - LDAP_HOST=ad.example.com
  - LDAP_GROUP=CN=Mercator-Users,OU=Groups,DC=example,DC=com
  - LDAP_NESTED_GROUPS=true
```

### Volumes requis

```yaml
volumes:
  - ./storage:/var/www/mercator/storage
  - ./bootstrap/cache:/var/www/mercator/bootstrap/cache
```

---

## Conseils pour le déploiement en production

* Désactiver `APP_DEBUG`
* Activer HTTPS
* Laisser la Content Security Policy appliquée (`CSP_MODE=enforce`)
* Utiliser un reverse proxy (Traefik, Nginx)
* Configurer des sauvegardes automatiques de la base de données
* Protéger `.env` et `storage/` avec les permissions appropriées
* N'utiliser les groupes imbriqués LDAP que sur **Active Directory**

---

## Tableau récapitulatif

| Fonctionnalité | Variable | Défaut | Notes |
|----------------|----------|--------|-------|
| Activer LDAP | `LDAP_ENABLED` | false | Active la connexion LDAP |
| Repli local | `LDAP_FALLBACK_LOCAL` | true | Essaie le mot de passe local dès que LDAP échoue, y compris sur un mauvais mot de passe LDAP |
| Auto-provisionnement | `LDAP_AUTO_PROVISION` | false | Crée l'utilisateur en base lors de la première connexion LDAP |
| Rôle auto-provisionnement | `LDAP_AUTO_PROVISION_ROLE` | null | Rôle attribué aux utilisateurs nouvellement créés |
| Serveur LDAP | `LDAP_HOST` | ldap.example.com | Pour la connexion au serveur LDAP |
| Utilisateur LDAP | `LDAP_USERNAME` | CN=ldap-reader,… | Pour la connexion au serveur LDAP |
| Mot de passe LDAP | `LDAP_PASSWORD` | secret | Pour la connexion au serveur LDAP |
| Port du serveur LDAP | `LDAP_PORT` | 389 | Pour la connexion au serveur LDAP |
| Chiffrement SSL | `LDAP_SSL` | false | Pour la connexion au serveur LDAP |
| Chiffrement TLS | `LDAP_TLS` | false | Pour la connexion au serveur LDAP |
| Base DN LDAP | `LDAP_BASE_DN` | dc=local,dc=com | Base de recherche par défaut |
| Délai LDAP | `LDAP_TIMEOUT` | 5 | Délai de connexion en secondes |
| Groupes imbriqués | `LDAP_NESTED_GROUPS` | false | AD uniquement |
| Groupe requis | `LDAP_GROUP` | "" | Restreint la connexion |
| Base de recherche LDAP | `LDAP_USERS_BASE_DN` | "" | Base de recherche |
| Attributs de connexion | `LDAP_LOGIN_ATTRIBUTES` | `uid,cn,mail,sAMAccountName,userPrincipalName` | Liste séparée par des virgules |
| Journalisation LDAP | `LDAP_LOGGING` | false | Écrit dans `ldap.log` |
| Limite d'appels API | `API_RATE_LIMIT` | 60 | Nombre de requêtes API autorisées dans la fenêtre de temps |
| Intervalle de limite API | `API_RATE_LIMIT_DECAY` | 1 | Durée en minutes de la fenêtre de limite de taux |
| Durée de session | `SESSION_LIFETIME` | 120 | Durée de session en minutes |
| Mode débogage | `APP_DEBUG` | false | Active ou désactive le mode débogage |
| URL de l'application | `APP_URL` | https://mercator.example.com | URL de base utilisée dans les liens et les exports |
| Content Security Policy | `CSP_MODE` | enforce | `enforce`, `report-only` ou `off` |
| Environnement de l'instance | `APP_ENV` | Production | Définit l'environnement de cette instance Mercator |
| Nom de l'application | `APP_NAME` | Mercator | Nom affiché, utile pour différencier les instances |
| Type de messagerie | `MAIL_MAILER` | smtp | Connexion au serveur de messagerie |
| Serveur de messagerie | `MAIL_HOST` | smtp.example.com | Connexion au serveur de messagerie |
| Port du serveur de messagerie | `MAIL_PORT` | 587 | Connexion au serveur de messagerie |
| Adresse de messagerie | `MAIL_USERNAME` | mailer@example.com | Connexion au serveur de messagerie |
| Mot de passe de messagerie | `MAIL_PASSWORD` | secret | Connexion au serveur de messagerie |
| Type de chiffrement | `MAIL_ENCRYPTION` | tls | Connexion au serveur de messagerie |
| Adresse d'expéditeur | `MAIL_FROM_ADDRESS` | noreply@example.com | Connexion au serveur de messagerie |
| Nom de l'expéditeur | `MAIL_FROM_NAME` | Mercator | Connexion au serveur de messagerie |