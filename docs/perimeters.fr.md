# Les périmètres dans Mercator

Les périmètres permettent de cartographier plusieurs entités (établissements, sites, unités métier) dans une seule instance de Mercator. Chaque entité gère ses propres objets, tout en pouvant les relier à ceux des autres périmètres. 

Cette documentation explique ce qu'est un périmètre, comment il se combine avec les rôles, et comment les utilisateurs travaillent au sein de leur périmètre.

## Introduction — Qu'est-ce qu'un périmètre ?

Un **périmètre** regroupe un sous-ensemble des objets de la cartographie indépendamment de leur type. Chaque objet (serveur, application, réseau, site…) n'appartient jamais qu'à un seul périmètre.

Les périmètres permettent de :

- Héberger la cartographie de **plusieurs entités** dans une seule instance
- Laisser chaque entité **gérer ses propres objets** de manière indépendante
- Identifier **les flux et les liens physiques** qui relient les entités
- Partitionner la visibilité et le contrôle d'accès par entité

**Note :** la gestion des périmètres est une **fonctionnalité optionnelle**. Tant que vous ne l'activez pas, tous les objets restent dans le périmètre par défaut et aucun filtrage ou partitionnement de la cartographie n'est appliqué.

!!! info "Trois mécanismes de contrôle d'accès"
    Mercator combine trois notions complémentaires :
    
    - Le **périmètre** définit *sur les objets de quel entité* ces droits s'appliquent 
    - Le [rôle](roles.md) définit *ce que* l'utilisateur peut faire dans un périmètre
    - L'assignation d'un [cartographe](cartographers.md) délègue la responsabilité d'**objets individuels précis**, indépendamment du périmètre
    
    Les rôles et les périmètres fonctionnent ensemble : les permissions d'un utilisateur dans un périmètre sont déterminées par son **rôle** dans ce **périmètre**.

!!! note "Modèle technique"
    - Chaque objet porte un entier `perimeter_id` (jamais NULL, toujours ≥ 1)
    - Un périmètre par défaut existe toujours avec `id = 1` (créé par la migration d'initialisation de la base de données)
    - Les noms d'objets sont **uniques par (perimeter_id, name)** — deux entités peuvent chacun avoir un « ServeurDNS »
    - Les périmètres sont gérés dans une table dédiée `perimeters`

## Fonctionnement des périmètres : avant et après activation

### Sans gestion des périmètres (état par défaut)

Lorsque la gestion des périmètres est **désactivée** :

- Chaque objet a `perimeter_id = 1` (le périmètre par défaut)
- **Aucun filtrage n'est appliqué** — tous les utilisateurs voient tous les objets
- Il n'y a pas de sélecteur de périmètre dans l'interface
- La fonctionnalité est totalement transparente

C'est l'état par défaut de toutes les instances Mercator existantes.

### Avec la gestion des périmètres (après activation)

Lorsque vous **activez** la gestion des périmètres depuis **Administration → Configuration → Périmètres** :

- Le filtrage est **activé**
- Les utilisateurs ne voient plus que les objets des périmètres auxquels leurs rôles donnent accès
- Un **sélecteur de périmètre** apparaît (si l'utilisateur a plusieurs périmètres)
- Les administrateurs peuvent **créer de nouveaux périmètres** (id > 1)
- Les administrateurs peuvent **déplacer des objets** entre périmètres

!!! note "Important"
    Activer les périmètres ne modifie pas les données existantes. Tous les objets restent assignés au périmètre par défaut (id = 1). Le  partitionnement ne prend effet que lorsque vous créez de nouveaux périmètres et réassignez des objets dans ceux-ci.

### Le périmètre par défaut

- **Existe toujours** avec `id = 1` (créé par la migration d'initialisation de la base de données)
- Ne peut pas être supprimé
- Porte un nom que vous définissez (initialement « Default »)
- Ne peut pas être renommé avec une chaîne vide
- Est le périmètre de tous les objets tant que vous ne les déplacez pas explicitement

## Activer la gestion des périmètres

La gestion des périmètres est **désactivée par défaut**. Pour l'activer :

1. Aller dans **Administration → Configuration → Périmètres**
2. Passer **Activer la gestion des périmètres** sur ON
3. Éventuellement, renommer le périmètre par défaut (ex. : « Siège »)
4. Enregistrer

### Que se passe-t-il à l'activation ?

✓ Le filtrage est activé (les utilisateurs ne voient plus que les périmètres qui leur sont assignés)  
✓ Le sélecteur de périmètre apparaît pour les utilisateurs multi-périmètres  
✓ Les outils d'administration pour créer/renommer/supprimer des périmètres deviennent disponibles  
✓ L'interface d'assignation des objets reçoit une liste déroulante de périmètre  
✓ Tous les objets existants restent dans le périmètre par défaut (id = 1)  

**Rien n'est encore masqué** — tant que vous n'avez pas créé de nouveaux périmètres et réassigné des objets, tous les utilisateurs voient toujours tout.

### Que se passe-t-il à la désactivation ?

✓ Le filtrage est désactivé  
✓ Tous les utilisateurs voient tous les objets, quelle que soit leur assignation de périmètre  
✓ Le sélecteur de périmètre disparaît de l'interface  
✓ Les outils d'administration des périmètres sont masqués  
✓ Tous les objets conservent leur `perimeter_id` (les données ne sont pas modifiées)  

Vous pouvez activer et désactiver la fonctionnalité sans risque de perte de données.

## Gérer les périmètres

Depuis **Administration → Configuration → Périmètres**, un administrateur peut :

### Ajouter un périmètre

1. Cliquer sur **+ Ajouter un périmètre**
2. Saisir un **nom** (2 à 32 caractères)
3. Cliquer sur **Créer**

Le périmètre reçoit un `id` numérique (à partir de 2). Les noms d'objets étant uniques par périmètre, deux établissements peuvent chacun avoir un « Serveur DNS ».

### Renommer un périmètre

1. Trouver le périmètre dans la liste
2. Cliquer sur **Modifier**
3. Changer le nom
4. Enregistrer

Même le périmètre par défaut peut être renommé (ex. : « Siège »).

### Supprimer un périmètre

Un périmètre ne peut être supprimé que si :

- Il ne contient **aucun objet** (les réassigner d'abord)
- Il n'a **aucun rôle** (réassigner d'abord les utilisateurs à d'autres rôles)
- Ce n'est **pas le périmètre par défaut** (l'id = 1 ne peut pas être supprimé)

Pour supprimer :

1. Réassigner tous les objets à d'autres périmètres ou au périmètre par défaut
2. Réassigner tous les rôles à d'autres périmètres ou au périmètre par défaut
3. Cliquer sur **Supprimer** dans la liste des périmètres
4. Confirmer

La suppression est **définitive** et ne peut pas être annulée.

!!! warning "Avant de supprimer un périmètre"
    - Auditer : vérifier si des objets ou des rôles y sont encore assignés
    - Prévenir les utilisateurs de ce périmètre
    - Migrer les données vers un autre périmètre si nécessaire

## Rôles et périmètres

Chaque **rôle** est assigné à **exactement un périmètre**. Les permissions du rôle ne s'appliquent qu'aux objets de ce périmètre. (Voir la documentation *Rôles* pour le détail complet de la gestion des rôles.)

### Assigner un périmètre à un rôle

1. **Administration → Rôles** → Créer ou Modifier
2. Sélectionner un **Périmètre** (liste déroulante)
3. Sélectionner les permissions comme d'habitude
4. Enregistrer

### Accès multi-périmètres

Un **utilisateur** peut détenir plusieurs rôles, chacun dans un périmètre différent. Les périmètres accessibles à l'utilisateur sont l'**union des périmètres de tous ses rôles**.

#### Exemple : équipe multi-établissements

Alice est responsable de deux établissements :

- **Rôle « Admin »** dans le Périmètre **1** (« Siège »)
  → Accès complet à tous les objets du Siège

- **Rôle « Lecteur »** dans le Périmètre **2** (« Agence A »)
  → Accès en lecture seule à tous les objets de l'Agence A

**Résultat :** Alice voit les objets des deux périmètres. Elle peut modifier les objets du Siège mais seulement consulter ceux de l'Agence A. Si un flux relie les deux, elle le voit des deux côtés.

### Accès administrateur

Les **administrateurs** ne sont jamais filtrés par périmètre. Ils voient toujours l'ensemble de la cartographie, quels que soient les périmètres existants ou leur nombre.

### Que se passe-t-il quand la gestion des périmètres est désactivée ?

Lorsque vous **désactivez** la gestion des périmètres :

- Toutes les assignations de périmètre des rôles restent inchangées dans la base de données
- Mais **aucun filtrage n'est appliqué** — tous les utilisateurs voient tous les objets
- Vous pouvez réactiver la fonctionnalité plus tard, et le filtrage reprendra

Cela permet d'activer et de désactiver la fonctionnalité sans perdre la configuration des rôles.

!!! tip "Travailler sur plusieurs établissements"
    Comme un rôle porte un seul périmètre, un utilisateur qui doit travailler dans plusieurs établissements reçoit **plusieurs rôles**, un par périmètre. Cela permet aussi de combiner un rôle en *lecture seule* dans un périmètre avec un rôle en *lecture-écriture* dans un autre.

!!! info "Tout voir est réservé aux administrateurs"
    Il n'existe pas de périmètre « super » qui verrait tous les autres. Un utilisateur qui doit superviser **l'ensemble** de la cartographie de tous les établissements est soit **administrateur**, soit détenteur d'un rôle dans chaque périmètre.

## Le périmètre de travail (sélecteur)

Lorsqu'un utilisateur est responsable de **plus d'un périmètre**, Mercator affiche un **sélecteur de périmètre** dans l'interface (juste au-dessus du champ de recherche).

### Ce que fait le sélecteur

Le sélecteur propose :

- Une entrée pour **tous vos périmètres** (sans filtrage)
- Chaque périmètre dans lequel vous avez un rôle

Sélectionner un périmètre a deux effets :

1. **Filtre** la page courante pour n'afficher que les objets de ce périmètre
2. Devient le **périmètre par défaut** de tout nouvel objet que vous créez

### Comportement du sélecteur

| Situation de l'utilisateur | Ce qu'il voit |
|---|---|
| Un seul périmètre (1 rôle) | Pas de sélecteur (nom du périmètre affiché en texte) |
| Plusieurs périmètres (plusieurs rôles) | Liste déroulante avec tous les périmètres accessibles |
| Gestion des périmètres non activée | Aucun sélecteur |
| Administrateur | Voit tous les objets (sans filtrage) |

!!! info "Changer de périmètre recharge la page"
    Lorsque vous changez de périmètre de travail, la page courante est rechargée. Les données de formulaire non enregistrées sont perdues. Si l'enregistrement courant est en dehors du nouveau périmètre, la page affiche « introuvable » (403 Forbidden).

!!! note "Le choix courant est conservé pour la session"
    Le périmètre sélectionné est conservé pendant toute la durée de votre session de connexion. À votre prochaine connexion, il revient à la valeur par défaut (tous les périmètres ou l'unique périmètre).

## Créer et déplacer des objets entre périmètres

### À la création d'un objet

Si vous avez **plus d'un périmètre**, une liste déroulante **Périmètre** apparaît dans le formulaire de création (avant le champ Nom).

- Elle est initialisée sur votre **périmètre de travail courant**
- Vous ne pouvez sélectionner que les périmètres auxquels vous avez accès
- La validation vérifie que le périmètre sélectionné est accessible en écriture

### À la modification d'un objet

Si vous avez **plus d'un périmètre**, la liste déroulante **Périmètre** affiche le périmètre courant de l'objet.

Vous pouvez :

- **Consulter** l'assignation de périmètre de l'objet
- Le **déplacer** vers un autre périmètre (si vous avez un accès en écriture aux deux)
- Le nom doit rester unique dans le périmètre cible

Après le déplacement, le journal d'audit de l'objet enregistre le changement.

### Modifier à travers vos périmètres

Vous ne pouvez modifier que les objets des périmètres pour lesquels vous avez un **rôle avec permissions d'écriture**. Les rôles en lecture seule ne permettent ni de déplacer ni de réassigner des objets.

Exemple :

- Rôle « Admin » dans le périmètre 1 → peut déplacer les objets du périmètre 1
- Rôle « Lecteur » dans le périmètre 2 → ne peut pas déplacer les objets du périmètre 2 (lecture seule)

### Identité de l'objet dans l'interface

Lorsqu'un objet est déplacé vers un périmètre autre que celui par défaut, son identité est affichée sous la forme :

```
[Nom du périmètre] / Nom de l'objet
```

Exemple : `[Agence A] / prod-db-01`, pour indiquer rapidement à quel périmètre il appartient.

!!! info "Vous ne pouvez utiliser que vos propres périmètres"
    Un utilisateur ne peut assigner un objet qu'à un périmètre dont il est responsable. Cette règle est appliquée à l'enregistrement du formulaire, et pas seulement dans la liste déroulante.

!!! tip "Le même nom peut être réutilisé entre périmètres"
    Les noms d'objets sont uniques **au sein d'un périmètre**. Deux établissements différents peuvent chacun avoir un objet nommé, par exemple, *« Serveur DNS »*, sans conflit.

## Flux applicatifs et liens physiques entre établissements

### Que sont les flux inter-périmètres ?

Un flux peut relier deux objets appartenant à des **périmètres différents**. Cela permet d'**identifier les interactions entre établissements** tout en gardant séparée la liste d'objets de chaque établissement.

Exemple :

```
Serveur DNS du Siège (Périmètre 1)
    ↓ résout pour ↓
Serveur de messagerie de l'Agence A (Périmètre 2)
```

### Qui voit un flux inter-périmètres ?

Les flux applicatifs et les liens physiques **relient deux objets qui peuvent appartenir à des périmètres différents**. Plutôt que d'appartenir à un seul périmètre, un tel lien est **visible depuis chaque périmètre qu'il touche**.

Un flux est **visible** des utilisateurs de **tout périmètre qu'il touche** :

- Les utilisateurs ayant des rôles dans le Périmètre 1 voient le flux (du point de vue du Siège)
- Les utilisateurs ayant des rôles dans le Périmètre 2 voient le flux (du point de vue de l'Agence A)
- Les utilisateurs des autres périmètres ne voient PAS le flux

C'est ce qui permet d'**identifier les flux entre applications d'établissements différents** : le même flux apparaît aux équipes des deux établissements, chacune le voyant de son côté.

### Objets locaux et objets distants

Dans un flux reliant des périmètres, vous voyez :

- Les **objets locaux** (dans votre périmètre de travail courant ou visibles via vos rôles)
  → affichés en **détail complet** (nom, type, tous les attributs)

- Les **objets distants** (dans un périmètre que vous ne gérez pas)
  → affichés sous forme de **fiche de référence** (nom + étiquette du périmètre)
  → vous ne pouvez pas modifier directement les objets distants
  → cliquer dessus ne mène PAS à la fiche complète (403 interdit)

Exemple (pour un administrateur du Siège) :

```
Mon serveur DNS (détail complet, modifiable)
    ↓ résout pour ↓
[Agence A] / Relais de messagerie (fiche de référence, lecture seule)
```

!!! info "Les enregistrements d'un autre périmètre apparaissent comme une référence"
    Lorsqu'un flux pointe vers un objet situé dans un périmètre que vous ne gérez pas, cet objet distant est affiché sous forme de **courte référence** (son nom et son périmètre) plutôt que sa fiche complète, de sorte que le flux reste lisible sans exposer les détails de l'autre établissement.

### Créer des flux entre périmètres

Vous pouvez créer un flux vers tout objet que vous pouvez **voir** :

- Les objets des périmètres que vous gérez
- Les objets dont vous êtes le **cartographe** désigné
- Les objets distants (uniquement si vous avez un rôle en lecture dans ce périmètre)

Toutefois, **au moins une extrémité doit se trouver dans un périmètre que vous pouvez modifier**. Vous ne pouvez pas créer un flux orphelin entre deux périmètres auxquels vous n'avez qu'un accès en lecture.

### Audit et propriété

Le créateur du flux et sa date de création sont enregistrés. Le flux appartient au périmètre principal du créateur (le premier périmètre qu'il gère, ou celui où se trouve l'extrémité principale).

!!! info "Les flux ne sont pas supprimés lorsque vous supprimez un périmètre"
    Si vous supprimez un périmètre, ses objets sont supprimés, mais les flux pointant vers ces objets subsistent (avec des références rompues). Planifiez soigneusement vos suppressions.

## Importer des données avec les périmètres (GLPI)

Lorsque Mercator synchronise des données depuis GLPI, les objets importés sont automatiquement assignés à un **périmètre cible** défini dans la configuration du connecteur.

### Configuration

Dans les paramètres du connecteur GLPI :

1. Sélectionner un **Périmètre cible** (liste déroulante des périmètres disponibles)
2. Laisser vide pour utiliser le **périmètre par défaut** (id = 1)
3. Enregistrer

Tous les objets importés ou mis à jour depuis cette instance GLPI auront le `perimeter_id` indiqué.

### Réconciliation

Le connecteur GLPI utilise **deux clés** pour faire correspondre les objets existants :

- **L'ID GLPI** (stocké sous la forme `[glpi_id:NNN]` dans la description de l'objet)
- **Le périmètre** (issu de la configuration du connecteur)

Cela signifie que :

- Le même ID GLPI dans des périmètres différents = **objets Mercator différents**
- Le même nom dans des périmètres différents = **objets Mercator différents** (les noms sont uniques par périmètre, pas globalement)

### Comportement à la ré-importation

**Lorsque vous relancez la synchronisation :**

1. **Les objets existants sont mis à jour sur place** (synchronisation non destructive)
   - Même ID GLPI + même périmètre → mise à jour de l'enregistrement existant
   - Nouvel ID GLPI ou nouveau périmètre → création d'un nouvel enregistrement

2. **Si vous changez le périmètre cible du connecteur :**
   - Les objets déjà importés restent dans leur périmètre d'origine
   - Les objets nouvellement importés arrivent dans le nouveau périmètre
   - Résultat : les objets d'une même instance GLPI se répartissent sur plusieurs périmètres

3. **Pour déplacer des objets importés vers un autre périmètre :**
   - Modifier manuellement chaque objet dans Mercator et réassigner le périmètre, OU
   - Supprimer le connecteur, changer le périmètre cible et relancer la synchronisation (cela crée des doublons, donc déconseillé)

!!! tip "Garder stable la correspondance de périmètres GLPI → Mercator"
    Décidez de votre correspondance de périmètres GLPI → Mercator **avant** la première importation. Si vous devez la changer plus tard, planifiez soigneusement la migration pour éviter de répartir les objets sur des périmètres non voulus.

## Sécurité : frontières des périmètres et contrôle d'accès

### Principe : les utilisateurs voient ce dont ils sont responsables

Par défaut, les utilisateurs ne voient et ne peuvent modifier que les objets des périmètres pour lesquels un rôle leur est assigné. Les frontières des périmètres sont appliquées à chaque couche :

- Au niveau des requêtes (filtrage automatique par scope)
- Au niveau des politiques (gates d'autorisation)
- Dans la validation des formulaires (on ne peut assigner des objets qu'à ses propres périmètres)

### Ce que les utilisateurs ne peuvent pas faire

- **Consulter** des objets en dehors de leurs périmètres assignés (sauf les références distantes dans les flux)
- **Modifier, déplacer ou supprimer** des objets en dehors de leurs périmètres assignés
- **Créer un flux** vers un objet d'un périmètre auquel ils n'ont pas accès
- **Assigner** un objet à un périmètre pour lequel ils n'ont pas d'accès en écriture
- **Accéder à la gestion des périmètres** (réservée aux administrateurs)

### Application des règles

```php
// Scope de requête : filtrage automatique à chaque lecture
Object::whereIn('perimeter_id', $user->rolePerimeters())
      ->get();

// Gate de politique : avant toute écriture
Gate::denies('update', $object) 
    // Vérifie : l'objet est-il dans l'un des périmètres de l'utilisateur ?
    abort_if(..., 403);

// Validation de formulaire : à la création/modification
$request->validate([
    'perimeter_id' => 'in:' . implode(',', $user->rolePerimeters())
]);
```

### Assignations de cartographes

Un **cartographe** est responsable d'un objet individuel précis, indépendamment du périmètre. Un cartographe :

- **Voit toujours** l'objet qui lui est assigné (même s'il est dans un autre périmètre)
- Peut **modifier** l'objet si son rôle lui accorde des permissions d'écriture dans ce périmètre
- Ne peut pas déplacer l'objet vers un autre périmètre (sauf s'il y a aussi un rôle)

### Piste d'audit

Chaque déplacement d'objet entre périmètres est journalisé :

- **Qui** l'a déplacé (utilisateur + rôle)
- **Quand** (horodatage)
- **Quoi** (nom de l'objet + ancien périmètre → nouveau périmètre)

Les administrateurs peuvent consulter les changements dans le **Journal d'audit** (Administration → Logs).

## Bonnes pratiques

### Avant d'activer la gestion des périmètres

1. **Identifier vos établissements/unités**
   - Chaque unité autonome et auto-gérée = 1 périmètre
   - Éviter de créer des périmètres par commodité organisationnelle (ex. : par équipe ou par fonction)
   - Ne pas créer de périmètres « vides » par anticipation

2. **Planifier votre structure de rôles**
   - Associer chaque équipe/utilisateur aux périmètres qu'il gère
   - Décider des niveaux de permission (Admin, Éditeur, Lecteur, etc.)
   - Identifier les rôles partagés (ex. : « Lecture seule globale » pour les dirigeants)
   - Voir la documentation *Rôles* pour la configuration des rôles

3. **Préparer le nom du périmètre par défaut**
   - Renommer le périmètre par défaut (id = 1) pour qu'il corresponde à votre établissement principal (ex. : « Siège »)
   - C'est le seul périmètre qui ne peut pas être supprimé

### Activer progressivement

1. **Activer** la fonctionnalité (Administration → Configuration → Périmètres)
2. **Renommer** le périmètre par défaut si besoin
3. **Créer** de nouveaux périmètres pour chaque établissement
4. **Déplacer** les objets existants vers leurs périmètres corrects
5. **Créer** ou mettre à jour les rôles et assigner les utilisateurs (voir la documentation *Rôles*)
6. **Tester** avec une équipe pilote avant le déploiement complet

### Déplacer des objets en toute sécurité

Avant de déplacer un objet vers un autre périmètre :

1. Vérifier que le **nom de l'objet n'existe pas déjà** dans le périmètre cible
2. Vérifier que les **flux qui pointent vers lui** restent valides (les flux s'adaptent automatiquement)
3. Consulter la **piste d'audit** pour comprendre l'historique de l'objet
4. Prévenir les équipes concernées (surtout si cela affecte leurs flux)

### Supprimer un périmètre en toute sécurité

1. **Auditer** le contenu du périmètre
   - Lister tous les objets (Administration → Périmètres → [périmètre] → Objets)
   - Lister tous les rôles (Administration → Périmètres → [périmètre] → Rôles)

2. **Migrer les données**
   - Déplacer tous les objets vers un autre périmètre ou le périmètre par défaut
   - Réassigner tous les rôles à un autre périmètre ou au périmètre par défaut

3. **Supprimer** le périmètre
   - Administration → Configuration → Périmètres → Supprimer
   - Confirmer (la suppression est définitive)

4. **Vérifier** que la migration est terminée
   - Contrôler qu'aucun objet ni rôle ne référence encore le périmètre supprimé

### Quand NE PAS utiliser les périmètres

Les périmètres sont conçus pour la **séparation organisationnelle** (plusieurs établissements, filiales, unités métier). Ils ne sont **pas** adaptés à :

- Un **regroupement temporaire** (utiliser plutôt des étiquettes)
- Un **filtrage au niveau d'une équipe** (utiliser les rôles et permissions)
- La **catégorisation d'objets** (utiliser les types, étiquettes ou une taxonomie)

Si vous n'avez pas plusieurs établissements autonomes, laissez la gestion des périmètres **désactivée**. Elle ajoute de la complexité sans aucun bénéfice.

## Dépannage

### « J'ai activé la gestion des périmètres mais rien n'a changé »

✓ **Comportement attendu.** Tous les objets sont encore dans le périmètre par défaut (id = 1).  
✓ Créez de nouveaux périmètres et déplacez des objets pour partitionner la cartographie.  
✓ Assignez des rôles aux utilisateurs dans chaque périmètre (voir la documentation *Rôles*).  

### « Je ne peux pas déplacer un objet vers un autre périmètre »

Raisons possibles :

- Vous n'avez pas de **rôle en écriture** dans le périmètre cible (seuls des rôles en lecture)
- Le périmètre cible contient déjà un objet portant le même nom
- Vous essayez de déplacer un objet dont vous êtes **cartographe** (demandez à un administrateur)

**Solution :** créer ou demander d'abord un rôle avec accès en écriture dans le périmètre cible (voir la documentation *Rôles*).

### « Un utilisateur ne voit pas des objets qu'il devrait voir »

Vérifier :

1. Les **rôles de l'utilisateur** (Administration → Utilisateurs → [utilisateur] → Rôles)
   - Vérifier que les rôles sont assignés aux bons périmètres
   - Vérifier que les rôles ont des permissions de lecture ou d'écriture (voir la documentation *Rôles*)

2. Le **périmètre de l'objet** (ouvrir l'objet, vérifier le champ Périmètre)
   - Vérifier que l'objet est dans l'un des périmètres de l'utilisateur

3. Les **assignations de cartographes** (si l'objet est isolé)
   - Vérifier si l'utilisateur est désigné comme cartographe

### « Un flux a disparu après que j'ai déplacé un objet »

Les flux ne sont **pas supprimés** lorsque vous déplacez un objet. Ils sont conservés et restent visibles pour les utilisateurs des périmètres concernés. Si le flux semble avoir disparu :

1. Vérifier que vous avez toujours un rôle dans le périmètre d'où part le flux
2. Recharger la page (le cache peut être périmé)
3. Consulter le journal d'audit pour voir si le flux a été explicitement supprimé

### « J'ai supprimé un périmètre et je n'ai plus accès à mes objets »

Cela arrive si :

- Les objets n'ont **pas été réassignés** avant la suppression (ils ont été perdus)
- Vous aviez encore un **rôle dans ce périmètre** (désormais supprimé)

**Solution :**

- Si les données sont perdues, restaurer depuis une sauvegarde
- Si seul le rôle manque, demander à un administrateur de vous réassigner à un autre périmètre (voir la documentation *Rôles*)
