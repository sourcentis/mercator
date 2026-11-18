# Vulnérabilités

Mercator intègre un moteur de détection de vulnérabilités basé sur [Vulnerability-Lookup](https://vulnerability.circl.lu/) et le référentiel [NVD](https://nvd.nist.gov/) (National Vulnerability Database). Dès lors que les champs `vendor` et `product` sont renseignés sur un actif, Mercator peut identifier les CVE connues associées à ce composant et les présenter avec leur score de criticité CVSS.

Ce document décrit l'architecture de la fonctionnalité, sa configuration, son initialisation et son utilisation au quotidien.

## Concepts

Trois standards forment la base du système de détection :

| Terme | Signification | Exemple |
|-------|--------------|---------|
| **CPE** | *Common Platform Enumeration* — dictionnaire standardisé des logiciels, OS et matériels connus | `cpe:2.3:a:apache:log4j:2.14.1:*:*:*:*:*:*:*` |
| **CVE** | *Common Vulnerabilities and Exposures* — liste publique des failles de sécurité connues | `CVE-2021-44228` (Log4Shell) |
| **NVD** | *National Vulnerability Database* — base américaine qui relie CPE ↔ CVE | Source de données interrogée par Mercator |

Le flux de fonctionnement est le suivant :

```
Inventaire Mercator
  └─> vendor + product + version renseignés sur un actif
        └─> Mercator recherche le CPE correspondant dans sa base locale
              └─> Mercator interroge le NVD pour les CVE associées à ce CPE
```

## Architecture

Mercator stocke les données CPE dans trois tables dédiées :

| Table | Contenu |
|-------|---------|
| `cpe_vendors` | Éditeurs et fabricants (ex : `apache`, `microsoft`, `cisco`) |
| `cpe_products` | Produits par éditeur (ex : `log4j`, `windows_10`) |
| `cpe_versions` | Versions de chaque produit |

Ces tables sont alimentées par la commande `mercator:cpe-sync`.

### Actifs supportant les champs CPE

Les champs `vendor`, `product` et `version` sont disponibles sur les types d'actifs suivants :

| Vue | Objet | Disponible dans l'UI |
|-----|-------|---------------------|
| Applications | Application | ✅ |
| Applications | Module applicatif | ✅ |
| Infrastructure logique | Équipement de sécurité (logique) | ✅ |
| Infrastructure physique | Périphérique | ✅ |
| Infrastructure physique | Serveur physique | ⚠️ présent en base, non affiché |
| Infrastructure physique | Poste de travail | ⚠️ présent en base, non affiché |
| Infrastructure physique | Commutateur physique | ⚠️ présent en base, non affiché |
| Infrastructure physique | Routeur physique | ⚠️ présent en base, non affiché |
| Infrastructure physique | Borne WiFi | ⚠️ présent en base, non affiché |
| Infrastructure physique | Téléphone | ⚠️ présent en base, non affiché |
| Infrastructure physique | Infra de stockage | ⚠️ présent en base, non affiché |

> Pour les actifs dont les champs ne sont pas encore visibles dans l'interface, ils restent accessibles via l'API REST ou par import JSON.


## Saisie des champs vendor / product / version

La détection de vulnérabilités repose entièrement sur la qualité des données saisies. Sans les trois champs `vendor`, `product` et `version` correctement renseignés, aucune recherche CVE ne peut aboutir.

### Conventions de nommage CPE

Les valeurs doivent correspondre exactement aux identifiants utilisés dans le NVD : **minuscules**, **underscores** à la place des espaces.

| Logiciel | `vendor` | `product` | `version` |
|----------|----------|-----------|-----------|
| Apache Log4j 2.14 | `apache` | `log4j` | `2.14.1` |
| Microsoft Windows 11 | `microsoft` | `windows_11` | `21h2` |
| Cisco IOS 15.4 | `cisco` | `ios` | `15.4` |
| SAP S/4HANA 2022 | `sap` | `s\/4hana` | `2022` |
| Ubuntu 22.04 | `canonical` | `ubuntu_linux` | `22.04` |
| Docker 24.0.5 | `docker` | `docker` | `24.0.5` |

En cas de doute sur les identifiants exacts, utiliser la fonction **Guess** (voir section suivante).

### Saisie dans l'interface

**Pour une Application :**  
`Applications` → `Applications` → ouvrir ou créer la fiche → renseigner `Vendor`, `Product`, `Version`.

**Pour un Module applicatif :**  
`Applications` → `Modules applicatifs` → même procédure.

### Import via l'API (JSON)

```json
{
  "name": "Apache Guacamole",
  "technology": "apache",
  "vendor": "apache",
  "product": "guacamole",
  "version": "1.5.4"
}
```

### Recherche

La recherche de vulnérabilités s'appuie sur [Vulnerability-Lookup](https://vulnerability.circl.lu/),  le service open source développé par [CIRCL](https://www.circl.lu/) (Computer Incident Response Center Luxembourg) et disponible sur
[GitHub](https://github.com/vulnerability-lookup/vulnerability-lookup). 

Contrairement à une recherche limitée au NVD/NIST, Vulnerability-Lookup agrège de multiples sources : GitHub Advisory Database, PySec, les flux CSAF de Red Hat, CISA, Cisco, Siemens et d'autres CERT, offrant ainsi une couverture bien plus large des vulnérabilités connues.

**Search** effectue une correspondance exacte : Mercator recherche dans sa base locale le CPE qui correspond précisément aux valeurs `vendor` et `product` saisies, puis interroge le NVD pour récupérer les CVE associées.

**Prérequis :** le deux champs doivent être renseignés et présents dans la base CPE locale.

**Utilisation :**

1. Ouvrir la fiche d'une application ou d'un asset ayant des champs `vendor`, `product` et `version`
2. Cliquer sur `Recherche CVE`
3. Les CVE s'affichent avec leur score CVSS et leur description

> Si aucun résultat ne s'affiche, vérifier que la base CPE est chargée et que les valeurs `vendor` et `product` correspondent exactement aux identifiants NVD.

### CPE-Guesser

[CPE-Guesser](https://vulnerability-lookup.github.io/cpe-guesser)  est un outil open source développé par [CIRCL (Computer Incident Response Center Luxembourg)](https://www.circl.lu). Il identifie les CPE les plus probables à partir d'un ou plusieurs mots-clés, en s'appuyant sur un index inverse construit à partir du dictionnaire NVD et pondéré par la fréquence d'apparition de chaque CPE dans les CVE connues. Mercator interroge CPE-Guesser pour proposer une liste de correspondances classées par pertinence, sans exiger que les champs vendor / product / version soient déjà alignés sur le référentiel NVD.

**Quand utiliser Guess :**

- Pour identifier le bon CPE à reporter ensuite dans la fiche de l'actif
- Les identifiants NVD exacts ne sont pas connus
- CVE Search ne retourne aucun résultat

**Utilisation :**

1. Accéder à la fiche de l'application ou au menu CVE
2. Utiliser la fonction `Guess`
3. Sélectionner le CPE correspondant dans la liste proposée
4. Reporter les valeurs `vendor` / `product` / `version` dans la fiche pour que Search fonctionne à l'avenir

### Comparatif

| | Search | Guess |
|--|--------|-------|
| **Prérequis** | Champs CPE renseignés et présents en base | Aucun |
| **Vitesse** | Instantané (base locale) | Plus lent (calcul d'approximation) |
| **Précision** | Très précis si les champs sont corrects | Peut proposer des faux positifs |
| **Usage typique** | Actifs bien documentés | Découverte du bon CPE, actifs peu documentés |

## Rapports

### Score CVSS

Chaque CVE est présentée avec son score **CVSS** (Common Vulnerability Scoring System) :

| Score | Niveau | Priorité |
|-------|--------|----------|
| 0.0 | Aucun | — |
| 0.1 – 3.9 | Faible | Basse |
| 4.0 – 6.9 | Moyen | Normale |
| 7.0 – 8.9 | Élevé | Haute |
| 9.0 – 10.0 | Critique | Immédiate |

Les CVE détectées sont disponibles dans les rapports de sécurité, accessibles via le menu **Rapports**.

## Sauvegarde des données CPE / CVE

Les données CPE sont stockées **uniquement en base de données** (tables `cpe_vendors`, `cpe_products`, `cpe_versions`). Il n'y a pas de fichiers spécifiques à sauvegarder sur le système de fichiers.

Trois stratégies sont envisageables selon le contexte :

**Option A — Ne pas sauvegarder les tables CPE**  
Les tables peuvent être régénérées à tout moment depuis le NVD. C'est la stratégie la plus légère, au prix d'un temps de resynchronisation après restauration.

**Option B — Sauvegarde complète de la base (recommandé)**

```bash
mysqldump -u mercator_user -p mercator > mercator_backup_$(date +%Y%m%d).sql

# Sous Docker
docker exec mercator-db mysqldump -u mercator_user -p mercator \
  > mercator_backup_$(date +%Y%m%d).sql
```

**Option C — Sauvegarde allégée sans les tables CPE**

```bash
mysqldump -u mercator_user -p mercator \
  --ignore-table=mercator.cpe_vendors \
  --ignore-table=mercator.cpe_products \
  --ignore-table=mercator.cpe_versions \
  > mercator_backup_sans_cpe_$(date +%Y%m%d).sql
```

Après restauration, relancer `mercator:cpe-sync --now` pour repeupler les tables.

## Configuration

### Clé API NVD

Le NVD impose des limites de débit selon que vous utilisez une clé API ou non :

| Sans clé | Avec clé |
|----------|----------|
| 5 requêtes / 30 s | 50 requêtes / 30 s |
| Chargement initial : 8–24 h | Chargement initial : 30 min – 2 h |

La clé est **gratuite**. Pour l'obtenir :

1. Se rendre sur [https://nvd.nist.gov/developers/request-an-api-key](https://nvd.nist.gov/developers/request-an-api-key)
2. Saisir une adresse e-mail
3. Recevoir la clé par e-mail (quelques minutes)

### Variables d'environnement

Ajouter les lignes suivantes au fichier `.env` :

```ini
# URL de l'API NVD (ne pas modifier)
NVD_API_URL=https://services.nvd.nist.gov/rest/json/cpes/2.0

# Clé API NVD — fortement recommandée
NVD_API_KEY=votre-clé-ici
```

### Déploiement Docker

Dans `docker-compose.yml`, ajouter ces variables dans la section `environment` du service `app` :

```yaml
environment:
  - NVD_API_URL=https://services.nvd.nist.gov/rest/json/cpes/2.0
  - NVD_API_KEY=votre-clé-ici
```

Puis recharger la configuration :

```bash
docker-compose up -d
docker exec -it mercator-app php artisan config:clear
docker exec -it mercator-app php artisan config:cache
```

## Initialisation

### Chargement initial de la base CPE

La base CPE doit être synchronisée une première fois avant de pouvoir effectuer des recherches CVE. Le NVD contient plusieurs centaines de milliers d'entrées ; la durée dépend directement de la disponibilité d'une clé API (voir section précédente).

```bash
# Installation classique (VM)
php artisan mercator:cpe-sync --now

# Déploiement Docker
docker exec -it mercator-app php artisan mercator:cpe-sync --now
```

### Reprise en cas d'interruption

Si la commande est interrompue (coupure réseau, timeout, redémarrage du container), il n'existe pas encore de mécanisme de reprise : la synchronisation repart de zéro.

Pour éviter ce problème, lancer la commande dans une session `screen` ou `tmux` afin de la protéger contre les déconnexions SSH :

```bash
screen -S cpe-sync
docker exec -it mercator-app php artisan mercator:cpe-sync --now
# Ctrl+A puis D pour détacher la session
```

Il est également utile de surveiller les journaux Laravel en parallèle :

```bash
docker exec -it mercator-app tail -f storage/logs/laravel.log
```

> **Point ouvert :** La possibilité de synchroniser par segments ou en se limitant aux applications déjà présentes dans la CMDB est à l'étude. Voir la [section Points ouverts](#points-ouverts).

## Mise à jour automatique

### Scheduler Laravel

Mercator utilise le planificateur de tâches de Laravel pour maintenir la base CPE à jour. La synchronisation est programmée **chaque jour à 03h30**, à condition que le cron système soit actif.

### Configuration du cron (VM classique)

```bash
sudo crontab -e
```

Ajouter la ligne suivante :

```cron
* * * * * cd /var/www/mercator && php artisan schedule:run >> /dev/null 2>&1
```

Cette entrée unique suffit : Laravel détermine lui-même l'heure d'exécution de chaque tâche planifiée.

### Vérification sous Docker

Pour afficher la liste des tâches planifiées et s'assurer que `cpe-sync` est bien enregistré :

```bash
docker exec -it mercator-app php artisan schedule:list
```

Pour déclencher manuellement le scheduler :

```bash
docker exec -it mercator-app php artisan schedule:run
```

## Vérification de la base CPE {#check}

Après l'initialisation ou en cas de doute sur l'état de la synchronisation, deux méthodes permettent de contrôler le contenu de la base.

### Via MySQL / MariaDB

```sql
-- Nombre d'entrées par table
SELECT 'vendors'  AS type, COUNT(*) AS nb FROM cpe_vendors
UNION ALL
SELECT 'products', COUNT(*) FROM cpe_products
UNION ALL
SELECT 'versions', COUNT(*) FROM cpe_versions;
```

Sous Docker :

```bash
docker exec -it mercator-db mysql -u mercator_user -p mercator \
  -e "SELECT 'vendors' AS type, COUNT(*) AS nb FROM cpe_vendors
      UNION ALL SELECT 'products', COUNT(*) FROM cpe_products
      UNION ALL SELECT 'versions', COUNT(*) FROM cpe_versions
      UNION ALL SELECT 'last_sync', MAX(updated_at) FROM cpe_versions;"
```

### Via les journaux Laravel

```bash
# VM classique
tail -n 100 /var/www/mercator/storage/logs/laravel.log | grep -i cpe

# Docker
docker exec -it mercator-app tail -n 100 storage/logs/laravel.log | grep -i cpe
```

> **Point ouvert :** La date de dernière synchronisation CPE n'est pas encore affichée dans l'interface ni dans les rapports. Voir la [section Points ouverts](#points-ouverts).

---


## Points ouverts {#points-ouverts}

Les points suivants ont été identifiés lors de l'utilisation. Ils seront mis à jour au fil des évolutions du projet.

---

### Reprise sur interruption de `cpe-sync`

**Question :** Si `cpe-sync --now` est interrompu, est-il possible de reprendre là où la synchronisation s'est arrêtée ?

**Statut :** ⏳ En attente. Piste envisagée : synchronisation par segments ou limitée aux applications déjà présentes dans la CMDB.

### Affichage de la date de synchronisation CPE dans l'interface

**Constat :** Il n'est pas possible de savoir depuis l'interface si la base CPE est chargée ni quelle est sa date de mise à jour. Le contournement actuel passe par une requête SQL directe (voir section [Vérification](#check)).

**Statut :** ⏳ En attente.  
**Proposition :** Afficher la date de dernière synchronisation CPE dans l'écran de recherche CVE et dans les rapports de sécurité.

### Champs CPE non affichés pour certains actifs

**Constat :** Les champs `vendor`, `product`, `version` existent en base pour plusieurs types d'actifs (serveurs physiques, postes de travail, commutateurs…) mais ne sont pas accessibles depuis l'interface graphique.

**Statut :** ⏳ Clarification demandée.  
**Proposition :** Rendre ces champs visibles dans l'UI pour tous les actifs supportant CPE, afin d'éviter de passer par l'API ou l'import JSON.

### Colonne "version" absente de la liste des Applications

**Constat :** La vue liste des Applications n'affiche pas le champ `version`, pourtant utilisé par le moteur CPE.

**Statut :** ⏳ Suggestion transmise.  
**Proposition :** Ajouter une colonne `version` dans cette vue pour améliorer la visibilité de l'état de documentation des actifs.

### Distinction Search / Guess peu visible dans l'UI

**Constat :** L'interface n'indique pas clairement que Search requiert les trois champs CPE renseignés, ni la différence de comportement avec Guess.

**Statut :** ⏳ Suggestion transmise.  
**Proposition :** Ajouter des infobulles ou un texte explicatif au niveau de ces deux fonctions.


## Annexe A — Référence rapide des commandes

```bash
# Chargement initial de la base CPE
docker exec -it mercator-app php artisan mercator:cpe-sync --now

# Lister les tâches planifiées
docker exec -it mercator-app php artisan schedule:list

# Exécuter manuellement le scheduler
docker exec -it mercator-app php artisan schedule:run

# Vider le cache de configuration (après modification .env)
docker exec -it mercator-app php artisan config:clear
docker exec -it mercator-app php artisan config:cache

# Vérifier l'état de la base CPE
docker exec -it mercator-db mysql -u mercator_user -p mercator \
  -e "SELECT 'vendors' AS type, COUNT(*) AS nb FROM cpe_vendors
      UNION ALL SELECT 'products', COUNT(*) FROM cpe_products
      UNION ALL SELECT 'versions', COUNT(*) FROM cpe_versions
      UNION ALL SELECT 'last_sync', MAX(updated_at) FROM cpe_versions;"
```

## Annexe B — Exemple de fichier `.env` minimal

```ini
APP_NAME=Mercator
APP_ENV=Production
APP_KEY=base64:votre_clé_générée_par_artisan_key_generate
APP_DEBUG=false
APP_URL=https://mercator.mondomaine.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=mercator
DB_USERNAME=mercator_user
DB_PASSWORD=motdepasse_sécurisé

# Mail — voir la page Configuration pour les options complètes
MAIL_MAILER=smtp
MAIL_HOST=smtp.mondomaine.com
MAIL_PORT=587
MAIL_USERNAME=noreply@mondomaine.com
MAIL_PASSWORD=motdepasse_smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mondomaine.com
MAIL_FROM_NAME="Mercator"

# API
API_RATE_LIMIT=60
API_RATE_LIMIT_DECAY=1

# CPE / CVE
NVD_API_URL=https://services.nvd.nist.gov/rest/json/cpes/2.0
NVD_API_KEY=votre_clé_nvd_gratuite
```
