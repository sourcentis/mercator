# Diagramme Cairn

Le **diagramme Cairn** génère automatiquement une vue applicative à partir d'une sélection d'objets de la cartographie : applications, services applicatifs, modules, bases de données, flux et entités.

Contrairement aux [cartes](maps.fr.md), qui se composent à la main, le diagramme Cairn est **calculé** : on choisit quelques objets de départ, Mercator en déduit les objets liés et les flux qui les relient, puis le diagramme est dessiné selon la notation [Cairn](https://github.com/R0kshan/cairn/).

Ce mode est idéal pour :

- obtenir rapidement le schéma d'architecture d'une application ;
- visualiser les échanges entre plusieurs applications ;
- documenter un périmètre applicatif sans dessiner chaque boîte ;
- vérifier la cohérence des flux renseignés dans la cartographie.

🔍 Voir aussi l'outil [Exploration et Dépendances](features.fr.md)

## Accès

L'outil est accessible via *Outils ➡️ Diagramme Cairn*.

Il nécessite la permission `cairn_access`, qui se coche dans la section **Outils** de la page de [gestion des rôles](roles.fr.md). Le menu **Outils** lui-même n'est visible qu'avec la permission `tools_access`.

Lors de la mise à jour vers la version qui introduit cet outil, la permission `cairn_access` est automatiquement accordée à tous les rôles disposant déjà de `explore_access`.

Les objets proposés et dessinés respectent le [périmètre](perimeters.fr.md) et les droits de [cartographe](cartographers.fr.md) de l'utilisateur : un objet hors périmètre n'apparaît ni dans la recherche ni dans le diagramme.

---

## Interface

| Élément de l'interface | Type            | Rôle                                                                     |
|------------------------|-----------------|--------------------------------------------------------------------------|
| **Type d'objet**       | Liste déroulante | Type d'objet à rechercher (entité, application, service, module, base de données, flux) |
| **Objet**              | Liste avec recherche | Sélectionne l'objet à ajouter (recherche sur le nom, 50 résultats maximum) |
| **Ajouter**            | Bouton bleu     | Ajoute l'objet à la sélection                                            |
| **Effacer**            | Bouton rouge    | Vide toute la sélection                                                  |
| **Objets sélectionnés** | Liste          | Objets de départ du diagramme, chacun avec un bouton de suppression      |
| **Diagramme**          | Zone de dessin  | Diagramme généré, avec défilement s'il dépasse la largeur de la page     |
| **Documentation**      | Lien            | Ouvre la documentation de la notation Cairn                              |

Le diagramme est **recalculé à chaque modification** de la sélection : il n'y a pas de bouton de génération.

Si la notation Cairn détecte une incohérence, un message d'erreur (rouge) ou d'avertissement (orange) s'affiche au-dessus du diagramme.

---

## Construction du diagramme

### Ce que chaque type d'objet ajoute

| Objet sélectionné       | Ce qui est dessiné                                                                            |
|-------------------------|-----------------------------------------------------------------------------------------------|
| **Application**         | L'application, ses services applicatifs et les modules de ces services, ainsi que tous leurs flux |
| **Entité**              | L'entité (nœud externe) et toutes les applications qu'elle utilise, développées comme ci-dessus |
| **Service applicatif**  | Le service seul (sans ses modules) et ses flux                                                |
| **Module applicatif**   | Le module et ses flux                                                                         |
| **Base de données**     | La base de données et ses flux                                                                |
| **Flux applicatif**     | Les deux extrémités du flux et la flèche qui les relie                                        |

### Les objets au bout des flux

Lorsqu'un flux relie un objet sélectionné à un autre objet, ce dernier est **dessiné** pour que la flèche ait une destination, mais il **n'est pas développé** : ses propres services, modules et flux ne sont pas ajoutés.

Pour développer cet objet, il suffit de l'ajouter à la sélection.

### Imbrication des services et des modules

Un service ou un module est dessiné **à l'intérieur** de son application lorsque cette application est elle-même présente dans le diagramme.

S'il n'a aucune application propriétaire dans le diagramme, ou s'il en a plusieurs, il est dessiné comme une **boîte grise** indépendante. Une note de légende l'indique.

### Groupes applicatifs

Les applications qui appartiennent à un **bloc applicatif** sont regroupées dans un cadre **jaune** portant le nom du bloc. Une note de légende l'indique.

---

## Représentation

| Objet Mercator             | Représentation dans le diagramme                                     |
|----------------------------|----------------------------------------------------------------------|
| Bloc applicatif            | Cadre jaune contenant ses applications                               |
| Application                | Boîte contenant ses services et modules                              |
| Application externe        | Nœud externe (si elle ne contient aucun service ni module dessiné)   |
| Service / module applicatif | Boîte imbriquée dans son application, ou boîte grise s'il est orphelin |
| Base de données            | Stockage de données                                                  |
| Base de données externe    | Nœud externe                                                         |
| Entité                     | Nœud externe, reliée à ses applications par une flèche pointillée « utilise » |
| Flux applicatif            | Flèche de la source vers la destination, libellée avec le nom du flux (ou son type) |
| Flux bidirectionnel        | Deux flèches, une dans chaque sens                                   |

Une application ou une base de données est considérée comme **externe** lorsque son attribut *Externe* contient le mot « extern » (sans tenir compte des majuscules), par exemple « Externe » ou « External ».

Un flux dont l'une des extrémités n'est pas dessinée (objet hors périmètre ou supprimé) n'est pas affiché.

---

## Exemples

### Architecture d'une application

> *« Quels sont les composants de RH-Solution et avec quoi échange-t-elle ? »*

1. Type d'objet : **Applications**
2. Objet : `RH-Solution` ➡️ **Ajouter**

Le diagramme montre RH-Solution avec ses services et modules, ainsi que toutes les applications et bases de données avec lesquelles elle échange des flux.

### Échanges entre deux applications

> *« Comment RH-Solution et Paie communiquent-elles ? »*

1. Ajouter l'application `RH-Solution`
2. Ajouter l'application `Paie`

Les deux applications sont développées ; les flux qui les relient apparaissent entre leurs services et modules.

### Applications utilisées par une entité

> *« Quelles applications utilise la Direction financière ? »*

1. Type d'objet : **Entités**
2. Objet : `Direction financière` ➡️ **Ajouter**

L'entité est reliée par une flèche pointillée à chacune de ses applications, qui sont développées avec leurs flux.

### Un flux précis

> *« Entre quels composants passe le flux Export-Salaires ? »*

1. Type d'objet : **Flux applicatifs**
2. Objet : `Export-Salaires` ➡️ **Ajouter**

Seules les deux extrémités du flux et la flèche qui les relie sont dessinées.

---

## Bonnes pratiques

- **Commencer petit** : une ou deux applications suffisent souvent ; ajouter ensuite les objets au bout des flux pour les développer.
- **Renseigner les liens application ↔ service ↔ module** : sans eux, les services et modules apparaissent en gris, hors de leur application.
- **Nommer les flux** : le nom du flux sert de libellé à la flèche ; à défaut, son type est utilisé.
- **Utiliser les blocs applicatifs** pour regrouper visuellement les applications d'un même domaine.
- **Renseigner l'attribut *Externe*** des applications et bases de données hébergées hors de l'organisation pour les distinguer dans le diagramme.
