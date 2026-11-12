# Administration

Les paramètres de configuration sont accessibles dans Mercator via :

> **Admin → Configuration → Paramètres** (`/admin/config/parameters`)

Contrairement aux variables d'environnement, ces paramètres sont stockés en base de données (les valeurs par défaut figurent dans **config/mercator.php**) et peuvent être modifiés à chaud par un administrateur, sans redémarrer l'application ni modifier le fichier `.env`.

La page des paramètres est organisée en neuf onglets : Paramètres, Périmètres, Certificats, Nettoyage, Vulnérabilités, Rappels, Notifications, Documents et Monarc.

---

## Paramètres

Cet onglet regroupe les options de comportement générales de l'application.

[<img src="/mercator/fr/images/config-general.png" width="700">](images/config-general.fr.png)

### Applications

| Paramètre | Description | Défaut |
|-----------|-------------|--------|
| **Documents associés aux applications** | Permet de charger et d'associer des fichiers locaux (documents) aux applications. | `inactif` |

### Infrastructure logique

| Paramètre | Description | Défaut |
|-----------|-------------|--------|
| **Authenticité** | Ajoute le critère de sécurité « Authenticité » aux critères Confidentialité, Intégrité, Disponibilité et Traçabilité déjà existants (exigence DORA). | `inactif` |

### Cartographie

| Paramètre | Description | Défaut |
|-----------|-------------|--------|
| **Nombre maximum de nœuds** | Au-delà de ce nombre de nœuds, les graphes ne sont pas affichés (`0` = pas de limite). | 500 |

---

## Périmètres

Cet onglet active le cloisonnement de la cartographie par établissement et permet de gérer la liste des périmètres.

[<img src="/mercator/fr/images/config-perimeters.png" width="700">](images/config-perimeters.fr.png)

| Élément | Description |
|---------|-------------|
| **Activer les périmètres** | Active ou désactive la gestion des périmètres (désactivée par défaut). Tant qu'elle n'est pas activée, l'application se comporte comme si un seul périmètre existait. L'activation et la désactivation ne modifient aucune donnée. |
| **Ajouter** | Crée un nouveau périmètre (nom de 2 à 32 caractères). |
| **Modifier** | Renomme un périmètre, y compris le périmètre par défaut. |
| **Supprimer** | Supprime un périmètre qui ne contient aucun objet et n'est associé à aucun rôle. Le périmètre par défaut (`#1`) ne peut pas être supprimé. |

Le fonctionnement complet (rôles, sélecteur de périmètre de travail, flux inter-périmètres, import) est décrit dans la page [Périmètres](perimeters.md).

---

## Certificats

Gère la surveillance automatisée et les notifications d'expiration des certificats TLS/SSL.

[<img src="/mercator/fr/images/config-cert.png" width="700">](images/config-cert.fr.png)

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

[<img src="/mercator/fr/images/cleanup.png" width="700">](images/cleanup.fr.png)

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

### Corbeille

Le bouton **Explorer la corbeille** de l'onglet Nettoyage active le **mode corbeille**, qui permet de consulter et de restaurer les objets supprimés avant leur purge définitive.

Le mode s'ouvre sur une vue d'ensemble qui indique, pour chaque type d'objet, le nombre d'objets supprimés. Les types sont regroupés par vue et présentés dans l'ordre du menu. Seuls les types dont l'utilisateur peut consulter la liste sont affichés.

[<img src="/mercator/fr/images/trash-overview.png" width="700">](images/trash-overview.fr.png)

Tant que le mode corbeille est actif :

* le fond de l'écran est hachuré de rouge et un bandeau rappelle que l'on est en mode corbeille ;
* les listes de la cartographie (accessibles par le menu ou par la vue d'ensemble) n'affichent **que les objets supprimés** ; les objets actifs n'y apparaissent pas ;
* chaque ligne propose les actions **Afficher** et **Restaurer** ; les actions de création, de modification et de suppression sont masquées, ainsi que les boutons **Explorer** (exploration des liens de l'objet) et **Copier** (duplication de l'objet ou d'un graphe) ;
* le tableau de bord et les rapports de maturité ne comptent que les objets supprimés, et les niveaux de conformité valent 0 ;
* la recherche globale est désactivée.

[<img src="/mercator/fr/images/trash-list.png" width="700">](images/trash-list.fr.png)

La fiche d'un objet supprimé indique la date de suppression et l'utilisateur qui l'a supprimé, et propose un bouton **Restaurer**.

[<img src="/mercator/fr/images/trash-show.png" width="700">](images/trash-show.fr.png)

Les droits s'appliquent comme en dehors du mode corbeille :

* l'accès au mode corbeille est réservé aux utilisateurs qui peuvent modifier la configuration ;
* afficher un objet supprimé nécessite le droit de consulter les objets de ce type ;
* restaurer un objet nécessite le droit de **supprimer** les objets de ce type ;
* un objet ne peut pas être restauré si un objet actif du même type porte déjà son nom dans le même périmètre.

Le bouton **Quitter la corbeille**, en bas du menu latéral au-dessus du numéro de version, désactive le mode corbeille et ramène à l'onglet Nettoyage.

Les utilisateurs et les rôles supprimés ne sont pas concernés par le mode corbeille.

---

## Vulnérabilités

Gère la recherche automatisée des vulnérabilités connues (CVE) affectant les éléments de l'inventaire.

[<img src="/mercator/fr/images/config-cve.png" width="700">](images/config-cve.fr.png)

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

## Rappels

Cet onglet configure les rappels envoyés par e-mail aux cartographes lorsque des objets dont ils sont responsables n'ont pas été mis à jour depuis un certain temps.

[<img src="/mercator/fr/images/config-reminders.png" width="700">](images/config-reminders.fr.png)

| Paramètre | Description | Défaut |
|-----------|-------------|--------|
| **Activer les rappels** | Active l'envoi des rappels. Les autres champs ne sont modifiables que lorsque l'option est active. | `inactif` |
| **Expéditeur** | Adresse d'envoi des rappels. | — |
| **Copie cachée à** | Adresse(s) recevant une copie cachée (Cci) de chaque rappel. | — |
| **Sujet du rappel** | Objet de l'e-mail. | `[Mercator] Rappel` |
| **Corps du rappel** | Contenu HTML de l'e-mail. | Modèle fourni |
| **Pas de mise à jour depuis (mois)** | Un objet est signalé s'il n'a pas été modifié depuis ce nombre de mois. | 1 |
| **Envoyer un rappel tous les (jours)** | Délai minimal entre deux campagnes de rappels. | 30 |

Variables utilisables dans le corps du rappel :

| Variable | Remplacée par |
|----------|---------------|
| `:name` / `:user` | Nom du cartographe |
| `:count` | Nombre d'objets à mettre à jour |
| `:list` | Liste des objets, avec un lien vers chaque fiche |
| `:months` | Nombre de mois sans mise à jour |
| `:mercator_url` | URL de Mercator |

Les rappels sont envoyés par la commande `mercator:remind-cartographers`, exécutée chaque jour par le planificateur. Le pied de l'onglet affiche la date du dernier envoi. Le bouton **Tester** envoie un e-mail de test à l'adresse de copie cachée (ou, à défaut, à l'expéditeur).

Voir aussi [Les cartographes](cartographers.md).

---

## Notifications

Cet onglet configure les notifications envoyées par e-mail aux cartographes d'un objet lorsque celui-ci est modifié par un autre utilisateur.

[<img src="/mercator/fr/images/config-notifications.png" width="700">](images/config-notifications.fr.png)

| Paramètre | Description | Défaut |
|-----------|-------------|--------|
| **Activer les notifications de modification** | Active l'envoi des notifications. Les autres champs ne sont modifiables que lorsque l'option est active. | `inactif` |
| **Expéditeur** | Adresse d'envoi des notifications. | — |
| **Copie cachée à** | Adresse(s) recevant une copie cachée (Cci) de chaque notification. | — |
| **Sujet de la notification** | Objet de l'e-mail ; peut contenir des variables. | `[Mercator] Objet :name modifié` |
| **Corps de la notification** | Contenu HTML de l'e-mail. | Modèle fourni |

Variables utilisables dans le sujet et le corps :

| Variable | Remplacée par |
|----------|---------------|
| `:user` / `:email` | Nom et e-mail de l'utilisateur qui a fait la modification |
| `:name` / `:id` | Nom et identifiant de l'objet |
| `:object` | Type de l'objet |
| `:object_url` | URL de la fiche de l'objet |
| `:object_history_url` | URL de l'historique de l'objet |
| `:fields` | Champs modifiés |
| `:date` / `:timestamp` | Date (et heure) de la modification |

Le bouton **Tester** envoie un e-mail de test à l'adresse de copie cachée (ou, à défaut, à l'expéditeur).

---

## Documents

Cet onglet est **en lecture seule** et fournit une vue d'ensemble des documents stockés dans Mercator.

[<img src="/mercator/fr/images/config-documents.png" width="700">](images/config-documents.fr.png)

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

## Monarc

Cet onglet configure la connexion à une instance MONARC Front Office, utilisée pour exporter la cartographie sous forme d'analyse de risques.

[<img src="/mercator/fr/images/config-monarc.png" width="700">](images/config-monarc.fr.png)

| Paramètre | Description |
|-----------|-------------|
| **Activer l'intégration Monarc** | Affiche l'entrée « Monarc » dans le menu Outils et autorise l'accès à l'écran de génération. |
| **URL de l'instance Monarc** | URL de base de l'instance MONARC Front Office. |
| **Identifiant** | Compte utilisé pour s'authentifier sur l'API Monarc. |
| **Mot de passe** | Laisser vide pour conserver le mot de passe déjà enregistré. |

Le bouton **Tester la connexion** vérifie l'authentification. Le bloc **Réinitialiser la liaison** affiche l'analyse (ANR) actuellement liée, la date de la dernière synchronisation et le nombre d'objets suivis ; il permet de supprimer cette liaison locale sans rien modifier dans Monarc.

Voir la page [Intégration Monarc](monarc.md).

---

## Tableau récapitulatif

| Onglet | Paramètre | Description | Défaut |
|--------|-----------|-------------|--------|
| Paramètres | Documents associés aux applications | Association de fichiers aux applications | `inactif` |
| Paramètres | Authenticité | Ajoute le critère de sécurité « Authenticité » (DORA) | `inactif` |
| Paramètres | Nombre maximum de nœuds | Taille maximale des graphes affichés | 500 |
| Périmètres | Activer les périmètres | Cloisonnement de la cartographie par établissement | `inactif` |
| Certificats | Sujet du message | Objet des e-mails d'alerte d'expiration | — |
| Certificats | Expéditeur | Adresse d'envoi des alertes de certificat | — |
| Certificats | Destinataires | Adresse(s) de destination des alertes de certificat | — |
| Certificats | Délai d'alerte | Jours avant expiration pour déclencher la notification | 30 jours |
| Certificats | Fréquence de vérification | Intervalle de recherche des certificats expirants | Jamais |
| Certificats | Regroupement | Un e-mail consolidé ou un par certificat | Un seul e-mail |
| Certificats | Répétition des notifications | Notification unique ou répétée jusqu'à résolution | Unique |
| Nettoyage | Purge du journal d'audit | Suppression des entrées du journal plus anciennes que la durée de conservation | `inactif`, 12 mois |
| Nettoyage | Purge des objets supprimés | Suppression définitive des objets supprimés depuis plus longtemps que la durée de conservation | `inactif`, 3 mois |
| Nettoyage | Corbeille | Consultation et restauration des objets supprimés | — |
| Vulnérabilités | Sujet du message | Objet des e-mails d'alerte CVE | — |
| Vulnérabilités | Expéditeur | Adresse d'envoi des alertes CVE | — |
| Vulnérabilités | Destinataires | Adresse(s) de destination des alertes CVE | — |
| Vulnérabilités | Fréquence de vérification | Intervalle de recherche des nouvelles CVE | Jamais |
| Vulnérabilités | URL du CPE Guesser | Point d'accès du service [cpe-guesser](https://github.com/vulnerability-lookup/cpe-guesser) | — |
| Vulnérabilités | URL du fournisseur CVE | Point d'accès du fournisseur [vulnerability-lookup](https://github.com/vulnerability-lookup/vulnerability-lookup) | — |
| Documents | — | Lecture seule : nombre de documents, taille totale, vérification d'intégrité | — |
| Rappels | Activer les rappels | Rappels aux cartographes pour les objets non mis à jour | `inactif` |
| Notifications | Activer les notifications de modification | Notification des cartographes lors d'une modification | `inactif` |
| Monarc | Activer l'intégration Monarc | Export de la cartographie vers une analyse de risques Monarc | `inactif` |
