# Mercator

[![Latest Release](https://img.shields.io/github/release/sourcentis/mercator.svg?style=flat-square)](https://github.com/sourcentis/mercator/releases/latest)
![License](https://img.shields.io/github/license/sourcentis/mercator.svg?style=flat-square)
![Contributors](https://img.shields.io/github/contributors/sourcentis/mercator.svg?style=flat-square)
![Stars](https://img.shields.io/github/stars/sourcentis/mercator?style=flat-square)
[![Artifact Hub](https://img.shields.io/endpoint?url=https://artifacthub.io/badge/repository/mercator)](https://artifacthub.io/packages/search?repo=mercator)
[![OW2 Project](https://img.shields.io/badge/OW2-Mercator-blue)](https://projects.ow2.org/view/mercator/)

### Introduction

À mesure que les systèmes d'information gagnent en complexité, les organisations peinent à maintenir une vision claire et à jour de ce qu'elles possèdent, de la façon dont les composants s'interconnectent et de leurs dépendances mutuelles. Sans cette visibilité, la gestion des risques, la conformité et la réponse aux incidents relèvent davantage de l'intuition que de la maîtrise.

Mercator est une application web Open Source qui répond à ce problème en permettant la cartographie structurée et dynamique de votre système d'information. Il offre aux équipes sécurité, aux architectes et aux responsables informatiques une représentation commune et vivante de leur environnement numérique — des processus métier jusqu'à l'infrastructure physique.

Mercator permet de visualiser les dépendances, d'identifier les actifs critiques, de suivre la conformité et d'alimenter les démarches d'audit et de gestion des risques — depuis une plateforme unique et cohérente. Il s'aligne sur les principaux référentiels et standards, dont le [Guide de cartographie de l'ANSSI](https://cyber.gouv.fr/publications/cartographie-du-systeme-dinformation), NIS2 et l'ISO 27001.

Reconnu pour sa pertinence opérationnelle et adopté par des organisations dans les secteurs de la santé, du secteur public et des infrastructures critiques, Mercator est l'outil Open Source de référence pour la gouvernance des systèmes d'information.


📚 [Explorer la Documentation](https://sourcentis.github.io/mercator/) | 🔍 [Voir les Sources de l'Application](https://github.com/sourcentis/mercator/tree/master/app)

📖 _Lire ceci dans d'autres langues :_ [Anglais](README.md)

## 🌟 **Principales fonctionnalités**

- 🖥️ **Visualisations Complètes :** Générer des représentations graphiques de votre écosystème, y compris les vues logiques, administratives et de l'infrastructure physique.
- 📝 **Rapports d'Architecture :** Créez automatiquement des rapports détaillés sur l'architecture de votre système d'information.
- 🗺️ **Diagrammes de Cartographie :** Dessinez et exportez des diagrammes de cartographie pour communiquer visuellement l'architecture du système.
- ✅ **Suivi de la Conformité :** Évaluez et calculez les niveaux de conformité de vos systèmes.
- 🔒 **Intégrations de Sécurité :** Recherchez des vulnérabilités en utilisant l'intégration [CVE-Search](https://github.com/cve-search/cve-search).
- 📊 **Exportation de Données :** Exportez des données dans divers formats, y compris Excel, CSV et PDF.
- 🌐 **API REST :** Intégrez facilement avec d'autres systèmes en utilisant l'API REST avec support JSON.
- 👥 **Gestion Multi-Utilisateurs :** Contrôle d'accès basé sur les rôles pour les environnements collaboratifs.
- 🌍 **Support Multilingue :** Disponible en plusieurs langues pour les équipes internationales.
- 🔗 **Intégration LDAP/Active Directory :** Connectez-vous avec des annuaires d'utilisateurs existants pour une authentification simplifiée.
- 🛠️ **Support CPE :** Exploitez [Common Platform Enumeration (CPE)](https://nvd.nist.gov/products/cpe) pour une identification améliorée du système.

## 🖼️ **Captures d'écran**

### 🏠 **Tableau de bord principal**
[<img src="public/screenshots/mercator1.png" width="400" height="300">](public/screenshots/mercator1.png) [<img src="public/screenshots/mercator2.png" width="400" height="300">](public/screenshots/mercator2.png)

### 📊 **Niveaux de Conformité**
[<img src="public/screenshots/mercator3.png" width="400">](public/screenshots/mercator3.png)

### 🔧 **Écrans de Saisie**
[<img src="public/screenshots/mercator4.png" width="400" height="200">](public/screenshots/mercator4.png) [<img src="public/screenshots/mercator5.png" width="400" height="200">](public/screenshots/mercator5.png)

### 🗺️ **Cartographie**
[<img src="public/screenshots/mercator6.png" width="400" height="300">](public/screenshots/mercator6.png) [<img src="public/screenshots/mercator7.png" width="400" height="300">](public/screenshots/mercator7.png)

### 🔍 **Exploration de Données**
[<img src="public/screenshots/mercator9.png" width="400">](public/screenshots/mercator9.png)

### 🗂️ **Modèle de Données**
[<img src="public/screenshots/mercator8.png" width="400">](public/screenshots/mercator8.png)

## 🛠️ **Technologies Utilisées**

- **Backend:** PHP, Laravel
- **Frontend:** JavaScript
- **Bases de Données:** MariaDB, MySQL, PostgreSQL, and SQLite ([Voir Documentation Laravel Database](https://laravel.com/docs/master/database#introduction))
- **Bibliothèques Supplémentaires:** WebAssembly, Graphviz, ChartJS

## 📦 **Installation**

### 🔧 Installation Manuelle

Pour des instructions détaillées, veuillez vous référer aux guides d'installation :
- [Installation sur Ubuntu](https://github.com/sourcentis/mercator/blob/master/guides/INSTALL_VM.fr.md)
- [Installation sur RedHat](https://github.com/sourcentis/mercator/blob/master/guides/INSTALL.RedHat.fr.md)

### 🐳 Installation via Docker

Démarrez rapidement avec Docker. Exécutez une instance locale en mode développement avec la base de données de démonstration :

```bash
docker run -it --rm -e USE_DEMO_DATA=1 -p 8080:8080 --name mercator ghcr.io/sourcentis/mercator:latest
```

Si vous ne souhaitez pas utiliser la base de données de démonstration, la première fois que vous démarrez Docker, vous devez initialiser la base de données pour créer l'utilisateur administrateur avec l'option SEED_DATABASE:

```bash
docker run -it --rm -e SEED_DATABASE=1 -p 8080:8080 --name mercator ghcr.io/sourcentis/mercator:latest
```

Pour rendre vos données persistantes :

```bash
touch ./db.sqlite
mkdir -p ./docs ./app
chmod go= ./db.sqlite ./docs ./app
sudo chown -R 1000:1000 ./docs ./app ./db.sqlite
docker run -it --rm \
           -e APP_ENV=development \
           -p 8080:8080 \
           -v $PWD/db.sqlite:/var/www/mercator/sql/db.sqlite \
           -v $PWD/docs:/var/www/mercator/storage/docs \
           -v $PWD/app:/var/www/mercator/storage/app \
           ghcr.io/sourcentis/mercator:latest
```

Populez la base de données avec des données de démonstration :

```bash
docker run -it --rm \
           -e APP_ENV=development \
           -p 8080:8080 \
           -v $PWD/db.sqlite:/var/www/mercator/sql/db.sqlite \
           -v $PWD/docs:/var/www/mercator/storage/docs \
           -v $PWD/app:/var/www/mercator/storage/app \
           -e USE_DEMO_DATA=1 \
           ghcr.io/sourcentis/mercator:latest
```

Accédez à votre instance via [http://127.0.0.1:8080](http://127.0.0.1:8080).

    user : admin@admin.com
    password : password

Pour un environnement de production prêt à l'emploi avec HTTPS et une configuration automatisée, consultez le dossier [docker-compose](docker-compose/).

## 📜 **Roadmap & Changelog**

Restez informé des dernières améliorations et mises à jour dans la [Roadmap](https://github.com/sourcentis/mercator/blob/master/ROADMAP.md).

## 📄 **Licence**

Mercator est un logiciel open-source distribué sous la licence [GPL](https://www.gnu.org/licenses/licenses.html).

## 🤝 Partenariats

Mercator est un projet open source soutenu par l'[organisation OW2](https://www.ow2.org/view/Mercator/), qui promeut des logiciels libres fiables, industriels et interopérables.

[![OW2 Project](https://img.shields.io/badge/OW2-Mercator-blue)](https://projects.ow2.org/view/mercator)
