# Administration

Les paramètres de configuration sont accessibles dans Mercator via :

> **Admin → Configuration → Paramètres** (`/admin/config/parameters`)

Contrairement aux variables d'environnement, ces paramètres sont stockés en base de données (les valeurs par défaut figurent dans **config/mercator.php**) et peuvent être modifiés à chaud par un administrateur, sans redémarrer l'application ni modifier le fichier `.env`.

La page des paramètres est organisée en onglets.

---

## Paramètres

Cet onglet regroupe les options de comportement générales de l'application.

### Applications

| Paramètre | Description | Défaut |
|-----------|-------------|--------|
| **Documents associés aux applications** | Permet de charger et d'associer des fichiers locaux (documents) aux applications. | `inactif` |

### Infrastructure logique

| Paramètre | Description | Défaut |
|-----------|-------------|--------|
| **Authentification des besoins de sécurité** | Lorsque cette option est activée, les champs de besoin de sécurité sur les serveurs logiques nécessitent une authentification préalable avant d'être affichés. | `false` |

---

## Certificats

Gère la surveillance automatisée et les notifications d'expiration des certificats TLS/SSL.

| Paramètre | Description | Valeurs |
|-----------|-------------|---------|
| **Sujet du message** | Objet de l'e-mail d'alerte d'expiration. | Texte libre |
| **Expéditeur** | Adresse d'envoi des e-mails d'alerte de certificat. | Adresse e-mail |
| **Destinataires** | Adresse(s) de destination des e-mails d'alerte de certificat. | Adresse(s) e-mail |
| **Délai d'alerte** | Nombre de jours avant expiration à partir duquel la notification est envoyée. | 1, 7, 15, 30, 60 ou 90 jours |
| **Fréquence de vérification** | Intervalle auquel Mercator recherche les certificats arrivant à expiration. | Jamais / Quotidien / Hebdomadaire / Mensuel |
| **Regroupement** | Envoyer un e-mail consolidé pour tous les certificats expirants, ou un e-mail par certificat. | Un seul e-mail / Un par certificat |
| **Répétition des notifications** | Envoyer une seule notification par événement d'expiration, ou répéter jusqu'au renouvellement ou à la suppression du certificat. | Unique / Répétée |

Un bouton **Test** permet d'envoyer un e-mail de test avec la configuration en cours sans sauvegarder.

---

## Nettoyage

Cet onglet active la purge automatique de la base de données. Deux purges sont disponibles ; chacune s'active indépendamment et a sa propre durée de conservation.

[<img src="/mercator/images/cleanup.fr.png" width="700">](images/cleanup.fr.png)

| Paramètre | Description | Valeurs | Défaut |
|-----------|-------------|---------|--------|
| **Purge du journal d'audit** | Supprime les entrées du journal d'audit (historique des créations, modifications et suppressions) plus anciennes que la durée de conservation. | Actif / Inactif | `inactif` |
| **Durée de conservation du journal d'audit** | Âge au-delà duquel une entrée du journal est supprimée. | 1, 3, 6, 12, 24, 36 ou 60 mois | 12 mois |
| **Purge des objets supprimés** | Supprime définitivement les objets supprimés (corbeille) depuis plus longtemps que la durée de conservation. | Actif / Inactif | `inactif` |
| **Durée de conservation des objets supprimés** | Délai après la suppression d'un objet au-delà duquel il est supprimé définitivement. | 1, 3, 6, 12, 24, 36 ou 60 mois | 3 mois |

Le nettoyage est exécuté **chaque nuit à 02:00** par le planificateur Laravel (commande `mercator:db-cleanup`) ; il nécessite donc que le planificateur soit actif (`php artisan schedule:run` lancé chaque minute par cron, ou `schedule:work`). Le pied de l'onglet affiche la date et le résultat de la dernière exécution.

La purge des objets supprimés :

* concerne tous les objets de la cartographie, **à l'exception des utilisateurs et des rôles**, qui restent dans la corbeille ;
* est **irréversible** : un objet purgé ne peut plus être restauré ;
* supprime aussi les liens de l'objet avec d'autres objets (tables de liaison) et ses données dépendantes (par exemple les événements d'une application). Pour un document, le fichier est également supprimé du disque ;
* retire la référence à l'objet purgé dans les objets qui pointaient encore vers lui (par exemple le bâtiment d'un poste de travail) ;
* **ignore** un objet encore référencé de façon obligatoire par un autre objet (par exemple une entité source d'une relation non supprimée). Il sera de nouveau traité lors des exécutions suivantes, une fois la référence supprimée.

La purge ne génère pas elle-même d'entrées dans le journal d'audit.

La commande peut aussi être lancée manuellement ; l'option `--dry-run` affiche ce qui serait supprimé sans rien supprimer :

```bash
php artisan mercator:db-cleanup --dry-run
```

---

## CVE

Gère la recherche automatisée des vulnérabilités connues (CVE) affectant les éléments de l'inventaire.

| Paramètre | Description | Valeurs |
|-----------|-------------|---------|
| **Sujet du message** | Objet de l'e-mail d'alerte CVE. | Texte libre |
| **Expéditeur** | Adresse d'envoi des e-mails d'alerte CVE. | Adresse e-mail |
| **Destinataires** | Adresse(s) de destination des e-mails d'alerte CVE. | Adresse(s) e-mail |
| **Fréquence de vérification** | Intervalle auquel Mercator recherche les nouvelles CVE affectant l'inventaire. | Jamais / Quotidien / Hebdomadaire / Mensuel |
| **URL du CPE Guesser** | URL du service CPE Guesser utilisé pour inférer les identifiants CPE à partir des noms de logiciels. | URL |
| **URL du fournisseur CVE** | URL du fournisseur de données CVE. | URL |

### Services externes

**Fournisseur CVE** — Mercator interroge une instance [vulnerability-lookup](https://github.com/vulnerability-lookup/vulnerability-lookup) pour récupérer les CVE affectant les éléments de l'inventaire. Une instance publique est disponible à l'adresse `https://vulnerability.circl.lu`. Le service peut également être auto-hébergé.

**CPE Guesser** — Mercator utilise [cpe-guesser](https://github.com/vulnerability-lookup/cpe-guesser) pour inférer automatiquement les identifiants CPE à partir des noms de logiciels. Une instance publique est disponible à l'adresse `https://cpe-guesser.cve-search.org`. Le service peut également être auto-hébergé.

Trois boutons **Test** sont disponibles :

* **Test Mail** — envoie un e-mail de test avec la configuration en cours.
* **Test Provider** — vérifie la connectivité avec le fournisseur CVE configuré.
* **Test Guesser** — vérifie la connectivité avec le service CPE Guesser configuré.

---

## Documents

Cet onglet est **en lecture seule** et fournit une vue d'ensemble des documents stockés dans Mercator.

| Information | Description |
|-------------|-------------|
| **Nombre de documents** | Nombre total de documents enregistrés en base de données. |
| **Taille totale** | Taille cumulée de tous les documents stockés (affichée en unités lisibles). |

Un bouton **Vérifier** déclenche une vérification d'intégrité de tous les documents : Mercator compare le hachage SHA-256 enregistré pour chaque document avec le fichier présent sur le disque et affiche l'un des trois statuts suivants :

| Statut | Signification |
|--------|---------------|
| `OK` | Le fichier est présent et son hachage correspond à l'enregistrement en base. |
| `MISSING` | Le fichier est référencé en base mais introuvable sur le disque. |
| `HASH FAILS` | Le fichier est présent mais son hachage ne correspond pas — le fichier est peut-être corrompu ou altéré. |

---

## Tableau récapitulatif

| Onglet | Paramètre | Description | Défaut |
|--------|-----------|-------------|--------|
| Général | Authentification des besoins de sécurité | Requiert une authentification pour afficher les champs de besoin de sécurité | `false` |
| Certificats | Sujet du message | Objet des e-mails d'alerte d'expiration | — |
| Certificats | Expéditeur | Adresse d'envoi des alertes de certificat | — |
| Certificats | Destinataires | Adresse(s) de destination des alertes de certificat | — |
| Certificats | Délai d'alerte | Jours avant expiration pour déclencher la notification | 30 jours |
| Certificats | Fréquence de vérification | Intervalle de recherche des certificats expirants | Jamais |
| Certificats | Regroupement | Un e-mail consolidé ou un par certificat | Un seul e-mail |
| Certificats | Répétition des notifications | Notification unique ou répétée jusqu'à résolution | Unique |
| CVE | Sujet du message | Objet des e-mails d'alerte CVE | — |
| CVE | Expéditeur | Adresse d'envoi des alertes CVE | — |
| CVE | Destinataires | Adresse(s) de destination des alertes CVE | — |
| CVE | Fréquence de vérification | Intervalle de recherche des nouvelles CVE | Jamais |
| CPE | URL du CPE Guesser | Point d'accès du service [cpe-guesser](https://github.com/vulnerability-lookup/cpe-guesser) | — |
| CVE | URL du fournisseur CVE | Point d'accès du fournisseur [vulnerability-lookup](https://github.com/vulnerability-lookup/vulnerability-lookup) | — |
| Documents | — | Lecture seule : nombre de documents, taille totale, vérification d'intégrité | — |
| Nettoyage | Purge du journal d'audit | Suppression des entrées du journal plus anciennes que la durée de conservation | `inactif`, 12 mois |
| Nettoyage | Purge des objets supprimés | Suppression définitive des objets supprimés depuis plus longtemps que la durée de conservation | `inactif`, 3 mois |
