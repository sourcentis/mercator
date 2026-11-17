# Requêtes

Les requêtes Mercator permettent d'explorer et de visualiser les données de votre cartographie de manière flexible, sans passer par l'interface standard. Elles s'écrivent dans un langage déclaratif inspiré du SQL et peuvent produire une **liste** ou un **graphe**.

## Format d'une requête

Une requête Mercator suit la syntaxe suivante :

```sql
FROM    <Modèle>
FIELDS  <champ1>, <champ2>, <relation.champ>, ...
WHERE   (<condition1>) AND|OR (<condition2>)
WITH    <relation1>, <relation2>, ...
GROUP BY <champ1>, <champ2>, ...
OUTPUT  list | graph
LIMIT   <n>
```

| Clause | Obligatoire | Description                                                                       |
|--------|-------------|-----------------------------------------------------------------------------------|
| `FROM` | ✅ | Modèle de données cible (voir [Modèles disponibles](#modeles-disponibles))        |
| `FIELDS` | ➖ | Liste des champs à afficher, y compris les champs de relations (`relation.champ`) |
| `WHERE` | ➖ | Filtre sur les données (voir [Conditions](#conditions))                           |
| `WITH` | ➖ | Relations à afficher dans le graphe                                               |
| `GROUP BY` | ➖ | Regroupe les lignes et concatène les autres valeurs (voir [GROUP BY](#group-by)) |
| `OUTPUT` | ➖ | Format de sortie : `list` ou `graph` (`list` par défaut)                          |
| `LIMIT` | ➖ | Nombre maximum d'enregistrements retournés (défaut : 100)                         |

## Modèles disponibles

Les modèles correspondent aux entités de l'[API](api.fr.md) Mercator et sont en **kebab-case**.

| Modèle             | Description |
|--------------------|-------------|
| `logical-servers`  | Serveurs logiques |
| `physical-servers` | Serveurs physiques |
| `applications`     | Applications |
| `databases`        | Bases de données |
| `certificates`     | Certificats SSL/TLS |
| `networks`         | Réseaux / sous-réseaux |
| `storage-devices`  | Dispositifs de stockage |
| `sites`            | Sites physiques |
| `bays`             | Baies d'hébergement |
| …                  | _Tous les modèles de l'API_ |

!!! info "Champs disponibles"
    Les champs utilisables dans `FIELDS` et `WHERE` sont ceux exposés par l'API Mercator.
    Consultez le [modèle de données](model.fr.md) pour connaître l'ensemble des attributs de chaque modèle.

## Clause FIELDS

La clause `FIELDS` liste les attributs à afficher dans le résultat. Elle accepte :

- Les **champs directs** du modèle : `name`, `cpu`, `environment`, `end_validity`, …
- Les **champs de relations** au format `relation.champ` : `applications.name`, `site.name`, `databases.name`, …

```sql
FIELDS name, operating_system, cpu, memory, applications.name
```

!!! info "Champs protégés"
    Les champs marqués comme cachés dans les modèles ($hidden Eloquent), tels que password ou remember_token, ne sont jamais retournés par le moteur de requêtes, même s'ils sont explicitement listés dans FIELDS.

!!! warning "Cohérence avec WITH"
    Si vous référencez un champ de relation dans `FIELDS` (ex. `applications.name`), la relation correspondante doit être déclarée dans `WITH` (ex. `WITH applications`), sans quoi les données ne seront pas chargées.

## Clause WHERE {#conditions}

La clause `WHERE` filtre les enregistrements selon des conditions sur les champs du modèle principal.

### Opérateurs supportés

| Opérateur | Syntaxe | Exemple |
|-----------|---------|---------|
| Égalité | `=` | `environment = "production"` |
| Inégalité | `!=` | `type != "virtual"` |
| Comparaison | `<`, `>`, `<=`, `>=` | `memory >= 16` |
| Recherche | `LIKE` | `operating_system LIKE "%Linux%"` |
| Liste de valeurs | `IN` | `environment IN ("production", "staging")` |
| Existence de relation | `EXISTS` | `EXISTS applications` |
| Absence de relation | `NOT EXISTS` | `NOT EXISTS certificates` |

### Combinaisons logiques

Les conditions peuvent être combinées avec `AND` et `OR`. Chaque condition doit être encadrée par des parenthèses :

```sql
WHERE (environment = "production") AND (operating_system LIKE "%Linux%")
```

```sql
WHERE (environment IN ("production", "staging")) AND (operating_system LIKE "%Windows%")
```

### Opérateur EXISTS {#exists}

L'opérateur `EXISTS` filtre les enregistrements selon qu'une relation est présente ou absente. Il s'applique au nom de la relation Eloquent (tel que déclaré dans `WITH`).

```sql
WHERE (EXISTS applications)
```

```sql
WHERE (NOT EXISTS certificates)
```

`EXISTS` peut être combiné avec d'autres conditions :

```sql
WHERE (environment = "production") AND (EXISTS certificates)
```

!!! info "EXISTS et eager loading"
    L'opérateur `EXISTS` n'implique pas le chargement des données de la relation.
    Si vous souhaitez également afficher les champs de cette relation dans `FIELDS`, déclarez-la explicitement dans `WITH`.

## Clause WITH

La clause `WITH` déclare les **relations à charger** (eager loading). Elle est indispensable pour accéder aux champs d'objets liés dans `FIELDS`.

```sql
WITH applications, databases, certificates
```

Les noms de relations correspondent aux noms des méthodes de relation des modèles Eloquent, en **snake_case** :

```sql
WITH logical_servers, databases, sites, bays
```

### Nœuds intermédiaires masqués

Par défaut, chaque segment d'un chemin `WITH` crée un nœud dans le graphe. Il est possible de **masquer un niveau intermédiaire** en l'entourant de parenthèses : le niveau est alors traversé pour accéder aux niveaux suivants, mais il n'apparaît ni comme nœud ni comme arête dans le graphe résultant.

```sql
WITH (subnetworks).vlan
```

Dans cet exemple, les sous-réseaux servent uniquement de pivot de traversée. Le graphe affiche directement des arêtes `networks → vlan`, sans représenter les `subnetworks`.

La syntaxe se généralise à plusieurs niveaux masqués :

```sql
-- Masquer un niveau sur deux
WITH (subnetworks).routers.(interfaces).vlan

-- Masquer plusieurs niveaux consécutifs
WITH (subnetworks).(routers).vlan
```

Les règles à respecter :

- Un chemin entièrement masqué (tous les segments entre parenthèses) est sans effet.
- Le dernier segment d'un chemin ne peut pas être masqué.
- Les parenthèses imbriquées `((rel))` sont interdites.

!!! tip "Quand masquer un niveau ?"
    Masquez un intermédiaire lorsque la relation pivot n'a pas de valeur sémantique dans la visualisation — par exemple, les sous-réseaux entre un réseau et ses VLANs, ou les interfaces entre un serveur et ses VLANs.

## Clause GROUP BY {#group-by}

Par défaut, une requête `OUTPUT list` qui affiche plusieurs champs de relations produit **une ligne par combinaison** (produit cartésien) : une application liée à 3 processus et 2 bases de données apparaît sur 6 lignes.

La clause `GROUP BY` produit à la place **une ligne par valeur distincte** des champs de regroupement. Chaque autre colonne contient la liste des valeurs distinctes, séparées par une virgule.

```sql
FROM applications
FIELDS id, name, processes.name, activities.name, databases.name
GROUP BY id, name
OUTPUT list
```

| id | name | processes.name | activities.name | databases.name |
|----|------|----------------|-----------------|----------------|
| 1 | ERP | Achats, Ventes | Facturation | Oracle, PostgreSQL |
| 2 | Portail | | Accueil | PostgreSQL |

Règles :

- Chaque champ de `GROUP BY` doit figurer dans `FIELDS`.
- `GROUP BY` n'est disponible qu'avec `OUTPUT list` (y compris pour l'export CSV).
- Les valeurs vides sont ignorées et les doublons supprimés ; l'ordre de première apparition est conservé.
- Une relation sans objet lié donne une cellule vide.
- On peut aussi regrouper sur un champ de relation, par exemple `GROUP BY databases.name` pour lister, par base de données, les applications qui l'utilisent.
- `LIMIT` s'applique aux objets du modèle `FROM`, **avant** le regroupement : `LIMIT 50` lit 50 applications, puis regroupe leurs valeurs.

!!! tip "Grouper sur `id`"
    Incluez `id` dans `GROUP BY` pour ne pas fusionner deux objets qui porteraient le même nom.

### Regrouper sur un champ de relation

Quand le champ de regroupement appartient à une relation, le tableau est « retourné » : on obtient une ligne par valeur liée, et ce sont les champs du modèle `FROM` qui sont concaténés.

```sql
FROM applications
FIELDS databases.name, name, logical_servers.name
GROUP BY databases.name
OUTPUT list
```

| databases.name | name | logical_servers.name |
|----------------|------|----------------------|
| Oracle | ERP | srv-erp-01 |
| PostgreSQL | ERP, Portail | srv-erp-01, srv-web-01 |

Les applications sans base de données forment un groupe dont la cellule `databases.name` est vide.

## Format de sortie (OUTPUT)

### `OUTPUT list`

Génère un **tableau** avec une ligne par enregistrement. C'est le format adapté pour les inventaires, les exports, ou les vues tabulaires.

Quand `FIELDS` contient plusieurs champs de relations, un enregistrement peut occuper plusieurs lignes (une par combinaison) ; utilisez [`GROUP BY`](#group-by) pour n'obtenir qu'une ligne par enregistrement.

```sql
OUTPUT list
```

### `OUTPUT graph`

Génère un **graphe de relations** entre les entités retournées. C'est le format adapté pour visualiser les dépendances, les cartographies applicatives ou les relations réseau.

```sql
OUTPUT graph
```

!!! tip "Quand utiliser `graph` ?"
    Préférez `OUTPUT graph` dès que votre requête charge des relations avec `WITH` et que vous souhaitez visualiser les liens entre entités (applications ↔ serveurs, réseaux ↔ serveurs, etc.).

## Sauvegarde des requêtes

Il est possible de **sauvegarder des requêtes** dans l'interface pour les retrouver et les réexécuter sans les ressaisir. Les requêtes sauvegardées peuvent être rendues publiques (visibles par tous les utilisateurs) ou privées (visibles uniquement par leur auteur).

## Exemples

### Serveurs Linux en production avec leurs applications

```sql
FROM logical-servers
FIELDS name, operating_system, environment, cpu, memory, applications.name
WHERE (environment = "production") AND (operating_system LIKE "%Linux%")
WITH applications
```

Retourne la liste des serveurs logiques sous Linux en environnement de production, avec le nom des applications hébergées.

### Toutes les applications et leurs bases de données

```sql
FROM applications
FIELDS name, description, databases.name, logical_servers.name
WITH databases, logical_servers
OUTPUT graph
```

Génère un graphe reliant les applications à leurs bases de données et serveurs logiques.

### Inventaire des serveurs physiques

```sql
FROM physical-servers
FIELDS name, type, cpu, memory, site.name, bay.name
WITH site, bay
```

Liste complète des serveurs physiques avec leur localisation (site et baie).

### Réseaux, sous-réseaux et VLANs

```sql
FROM networks
FIELDS name, subnetworks.name, subnetworks.vlan.id, subnetworks.vlan.name
WITH subnetworks, subnetworks.vlan
```

Visualise les réseaux, sous-réseaux et leurs VLANs.

### Réseaux et VLANs sans les sous-réseaux intermédiaires

```sql
FROM networks
WITH (subnetworks).vlan
OUTPUT graph
```

Génère un graphe reliant directement chaque réseau à ses VLANs. Les sous-réseaux servent de pivot de traversée mais n'apparaissent pas dans le graphe — utile pour obtenir une vue synthétique lorsque les sous-réseaux n'apportent pas d'information supplémentaire dans la visualisation.

### Filtres multiples avec `IN`

```sql
FROM logical-servers
FIELDS applications.name, certificates.name
WHERE (environment IN ("production", "staging")) AND (operating_system LIKE "%Windows%")
WITH applications, certificates
```

Liste les applications et certificats installés sur les serveurs Windows en production ou en staging.

### Certificats SSL avec date d'expiration et périmètre d'installation

```sql
FROM certificates
FIELDS name, type, end_validity, domains, logical_servers.name, applications.name
WITH logical_servers, applications
```

Inventaire des certificats SSL/TLS avec leur date d'expiration et les serveurs/applications sur lesquels ils sont déployés. Utile pour anticiper les renouvellements.

### Applications critiques avec leurs serveurs et bases de données

```sql
FROM applications
FIELDS name, security_need_c, description, responsible, logical_servers.name, databases.name
WHERE (security_need_c IN ("3", "4"))
WITH logical_servers, databases
OUTPUT graph
```

Cartographie des applications à fort besoin de confidentialité (niveaux 3 et 4), avec leurs dépendances d'infrastructure.

### Serveurs sans certificat SSL

```sql
FROM logical-servers
FIELDS name, environment, operating_system
WHERE (environment = "production") AND (NOT EXISTS certificates)
WITH certificates
```

Identifie les serveurs de production sur lesquels aucun certificat SSL/TLS n'est enregistré. Utile pour détecter des angles morts dans la gestion des certificats.

### Serveurs en production sans plans de backups avec une application

```sql
FROM logical-servers
FIELDS name, applications.name
WHERE environment = "production"
AND NOT EXISTS backups
AND EXISTS applications
OUTPUT list
```

Identifier les serveurs et le nom des applications en production qui n'ont pas de plans de backups et au moins une application.

### Applications sans serveur logique associé

```sql
FROM applications
FIELDS name, responsible, security_need_c
WHERE (NOT EXISTS logical_servers)
WITH logical_servers
```

Liste les applications non rattachées à un serveur logique, symptôme possible d'une cartographie incomplète.

### Une ligne par serveur avec ses applications

```sql
FROM logical-servers
FIELDS id, name, environment, applications.name, certificates.name
WHERE (environment = "production")
GROUP BY id, name, environment
OUTPUT list
```

Inventaire des serveurs de production avec, sur une seule ligne, les applications hébergées et les certificats installés. Prêt à être exporté en CSV.

### Applications utilisant chaque base de données

```sql
FROM applications
FIELDS databases.name, name, responsible
WHERE (EXISTS databases)
GROUP BY databases.name
OUTPUT list
```

Liste, pour chaque base de données, les applications qui l'utilisent et leurs responsables. `EXISTS databases` écarte les applications sans base de données, qui formeraient sinon un groupe vide.

## Bonnes pratiques

- **Utilisez `LIMIT`** pour limiter le nombre de résultats à la valeur nécessaire : des requêtes trop larges peuvent être lentes sur de grands référentiels.
- **Utilisez `OUTPUT graph`** uniquement lorsque les relations sont déclarées dans `WITH` ; un graphe sans relations ne sera composé que de nœuds isolés.
- **Vérifiez les noms de champs** dans la [référence API](api.fr.md) — une faute de frappe dans un champ n'affiche simplement rien, sans message d'erreur.
- **Avec `EXISTS`**, déclarez la relation dans `WITH` uniquement si vous avez besoin d'afficher ses champs dans `FIELDS` ; sinon, `EXISTS` seul suffit à filtrer sans surcharge.
- **Utilisez `GROUP BY`** dès qu'une liste affiche plusieurs champs de relations : le produit cartésien multiplie vite les lignes et rend le tableau illisible.
- **Sauvegardez les requêtes récurrentes** pour faciliter le travail en équipe et garantir la reproductibilité des cartographies.