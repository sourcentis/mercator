# Analyse — Refonte du rapport de cartographie (`CartographyController`)

> Document temporaire (Phase 1 — analyse et plan). À supprimer après validation et implémentation.

## 0. Portée et décisions

- **7 vues** dans le rapport (les 6 actées initialement + **GDPR ajoutée sur décision utilisateur** du 2026-07-05, cf. §0.3). Chaque vue correspond à une entrée du futur `vues[]` (valeurs `1` à `7`).
- `granularity` supprimé partout (contrôleur, formulaire, logique conditionnelle).
- Objets exclus (jamais dans le rapport) : `User`, `Role`, `Permission`, `Graph`, `SavedQuery`, `AuditLog`, `CPEProduct`, `CPEVendor`, `CPEVersion`. On y ajoute de fait deux objets de configuration qui ne sont ni cartographiés ni rattachables à une vue : `Cartographer` (contact/propriétaire, référencé mais jamais affiché comme relation dans aucun `_details.blade.php` malgré `HasCartographers` présent sur quasiment tous les modèles) et `Parameter` (réglages applicatifs). Aucun des deux n'a de section dédiée dans le rapport ; `Cartographer` n'apparaît nulle part comme champ affiché, il n'y a donc rien à régénérer pour lui.
- Architecture retenue : découpage par vue (`app/Services/Report/<Vue>Section.php`), orchestrateur `ReportBuilder`, helper PhpWord partagé, un `GraphBuilder` par vue sous `app/Services/Graph/`. Détails complets en §1.3.

### 0.1 Interprétation retenue pour la référence de contenu (§1.1)

La mission précise que **le partial `_details.blade.php` évalué avec `withLink = false`** est la référence de contenu. Or l'inventaire (voir §1.1) montre que pour un nombre important d'objets, `show.blade.php` affiche des cartes/tableaux **supplémentaires en dehors du partial inclus** (ex. BIA/DRP sur `Activity`, historique sur `Application`, configuration matérielle sur `PhysicalServer`/`Workstation`, tableaux de flux sur `Application`/`ApplicationService`/`ApplicationModule`/`Database`, hiérarchie parent/enfant sur `Information`, connexion/adressage sur `Subnetwork`, etc.).

**Interprétation retenue — confirmée par l'utilisateur le 2026-07-05** : la référence de contenu est **la page `show.blade.php` complète** (partial inclus + contenu additionnel affiché directement dans `show.blade.php`), à l'exclusion des seuls éléments de navigation/édition (liens d'édition, boutons de suppression, icônes crayon). La consigne « `_details.blade.php` avec `withLink=false` » est comprise comme une précision technique sur l'évaluation du partial lui-même (ignorer son bloc `@if($withLink)`), pas comme une restriction excluant le contenu hors-partial de `show.blade.php`. Chaque inventaire ci-dessous liste donc, objet par objet, le contenu du partial **et**, quand il existe, le contenu additionnel de `show.blade.php`, sous un flag explicite « Contenu additionnel hors partial ». Toutes les méthodes `add*()` de la Phase 2 (§1.4) doivent donc couvrir l'intégralité de ces lignes, pas seulement celles du partial.

### 0.2 Bugs et incohérences trouvés dans le code existant

> **Mise à jour 2026-07-05** : les items 1–8, 12 et 13 ont été **corrigés directement dans le code applicatif actuel** (indépendamment de la refonte du rapport, ce sont des bugs/incohérences vivants sur les écrans interactifs), avant même le démarrage de la Phase 2. Suite de tests complète (`./vendor/bin/pest`) rejouée après chaque lot de correctifs : 1587 passed, 0 failed (+ tests dédiés `Domain`/`Peripheral`/`Workstation`). Détail des correctifs en fin de tableau (colonne Statut). **Décision utilisateur (2026-07-05)** sur les items restants : items 9–11 laissés pour la Phase 2 (pas de patch de secours dans l'ancien `CartographyController`, pour éviter du travail jetable).

Le rapport étant régénéré **à partir de zéro** sur la base des `show.blade.php`/`_details.blade.php`, les anomalies suivantes, repérées pendant l'inventaire, ne seront pas reproduites dans le futur rapport (sauf indication contraire) :

| # | Objet | Anomalie | Recommandation | Statut |
|---|---|---|---|---|
| 1 | `Vlan` | `_details.blade.php` affiche `$vlan->id` (clé primaire) sous le libellé "vlan_id" au lieu du vrai champ `vlan_id`. L'ancien rapport Word, lui, utilise correctement `$vlan->vlan_id`. | Utiliser le vrai champ `vlan_id` dans le nouveau rapport. | **Corrigé** — `resources/views/admin/vlans/_details.blade.php` utilise désormais `$vlan->vlan_id`. |
| 2 | `Application` | Le bloc `security_need_auth` est gardé par `config('mercator-config.parameters.security_need_auth')`, clé qui n'existe pas (la vraie clé est `mercator.parameters.security_need_auth`) → toujours masqué, mort. | Utiliser la bonne clé de config, cohérente avec les autres objets (`MacroProcessus`, `Process`) qui l'utilisent correctement. | **Corrigé** — `resources/views/admin/applications/show.blade.php` utilise la clé `mercator.parameters.security_need_auth`. |
| 3 | `Database` | Le bloc `security_need_auth` est gardé par un `@if (false)` en dur → toujours masqué, incohérent avec les autres objets. | Traiter `authenticity` comme les autres objets (gate par config). | **Corrigé** — `resources/views/admin/databases/_details.blade.php` gate désormais par `config('mercator.parameters.security_need_auth')`. |
| 4 | `Application` | Ligne `external` : markup HTML mal formé (`<th>` enveloppant un `<td>`) — sans impact sur le contenu affiché. | Sans objet pour la régénération (nouveau code, pas de HTML à corriger). | **Corrigé** — markup `<th>`/`<td>` normalisé dans `applications/show.blade.php` (correction ponctuelle, faite à l'occasion du correctif #2 sur le même fichier). |
| 5 | `AdminUser` | `description` dans un `<dt>` imbriqué dans un `<th>` — markup quirk sans impact contenu. | Idem, sans objet. | **Corrigé** — `<dt>` retiré dans `resources/views/admin/adminUser/_details.blade.php`. |
| 6 | `WifiTerminal` | Les champs CPE (`vendor`/`product`/`version`) utilisent par erreur les clés de traduction `cruds.application.fields.*` au lieu de `cruds.wifiTerminal.fields.*`. | Utiliser les clés `cruds.wifiTerminal.fields.*` si elles existent, sinon les créer (cf. Phase 2, contraintes traduction). | **Corrigé** — clés `cruds.wifiTerminal.fields.vendor/product/version` créées dans `resources/lang/{en,fr}/cruds.php` et utilisées dans `wifiTerminals/show.blade.php`. |
| 7 | `Domain` | Les libellés des relations `forestAds`/`logicalServers` réutilisent les clés de titre d'autres objets (`cruds.forestAd.title`, `cruds.logicalServer.title`) plutôt que des clés `cruds.domaine.fields.*` dédiées. | Utiliser des clés dédiées — décision utilisateur du 2026-07-05. | **Corrigé** — `resources/views/admin/domains/_details.blade.php` utilise désormais `cruds.domaine.fields.forestAds` / `cruds.domaine.fields.logical_servers` (ces clés existaient déjà dans `lang/{en,fr}/cruds.php`, simplement inutilisées). Au passage, la valeur anglaise de `logical_servers`/`logical_servers_helper` était en français par erreur — corrigée aussi. |
| 8 | `Peripheral` | Le champ `domain` est une chaîne libre, pas une relation — à ne pas confondre avec `Workstation.domain` qui est une vraie relation vers `Domain`. | Transformer `domain` en `domain_id` (FK vers `domains`), à l'image de `Workstation`. Décision utilisateur du 2026-07-05. | **Corrigé** — migration `2026_07_05_000000_change_peripheral_domain_to_domain_id.php` (ajout `domain_id` + FK vers `domains`, backfill par correspondance de nom, suppression de l'ancienne colonne `domain`) ; `Peripheral` a une relation `domain(): BelongsTo` ; `PeripheralController` (Admin) sert désormais une liste `$domains` (`id => name`) au lieu d'une liste de valeurs texte distinctes ; vues `create`/`edit`/`_details`/`index` mises à jour (select2 par `domain_id`, lien vers `admin.domains.show` dans `_details`) ; `PeripheralFactory` génère un `Domain::factory()`. Le rapport Word (`CartographyController`) et les graphes (Infrastructure logique/physique) n'affichaient déjà pas ce champ — aucun changement requis de ce côté. |
| 9 | Ancien rapport (`CartographyController`) | `AdminUser` totalement absent de la section Administration (ni requêté, ni affiché, ni dans le graphe) alors que l'écran interactif `AdministrationView`/`administration.blade.php` l'inclut pleinement. | Corrigé de facto par la régénération à partir de `_details.blade.php`/`show.blade.php` (AdminUser fait partie de l'inventaire §1.1 Administration). | **En attente** — dépend de la Phase 2 (cf. note ci-dessous) ; non patché dans l'ancien contrôleur pour éviter du travail jetable. |
| 10 | Ancien rapport | `PhysicalSecurityDevice` requêté mais jamais ajouté au graphe DOT (la boucle s'arrête avant), alors qu'il a bien sa propre section de contenu textuel. | Corrigé par le futur `PhysicalInfrastructureGraphBuilder` (§1.2). | **En attente** — idem, dépend de la Phase 2. |
| 11 | Ancien rapport | Aucune section pour `Zone`, `PhysicalLink`, `Backup`, `LogicalFlow`, `ApplicationFlow` (graphe), `NetworkSwitch`, `SecurityDevice`, `Cluster`, `Container` (graphe) — objets absents de l'ancien rapport malgré migrations existantes. | Corrigé de facto : tous ces objets font partie de l'inventaire de contenu et/ou de graphe ci-dessous. | **En attente** — idem, dépend de la Phase 2. |
| 12 | Écrans interactifs (`AdministrationView`) | `AdminUser::All()` non filtré par `Cartographer::scopedQuery(...)` contrairement aux autres collections de la même méthode. | Signalé pour information ; le nouveau report doit scoper `AdminUser` comme les autres objets administratifs pour rester cohérent (à confirmer, cf. §1.2). | **Corrigé** — `app/Http/Controllers/Report/AdministrationView.php` utilise désormais `Cartographer::scopedQuery(AdminUser::query())->get()`. |
| 13 | Écrans interactifs (`GDPRView`) | La gate d'accès ne vérifie pas `SecurityControl::class` (seulement `DataProcessing`, `MacroProcessus`, `Process`). | Le futur vue 7 (GDPR) du rapport devra vérifier l'accès aux deux objets. | **Corrigé** — `app/Http/Controllers/Report/GDPRView.php` ajoute `SecurityControl::class` à la vérification `Cartographer::canAccessAny(...)`. |

**Note sur les items 9–11** : contrairement aux items 1–6/12/13 (bugs vivants sur des écrans interactifs indépendants de la refonte), les items 9–11 ne décrivent pas un bug de code isolé mais l'incomplétude structurelle de la méthode monolithique `CartographyController::cartography()` — précisément ce que la Phase 2 remplace. Les patcher maintenant dans l'ancien contrôleur serait un travail jetable si la Phase 2 est menée à son terme (cf. §1.4, Étape 3, qui supprime cette méthode). Décision en attente de l'utilisateur : patch de secours dans l'ancien contrôleur (si la Phase 2 est différée) ou attente de la régénération complète.

### 0.3 GDPR (vue 7) — décision

`DataProcessing` et `SecurityControl` (menu "GDPR") ne faisaient partie d'aucune des 6 vues d'origine et n'avaient pas d'option dans `vues[]`. **Décision utilisateur : ajouter une 7ᵉ vue GDPR.** Bonne nouvelle : les clés de traduction `cruds.report.cartography.gdpr` existent déjà dans `lang/en/cruds.php` et `lang/fr/cruds.php` ("GDPR"/"RGPD"), il ne reste qu'à ajouter l'option `<option value="7">` au formulaire et le bloc `vues[] == '7'` au contrôleur/`ReportBuilder`.

---

## 1.1 Inventaire de contenu par vue

Convention des tableaux : `#` = ordre d'apparition, `Libellé (clé trans)` = clé exacte de traduction, `Type de rendu`, `Source` = attribut/relation Eloquent (nécessaire pour les `with()`), `Notes`.

Sauf mention contraire : le bloc `@if($withLink)` (lien d'édition vers la page show du même objet autour du champ `name`) existe dans presque tous les partials et est **toujours ignoré** (édition hors périmètre). Aucun objet des 7 vues n'affiche de relation `Cartographer` dans son `show`/`_details` (le trait `HasCartographers` sert uniquement au scoping des requêtes côté contrôleurs interactifs, jamais à l'affichage). `Document` est utilisé de deux façons distinctes selon les objets : (a) FK simple `icon_id` → une image (la majorité des objets physiques/logiques), (b) relation `BelongsToMany` d'attachement multiple, rendue en liste de liens (uniquement sur `Relation`, `ExternalConnectedEntity`, `DataProcessing` — pas de mécanisme polymorphe générique).

### Vue 1 — Écosystème

#### Entity (`entities`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.entity.fields.name` | texte | `name` | |
| 2 | `cruds.entity.fields.type` | texte | `type` | |
| 3 | `cruds.entity.fields.parent_entity` | lien (Entity) | `parentEntity` (BelongsTo self) | |
| 4 | `cruds.entity.fields.subsidiaries` | liste de liens (Entity) | `entities` (HasMany self via `parent_entity_id`) | affiché seulement si count > 0 |
| 5 | `cruds.entity.fields.description` | HTML riche + icône | `description`, `icon_id` (Document, fallback `/images/application.png`) | |
| 6 | `cruds.entity.fields.security_level` | HTML riche | `security_level` | |
| 7 | `cruds.entity.fields.contact_point` | HTML riche | `contact_point` | |
| 8 | `cruds.entity.fields.relations` | liste de liens couplés (Relation + Entity destination/source) | `sourceRelations`, `destinationRelations` (HasMany) | |
| 9 | `cruds.entity.fields.processes` | liste de liens (Process) | `processes` (BelongsToMany) | → vue Système d'information |
| 10 | `cruds.entity.fields.exploits` | liste de liens (Application + Database) | `respApplications`, `databases` (HasMany) | → vue Applicatif |

Note : `is_external` a été supprimé ; le caractère externe d'une entité est désormais porté par l'attribut `extern` dans le champ `attributes`, exposé via `Entity::isExternal()`.

#### Relation (`relations`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.relation.fields.name` | texte | `name` | ce partial ne gère pas `withLink` (jamais un lien) |
| 2 | `cruds.relation.fields.type` | texte | `type` | |
| 3 | `cruds.relation.fields.attributes` | badges | `attributes` (chaîne éclatée) | |
| 4 | `cruds.relation.fields.reference` | texte | `reference` | |
| 5 | `cruds.relation.fields.order_number` | texte | `order_number` | |
| 6 | `cruds.relation.fields.responsible` | texte | `responsible` | |
| 7 | `cruds.relation.fields.source` | lien (Entity) | `source` (BelongsTo) | |
| 8 | `cruds.relation.fields.destination` | lien (Entity) | `destination` (BelongsTo) | |
| 9 | `cruds.relation.fields.description` | HTML riche | `description` | |

**Contenu additionnel hors partial** (carte "Termes du contrat" + suite, dans `show.blade.php`) :

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 10 | `start_date` / `end_date` | dates | `start_date`, `end_date` | |
| 11 | `active` | booléen (affiché seulement si vrai) | `active` | |
| 12 | `importance` | badge-enum (1–4 low/medium/high/critical) | `importance` | |
| 13 | tableau imbriqué "Date/Valeur" | **sous-tableau imbriqué** | `RelationValue` via `relation->values()` | `RelationValue` n'a pas d'écran propre — nesting confirmé sous Relation uniquement |
| 14 | graphique d'historique de prix | graphique (Chart.js) | dérivé de `values` | non transposable tel quel dans un document Word — proposer un tableau ou l'omettre (à trancher Phase 2) |
| 15 | `comments` | HTML riche | `comments` | |
| 16 | pièces jointes | liste de liens (fichiers) | `documents` (BelongsToMany → `Document`) | seul usage "attachment multiple" de cette vue |

#### RelationValue

Pas d'écran admin propre — entièrement imbriqué sous `Relation` (cf. ligne 13 ci-dessus). Pas de section indépendante dans le rapport.

---

### Vue 2 — Système d'information

#### MacroProcessus (`macroProcessuses`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.macroProcessus.fields.name` | texte | `name` | |
| 2 | `cruds.macroProcessus.fields.description` | HTML riche | `description` | |
| 3 | `cruds.macroProcessus.fields.io_elements` | HTML riche | `io_elements` | |
| 4 | `cruds.macroProcessus.fields.security_need` | badges C/I/A/T(+Auth) | `security_need_c/i/a/t(/auth)` | gate config correcte |
| 5 | `cruds.macroProcessus.fields.owner` | texte | `owner` | |
| 6 | `cruds.macroProcessus.fields.processes` | liste de liens (Process) | `processes` (HasMany) | |

Pas de contenu additionnel hors partial.

#### Process (`processes`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.process.fields.name` | texte | `name` | |
| 2 | `cruds.process.fields.macroprocessus` | lien (MacroProcessus) | `macroProcess` (BelongsTo) | |
| 3 | `cruds.process.fields.description` | HTML riche + icône | `description`, `icon_id` (fallback `/images/process.png`) | |
| 4 | `cruds.process.fields.in_out` | HTML riche | `in_out` | |
| 5 | `cruds.process.fields.security_need` | badges C/I/A/T(+Auth) | `security_need_c/i/a/t(/auth)` | |
| 6 | `cruds.process.fields.owner` | texte | `owner` | |
| 7 | `cruds.process.fields.activities` | liste de liens (Activity) | `activities` (BelongsToMany) | |
| 8 | `cruds.process.fields.entities` | liste de liens (Entity) | `entities` (BelongsToMany) | → Écosystème |
| 9 | `cruds.process.fields.informations` | liste de liens (Information) | `information` (BelongsToMany) | |
| 10 | `cruds.process.fields.applications` | liste de liens (Application) | `applications` (BelongsToMany) | → Applicatif |
| 11 | "BPMN" (pas de clé trans) | liste de liens (Graph/BPMN) | `graphs()` | **Graph exclu du périmètre** → cette ligne est à omettre du rapport (référence vers un objet explicitement exclu) |

#### Activity (`activities`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.activity.fields.name` | texte | `name` | |
| 2 | `cruds.activity.fields.description` | HTML riche | `description` | |
| 3 | `cruds.activity.fields.processes` | liste de liens (Process) | `processes` (BelongsToMany) | |
| 4 | `cruds.activity.fields.operations` | liste de liens (Operation) | `operations` (BelongsToMany) | |
| 5 | `cruds.activity.fields.applications` | liste de liens (Application) | `applications` (BelongsToMany) | → Applicatif |
| 6 | "BPMN" | liste de liens (Graph) | `graphs()` | à omettre (objet exclu) |

**Contenu additionnel hors partial** (cartes BIA/DRP) :

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 7 | `maximum_tolerable_downtime` / `maximum_tolerable_data_loss` / `recovery_time_objective` / `recovery_point_objective` | durées calculées (j/h/min) | champs en minutes | carte "BIA" |
| 8 | `cruds.activity.impacts` (sous-tableau) | **sous-tableau imbriqué** : `impact_type` (texte) + `severity` (badge 0–4) | `ActivityImpact` via `activity->impacts` (HasMany) | pas d'écran propre — imbriqué uniquement ici |
| 9 | `drp` | HTML riche | `drp` | carte "DRP" |
| 10 | `drp_link` | lien auto (si URL valide) | `drp_link` | |

#### ActivityImpact

Pas d'écran admin propre, imbriqué sous `Activity` uniquement (cf. ligne 8).

#### Operation (`operations`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.operation.fields.name` | texte | `name` | |
| 2 | `cruds.operation.fields.description` | HTML riche | `description` | |
| 3 | `cruds.operation.fields.process` | lien (Process) | `process` (BelongsTo) | |
| 4 | `cruds.operation.fields.activities` | liste de liens (Activity) | `activities` (BelongsToMany) | |
| 5 | `cruds.operation.fields.actors` | liste de liens (Actor) | `actors` (BelongsToMany) | |
| 6 | `cruds.operation.fields.tasks` | liste de liens (Task) | `tasks` (BelongsToMany) | |
| 7 | "BPMN" | liste de liens (Graph) | `graphs()` | à omettre |

#### Task (`tasks`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.task.fields.name` | texte | `name` | |
| 2 | `cruds.task.fields.description` | HTML riche | `description` | |
| 3 | `cruds.task.fields.operations` | liste de liens (Operation) | `operations` (BelongsToMany) | |
| 4 | "BPMN" | liste de liens (Graph) | `graphs()` | à omettre |

#### Actor (`actors`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.actor.fields.name` | texte | `name` | |
| 2 | `cruds.actor.fields.contact` | texte | `contact` | |
| 3 | `cruds.actor.fields.nature` | texte | `nature` | |
| 4 | `cruds.actor.fields.type` | texte | `type` | |
| 5 | `cruds.actor.fields.operations` | liste de liens (Operation) | `operations` (BelongsToMany) | |
| 6 | "BPMN" | liste de liens (Graph) | `graphs()` | à omettre |

#### Information (`information`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.information.fields.name` | texte | `name` | |
| 2 | `cruds.information.fields.type` | texte | `type` | |
| 3 | `cruds.information.fields.attributes` | badges | `attributes` | |
| 4 | `cruds.information.fields.description` | HTML riche + icône | `description`, `icon_id` (fallback `/images/information.png`) | |
| 5 | "BPMN" | liste de liens (Graph) | `graphs()` | à omettre |

**Contenu additionnel hors partial (important — le partial est très incomplet ici)** :

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 6 | `owner` / `administrator` / `storage` | texte | idem | |
| 7 | `sensitivity` | texte | `sensitivity` | |
| 8 | `security_need` | badges C/I/A/T(+Auth) | `security_need_c/i/a/t(/auth)` | |
| 9 | `parents` | liste de liens (Information, self) | `parents` (BelongsToMany self) | hiérarchie catégorielle |
| 10 | `children` | liste de liens (Information, self) | `children` (BelongsToMany self) | idem, sens inverse |
| 11 | `processes` | liste de liens (Process) | `processes` (BelongsToMany) | |
| 12 | `constraints` (carte GDPR) | HTML riche | `constraints` | |

Note : `databases()` et `fluxes()` existent sur le modèle mais ne sont affichés nulle part (ni partial ni show) — à ne pas inventer de section pour ces relations.

---

### Vue 3 — Applicatif

#### ApplicationBlock (`applicationBlocks`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.applicationBlock.fields.name` | texte | `name` | |
| 2 | `cruds.applicationBlock.fields.description` | HTML riche | `description` | |
| 3 | `cruds.applicationBlock.fields.responsible` | texte | `responsible` | |
| 4 | `cruds.applicationBlock.fields.applications` | liste de liens (Application) | `applications` (HasMany) | |

#### Application (`applications`)

Partial :

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.application.fields.name` | texte | `name` | |
| 2 | `cruds.application.fields.application_block` | lien (ApplicationBlock) | `applicationBlock` (BelongsTo) | |
| 3 | `cruds.application.fields.attributes` | texte | `attributes` | |
| 4 | `cruds.application.fields.description` | HTML riche + icône | `description`, `icon_id` (fallback `/images/application.png`) | |
| 5 | `cruds.application.fields.documents` | liste de liens (fichiers) | `documents` (BelongsToMany → Document) | masqué par défaut (`config('mercator.parameters.application_documents')`) |

**Contenu additionnel hors partial** (le `show.blade.php` d'Application est un tableau de bord complet) :

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 6 | `responsible` | texte | `responsible` | |
| 7 | `entity_resp` | lien (Entity) | `entityResp` (BelongsTo) | → Écosystème |
| 8 | `entities` | liste de liens (Entity) | `entities` (BelongsToMany) | → Écosystème |
| 9 | `functional_referent` | texte | `functional_referent` | |
| 10 | `editor` | texte | `editor` | |
| 11 | `users` | texte | `users` | |
| 12 | `administrators` | liste de liens (AdminUser) | `administrators` (BelongsToMany) | → Administration |
| 13 | `technology` / `type` / `external` | texte | idem | |
| 14 | `install_date` / `update_date` | dates | idem | |
| 15 | Historique d'événements | **imbriqué** (modal JS côté client, pas un sous-tableau serveur) | `ApplicationEvent` via `events` (HasMany), + `event->user` | pas d'écran propre ; côté rapport, rendre comme un vrai sous-tableau (message, date, utilisateur), en `with('events.user')` |
| 16 | `documentation` | texte ou lien si URL | `documentation` | |
| 17 | `databases` | liste de liens (Database) | `databases` (BelongsToMany) | |
| 18 | `services` | liste de liens (ApplicationService) | `services` (BelongsToMany) | |
| 19 | Sécurité : `security_need` C/I/A/T(+Auth) | badges | `security_need_*` | corriger bug #2 (§0.2) |
| 20 | RTO / RPO | durée calculée | `rto`, `rpo` (minutes) | |
| 21 | CPE : `vendor` / `product` / `version` | texte | idem | |
| 22 | `processes` | liste de liens (Process) | `processes` (BelongsToMany) | → Système d'information |
| 23 | `activities` | liste de liens (Activity) | `activities` (BelongsToMany) | → Système d'information |
| 24 | Flux (sous-tableau) : name/type/attributes/source/dest/information | **sous-tableau imbriqué** | `applicationSourceFluxes` ∪ `applicationDestFluxes` (`ApplicationFlow`) | |
| 25 | `logical_servers` | liste de liens (LogicalServer) | `logicalServers` (BelongsToMany) | → Infrastructure logique |
| 26 | `containers` | liste de liens (Container) | `containers` (BelongsToMany) | → Infrastructure logique |
| 27 | `security_devices` | liste de liens (SecurityDevice) | `securityDevices` (BelongsToMany) | → Infrastructure logique |

#### ApplicationEvent

Pas d'écran propre, imbriqué sous `Application` (ligne 15) uniquement, actuellement rendu côté client (JS) — à transformer en sous-tableau serveur pour le rapport.

#### ApplicationService (`applicationServices`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.applicationService.fields.name` | texte | `name` | |
| 2 | `cruds.applicationService.fields.description` | HTML riche | `description` | |
| 3 | `cruds.applicationService.fields.exposition` | texte | `exposition` | |
| 4 | `cruds.applicationService.fields.modules` | liste de liens (ApplicationModule) | `modules` (BelongsToMany) | |
| 5 | Flux (sous-tableau, hors partial) | **sous-tableau imbriqué** | `serviceSourceFluxes` ∪ `serviceDestFluxes` | |

#### ApplicationModule (`applicationModules`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.applicationModule.fields.name` | texte | `name` | |
| 2 | `cruds.applicationModule.fields.description` | HTML riche | `description` | |
| 3 | `cruds.applicationModule.fields.entities` | liste de liens (Entity) | `entities` (BelongsToMany) | → Écosystème |
| 4 | `cruds.applicationModule.fields.services` | liste de liens (ApplicationService) | `applicationServices` (BelongsToMany) | |
| 5 | CPE : vendor/product/version (hors partial, réutilise clés `cruds.application.fields.*`) | texte | idem | |
| 6 | Flux (sous-tableau, hors partial) | **sous-tableau imbriqué** | `moduleSourceFluxes` ∪ `moduleDestFluxes` | |

#### Database (`databases`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.database.fields.name` / `.type` | texte | `name`, `type` | |
| 2 | `cruds.database.fields.description` | HTML riche + icône | `description`, `icon_id` (fallback `/images/database.png`) | |
| 3 | `cruds.database.fields.entities` | badges (pas des liens ici) | `entities` (BelongsToMany) | → Écosystème |
| 4 | `cruds.database.fields.entity_resp` | texte (pas un lien) | `entityResp` (BelongsTo) | → Écosystème |
| 5 | `cruds.database.fields.responsible` | texte | `responsible` | |
| 6 | `cruds.database.fields.informations` | liste de liens (Information) | `informations` (BelongsToMany) | → Système d'information |
| 7 | `cruds.database.fields.applications` | liste de liens (Application) | `applications` (BelongsToMany) | |
| 8 | `cruds.database.fields.logical_servers` / `.external` | liste de liens + texte | `logicalServers` (BelongsToMany), `external` | → Infrastructure logique |
| 9 | `cruds.database.fields.containers` | liste de liens (Container) | `containers` (BelongsToMany) | → Infrastructure logique |
| 10 | `cruds.database.fields.security_need` | badges C/I/A/T | `security_need_c/i/a/t` | corriger bug #3 (§0.2) pour l'authenticité |
| 11 | Flux (sous-tableau, hors partial) | **sous-tableau imbriqué** | `databaseSourceFluxes` ∪ `databaseDestFluxes` | |

#### ApplicationFlow (`application-flows`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.flux.fields.name` | texte | `name` | |
| 2 | `cruds.flux.fields.type` | texte | `type` | |
| 3 | `cruds.flux.fields.attributes` | badges | `attributes` | |
| 4 | `cruds.flux.fields.description` | HTML riche | `description` | |
| 5 | `cruds.flux.fields.source` | lien + type (Application/Service/Module/Database) | un des `applicationSource`/`serviceSource`/`moduleSource`/`databaseSource` | polymorphe |
| 6 | `cruds.flux.fields.destination` | lien + type | un des `*Dest` | polymorphe |
| 7 | `cruds.flux.fields.information` | liste de liens (Information) | `informations` (BelongsToMany) | → Système d'information |
| 8 | `cruds.flux.fields.crypted` | booléen | `crypted` | |
| 9 | `cruds.flux.fields.bidirectional` | booléen | `bidirectional` | |

---

### Vue 4 — Administration

#### ZoneAdmin (`zoneAdmins`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.zoneAdmin.fields.name` | texte | `name` | |
| 2 | `cruds.zoneAdmin.fields.description` | HTML riche | `description` | |
| 3 | `cruds.zoneAdmin.fields.annuaires` | liste de liens (Annuaire) | `annuaires` (HasMany) | |
| 4 | `cruds.zoneAdmin.fields.forests` | liste de liens (ForestAd) | `forestAds` (HasMany) | |

#### Annuaire (`annuaires`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.annuaire.fields.name` | texte | `name` | |
| 2 | `cruds.annuaire.fields.description` | HTML riche | `description` | |
| 3 | `cruds.annuaire.fields.solution` | texte | `solution` | |
| 4 | `cruds.annuaire.fields.zone_admin` | lien (ZoneAdmin) | `zoneAdmin` (BelongsTo) | |

#### ForestAd (`forestAds`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.forestAd.fields.name` | texte | `name` | |
| 2 | `cruds.forestAd.fields.description` | HTML riche | `description` | |
| 3 | `cruds.forestAd.fields.zone_admin` | lien (ZoneAdmin) | `zoneAdmin` (BelongsTo) | |
| 4 | `cruds.forestAd.fields.domains` | liste de liens (Domain) | `domains` (BelongsToMany) | |

#### Domain (`domains`, traductions sous `cruds.domaine.*`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.domaine.fields.name` | texte | `name` | |
| 2 | `cruds.domaine.fields.description` | HTML riche | `description` | |
| 3 | `cruds.domaine.fields.domain_ctrl_cnt` | nombre | `domain_ctrl_cnt` | |
| 4 | `cruds.domaine.fields.user_count` | nombre | `user_count` | |
| 5 | `cruds.domaine.fields.machine_count` | nombre | `machine_count` | |
| 6 | `cruds.domaine.fields.relation_inter_domaine` | texte | `relation_inter_domaine` | |
| 7 | `cruds.domaine.fields.forestAds` | liste de liens (ForestAd) | `forestAds` (BelongsToMany) | corrigé le 2026-07-05 (utilisait auparavant `cruds.forestAd.title` par erreur, cf. §0.2 item 7) |
| 8 | `cruds.domaine.fields.logical_servers` | liste de liens (LogicalServer) | `logicalServers` (HasMany) | corrigé le 2026-07-05 (utilisait auparavant `cruds.logicalServer.title` par erreur, cf. §0.2 item 7) ; → Infrastructure logique |

#### AdminUser (`adminUser`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.adminUser.fields.user_id` | texte (sert de titre de page, pas de champ `name`) | `user_id` | |
| 2 | `cruds.adminUser.fields.firstname` / `.lastname` | texte | idem | |
| 3 | `cruds.adminUser.fields.type` / `.attributes` | texte + badges | `type`, `attributes` | |
| 4 | `cruds.adminUser.fields.domain` | lien (Domain) | `domain` (BelongsTo) | → Infrastructure logique (Domain classé Administration dans ce doc, cf. objets ci-dessus — relation intra-vue) |
| 5 | `cruds.adminUser.fields.description` | HTML riche | `description` | |

Note : `AdminUser::applications()` existe mais n'est affiché nulle part sur cette page (uniquement visible en sens inverse depuis `Application`, cf. ligne 12 Application ci-dessus).

---

### Vue 5 — Infrastructure logique

#### Network (`networks`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.network.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.network.fields.attributes` | badges | `attributes` | |
| 3 | `cruds.network.fields.description` | HTML riche | `description` | |
| 4 | `cruds.network.fields.protocol_type` | texte | `protocol_type` | |
| 5 | `cruds.network.fields.responsible` / `.responsible_sec` | texte | idem | |
| 6 | `cruds.information.fields.security_need` (clé réutilisée) | badges C/I/A/T(+Auth) | `security_need_*` | |
| 7 | `cruds.network.fields.subnetworks` | liste de liens (Subnetwork) | `subnetworks` (HasMany) | |

#### Subnetwork (`subnetworks`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.subnetwork.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.subnetwork.fields.attributes` | badges | `attributes` | |
| 3 | `cruds.subnetwork.fields.description` | HTML riche | `description` | |

**Contenu additionnel hors partial** :

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 4 | `network` | lien (Network) | `network` (BelongsTo) | |
| 5 | `subnetwork` (parent) | lien (Subnetwork, self) | `subnetwork` (BelongsTo self) | |
| 6 | `address` + plage IP | texte + calculé | `address`, `ipRange()` | |
| 7 | `gateway` | lien (Gateway) | `gateway` (BelongsTo) | |
| 8 | `vlan` | lien (Vlan) | `vlan` (BelongsTo) | |
| 9 | `ip_allocation_type` | texte | idem | |
| 10 | `zone` / `dmz` / `wifi` | texte/booléens | idem | |
| 11 | `responsible_exp` | texte | idem | |

#### Gateway (`gateways`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.gateway.fields.name` | texte | `name` | pas de gate `withLink` sur ce partial |
| 2 | `cruds.gateway.fields.description` | HTML riche | `description` | |
| 3 | `cruds.gateway.fields.authentification` | texte | `authentification` | |
| 4 | `cruds.gateway.fields.ip` | texte | `ip` | |

#### ExternalConnectedEntity (`externalConnectedEntities`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.externalConnectedEntity.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.externalConnectedEntity.fields.description` | HTML riche | `description` | |

**Contenu additionnel hors partial** :

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 3 | `entity` | lien (Entity) | `entity` (BelongsTo) | → Écosystème |
| 4 | `network` | lien (Network) | `network` (BelongsTo) | |
| 5 | `contacts` / `src_desc` / `dest_desc` / `src` / `dest` | texte | idem | |
| 6 | `subnetworks` | liste de liens | `subnetworks` (BelongsToMany) | |
| 7 | `security` | HTML riche | `security` | |
| 8 | pièces jointes | liste de liens (fichiers) | `documents` (BelongsToMany → Document) | |

#### Router (`routers`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.router.fields.name` / `.type` | texte | idem | pas de gate `withLink` |
| 2 | `cruds.router.fields.description` | HTML riche | `description` | |
| 3 | `cruds.router.fields.rules` | HTML riche | `rules` | |
| 4 | `cruds.router.fields.physical_routers` | liste de liens (PhysicalRouter) | `physicalRouters` (BelongsToMany) | → Infrastructure physique |

#### NetworkSwitch (`networkSwitches`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.networkSwitch.fields.name` | texte | `name` | |
| 2 | `cruds.networkSwitch.fields.description` | HTML riche | `description` | |
| 3 | `cruds.networkSwitch.fields.ip` | texte | `ip` | |
| 4 | `cruds.networkSwitch.fields.physical_switches` | liste de liens (PhysicalSwitch) | `physicalSwitches` (BelongsToMany) | → Infrastructure physique |

Note : `vlans()` existe sur le modèle mais n'est affiché nulle part (seulement utilisé par le graphe).

#### SecurityDevice (`securityDevices`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.securityDevice.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.securityDevice.fields.attributes` | badges | `attributes` | |
| 3 | `cruds.securityDevice.fields.description` | HTML riche + icône | `description`, `icon_id` (fallback `/images/securitydevice.png`) | |
| 4 | `cruds.securityDevice.fields.address_ip` | texte | `address_ip` | |
| 5 | `cruds.securityDevice.fields.applications` | liste de liens (Application) | `applications` (BelongsToMany) | → Applicatif |
| 6 | `cruds.securityDevice.fields.physical_security_devices` | liste de liens (PhysicalSecurityDevice) | `physicalSecurityDevices` (BelongsToMany) | → Infrastructure physique |

#### DhcpServer (`dhcpServers`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.dhcpServer.fields.name` | texte | `name` | |
| 2 | `cruds.dhcpServer.fields.description` | HTML riche | `description` | |
| 3 | `cruds.dhcpServer.fields.address_ip` | texte | `address_ip` | |

#### Dnsserver (`dnsservers`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.dnsserver.fields.name` | texte | `name` | |
| 2 | `cruds.dnsserver.fields.description` | HTML riche | `description` | |
| 3 | `cruds.dnsserver.fields.address_ip` | texte | `address_ip` | |

#### LogicalServer (`logicalServers`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.logicalServer.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.logicalServer.fields.attributes` | badges | `attributes` | |
| 3 | `cruds.logicalServer.fields.description` | HTML riche + icône | `description`, `icon_id` (fallback `/images/lserver.png`) | |
| 4 | `cruds.logicalServer.fields.operating_system` | texte | `operating_system` | |
| 5 | `cruds.logicalServer.fields.install_date` / `.update_date` | dates | idem | |
| 6 | `cruds.logicalServer.fields.cluster` | liste de liens (Cluster) | `clusters` (BelongsToMany) | |
| 7 | `cruds.logicalServer.fields.environment` | texte | `environment` | |
| 8 | `cruds.logicalServer.fields.address_ip` / `.net_services` | texte | idem | |
| 9 | `cruds.logicalServer.fields.cpu` / `.memory` | texte | idem | |
| 10 | `cruds.logicalServer.fields.disk` / `.disk_used` | texte + % calculé | idem | |
| 11 | `cruds.logicalServer.fields.configuration` | HTML riche | `configuration` | |

**Contenu additionnel hors partial (4 cartes)** :

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 12 | `applications` | liste de liens (Application) | `applications` (BelongsToMany) | → Applicatif |
| 13 | `databases` | liste de liens (Database) | `databases` (BelongsToMany) | → Applicatif |
| 14 | `domain` | lien (Domain) | `domain` (BelongsTo) | → Administration |
| 15 | `physicalServers` | liste de liens (PhysicalServer) | `physicalServers` (BelongsToMany) | → Infrastructure physique |
| 16 | Sauvegardes (sous-tableau, gate `backup_show`) | **sous-tableau imbriqué** : name, storageDevices (liens), backup_frequency, backup_cycle, backup_retention | `Backup` via `logicalServer->backups` | pas d'écran propre pour cette relation (mais `Backup` a par ailleurs son propre écran admin top-level listé ci-dessous) |

Note : `certificates()` et `containers()` existent (relation inverse) mais ne sont pas rendus ici ; `documents()` (BelongsToMany) existe aussi mais inutilisé.

#### Cluster (`clusters`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.cluster.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.cluster.fields.attributes` | badges | `attributes` | |
| 3 | `cruds.cluster.fields.description` | HTML riche + icône | `description`, `icon_id` (fallback `/images/cluster.png`) | |
| 4 | `cruds.cluster.fields.address_ip` | texte | `address_ip` | |
| 5 | `cruds.cluster.fields.logical_servers` + "Routers" (libellé concaténé, pas 100% trans) | liste de liens (LogicalServer + Router fusionnés) | `logicalServers`, `routers` (BelongsToMany) | à séparer proprement en deux lignes dans le nouveau rapport (recommandé) |
| 6 | `cruds.cluster.fields.physical_servers` | liste de liens (PhysicalServer) | `physicalServers` (BelongsToMany) | → Infrastructure physique |

#### Backup (`backups`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.backup.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.backup.fields.attributes` | texte brut (pas en badges, incohérent avec les autres objets) | `attributes` | à harmoniser en badges dans le nouveau rapport (recommandé) |
| 3 | `cruds.backup.fields.description` | HTML riche | `description` | |
| 4 | `cruds.backup.frequency` (enum traduit) | texte | `backup_frequency` | |
| 5 | `cruds.backup.cycle` (enum traduit) | texte | `backup_cycle` | |
| 6 | `cruds.backup.retention` + unité | texte | `backup_retention` | |
| 7 | `cruds.backup.fields.logical_servers` | liste de liens (LogicalServer) | `logicalServers` (BelongsToMany) | |
| 8 | `cruds.backup.fields.storage_devices` | liste de liens (StorageDevice) | `storageDevices` (BelongsToMany) | → Infrastructure physique |

#### Container (`containers`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.container.fields.name` / `.type` | texte | idem | pas de gate `withLink` |
| 2 | `cruds.container.fields.description` | HTML riche + icône | `description`, `icon_id` (fallback `/images/container.png`) | |

**Contenu additionnel hors partial** :

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 3 | `logicalServers` | liste de liens (LogicalServer) | `logicalServers` (BelongsToMany) | |
| 4 | `applications` | liste de liens (Application) | `applications` (BelongsToMany) | → Applicatif |
| 5 | `databases` | liste de liens (Database) | `databases` (BelongsToMany) | → Applicatif |

#### LogicalFlow (`logicalFlows`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.logicalFlow.fields.name` (fallback `"NONAME"`) | texte | `name` | pas de gate `withLink` |
| 2 | `cruds.logicalFlow.fields.type` | texte | `type` | |
| 3 | `cruds.logicalFlow.fields.attributes` | badges | `attributes` | |
| 4 | `cruds.logicalFlow.fields.description` | HTML riche | `description` | |
| 5 | `cruds.logicalFlow.fields.chain` / `.interface` | texte | idem | |
| 6 | `cruds.logicalFlow.fields.router` | lien (Router) | `router` (BelongsTo) | |
| 7 | `cruds.logicalFlow.fields.priority` / `.action` / `.protocol` | texte | idem | |
| 8 | `cruds.logicalFlow.fields.source_ip_range` + port | texte ou lien polymorphe + IP | `source_ip_range` ou un de : `logicalServerSource`, `peripheralSource`, `physicalServerSource`, `storageDeviceSource`, `workstationSource`, `physicalSecurityDeviceSource`, `securityDeviceSource`, `subnetworkSource`, `clusterSource` | `peripheralSource`/`physicalServerSource`/`workstationSource`/`physicalSecurityDeviceSource` → Infrastructure physique |
| 9 | `cruds.logicalFlow.fields.dest_ip_range` + port | idem (suffixe Dest) | idem | idem |
| 10 | `cruds.logicalFlow.fields.users` / `.schedule` | texte | idem | |

#### Vlan (`vlans`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.vlan.fields.name` | texte | `name` | |
| 2 | `cruds.vlan.fields.vlan_id` | texte | `vlan_id` (**pas `id`** — corriger le bug #1 §0.2) | |
| 3 | `cruds.vlan.fields.description` | HTML riche | `description` | |
| 4 | `cruds.vlan.fields.subnetworks` | liste de liens (Subnetwork) | `subnetworks` (HasMany) | |
| 5 | `cruds.vlan.fields.network_switches` | liste de liens (NetworkSwitch) | `networkSwitches` (BelongsToMany) | |

#### Certificate (`certificates`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.certificate.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.certificate.fields.description` | HTML riche | `description` | |
| 3 | `cruds.certificate.fields.start_validity` / `.end_validity` | dates | idem | |
| 4 | `cruds.certificate.fields.last_notification` (+ texte d'aide) | date | `last_notification` | |
| 5 | `cruds.certificate.fields.logical_servers` | liste de liens (LogicalServer) | `logicalServers` (BelongsToMany) | |
| 6 | `cruds.certificate.fields.applications` | liste de liens (Application) | `applications` (BelongsToMany) | → Applicatif |

---

### Vue 6 — Infrastructure physique

#### Site (`sites`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.site.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.site.fields.attributes` | badges | `attributes` | |
| 3 | `cruds.site.fields.description` | HTML riche + icône | `description`, `icon_id` (fallback `/images/site.png`) | |
| 4 | `cruds.site.fields.buildings` | liste de liens (Building) | `buildings` (HasMany) | |

#### Building (`buildings`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.building.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.building.fields.attributes` | badges | `attributes` | |
| 3 | `cruds.building.fields.description` | HTML riche + icône | `description`, `icon_id` (fallback `/images/building.png`) | |
| 4 | `cruds.building.fields.site` | lien (Site) | `site` (BelongsTo) | |
| 5 | `cruds.building.fields.parent` | lien (Building, self) | `building` (BelongsTo self) | |
| 6 | `cruds.building.fields.children` | liste de liens (Building, self) | `buildings` (HasMany self) | |
| 7 | `cruds.building.fields.bays` | liste de liens (Bay) | `bays` (HasMany) | |

#### Bay (`bays`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.bay.fields.name` | texte | `name` | |
| 2 | `cruds.bay.fields.description` | HTML riche | `description` | |
| 3 | `cruds.bay.fields.site` | lien (Site) | `site` (BelongsTo) | |
| 4 | `cruds.bay.fields.building` | lien (Building) | `building` (BelongsTo) | |
| 5 | `cruds.menu.physical_infrastructure.title_short` | **6 listes fusionnées dans une cellule** : PhysicalServer, StorageDevice, Peripheral, PhysicalSwitch, PhysicalRouter, PhysicalSecurityDevice | 6 relations (baie → équipement monté) | recommandé : séparer en 6 lignes propres dans le nouveau rapport |

#### Zone (`zones`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.zone.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.zone.fields.attributes` | badges | `attributes` | |
| 3 | `cruds.zone.fields.description` | HTML riche | `description` | |
| 4 | `cruds.zone.fields.parent_zones` | liste de liens (Zone, self) | `parentZones` | |
| 5 | `cruds.zone.fields.child_zones` | liste de liens (Zone, self) | `childZones` | |
| 6 | `cruds.zone.fields.buildings` | liste de liens (Building) | `buildings` (BelongsToMany) | |
| 7 | `cruds.zone.fields.admin_users` | liste de liens (`user_id` en libellé) | `adminUsers` (BelongsToMany) | → Administration (`AdminUser`) |

Distinct de `ZoneAdmin` (Administration) — confirmé (modèle, préfixe UID, table).

#### PhysicalServer (`physicalServers`)

Partial : name/type/description (+icône `icon_id`, fallback `/images/server.png`).

**Contenu additionnel hors partial (majoritaire ici)** :

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 5 | `cpu` / `memory` / `disk` / `disk_used` / `configuration` | HTML riche | idem | |
| 6 | `applications` | liste de liens (Application) | `applications` (BelongsToMany) | → Applicatif |
| 7 | `operating_system` | HTML riche | `operating_system` | |
| 8 | `install_date` / `update_date` | date (non formatée) | idem | |
| 9 | `address_ip` / `responsible` | texte | idem | |
| 10 | `clusters` | liste de liens (Cluster) | `clusters` (BelongsToMany) | → Infrastructure logique |
| 11 | `logical_servers` | liste de liens (LogicalServer) | `logicalServers` (BelongsToMany) | → Infrastructure logique |
| 12 | `site` / `building` / `bay` | liens | idem | intra-vue |

#### Workstation (`workstations`)

Partial : name/type/status/description(+icône, fallback `/images/workstation.png`)/manufacturer/model/serial_number/cpu/memory/disk/operating_system.

**Contenu additionnel hors partial** :

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 12 | `entity` | lien (Entity) | `entity` (BelongsTo) | → Écosystème |
| 13 | `domain` | lien (Domain) | `domain` (BelongsTo) | → Infrastructure logique |
| 14 | `user` (libellé `user_id`) / `other_user` | lien / texte | `user` (BelongsTo), `other_user` | → Administration |
| 15 | `applications` | liste de liens (Application) | `applications` (BelongsToMany) | → Applicatif |
| 16 | `network` | lien (Network) | `network` (BelongsTo) | → Infrastructure logique |
| 17 | `address_ip` / `mac_address` / `network_port_type` | texte/HTML | idem | |
| 18 | `site` / `building` | liens | idem | intra-vue |

#### StorageDevice (`storageDevices`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.storageDevice.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.storageDevice.fields.description` | HTML riche | `description` | |
| 3 | `cruds.storageDevice.fields.address_ip` | texte | `address_ip` | |
| 4 | `cruds.storageDevice.fields.site` / `.building` / `.bay` | liens | idem | pas d'icône pour cet objet |

Pas de contenu additionnel hors partial.

#### Peripheral (`peripherals`)

Partial : name/domain(lien vers `Domain`, corrigé le 2026-07-05 — cf. §0.2 item 8, était auparavant une chaîne libre)/type/description(+icône, fallback `/images/peripheral.png`).

**Contenu additionnel hors partial** :

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 5 | `provider` | lien (Entity) | `provider` (BelongsTo) | → Écosystème |
| 6 | `responsible` | texte | `responsible` | |
| 7 | `applications` | liste de liens (Application) | `applications` (BelongsToMany) | → Applicatif |
| 8 | `vendor` / `product` / `version` (attribut réel `pversion`) | texte | idem | |
| 9 | `address_ip` | texte | `address_ip` | |
| 10 | `site` / `building` / `bay` | liens | idem | intra-vue |

#### Phone (`phones`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.phone.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.phone.fields.description` | HTML riche | `description` | |
| 3 | `cruds.phone.fields.address_ip` | texte | `address_ip` | |
| 4 | `cruds.phone.fields.site` / `.building` | liens | idem | pas d'icône, pas de contenu additionnel |

#### PhysicalSwitch (`physicalSwitches`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.physicalSwitch.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.physicalSwitch.fields.description` | HTML riche + icône | `description`, `icon_id` (fallback `/images/switch.png`) | |
| 3 | `cruds.physicalSwitch.fields.site` / `.building` / `.bay` | liens | idem | |
| 4 | `cruds.physicalSwitch.fields.network_switches` | liste de liens (NetworkSwitch) | `networkSwitches` (BelongsToMany) | → Infrastructure logique |

#### PhysicalRouter (`physicalRouters`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.physicalRouter.fields.name` / `.type` | texte | idem | pas d'icône pour cet objet |
| 2 | `cruds.physicalRouter.fields.description` | HTML riche | `description` | |
| 3 | `cruds.physicalRouter.fields.site` / `.building` / `.bay` | liens | idem | |
| 4 | `cruds.physicalRouter.fields.routers` | liste de liens (Router) | `routers` (BelongsToMany) | → Infrastructure logique |
| 5 | `cruds.physicalRouter.fields.vlan` | liste de liens (Vlan) | `vlans` (BelongsToMany) | → Infrastructure logique |

#### WifiTerminal (`wifiTerminals`)

Partial : name/type/description/address_ip (pas d'icône).

**Contenu additionnel hors partial** :

| # | Libellé (clé, mal étiqueté cf. bug #6 §0.2) | Type | Source | Notes |
|---|---|---|---|---|
| 5 | vendor/product/version | texte | idem | clés à corriger |
| 6 | `site` / `building` | liens | idem | intra-vue |

#### PhysicalSecurityDevice (`physicalSecurityDevices`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.physicalSecurityDevice.fields.name` / `.type` | texte | idem | |
| 2 | `cruds.physicalSecurityDevice.fields.attributes` | badges | `attributes` | |
| 3 | `cruds.physicalSecurityDevice.fields.description` | HTML riche + icône | `description`, `icon_id` (fallback `/images/securitydevice.png`) | |
| 4 | `cruds.physicalSecurityDevice.fields.address_ip` | texte | `address_ip` | |
| 5 | `cruds.physicalSecurityDevice.fields.security_devices` | liste de liens (SecurityDevice) | `securityDevices` (BelongsToMany) | → Infrastructure logique |
| 6 | `cruds.physicalSecurityDevice.fields.site` / `.building` / `.bay` | liens | idem | |

#### PhysicalLink (`links`)

Objet sans champ `name` (relie deux équipements).

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.physicalLink.fields.type` | texte | `type` | |
| — | couleur (pas de libellé) | pastille visuelle | `color` | |
| 2 | `cruds.physicalLink.fields.attributes` | badges | `attributes` | |
| 3 | `cruds.physicalLink.fields.src` | lien polymorphe (10+2 relations) | un de : `peripheralSrc`, `phoneSrc`, `physicalRouterSrc`, `physicalSecurityDeviceSrc`, `physicalServerSrc`, `physicalSwitchSrc`, `storageDeviceSrc`, `wifiTerminalSrc`, `workstationSrc`, `routerSrc`, `networkSwitchSrc` | `routerSrc`/`networkSwitchSrc` → Infrastructure logique |
| 4 | `cruds.physicalLink.fields.src_port` | texte | `src_port` | |
| 5 | `cruds.physicalLink.fields.dest` | idem (suffixe Dest) | idem | idem |
| 6 | `cruds.physicalLink.fields.dest_port` | texte | `dest_port` | |
| 7 | `cruds.physicalLink.fields.description` | HTML riche | `description` | |

#### Wan (`wans`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.wan.fields.name` | texte | `name` | pas de champ description dans ce partial |
| 2 | `cruds.wan.fields.mans` | liste de liens (Man) | `mans` (BelongsToMany) | |
| 3 | `cruds.wan.fields.lans` | liste de liens (Lan) | `lans` (BelongsToMany) | |

#### Man (`mans`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.man.fields.name` | texte | `name` | |
| 2 | `cruds.man.fields.description` | HTML riche | `description` | |
| 3 | `cruds.man.fields.wans` | liste de liens (Wan) | `wans` (BelongsToMany) | |
| 4 | `cruds.man.fields.parent_man` | lien (Man, self) | `parentMan` (BelongsTo self) | |
| 5 | `cruds.man.fields.lans` | liste de liens (Lan) | `lans` (BelongsToMany) | |

#### Lan (`lans`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.lan.fields.name` | texte | `name` | pas de gate `withLink` |
| 2 | `cruds.lan.fields.description` | **texte brut** (pas de `{!! !!}`, incohérent avec tous les autres objets) | `description` | harmoniser en HTML riche dans le nouveau rapport (recommandé) |

Pas de relation affichée, pas de contenu additionnel.

---

### Vue 7 — GDPR

#### DataProcessing (`dataProcessing`)

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.dataProcessing.fields.name` | texte | `name` | |
| 2 | `cruds.dataProcessing.fields.legal_basis` | texte | `legal_basis` | |
| 3 | `cruds.dataProcessing.fields.description` | HTML riche | `description` | |
| 4 | `cruds.dataProcessing.fields.responsible` | HTML riche | `responsible` | hors partial |
| 5 | `cruds.dataProcessing.fields.purpose` | HTML riche | `purpose` | hors partial |
| 6 | `cruds.dataProcessing.fields.lawfulness` (6 sous-cases) | 6 booléens | `lawfulness_consent`, `_contract`, `_legal_obligation`, `_vital_interest`, `_public_interest`, `_legitimate_interest` | hors partial |
| 7 | (sans libellé) | HTML riche | `lawfulness` (texte libre) | hors partial |
| 8 | `cruds.dataProcessing.fields.categories` | HTML riche | `categories` | hors partial |
| 9 | `cruds.dataProcessing.fields.data_source` | HTML riche | `data_source` | hors partial |
| 10 | `cruds.dataProcessing.fields.data_collection_obligation` | HTML riche | idem | hors partial |
| 11 | `cruds.dataProcessing.fields.recipients` | HTML riche | `recipients` | hors partial |
| 12 | `cruds.dataProcessing.fields.transfert` | HTML riche | `transfert` | hors partial |
| 13 | `cruds.dataProcessing.fields.automated_decision_making` | HTML riche | idem | hors partial |
| 14 | `cruds.dataProcessing.fields.retention` | HTML riche | `retention` | hors partial |
| 15 | `cruds.dataProcessing.fields.data_subject_rights` | HTML riche | idem | hors partial |
| 16 | `cruds.dataProcessing.fields.update_date` | date | `update_date` | hors partial |
| 17 | `cruds.dataProcessing.fields.processes` | liste de liens (Process) | `processes` (BelongsToMany) | hors partial ; → Système d'information |
| 18 | `cruds.dataProcessing.fields.applications` | liste de liens (Application) | `applications` (BelongsToMany) | hors partial ; → Applicatif |
| 19 | `cruds.dataProcessing.fields.information` | liste de liens (Information) | `informations` (BelongsToMany) | hors partial ; → Système d'information |
| 20 | `cruds.dataProcessing.fields.documents` | liste de liens (fichiers) | `documents` (BelongsToMany → Document) | hors partial |

#### SecurityControl (`securityControls`)

**Pas de `_details.blade.php`** — tout est inline dans `show.blade.php`.

| # | Libellé (clé) | Type | Source | Notes |
|---|---|---|---|---|
| 1 | `cruds.securityControl.fields.name` | texte | `name` | |
| 2 | `cruds.securityControl.fields.description` | HTML riche | `description` | |

Aucune relation affichée sur cette page. Les relations inverses existent mais ne sont **pas** rendues ici : `Application::securityControls()`, `Process::securityControls()` (pivots `belongsToMany`). Décision à prendre en Phase 2 (§1.4) : afficher ces deux listes sur `SecurityControl` dans le nouveau rapport (recommandé, pour cohérence avec les autres objets qui montrent leurs relations), ou les laisser en visibilité uniquement depuis `Application`/`Process` (non rendues actuellement nulle part, y compris côté Application/Process eux-mêmes — à vérifier : les partials Application/Process inventoriés ci-dessus au §Vue3/Vue2 ne montrent pas non plus `securityControls`, donc cette relation n'apparaît nulle part dans l'appli actuelle).

---

## 1.2 Graphes — plan de mutualisation

Constat général : Mercator dispose déjà, pour (presque) chaque vue, d'un écran interactif dédié sous `app/Http/Controllers/Report/<Nom>View.php` + `resources/views/admin/reports/<slug>.blade.php`, qui construit une chaîne DOT **directement en Blade** (boucles `@foreach` dans un bloc `<script>`), rendue côté client par `resources/js/graphviz.js` (Graphviz WASM). **Aucune extraction en service n'existe aujourd'hui** : ni `app/Services/Graph*`, ni `app/Services/Report*`. Le rapport Word (`CartographyController`) reconstruit sa propre chaîne DOT, en dur, de façon indépendante et divergente, rasterisée côté serveur via `generateGraphImage()` (`shell_exec('/usr/bin/dot -Tpng ...')`).

Décision actée : extraire un builder DOT par vue sous `app/Services/Graph/`, appelé à la fois par l'écran interactif et par le rapport, sans filtre côté rapport.

### Vue 1 — Écosystème

- Écran : `Report\EcosystemView` (`report/ecosystem`) → `admin/reports/ecosystem.blade.php` (DOT lignes 146–183).
- Filtres actuels (session) : `perimeter` (All/Internes/Externes), `type`.
- Nœuds : `E{id}` (Entity, image = icône Document ou `/images/entity.png`), pas de nœud dédié pour Relation (= arêtes).
- Arêtes : hiérarchie parent `E{parent}->E{id}`, puis chaque `Relation` filtrée `E{source}->E{destination}` étiquetée par son nom.
- **`app/Services/Graph/EcosystemGraphBuilder.php`** : `buildDot(Collection $entities, Collection $relations, array $options = [])`. `$options` : `withHref` (ancre `#UID`, true pour l'écran interactif, false pour le Word), `iconResolver` (route HTTP pour l'écran, chemin filesystem pour le PNG serveur).

### Vue 2 — Système d'information

- Écran : `Report\InformationSystemView` (`report/information_system`) → `admin/reports/information_system.blade.php` (DOT lignes 262–327).
- Filtres (session) : `macroprocess`, `process` (cascade).
- Nœuds (icônes fixes, pas d'icône par enregistrement) : MacroProcessus, Process, Activity, Operation, Task, Actor, Information.
- Arêtes non étiquetées : MP→P, P→A, P→I, P→O, A→O, O→T, O→ACT, I→I (hiérarchie Information).
- **`app/Services/Graph/InformationSystemGraphBuilder.php`** : `buildDot(Collection $macroProcessuses, Collection $processes, Collection $activities, Collection $operations, Collection $tasks, Collection $actors, Collection $informations, array $options = [])`. `$options['granularity']` reprend le concept aujourd'hui propre au Word (1–3) pour permettre, si souhaité, de le proposer aussi côté écran interactif — **à confirmer si on veut vraiment garder une notion de granularité du graphe alors que `granularity` est supprimé pour le contenu textuel** (cf. question ouverte ci-dessous).

> **Question ouverte** : la mission supprime `granularity` du rapport (contenu textuel toujours complet). L'ancien graphe "Système d'information" utilisait ce paramètre pour élaguer les nœuds. Puisque le rapport n'a plus de granularité, faut-il que son graphe soit **toujours complet** (tous les 7 types de nœuds), ce qui semble cohérent avec "le rapport montre le graphe complet, sans filtre" ? → **Oui, c'est la lecture retenue** : le builder n'aura pas de paramètre `granularity` pour l'appel depuis le rapport (toujours `null`/désactivé) ; il ne serait exposé que si on décide un jour d'ajouter ce réglage à l'écran interactif, ce qui est hors périmètre ici.

### Vue 3 — Applicatif (2 graphes distincts)

- **Graphe structurel** : `Report\ApplicationView` (`report/applications`) → `admin/reports/applications.blade.php` (DOT lignes 243–286). Filtres (session) : `applicationBlock`, `application`. Nœuds : ApplicationBlock, Application (icône par enregistrement), ApplicationService, ApplicationModule, Database. Arêtes de containment uniquement : AB→A, A→AS, A→DB (**pas d'arête ApplicationFlow ici**).
  - **`app/Services/Graph/ApplicationGraphBuilder.php`** : `buildDot(Collection $applicationBlocks, Collection $applications, Collection $applicationServices, Collection $applicationModules, Collection $databases, array $options = [])`.
- **Graphe de flux** : `Report\ApplicationFlowView` (`report/application_flows`) → `admin/reports/application_flows.blade.php` (DOT lignes 621–673). Filtres (session, multi-select) : `applicationBlocks[]`, `applications[]`, `databases[]`. Nœuds : mêmes 4 types (préfixes différents : `S` au lieu de `AS` pour Service — à harmoniser). Arêtes = chaque `ApplicationFlow`, étiquetées par `type`, tenant compte de `bidirectional`.
  - **`app/Services/Graph/ApplicationFlowGraphBuilder.php`** : `buildDot(Collection $applications, Collection $applicationServices, Collection $applicationModules, Collection $databases, Collection $flows, array $options = [])`. **Aucun équivalent dans l'ancien rapport Word** — capacité entièrement nouvelle pour le Word, pas juste une déduplication.
- La vue "Applicatif" du rapport insère donc **les deux graphes à la suite**, en tête de section, avant le contenu par type d'objet.

### Vue 4 — Administration

- Écran : `Report\AdministrationView` (`report/administration`) → `admin/reports/administration.blade.php` (DOT lignes 183–220). Pas de filtre (tout est chargé).
- Nœuds : ZoneAdmin, Annuaire, ForestAd, Domain, AdminUser (ce dernier seulement si son `domain_id` est dans le périmètre affiché).
- Arêtes : Z→Annuaire, Z→Forest, Forest→Domain, Domain→AdminUser.
- **`app/Services/Graph/AdministrationGraphBuilder.php`** : `buildDot(Collection $zoneAdmins, Collection $annuaires, Collection $forestAds, Collection $domains, Collection $adminUsers, array $options = [])`. Corrige au passage le fait que l'ancien Word omettait totalement `AdminUser` (bug #9 §0.2) et que `AdminUser::All()` n'était pas scopé comme les autres (bug #12) — le nouveau builder consomme des collections déjà scopées de façon homogène par l'appelant.

### Vue 5 — Infrastructure logique

- Écran unique : `Report\LogicalInfrastructureView` (`report/logical_infrastructure`) → `admin/reports/logical_infrastructure.blade.php` (DOT lignes 585–869 environ).
- Filtres (session) : `network`, `subnetwork` (+ descendants), `show_ip` (affichage des IP dans les libellés), `engine` (non persistant, juste rendu).
- Nœuds (icône par enregistrement pour LogicalServer/SecurityDevice/Peripheral/Workstation/Container ; icône fixe sinon) : Network, Gateway, Subnetwork, ExternalConnectedEntity, Cluster (seulement si référencé), LogicalServer, DhcpServer, Dnsserver, Certificate (seulement si lié à un LogicalServer rendu), Container (idem), Router, NetworkSwitch, SecurityDevice, Vlan — plus des nœuds "de contexte" hors-vue (Workstation, WifiTerminal, Phone, PhysicalSecurityDevice, Peripheral, StorageDevice) pour montrer les équipements physiques rattachés à une sous-réseau.
- Arêtes : nombreuses, toutes basées sur l'appartenance IP à un sous-réseau ou sur des FK directes (Subnetwork→Vlan, Subnetwork parent/enfant, Network→Subnetwork, Subnetwork→Gateway, ExternalConnectedEntity→Network, LogicalServer→Cluster, LogicalServer→Certificate, LogicalServer→Container, Vlan→NetworkSwitch, Subnetwork→{LogicalServer, DhcpServer, Dnsserver, Workstation, WifiTerminal, Phone, PhysicalSecurityDevice, SecurityDevice, Peripheral, StorageDevice, Router, NetworkSwitch}).
- **`app/Services/Graph/LogicalInfrastructureGraphBuilder.php`** : `buildDot(Collection $networks, Collection $subnetworks, Collection $gateways, Collection $externalConnectedEntities, Collection $vlans, Collection $networkSwitches, Collection $clusters, Collection $logicalServers, Collection $dhcpServers, Collection $dnsservers, Collection $certificates, Collection $containers, Collection $routers, Collection $securityDevices, Collection $workstations, Collection $wifiTerminals, Collection $phones, Collection $peripherals, Collection $physicalSecurityDevices, Collection $storageDevices, array $options = [])`. Corrige au passage les manques du Word actuel : `NetworkSwitch`, `SecurityDevice`, `Cluster`, `Container` absents du graphe Word, icônes génériques au lieu des icônes par enregistrement (bug listés en détail dans le rapport agent, repris ci-dessous en §1.4).

> **Précision de classement** : `Report\NetworkInfrastructureView` (`report/network_infrastructure`) ne fait **pas** partie de cette vue malgré son nom — il ne requête que des objets physiques (Site/Building/Bay/PhysicalServer/.../PhysicalLink) et exclut explicitement les liaisons touchant le logique. Son libellé de menu (`cruds.menu.network_schema.title`) est d'ailleurs une troisième entrée de traduction, distincte de `logical_infrastructure` et `physical_infrastructure`. Il est rattaché à la Vue 6 (voir ci-dessous). De même, `Report\ZoneList` (`report/zones`) ne concerne que `Subnetwork` (regroupement par attribut `zone`/VLAN) et n'est l'équivalent d'aucun objet de cette vue au sens strict — il n'a pas d'équivalent Word aujourd'hui et n'est pas nécessaire pour reproduire le contenu déjà couvert par `Subnetwork`/`Vlan` en §1.1 ; pas d'action requise.

### Vue 6 — Infrastructure physique (3 graphes)

- **Graphe hiérarchique** : `Report\PhysicalInfrastructureView` (`report/physical_infrastructure`) → `admin/reports/physical_infrastructure.blade.php` (DOT lignes 386–534). Filtres (session) : `site`, `building` (sous-arbre). Nœuds : Site, Building (parent/enfant), Bay, PhysicalServer, Workstation (regroupés au-delà de 5), StorageDevice, Peripheral, Phone, PhysicalSwitch, PhysicalRouter, PhysicalSecurityDevice. Arêtes de containment uniquement (Site→Building→Bay→équipement).
  - **`app/Services/Graph/PhysicalInfrastructureGraphBuilder.php::buildLocationDot(...)`**. Corrige le bug §0.2 #10 (PhysicalSecurityDevice absent du graphe Word malgré son contenu textuel présent).
- **Graphe de connectivité physique** : `Report\NetworkInfrastructureView` (`report/network_infrastructure`) → `admin/reports/network_infrastructure.blade.php` + partial récursif `network_infrastructure_building.blade.php`. Filtres (session) : `show_ports`, `site`, `buildings[]` (avec ascendants/descendants). Regroupement visuel en `subgraph` colorés par site/bâtiment/baie. Arêtes = `PhysicalLink` filtrés pour exclure les liaisons touchant un objet logique (`router_*_id`/`logical_server_*_id`/`network_switch_*_id`), étiquetées par ports si `show_ports`.
  - **`app/Services/Graph/PhysicalInfrastructureGraphBuilder.php::buildConnectivityDot(...)`**, même builder que ci-dessus (partage le vocabulaire de nœuds "device"), méthode distincte car les arêtes/le filtre sont d'une autre nature. Remplace aussi la récursion par inclusion Blade (`$visited` array) par une méthode PHP privée récursive propre. **Aucun équivalent dans l'ancien Word** (bug §0.2 #11) — capacité nouvelle.
- **Graphe des zones de sécurité** : `Report\SecurityZoneView` (`report/security_zones`) → `admin/reports/security_zones.blade.php` (DOT lignes 129–164). Filtre (session) : liste explicite de `zones[]` (filtre DB direct, pas de closures). Nœuds : Zone, Building, AdminUser (dérivés des zones filtrées). Arêtes : Zone→Zone(enfant), Zone→Building, Zone→AdminUser.
  - **`app/Services/Graph/SecurityZoneGraphBuilder.php`** : `buildDot(Collection $zones, Collection $buildings, Collection $adminUsers, array $options = [])`. **Aucun équivalent dans l'ancien Word** — capacité nouvelle.
- La section "Infrastructure physique" du rapport insère donc **les trois graphes à la suite** (hiérarchie, connectivité, zones de sécurité) en tête de section.

### Vue 7 — GDPR

- Écran : `Report\GDPRView` (`report/gdpr`) → `admin/reports/gdpr.blade.php` (DOT lignes 113–141). Filtres (session, cascade) : `macroprocess`, `process`.
- Nœuds (icônes fixes) : MacroProcessus, Process, DataProcessing (ancre `#UID` via préfixe `DATAPROC_`), Application.
- Arêtes non étiquetées : MP→P, P→DataProcessing, DataProcessing→Application.
- **`SecurityControl` n'a aucune représentation dans ce graphe.**
- **`app/Services/Graph/GdprGraphBuilder.php`** : `buildDot(Collection $macroProcessuses, Collection $processes, Collection $dataProcessings, Collection $applications, array $options = [])`. Décision à prendre (Phase 2, cf. §1.4) : ajouter ou non des nœuds/arêtes `SecurityControl` (aucune relation affichée nulle part actuellement, cf. §1.1 Vue 7) — recommandation : **ne pas ajouter de nœuds graphe pour SecurityControl** tant qu'il n'a pas de relation affichée dans son propre contenu, pour rester cohérent ; seul son tableau "nom + description" apparaît dans la section GDPR du rapport, sans graphe associé.
- **Premier builder du genre** : contrairement aux 6 autres vues, GDPR n'a aujourd'hui aucun code de graphe dans l'ancien `CartographyController` (vue absente) — c'est une capacité 100% nouvelle pour le Word, mais le principe (réutiliser le DOT de l'écran interactif) est identique.
- Gate d'accès à étendre pour inclure `SecurityControl::class` (bug #13 §0.2), cohérent avec le fait que le contenu GDPR du rapport couvre aussi `SecurityControl`.

### Principe de conception commun à tous les builders

- Chaque `*GraphBuilder` est un service **sans dépendance HTTP/session/vue** : il reçoit des collections déjà filtrées/scopées (la logique de filtrage — `Cartographer::scopedQuery`, filtres session, etc. — reste dans les contrôleurs `Report\*View` et dans les `Section` du rapport ; le rapport les appelle simplement sans filtre = collections complètes scopées par `Cartographer`).
- Signature de sortie uniforme : `buildDot(...): string` (chaîne DOT pure).
- Un `$options` commun porte les seuls axes de variation légitimes entre les deux consommateurs : `withHref` (ancres `#UID`, utiles à l'écran interactif et au PDF/SVG, inutiles pour un PNG rasterisé) et `iconResolver` (URL de route pour l'écran, chemin `public_path()` pour la rasterisation serveur `dot -Tpng`).
- La rasterisation serveur (`generateGraphImage()`, aujourd'hui une méthode privée de `CartographyController`) est déplacée telle quelle (même mécanisme `shell_exec('/usr/bin/dot ...')`) dans le `WordHelper` partagé (§1.3), en entrée de laquelle on branche la sortie de n'importe quel `*GraphBuilder`.

---

## 1.3 Conception détaillée de l'architecture

### 1.3.1 Services de section — signature commune

Chaque service de vue est une classe stateless avec une méthode publique d'entrée, une méthode privée par type d'objet, et reçoit ses dépendances (PhpWord `Section`, helper, éventuellement un registre d'ancres) en paramètres — pas d'injection de constructeur lourde, pour rester simple à instancier depuis `ReportBuilder` :

```php
namespace App\Services\Report;

interface ReportSection
{
    /**
     * Ajoute la section complète (titre de vue, graphe(s), puis un bloc par type d'objet)
     * au document PhpWord en cours de construction.
     */
    public function build(\PhpOffice\PhpWord\Element\Section $section, WordHelper $helper): void;
}
```

- `EcosystemSection implements ReportSection` — méthodes privées `addEntities()`, `addRelations()`.
- `InformationSystemSection` — `addMacroProcessuses()`, `addProcesses()`, `addActivities()`, `addOperations()`, `addTasks()`, `addActors()`, `addInformation()`.
- `ApplicationSection` — `addApplicationBlocks()`, `addApplications()`, `addApplicationServices()`, `addApplicationModules()`, `addDatabases()`, `addApplicationFlows()`.
- `AdministrationSection` — `addZoneAdmins()`, `addAnnuaires()`, `addForestAds()`, `addDomains()`, `addAdminUsers()`.
- `LogicalInfrastructureSection` — une méthode par type parmi les 16 objets de la vue.
- `PhysicalInfrastructureSection` — une méthode par type parmi les 17 objets de la vue.
- `GdprSection` — `addDataProcessings()`, `addSecurityControls()`.

Chaque méthode privée par type d'objet suit le même schéma interne :
1. Charger la collection avec les `with()` nécessaires (issus de l'inventaire §1.1), triée par `mb_strtolower(name)` (sauf objets sans `name`, ex. `PhysicalLink` → tri par `type` puis `id`, `AdminUser` → tri par `user_id`).
2. Titre de sous-section (`Heading 2`), une table PhpWord par instance (via `WordHelper::addTable()`), une ligne par champ (`addTextRow`/`addHTMLRow`/liste de liens avec signets).
3. Pour chaque relation "liste de liens" ou "lien simple" vers un objet cartographié : ajouter un lien interne Word vers le signet `$model->getUID()` de la cible (que la cible soit dans la même section ou une autre — tous les signets sont déclarés à l'avance, cf. §1.3.2).

### 1.3.2 Gestion des signets inter-sections (liens internes)

Le nom du signet Word pour un objet est **toujours** `$model->getUID()` (méthode déjà fournie par `HasUniqueIdentifier`, utilisée aujourd'hui côté écrans interactifs comme ancre `href="#UID"` et déjà utilisée comme préfixe, ex. `DATAPROC_{id}` pour `DataProcessing`, `ZONE_SEC_{id}` pour `Zone`, `ZONE_AD_{id}` pour `ZoneAdmin`). Pas de schéma alternatif à inventer.

Contrainte PhpWord : un signet (`addBookmark`) doit exister dans le document **avant** qu'un lien ne le référence pour être résolu à l'ouverture — en pratique PhpWord accepte que le bookmark soit ajouté n'importe où dans le document tant qu'il existe une fois au moment de la génération finale (pas de contrainte d'ordre strict à l'écriture, seulement à la résolution par Word/LibreOffice à l'ouverture). Comme l'ordre des vues est fixe (écosystème → ... → GDPR) et que les relations peuvent pointer aussi bien vers l'avant que vers l'arrière (ex. `Application` référence `Entity` qui est rendu avant, mais aussi `SecurityDevice` rendu après), il faut que **tous les signets existent quelle que soit la vue sélectionnée par l'utilisateur**. Or si l'utilisateur ne sélectionne pas la vue "Infrastructure logique", `SecurityDevice` n'existe pas dans le document et un lien vers lui serait rompu.

**Règle retenue** : un lien interne n'est généré **que si la vue cible fait partie des vues sélectionnées** (`in_array` sur `vues[]`) ; sinon, la relation est affichée en texte simple (nom de l'objet, sans lien), exactement comme le fait déjà l'app aujourd'hui via les gates `@canAccess(Model::class)` (ici on remplace la vérification de permission par une vérification de présence-dans-le-rapport). Chaque `Section` reçoit donc la liste des vues sélectionnées (ou, plus simple : le `WordHelper` expose `linkOrText(Model $target, string $label): void` qui consulte un registre des UID effectivement rendus, alimenté au fur et à mesure — nécessite de construire les sections dans un ordre qui peuple le registre progressivement, ou d'accepter un texte simple pour toute cible pas *encore* rendue au moment de l'appel si l'on veut éviter un pré-passage. Recommandation simple et robuste : passer explicitement `array $selectedVues` à chaque `Section::build()`, calculé une fois par `ReportBuilder`, et cartographier objet → vue en dur dans `WordHelper` (table de correspondance `Model::class => numéro de vue`, dérivée directement de l'inventaire §1.1) pour décider lien-ou-texte sans dépendre de l'ordre de construction.

### 1.3.3 Gestion mémoire

- Chaque `Section::build()` charge ses propres collections avec `select()`/`with()` ciblés (pas de `Model::all()`), au moment de son propre appel — pas de préchargement global de toutes les vues en mémoire simultanément dans `ReportBuilder`.
- Pour les tables de correspondance (pivot N-N affichées comme "liste de liens"), charger uniquement les colonnes nécessaires à l'affichage + `id`/`name` de la cible via `->select(['id', 'name'])` sur la relation quand c'est simple (Eloquent le permet via `with('relation:id,name')`), pour limiter la charge sur les tables volumineuses (`Application`, `LogicalServer`, `Workstation`, etc.).
- Le rendu des graphes (rasterisation PNG serveur) génère des fichiers temporaires (`tempnam`) — le `WordHelper`/`GraphImageRenderer` doit les supprimer (`finally { @unlink(...) }`, déjà le cas dans le code existant) immédiatement après insertion dans le document, vue par vue, plutôt que d'accumuler tous les chemins en mémoire jusqu'à la fin (le code actuel utilise déjà `$image_paths[]` — à conserver/adapter, mais nettoyer immédiatement après `Shape::addImage()` plutôt qu'à la toute fin).
- Pas de eager-loading transverse entre vues : chaque `Section` est autonome et ne dépend pas d'une collection chargée par une autre section (seule dépendance : le registre UID→vue pour la résolution des liens, cf. §1.3.2, qui est une simple table statique en mémoire, pas des collections de modèles).

### 1.3.4 Répartition des objets par service (table de correspondance objet → vue)

**Ordre de rendu dans le document — décision utilisateur du 2026-07-06** : la vue GDPR s'affiche **en premier** dans le document (avant Écosystème), pour donner de la visibilité aux traitements de données personnelles avant le reste de la cartographie. L'identifiant `vues[]` de chaque vue (utilisé dans le formulaire et par `ReportBuilder`) ne change pas — seul l'ordre d'itération dans `ReportBuilder::VUE_TITLE_KEYS` a été réordonné pour placer l'entrée `'7'` (GDPR) en tête. Colonne « Ordre document » ci-dessous = ordre réel d'apparition dans le rapport.

| Ordre document | Vue (`vues[]`) | Service | Objets (méthodes `add*()`) |
|---|---|---|---|
| 1 | 7 | `GdprSection` | DataProcessing, SecurityControl |
| 2 | 1 | `EcosystemSection` | Entity, Relation (+ RelationValue imbriqué) |
| 3 | 2 | `InformationSystemSection` | MacroProcessus, Process, Activity (+ ActivityImpact imbriqué), Operation, Task, Actor, Information |
| 4 | 3 | `ApplicationSection` | ApplicationBlock, Application (+ ApplicationEvent imbriqué), ApplicationService, ApplicationModule, Database, ApplicationFlow |
| 5 | 4 | `AdministrationSection` | ZoneAdmin, Annuaire, ForestAd, Domain, AdminUser |
| 6 | 5 | `LogicalInfrastructureSection` | Network, Subnetwork, Gateway, ExternalConnectedEntity, Router, NetworkSwitch, SecurityDevice, DhcpServer, Dnsserver, LogicalServer, Cluster, Backup, Container, LogicalFlow, Vlan, Certificate |
| 7 | 6 | `PhysicalInfrastructureSection` | Site, Building, Bay, Zone, PhysicalServer, Workstation, StorageDevice, Peripheral, Phone, PhysicalSwitch, PhysicalRouter, WifiTerminal, PhysicalSecurityDevice, PhysicalLink, Wan, Man, Lan |

`RelationValue`, `ActivityImpact`, `ApplicationEvent` n'ont pas de méthode `add*()` dédiée au niveau section — ils sont rendus comme sous-tableau à l'intérieur de la méthode de leur parent (`addRelations()`, `addActivities()`, `addApplications()` respectivement).

### 1.3.5 `WordHelper` (partagé)

Reprend et étend les helpers déjà présents dans `CartographyController`/`Report\ReportController` (styles, `addTable`, `addTextRow`, `addHTMLRow`, `addTextRunRow`, `dotImage`, `generateGraphImage`, `embedImagesInSvg`), plus :
- `addBookmarkedTitle(Section $section, string $uid, string $title, int $depth)` — titre de sous-section qui pose aussi le signet `$uid`.
- `linkOrText(TextRun $run, Model $target, array $selectedVues)` — résout lien interne vs texte simple selon §1.3.2.
- `insertGraph(Section $section, string $dot, string $altTitle)` — encapsule `generateGraphImage()` + insertion + suppression du fichier temporaire.

### 1.3.6 `ReportBuilder` (orchestrateur)

```php
namespace App\Services\Report;

class ReportBuilder
{
    public function build(array $selectedVues): string // chemin du fichier .docx généré
    {
        // PhpWord::init(), styles de titres, page de garde (titre, entité, date, version Mercator),
        // TOC automatique, en-tête/pied de page (titre + date + pagination), saut de page entre vues.
        // Pour chaque vue sélectionnée dans l'ordre fixe GDPR, 1, 2, 3, 4, 5, 6 : instancier le Section correspondant,
        // appeler build($section, $helper), saut de page.
        // Sauvegarde du document, retour du chemin.
    }
}
```

Le contrôleur `CartographyController` (réduit) ne garde que : validation de la requête (`vues[]` uniquement, `granularity` supprimé), `abort_if(Gate::denies('reports_access'), ...)`, appel à `ReportBuilder::build($request->input('vues', []))`, réponse de téléchargement du fichier généré.

---

## 1.4 Plan d'implémentation

Découpage incrémental, dans l'ordre imposé par la mission (squelette → graphes → contenu vue par vue → suppression ancien code → tests). Chaque étape est livrable et testable indépendamment.

### Étape 0 — Squelette du rapport
**Fichiers** : `app/Services/Report/ReportBuilder.php` (nouveau), `app/Services/Report/WordHelper.php` (nouveau, styles/helpers repris de `CartographyController`/`Report\ReportController`), `app/Http/Controllers/Admin/CartographyController.php` (réduit — validation + gate + appel), `resources/views/doc/report.blade.php` (suppression du champ `granularity`, ajout de l'option `vues[]` valeur `7` = GDPR).
**Contenu** : page de garde (titre, entité, date, version via `app('mercator.version')`), TOC automatique, en-têtes/pieds de page, sauts de page entre vues sélectionnées — sections vides à ce stade (juste le titre de chaque vue sélectionnée).
**Tests** : test Pest `controller` — génération avec 0, 1, toutes les vues sélectionnées → statut 200, fichier `.docx` non vide, `granularity` absent de la requête ne provoque plus d'erreur.

### Étape 1 — Mutualisation des graphes — ✅ terminée (2026-07-06)

**Réalisé** : les 9 builders (`app/Services/Graph/EcosystemGraphBuilder.php`, `InformationSystemGraphBuilder.php`, `ApplicationGraphBuilder.php`, `ApplicationFlowGraphBuilder.php`, `AdministrationGraphBuilder.php`, `LogicalInfrastructureGraphBuilder.php`, `PhysicalInfrastructureGraphBuilder.php` avec ses 2 méthodes `buildLocationDot`/`buildConnectivityDot`, `SecurityZoneGraphBuilder.php`, `GdprGraphBuilder.php`) sont créés et extraient fidèlement la construction DOT auparavant dupliquée dans les Blade `resources/views/admin/reports/*.blade.php`. Les 10 contrôleurs `Report\*View` délèguent désormais à ces builders (`buildDot()`/`imageManifest()`) sans changement de comportement filtré. Les blocs `<script>` correspondants sont réduits à `let dotSrc = \`{!! $dotSrc !!}\`;` + `@json($imageManifest)`. Le fichier `network_infrastructure_building.blade.php` (récursion Blade) est supprimé, remplacé par la méthode privée récursive `PhysicalInfrastructureGraphBuilder::buildBuildingCluster()`.

**Bugs trouvés et corrigés pendant l'extraction** (portage fidèle avec correction des cas sans ambiguïté) :
- `ApplicationFlowGraphBuilder` : les ancres `href="#APPLICATION{id}"` etc. de l'ancien `application_flows.blade.php` ne correspondaient à aucun signet réel (le vrai signet est `$model->getUID()`, ex. `APP_5`, pas `APPLICATION5`) — liens morts corrigés pour utiliser `getUID()` partout, conformément au principe déjà retenu en §1.3.2.
- `PhysicalInfrastructureGraphBuilder::buildLocationDot()` : le nœud de regroupement des workstations (`WG{id}`) référençait `$workstation->getUID()` — une variable hors de portée dans le bloc d'origine (`physical_infrastructure.blade.php` ligne 413), donnant un lien mort ou une valeur d'une itération précédente. Corrigé pour utiliser `$building->workstations()->first()->getUID()`, la même instance que celle utilisée pour le libellé du nœud.

**Écart corrigé (2026-07-06)** : dans `buildConnectivityDot()` (graphe de connectivité physique), un `PhysicalServer` ou `StorageDevice` rattaché directement à un `Building` (sans `bay_id`) n'obtenait aucun nœud — l'ancien `network_infrastructure_building.blade.php` ne les listait qu'au niveau des baies, jamais au niveau bâtiment (contrairement à `Peripheral`/`PhysicalSwitch`/`PhysicalRouter` qui avaient déjà un repli `bay_id===null`). Corrigé dans `PhysicalInfrastructureGraphBuilder::buildBuildingCluster()` en ajoutant le même repli pour ces deux types d'objets (décision utilisateur : corriger maintenant plutôt que différer). Nouveau test `PhysicalInfrastructureGraphBuilderTest::buildConnectivityDot draws a physical server and a storage device attached directly to a building without a bay` couvrant ce cas.

**Tests** : tous les `tests/Feature/View/*ViewTest.php` existants passent sans changement observable ; ajout de tests de branche filtrée (site/réseau sélectionné, `show_ip`, `show_ports`) sur `LogicalInfrastructureViewTest`, `PhysicalInfrastructureViewTest`, `NetworkInfrastructureViewTest` ; création de `SecurityZoneViewTest.php` et `GDPRViewTest.php` (aucune couverture n'existait avant) ; 9 fichiers `tests/Unit/Services/Graph/*BuilderTest.php` (16 tests) vérifiant que des collections factory produisent un DOT contenant les nœuds/arêtes attendus. `tests/Pest.php` mis à jour pour lier `Tests\TestCase` + `RefreshDatabase` au dossier `Unit` (nécessaire pour que les builders puissent appeler `route()`/`trans()`/`Cartographer::canAccess()` en test). Suite complète : 1617 passed, 0 failed. `phpstan` (tout le projet) et `pint` (aucune régression, vérifié par comparaison `git stash` fichier par fichier) clean.

### Étape 2 — Régénération du contenu, vue par vue

Pour chaque vue, dans l'ordre : Écosystème → Système d'information → Applicatif → Administration → Infrastructure logique → Infrastructure physique → GDPR (ordre du document ; permet de valider le mécanisme de section/signet sur les vues les plus simples avant les plus grosses).

- **2.1 `EcosystemSection`** — ✅ terminée (2026-07-06) : `addEntities()`, `addRelations()` (avec sous-tableau `RelationValue`, historique de prix rendu en tableau simple sans graphique, et liste `documents` en liens externes). Fichier : `app/Services/Report/EcosystemSection.php`. Le graphe de vue (via `EcosystemGraphBuilder`, Étape 1) est inséré en tête via `WordHelper::insertGraph()`.
  - **Infrastructure commune posée à cette occasion** (réutilisable par toutes les sections suivantes) : interface `App\Services\Report\ReportSection` (`build(Section $section, WordHelper $helper, array $selectedVues): void`) ; `WordHelper::MODEL_VUE_MAP` (table de correspondance des 55 objets cartographiés vers leur `vues[]`, §1.3.4) ; `WordHelper::linkOrText()` / `addLinkListRow()` (lien interne si la vue cible est sélectionnée, sinon texte simple — §1.3.2) ; `WordHelper::resolveIconPath()` / `addImageRow()` (résolution d'icône vers un chemin filesystem — `Document` stocké dans `storage_path('docs/{id}')` ou image statique `public_path()` — et insertion dans le tableau ; décision utilisateur du 2026-07-06 : les icônes sont intégrées dans le rendu Word, pas seulement le texte) ; `WordHelper::addNestedTableRow()` (sous-tableau imbriqué générique, réutilisé par `ActivityImpact`/`ApplicationEvent`/flux/Backup dans les étapes suivantes) ; `WordHelper::addDocumentLinksRow()` (liste de pièces jointes en liens hypertexte externes, réutilisé par `ExternalConnectedEntity`/`DataProcessing`).
  - **Pré-requis créé pour que `linkOrText()` soit typable proprement** : nouvelle interface `App\Contracts\HasUniqueIdentifierContract` (`getUID(): string`), ajoutée à l'`implements` des 53 modèles cartographiés qui utilisent déjà le trait `HasUniqueIdentifier` (tous sauf `PhysicalLink` et `SecurityControl`, qui n'ont pas de `getUID()` — non utilisés comme cible de lien dans l'inventaire §1.1, donc sans impact ; `PhysicalLink` nécessitera un identifiant de repli propre à sa section en Étape 2.6, cf. note ci-dessous).
  - **Tests** : `tests/Feature/Report/EcosystemSectionTest.php` (rendu direct de la section dans un document PhpWord autonome, assertions sur `word/document.xml` + `word/_rels/document.xml.rels` pour les liens externes) ; test ajouté dans `CartographyControllerTest` confirmant que le contenu réel (nom + signet d'une Entity) traverse tout le pipeline HTTP → `ReportBuilder` → `EcosystemSection`. `tests/Pest.php` étendu pour lier `Feature/Report` à `Tests\TestCase`+`RefreshDatabase`. Suite complète rejouée : 1622 passed. `phpstan` (tout le projet, 1082 fichiers) et `pint` clean (vérifié fichier par fichier via `git stash` pour les 53 modèles touchés — aucune régression de style, tous les signalements pint pré-existent).
  - **Point à surveiller pour la suite** : `PhysicalLink` (Étape 2.6) n'a pas de `getUID()` — sa section devra utiliser un signet de repli ad hoc (ex. `'PHYSICAL_LINK_'.$link->id`) puisqu'il n'implémente pas `HasUniqueIdentifierContract`.
- **2.2 `InformationSystemSection`** — ✅ terminée (2026-07-06) : 7 méthodes (`addMacroProcessuses`, `addProcesses`, `addActivities`, `addOperations`, `addTasks`, `addActors`, `addInformation`). Fichier : `app/Services/Report/InformationSystemSection.php`. `Information` régénéré avec l'intégralité du contenu hors partial (`owner`/`administrator`/`storage`/`sensitivity`/`security_need`/`parents`/`children`/`processes`/`constraints`), pas seulement le partial incomplet. `Activity` inclut le BIA (durées formatées jours/heures/minutes), le sous-tableau imbriqué `ActivityImpact` (type + gravité, via `WordHelper::riskLabel()`) et le DRP (lien externe si URL valide). Les références vers `Graph`/BPMN sont omises partout (objet exclu du périmètre).
  - **Infrastructure commune enrichie** : `WordHelper::addSecurityNeedRow()` (badges C/I/A/T(+Auth) génériques, réutilisés par `MacroProcessus`/`Process`/`Information` ici, et par tous les objets à `security_need_*` dans les étapes suivantes) ; `WordHelper::riskLabel()` exposée publiquement (partagée entre `security_need_*` et `ActivityImpact.severity`, même échelle 0–4).
  - **Bug latent trouvé et corrigé** : `InformationSystemGraphBuilder::buildDot()` codait en dur des chemins d'image web-relatifs (`/images/macroprocess.png`, etc.) sans mécanisme de résolution — correct pour l'écran interactif (le navigateur les récupère via HTTP) mais invalide pour la rasterisation serveur `dot -Tpng` du rapport Word (qui a besoin d'un vrai chemin filesystem). Ajout d'une option `iconPathResolver` (même principe que `iconResolver` sur `EcosystemGraphBuilder`), avec un résolveur par défaut neutre (identité, préserve le comportement de l'écran interactif) et `InformationSystemSection` fournissant `public_path(ltrim($path, '/'))` pour le rendu Word. **Point de vigilance pour la suite** : les 7 autres builders de graphe (`Application`, `ApplicationFlow`, `Administration`, `LogicalInfrastructure`, `PhysicalInfrastructure`, `SecurityZone`, `Gdpr`) ont probablement le même écart (y compris pour leurs icônes par enregistrement, qui utilisent `route(...)` — une URL HTTP, pas un chemin filesystem, tout aussi inutilisable par `dot`) ; à corriger au fur et à mesure, dans chaque étape où le builder correspondant est réellement exercé côté Word (2.3 et suivantes), pas de correctif spéculatif fait à l'avance.
  - **Correctif collatéral** : `Information::attributes`/`Information::type` provoquaient une fausse alerte `phpstan` (« accès à une propriété protégée/indéfinie ») à cause d'une collision de nom avec la propriété interne Eloquent `protected $attributes` — reproduit et confirmé en isolation. Corrigé selon le pattern déjà établi ailleurs dans la codebase (`ApplicationFlow`, `Zone`) : ajout d'un bloc `@property` complet sur `Information` déclarant ses colonnes réelles.
  - **Tests** : `tests/Feature/Report/InformationSystemSectionTest.php` (3 tests : chaîne complète macroprocess→information avec BPMN absent, BIA/ActivityImpact/DRP, hiérarchie Information + contrainte GDPR) ; test bout-en-bout ajouté à `CartographyControllerTest` (vue 2 réelle via HTTP). Suite complète : 1626 passed. `phpstan` (1083 fichiers) et `pint` clean.
- **2.3 `ApplicationSection`** — ✅ terminée (2026-07-05) : 6 méthodes (`addApplicationBlocks`, `addApplications`, `addApplicationServices`, `addApplicationModules`, `addDatabases`, `addApplicationFlows`). Fichier : `app/Services/Report/ApplicationSection.php`. L'historique `ApplicationEvent` (aujourd'hui rendu en JS/modal côté écran interactif) est transformé en vrai sous-tableau serveur (date/utilisateur/message, via `with('events.user')`, réutilise `WordHelper::addNestedTableRow()`). Les 4 tableaux de flux imbriqués (`Application`, `ApplicationService`, `ApplicationModule`, `Database`) sont unifiés via une méthode privée commune `addFluxTable(Table $table, WordHelper $helper, Collection $sourceFluxes, Collection $destFluxes, array $selectedVues)`, réutilisée par les 4 (évite la duplication x4 déjà présente dans les Blade actuels) ; elle résout l'endpoint source/dest via la chaîne `applicationSource ?? serviceSource ?? moduleSource ?? databaseSource` sans tag de type, fidèle à l'affichage imbriqué des `show.blade.php` actuels. La page autonome `ApplicationFlow` (`addApplicationFlows()`), elle, affiche bien un tag `[Application]`/`[Service]`/`[Module]`/`[Database]` à côté de chaque lien, fidèle à `application-flows/_details.blade.php`. Les deux graphes (`ApplicationGraphBuilder` structurel + `ApplicationFlowGraphBuilder` de flux) sont insérés en tête de section via `WordHelper::insertGraph()`. Bugs #2/#4 (§0.2, déjà corrigés sur l'écran interactif le 2026-07-05) non reproduits ici (la clé de config `mercator.parameters.security_need_auth` et le badge `security_need` passent par `WordHelper::addSecurityNeedRow()`, commun).
  - **Bug latent trouvé et corrigé (même classe que 2.2)** : `ApplicationGraphBuilder::buildDot()` et `ApplicationFlowGraphBuilder::buildDot()` codaient en dur soit des chemins web-relatifs (icônes statiques), soit des URL `route('admin.documents.show', ...)` (icônes par enregistrement sur `Application`/`Database`) — les deux invalides pour la rasterisation serveur `dot -Tpng`. Ajout d'une option `iconResolver` (signature `callable(?int $iconId, string $fallback): string`, différente de `iconPathResolver` sur `InformationSystemGraphBuilder` car ces builders ont des icônes par enregistrement, pas seulement statiques), résolveur par défaut inchangé (`route(...)`, préserve l'écran interactif), `ApplicationSection` fournissant `WordHelper::resolveIconPath()` pour le rendu Word.
  - **Incohérence de rendu confirmée en relisant les Blade sources (pas un bug, une nuance à préserver)** : `Application.attributes` s'affiche en texte brut (`_details.blade.php`), alors que `ApplicationFlow.attributes`/`Relation.attributes`/`Information.attributes` s'affichent en badges (`explode(" ", ...)`) — `ApplicationSection` respecte cette différence (pas de `formatAttributes()` sur `Application`, seulement sur `ApplicationFlow`, via une 3ᵉ copie du helper privé déjà dupliqué dans `EcosystemSection`/`InformationSystemSection` — cohérent avec le pattern existant, non consolidé dans `WordHelper` à ce stade). `Database.entities`/`Database.entityResp` s'affichent en texte simple (pas des liens), contrairement aux autres relations `BelongsToMany`/`BelongsTo` de la vue — également respecté.
  - **Tests** : `tests/Feature/Report/ApplicationSectionTest.php` (3 tests : chaîne complète bloc→application→service→module→base+flux, administrateurs/RTO-RPO/historique d'événements/documentation en lien, flux autonome avec tags de type polymorphes) ; test bout-en-bout ajouté à `CartographyControllerTest` (vue 3 réelle via HTTP). Suite complète rejouée : 1630 passed, 0 failed. `phpstan` (tout le projet) et `pint` clean sur tous les fichiers touchés.
- **2.4 `AdministrationSection`** — ✅ terminée (2026-07-05) : 5 méthodes (`addZoneAdmins`, `addAnnuaires`, `addForestAds`, `addDomains`, `addAdminUsers`). Fichier : `app/Services/Report/AdministrationSection.php`. Contenu entièrement couvert par les partials `_details.blade.php` (pas de contenu hors-partial pour cette vue, contrairement à `Application`/`Activity`) — vérifié en relisant les 5 partials sources. `AdminUser` inclus (corrige de facto le bug #9 §0.2 : absent de l'ancien rapport) : sa page utilise `user_id` comme titre/signet (pas de champ `name`), et son champ `attributes` est rendu en liste badges (`formatAttributes()`, 4ᵉ copie du même helper privé déjà présent dans `EcosystemSection`/`InformationSystemSection`/`ApplicationSection` — cohérent avec le pattern établi). Le graphe (`AdministrationGraphBuilder`) est inséré en tête via `WordHelper::insertGraph()`.
  - **Bug latent trouvé et corrigé (même classe que 2.2/2.3, plus sévère ici)** : `AdministrationGraphBuilder::buildDot()` n'avait *aucun* mécanisme de résolution d'icône — les 5 chemins (`/images/zoneadmin.png`, `/images/annuaire.png`, `/images/ldap.png`, `/images/domain.png`, `/images/user.png`) étaient codés en dur en web-relatif, invalides pour `dot -Tpng`. Ajout d'une option `iconPathResolver` (même signature que sur `InformationSystemGraphBuilder`, ces 5 icônes étant toutes statiques — pas d'icône par enregistrement ici, confirmé en relisant `AdministrationView`/`administration.blade.php`), résolveur par défaut neutre (identité), `AdministrationSection` fournissant `public_path(ltrim($path, '/'))`.
  - **Tests** : `tests/Feature/Report/AdministrationSectionTest.php` (2 tests : chaîne complète zone-admin→annuaire/forêt→domaine→serveur logique, `AdminUser` avec titre `user_id`/attributs badges/lien domaine) ; test bout-en-bout ajouté à `CartographyControllerTest` (vue 4 réelle via HTTP). Suite complète rejouée : 1633 passed, 0 failed. `phpstan` (tout le projet) et `pint` clean sur tous les fichiers touchés.
- **2.5 `LogicalInfrastructureSection`** — ✅ terminée (2026-07-05) : 16 méthodes (`addNetworks`, `addSubnetworks`, `addGateways`, `addExternalConnectedEntities`, `addRouters`, `addNetworkSwitches`, `addSecurityDevices`, `addDhcpServers`, `addDnsservers`, `addLogicalServers`, `addClusters`, `addBackups`, `addContainers`, `addLogicalFlows`, `addVlans`, `addCertificates`). Fichier : `app/Services/Report/LogicalInfrastructureSection.php` — la plus grosse section à ce jour. Bug #1 (`vlan_id`) non reproduit : `Vlan.vlan_id` (vrai champ) est utilisé directement, pas `$vlan->id`. `Cluster.logical_servers`/`Routers` séparés en deux lignes propres (`cruds.cluster.fields.logical_servers` et `cruds.router.title` comme libellés), au lieu de la cellule fusionnée avec le libellé anglais en dur `'Routers'` de l'ancien Blade. `Backup.attributes` harmonisé en badges (`formatAttributes()`, 5ᵉ copie du helper déjà dupliqué dans les 4 sections précédentes) alors que le Blade actuel l'affichait en texte brut. Le sous-tableau flottant `LogicalFlow` source/destination (polymorphe sur 9 relations `BelongsTo` chacun : `logicalServer(Source|Dest)`, `peripheral*`, `physicalServer*`, `storageDevice*`, `workstation*`, `physicalSecurityDevice*`, `securityDevice*`, `subnetwork*`, `cluster*`) est rendu via une méthode privée commune `addFlowEndpointRun()` prenant une liste ordonnée `[modèle, champ d'adresse]` et retournant au premier modèle non nul — fidèle à la chaîne `@if/@elseif` de `logicalFlows/_details.blade.php`, mais éclatée en lignes séparées (adresse+lien, puis port) plutôt que les colonnes multiples du Blade (tableau Word à 2 colonnes, cf. convention déjà établie). Le sous-tableau de sauvegardes imbriqué sous `LogicalServer` (§1.1 ligne 16) est gardé par `Gate::allows('backup_show')`, exactement comme `logicalServers/show.blade.php` — vérifié en test que ce gate est bien franchi par l'admin de test (fallback rôle→permissions DB de `AuthServiceProvider::boot()`, cf. §1.4 point technique). `Backup` a aussi sa propre méthode top-niveau `addBackups()` (16ᵉ objet de l'inventaire), distincte du sous-tableau imbriqué.
  - **Bug latent trouvé et corrigé (même classe que 2.2/2.3/2.4)** : `LogicalInfrastructureGraphBuilder::buildDot()` mélangeait des chemins web-relatifs codés en dur ET des URL `route('admin.documents.show', ...)` pour les icônes par enregistrement (LogicalServer/Container/Workstation/SecurityDevice/PhysicalSecurityDevice/Peripheral) — les deux invalides pour `dot -Tpng`. Ajout d'une option `iconResolver` unique (signature `callable(?int $iconId, string $fallback): string`), appliquée uniformément aux 20 emplacements d'image du fichier (statiques passés avec `$iconId = null`), résolveur par défaut inchangé. `LogicalInfrastructureSection` fournit `WordHelper::resolveIconPath()`. Le graphe consomme aussi 6 collections d'objets "de contexte" hors vue (`Workstation`, `WifiTerminal`, `Phone`, `Peripheral`, `PhysicalSecurityDevice`, `StorageDevice`) — requêtées ici uniquement pour le graphe (scopées via `Cartographer::scopedQuery`, sans eager-loading de relations puisque leur contenu propre est du ressort de la future vue 6).
  - **Correctifs collatéraux (même pattern que `Information`/`ApplicationFlow` en 2.2)** : `Network`, `Subnetwork` et `LogicalFlow` provoquaient chacun une fausse alerte `phpstan` (`attributes`/`type` non résolus, collision avec la propriété interne Eloquent `protected $attributes`) — corrigé par l'ajout d'un bloc `@property` complet sur les 3 modèles.
  - **Tests** : `tests/Feature/Report/LogicalInfrastructureSectionTest.php` (3 tests : chaîne réseau→sous-réseau→passerelle/vlan avec adressage, `LogicalServer` avec pourcentage de disque calculé et sous-tableau de sauvegardes gardé par `backup_show`, `LogicalFlow` autonome résolu via la relation polymorphe `Subnetwork`/`Cluster`) ; test bout-en-bout ajouté à `CartographyControllerTest` (vue 5 réelle via HTTP). Suite complète rejouée : 1637 passed, 0 failed. `phpstan` (tout le projet) et `pint` clean sur tous les fichiers touchés.
- **2.6 `PhysicalInfrastructureSection`** — ✅ terminée (2026-07-06) : 17 méthodes (`addSites`, `addBuildings`, `addBays`, `addZones`, `addPhysicalServers`, `addWorkstations`, `addStorageDevices`, `addPeripherals`, `addPhones`, `addPhysicalSwitches`, `addPhysicalRouters`, `addWifiTerminals`, `addPhysicalSecurityDevices`, `addPhysicalLinks`, `addWans`, `addMans`, `addLans`). Fichier : `app/Services/Report/PhysicalInfrastructureSection.php` — vue avec le plus de graphes (3, insérés à la suite en tête de section : hiérarchie site→bâtiment→baie→équipement, connectivité physique via `PhysicalLink`, zones de sécurité). `Bay` : les 6 relations fusionnées dans une seule cellule Blade (`PhysicalServer`/`StorageDevice`/`Peripheral`/`PhysicalSwitch`/`PhysicalRouter`/`PhysicalSecurityDevice`) séparées en 6 lignes distinctes, comme recommandé dans l'inventaire. `WifiTerminal` utilise déjà les clés `cruds.wifiTerminal.fields.vendor/product/version` (bug #6 corrigé en amont de la Phase 2). `Lan.description`, affiché en texte brut dans le Blade actuel (seul objet de la vue à ne pas utiliser `{!! !!}`), harmonisé en HTML riche comme recommandé. `Peripheral.version` utilise le vrai champ `version` — pas `pversion`, un attribut inexistant sur le modèle que `peripherals/show.blade.php` affiche par erreur (toujours vide en pratique), repéré en relisant le Blade ; l'inventaire l'avait déjà noté comme « attribut réel ». `PhysicalLink` (seul objet du rapport sans `name` ni `HasUniqueIdentifierContract`/`getUID()`, cf. point de vigilance de l'Étape 2.1) utilise un signet ad hoc `'PHYSICAL_LINK_'.$link->id` et un titre `cruds.physicalLink.title` suffixé de `#id` ; son couple source/destination (11 relations `BelongsTo` chacun) est résolu sans tag de type, fidèle à `links/_details.blade.php`.
  - **Bug latent trouvé et corrigé (même classe que 2.2-2.5)** : `PhysicalInfrastructureGraphBuilder` (`buildLocationDot()` ET `buildConnectivityDot()`, y compris la méthode récursive privée `buildBuildingCluster()`) et `SecurityZoneGraphBuilder::buildDot()` mélangeaient chemins web-relatifs codés en dur et URL `route('admin.documents.show', ...)` pour les icônes par enregistrement — invalides pour `dot -Tpng`. Ajout d'une option `iconResolver` uniforme sur les 3 méthodes publiques, `$iconResolver` propagé explicitement en paramètre supplémentaire à `buildBuildingCluster()` (méthode récursive) plutôt que recalculé à chaque appel. `PhysicalInfrastructureSection` fournit `WordHelper::resolveIconPath()` aux trois builders.
  - **Correctif collatéral (même pattern que 2.2/2.5)** : `Site` provoquait la même fausse alerte `phpstan` (`attributes`/`type`, collision `protected $attributes`) — corrigé par un bloc `@property`. Fait notable : contrairement aux étapes précédentes, la plupart des 17 modèles de cette vue (`Building`, `Zone` — déjà annoté en 2.1 —, `PhysicalSecurityDevice`, `PhysicalLink`, etc.) n'ont **pas** déclenché cette collision malgré un champ `attributes` similaire ; seul `Site` l'a fait, confirmant que le déclenchement dépend de subtilités d'introspection Larastan non prévisibles à l'avance — la vérification au cas par cas via `phpstan analyse` reste donc nécessaire à chaque étape plutôt qu'une règle générale.
  - **Tests** : `tests/Feature/Report/PhysicalInfrastructureSectionTest.php` (4 tests : chaîne site→bâtiment→baie avec les 6 relations en lignes séparées, `Zone` parent/enfant + utilisateurs admin libellés par `user_id`, champs HTML de `PhysicalServer`/lien `Workstation.user` par `user_id`/`Peripheral.version` correct, `PhysicalLink` autonome avec signet ad hoc et résolution polymorphe) ; test bout-en-bout ajouté à `CartographyControllerTest` (vue 6 réelle via HTTP). Suite complète rejouée : 1642 passed, 0 failed. `phpstan` (tout le projet) et `pint` clean sur tous les fichiers touchés.
- **2.7 `GdprSection`** — ✅ terminée (2026-07-06) : 2 méthodes (`addDataProcessing`, `addSecurityControls`). Fichier : `app/Services/Report/GdprSection.php`. `SecurityControl` : conforme à la décision confirmée (Questions ouvertes item 4) — name/description uniquement, pas de relations inverses (`Application::securityControls()`/`Process::securityControls()` restent invisibles partout, y compris depuis `Application`/`Process` eux-mêmes). Comme `PhysicalLink` (Étape 2.6), `SecurityControl` n'implémente pas `HasUniqueIdentifierContract` (pas de `getUID()`) — signet ad hoc `'SECURITY_CONTROL_'.$securityControl->id`. `DataProcessing` : les 6 booléens `lawfulness_*` (rendus en cases à cocher désactivées dans le Blade) sont rendus en une ligne "liste des bases légales cochées" (labels concaténés), suivie du champ libre `lawfulness` (HTML, sans libellé propre — fidèle au `<th>&nbsp;</th>` du Blade).
  - **Bug latent trouvé et corrigé (même classe que 2.2-2.6, dernier des 7 builders)** : `GdprGraphBuilder::buildDot()` codait en dur 4 chemins d'image web-relatifs statiques (aucune icône par enregistrement dans ce graphe). Ajout d'un `iconPathResolver` (même principe que `InformationSystemGraphBuilder`/`AdministrationGraphBuilder`), `GdprSection` fournissant `public_path(ltrim($path, '/'))`. Tous les graph builders de la Phase 2 utilisent désormais une résolution d'icône correcte pour la rasterisation Word.
  - **Nettoyage collatéral dans `ReportBuilder`** : avec les 7 vues désormais couvertes, `VUE_SECTION_CLASSES` est une table exhaustive (clés 1–7) — `phpstan` a détecté que la branche de repli (`$sectionClass ?? null` puis `else { addTitle(...) }`, utilisée quand une vue n'avait pas encore de section) était devenue du code mort provably-toujours-vrai. Simplifiée : accès direct `self::VUE_SECTION_CLASSES[$vueId]` sans repli. La constante `VUE_TITLE_KEYS` (clé de traduction du titre, désormais inutile puisque chaque section rend déjà son propre titre de vue 1 via `$section->addTitle(trans(...), 1)`) remplacée par `VUE_ORDER`, une simple liste ordonnée des ids de vue (GDPR en tête, décision utilisateur) — élimine une donnée devenue morte plutôt que de la laisser traîner.
  - **Tests** : `tests/Feature/Report/GdprSectionTest.php` (2 tests : bases légales RGPD cochées + `update_date` + liens `Application`/`Document`, `SecurityControl` avec signet ad hoc) ; test bout-en-bout ajouté à `CartographyControllerTest` (vue 7 réelle via HTTP). Suite complète rejouée : 1645 passed, 0 failed. `phpstan` (tout le projet) et `pint` clean sur tous les fichiers touchés.
  - **Phase 2 terminée** : les 7 vues (Écosystème, Système d'information, Applicatif, Administration, Infrastructure logique, Infrastructure physique, GDPR) ont chacune leur `ReportSection`, `ReportBuilder::VUE_SECTION_CLASSES` est exhaustif. Prochaine étape du plan (§1.4) : **Étape 3** — suppression de l'ancien code (`CartographyController::cartography()` et helpers dupliqués), à ne démarrer que sur confirmation explicite de l'utilisateur.
- **Tests** (par sous-étape) : un test Pest par section vérifiant, pour chaque type d'objet créé via factory, que les champs/relations attendus apparaissent dans le document généré (lecture du `.docx` via `PhpOffice\PhpWord\IOFactory` ou vérification de taille/contenu XML), plus un test dédié "objets exclus absents" (`User`, `Role`, `Graph`, etc. ne génèrent jamais de section/lien).

### Étape 3 — Suppression de l'ancien code — ✅ terminée (2026-07-06)

**Constat** : `app/Http/Controllers/Admin/CartographyController.php` était déjà réduit à sa forme finale (26 lignes, ne fait plus que déléguer à `ReportBuilder`) au moment d'attaquer cette étape — la méthode monolithique et ses helpers dupliqués (`addTable`, `addTextRow`, `addHTMLRow`, `addTextRunRow`, `imageToBase64`, `embedImagesInSvg`, `dotImage`, `generateGraphImage`) avaient déjà été supprimés (probablement au moment du branchement initial de `ReportBuilder`, avant l'Étape 2.1 — `git diff` montre 2588 lignes déjà retirées, non committées). Vérifié via `grep` sur `app/` : aucune de ces méthodes n'existe plus ailleurs que dans `WordHelper` (leur nouvelle unique implémentation) ; les routes `web.php`/`api.php` pointent toutes deux correctement vers le contrôleur allégé.

**Bug trouvé et corrigé en cours de vérification** : la checkbox « avec schémas » du formulaire de rapport (`name="graph"`, décochée par défaut — traduction `graph_helper` = « avec schémas », donc opt-in, pas opt-out) alimente `$withGraphs` dans le contrôleur puis `ReportBuilder::build($vues, $withGraphs)`, mais ce paramètre n'était **jamais utilisé** : chaque section appelait `WordHelper::insertGraph()` sans condition, donc la checkbox n'avait plus aucun effet depuis la réécriture (régression silencieuse, la génération des graphes — l'étape coûteuse en temps, un `shell_exec('/usr/bin/dot ...')` par graphe — se faisait donc systématiquement même quand l'utilisateur ne les demandait pas). Question posée à l'utilisateur : re-brancher proprement (recommandé), supprimer la checkbox, ou laisser tel quel. **Décision : re-brancher.** Implémenté via `WordHelper::setIncludeGraphs(bool)` (nouvelle méthode, propriété `$includeGraphs` par défaut `true` — ne casse donc pas les tests de section autonomes qui construisent un `WordHelper` sans jamais appeler ce setter) qui fait de `insertGraph()` un no-op quand désactivé ; `ReportBuilder::build()` appelle `$this->wordHelper->setIncludeGraphs($withGraphs)` avant de construire les sections.

**Tests** : 2 tests ajoutés à `CartographyControllerTest.php` prouvant que la checkbox fonctionne à nouveau de bout en bout (absence/présence de `<w:pict` dans le `.docx` généré — vérifié en isolant le cas à une vue 1 sans aucune `Entity` créée, pour que le graphe soit la seule source d'image possible ; une première tentative avec une `Entity` factice a été invalidée car son icône `addImageRow()` produit elle aussi un `<w:pict`, faussant le test). Suite complète rejouée : 1647 passed, 0 failed. `phpstan` (tout le projet) et `pint` clean.

### Correctif post-Étape 3 (2026-07-06) — graphes affichés en blanc dans le rapport

**Signalé par l'utilisateur** : « Les graphiques ne s'affichent pas dans le rapport de cartographie. Un espace se trouve dans le rapport mais il est blanc. »

**Cause** : `WordHelper::insertGraph()` supprimait le PNG temporaire (`@unlink($imagePath)`) immédiatement après l'appel à `$section->addImage($imagePath, ...)`. Or PhpWord ne fait qu'enregistrer le chemin source à cet instant — il ne lit le fichier que plus tard, au moment de `$writer->save()` (qui n'intervient qu'après la construction de **toutes** les sections), pour l'intégrer dans `word/media/*.png` du zip `.docx`. Le fichier temporaire était donc systématiquement déjà supprimé avant cette lecture : la relation `r:id` était bien écrite dans `document.xml`/`document.xml.rels`, mais le média référencé n'existait jamais dans l'archive — d'où l'espace réservé mais blanc dans Word. Confirmé en extrayant manuellement un `.docx` généré (`unzip` + inspection) : `word/media/` était vide malgré la présence de `<w:pict>` dans `document.xml`.

**Corrigé** : `WordHelper` accumule désormais les chemins des PNG insérés (`$tempImagePaths`) au lieu de les supprimer immédiatement ; nouvelle méthode `cleanupTempFiles()` à appeler une fois le document réellement sauvegardé. `ReportBuilder::build()` sauvegarde le `.docx` dans un `try`/`finally` qui appelle `$this->wordHelper->cleanupTempFiles()` après `$objWriter->save($filepath)`. Vérifié par extraction manuelle du `.docx` généré après correctif : `word/media/section_image1.png` contient bien le graphe rasterisé (image non blanche, icônes + labels visibles).

**Test de régression** : le test `CartographyControllerTest` « includes graphs when the "with schemas" checkbox is checked » a été renforcé — au lieu de vérifier seulement la présence de `<w:pict` (qui passait à tort même avec le bug, l'icône de l'entité produisant elle aussi un `<w:pict`), il compte désormais le nombre de médias PNG réellement embarqués dans le zip (2 attendus : icône d'entité + graphe) et vérifie leur taille (> 1000 octets, pour exclure un PNG blanc quasi-vide). Vérifié manuellement que ce test échoue bien si le bug est réintroduit (`unlink` immédiat) et passe avec le correctif. Par hygiène, les 7 fonctions `render*SectionXml()` des tests de section autonomes (`tests/Feature/Report/*SectionTest.php`) appellent désormais aussi `$helper->cleanupTempFiles()` après la sauvegarde, pour éviter d'accumuler des PNG temporaires à chaque exécution de la suite (elles ne testaient pas ce comportement auparavant, mais fuyaient silencieusement des fichiers dans `/tmp` avec le nouveau code sans cet appel).

**Suite complète rejouée** : 1647 passed, 0 failed. `phpstan` (tout le projet) et `pint` clean sur tous les fichiers touchés.

### Étape 5 — Un graphe par élément de regroupement, par vue — ✅ terminée (2026-07-06)

**Demande utilisateur** : au lieu d'un graphe unique par vue, générer plusieurs graphes par vue — un par élément principal de regroupement — chacun placé directement sous le sous-titre de son propre élément (dans la boucle `foreach` de l'objet concerné, juste après `addBookmarkedTitle()`), pas en tête de section comme auparavant. Décisions par vue, données comme définitives par l'utilisateur (« toutes les ambiguïtés sont résolues ») :

| Vue | Élément de regroupement | Détail |
|---|---|---|
| 1 Écosystème | `Entity` | l'entité + son parent direct + ses enfants directs via `Entity.parent_entity_id` (hiérarchie propre, pas le modèle générique `Relation`) ; graphe omis si l'entité n'a ni parent ni enfant |
| 2 Système d'information | `MacroProcessus` | ses descendants Process→Activity→Operation→Task→Actor→Information ; graphe omis si le macro-processus n'a aucun process |
| 3 Applicatif | `ApplicationBlock` | applications/services/modules/bases du bloc ; **+1 graphe supplémentaire** en fin de section pour les objets sans bloc (« non affectés »), pour ne rien perdre de l'ancien graphe global |
| 4 Administration | — | **pas de split** — reste un graphe unique et global, inchangé |
| 5 Infrastructure logique | `Network` | si un seul `Network` est visible (`Cartographer::scopedQuery()`), bascule automatiquement sur un découpage par `Subnetwork` à la place |
| 6 Infrastructure physique | `Building` (hiérarchie) + `Site`/bâtiment racine (connectivité) | hiérarchie : un graphe par bâtiment, mais placé sous le sous-titre du **site** (pas du bâtiment) ; connectivité : un graphe site entier + un graphe par bâtiment racine (`building_id === null`) du site ; zones de sécurité (`SecurityZoneGraphBuilder`) : **inchangé**, un seul graphe global en tête de section |
| 7 GDPR | `DataProcessing` | un graphe par registre, réutilisant la structure existante `GdprGraphBuilder` filtrée à ce seul `DataProcessing` et ses liens directs (MacroProcessus/Process/Application) |

**Réalisé** :
- `EcosystemSection::addEntityFamilyGraph()`, `InformationSystemSection::addMacroProcessusGraph()`, `ApplicationSection::addApplicationGroupGraphs()`, `GdprSection::addDataProcessingGraph()` — filtrage en mémoire des collections déjà chargées (`->where()`/`->filter()`/`->contains()`), aucune requête SQL supplémentaire.
- `LogicalInfrastructureSection` : nouvelle classe `LogicalInfrastructureGraphContext` (DTO, 19 collections d'appareils) pour éviter un appel à ~19 paramètres ; `addFilteredNetworkGraph()` + `devicesInSubnetworks` remplacé en cours de route par un filtrage inline direct sur chaque collection concrète (voir point PHPStan ci-dessous) réutilisant `Subnetwork::contains(?string $ip): bool` déjà existante pour le matching CIDR.
- `PhysicalInfrastructureSection` : nouvelle classe `PhysicalInfrastructureGraphContext` (DTO, 12 collections) ; `addSiteLocationGraphs()` (un graphe isolé par bâtiment, via `buildLocationDot(..., buildingSelected: true)`) et `addSiteConnectivityGraphs()`/`addFilteredConnectivityGraph()`/`buildingSubtree()` (graphe site entier puis un par sous-arbre de bâtiment racine) — les deux appelés depuis la boucle `addSites()`, pas `addBuildings()`, conformément à la décision « placé sous le sous-titre du site ». Le graphe `SecurityZoneGraphBuilder` reste inséré une seule fois, sans changement, avant la boucle des sites.
- `AdministrationSection` : aucune modification (confirmé par `grep insertGraph`, un seul appel inchangé).
- Tous les builders de graphe existants (`EcosystemGraphBuilder`, `InformationSystemGraphBuilder`, `ApplicationGraphBuilder`, `LogicalInfrastructureGraphBuilder`, `PhysicalInfrastructureGraphBuilder`, `GdprGraphBuilder`) sont réutilisés tels quels, sans modification — ils filtrent déjà leurs arêtes via `->contains('id', ...)` sur les collections passées, donc leur passer des sous-ensembles suffit à obtenir un sous-graphe correct. Seule exception connue (déjà documentée à l'Étape 1) : `ApplicationFlowGraphBuilder` ne s'auto-filtre pas sur les collections passées — `ApplicationSection::addApplicationGroupGraphs()` calcule donc explicitement le sous-ensemble de flux à double extrémité dans le groupe avant de l'appeler.

**Point technique rencontré (PHPStan, générique de collection invariant)** : une première version de `LogicalInfrastructureSection::devicesInSubnetworks()` tentait de mutualiser le filtrage IP/CIDR pour les 13 types d'appareils dans une seule méthode privée générique (`@template TModel of Model`, paramètre/retour `Collection<int, TModel>`). PHPStan/Larastan a rejeté cette approche à chaque tentative (4 essais) : ni `Illuminate\Database\Eloquent\Collection<int, TModel>` ni `Illuminate\Support\Collection<int, TValue>` ne sont covariants dans la configuration Larastan de ce projet, donc passer une collection concrète (`Collection<int, DhcpServer>`) dans un paramètre `Collection<int, Model>` (ou vice-versa pour le retour) échoue systématiquement même quand `DhcpServer extends Model`. Résolu en abandonnant la méthode générique partagée : extraction d'un simple prédicat booléen `ipMatchesSubnetworks(?string $rawIps, Collection $subnetworks): bool` (aucun type générique de collection), appelé inline via `$ctx->dhcpServers->filter(fn (DhcpServer $d) => $this->ipMatchesSubnetworks($d->address_ip, $ownSubnetworks))` à chaque site d'appel — chaque `->filter()` s'exécute directement sur sa collection concrètement typée, sans jamais transiter par un type générique partagé. `phpstan analyse` (fichier puis projet entier) clean après ce changement.

**Tests** : les 7 fichiers `tests/Feature/Report/*SectionTest.php` ont été mis à jour pour compter le nombre réel de `word/media/*.png` embarqués dans le zip (pas seulement la présence de `<w:pict>`, qui donnait déjà un faux positif documenté à l'Étape post-3), avec une cardinalité calculée pour chaque scénario :
- Écosystème : 3 entités (parent+enfant+isolée) → 2 graphes (parent, enfant) + 1 icône (fallback dédupliqué par le registre statique `PhpOffice\PhpWord\Media`) = 3.
- Système d'information : 2 macro-processus (avec/sans process) → 1 graphe + 1 icône (process) = 2.
- Applicatif : 2 blocs + 1 application non affectée → 2 blocs (dont 1 avec un flux propre → 2 graphes) + 1 bloc simple (1 graphe) + 1 non-affecté (1 graphe) = 4 graphes + 2 icônes (application, base) = 6.
- Administration : test dédié confirmant qu'un scénario à 2 zones/3 forêts/1 domaine ne produit toujours qu'**un seul** média (pas de split).
- Infrastructure logique : les deux branches testées séparément — 2 réseaux visibles → 1 graphe par réseau (2) ; exactement 1 réseau visible avec 2 sous-réseaux → bascule et 1 graphe par sous-réseau (2).
- Infrastructure physique : 2 sites (dont 1 sans bâtiment) avec 2 bâtiments frères sur le premier → zone de sécurité (1, toujours) + hiérarchie (2, un par bâtiment) + connectivité (site entier + 2 bâtiments racine = 3, le 2ᵉ site sans bâtiment ne produisant rien) + 2 icônes (site, bâtiment) = 8.
- GDPR : 3 registres (2 avec application liée, 1 sans aucun lien) → 2 graphes (le 3ᵉ est omis), aucune icône dans cette vue = 2.

Toutes les cardinalités ci-dessus ont été vérifiées en exécutant réellement chaque test (pas de calcul spéculatif non vérifié) ; chacune est passée du premier coup après implémentation, confirmant la compréhension du filtrage des builders. `phpstan analyse` (tout le projet) et `pint --test` (tous les fichiers touchés) clean.

**Régression trouvée et corrigée pendant la suite complète** : `CartographyControllerTest` « includes graphs when the "with schemas" checkbox is checked... » créait une `Entity` isolée (ni parent ni enfant) pour isoler l'icône du graphe dans son assertion — un scénario valide avant le split, mais qui déclenche désormais le cas de saut de `EcosystemSection::addEntityFamilyGraph()` (aucun graphe n'est généré pour une entité sans parent ni enfant), faisant passer le nombre de médias attendu de 2 à 1. Corrigé en remplaçant l'entité isolée par une paire parent/enfant (chacune obtient son propre graphe de famille) et en ajustant l'assertion à 3 médias (1 icône dédupliquée + 2 graphes).

**Suite complète rejouée** : 1655 passed, 0 failed (en excluant les items marqués `todo`/`skipped`, non liés à ce travail). `phpstan analyse` (tout le projet) et `pint --test` clean.

### Étape 4 — Tests Pest finaux
- Génération complète (toutes vues) : statut 200, `.docx` non vide, `actingAs($this->admin)` avec `PermissionsTableSeeder`/`RolesTableSeeder`.
- Tests unitaires par section (déjà listés en étape 2, consolidés ici).
- Test "absence des objets exclus" (consolidé, toutes vues).
- Tests des builders de graphes avec et sans filtres (déjà listés en étape 1, consolidés ici).
- Test de génération partielle (`vues[] = ['3','7']` par ex.) vérifiant que les liens internes vers une vue non sélectionnée (ex. Application → Entity si vue 1 non sélectionnée) sont rendus en texte simple, pas en lien rompu (cf. §1.3.2).

### Questions ouvertes — toutes tranchées (2026-07-05)

Aucun point ouvert restant avant le démarrage de la Phase 2. Décisions actées :

1. **Périmètre de contenu (§0.1)** — confirmé : `show.blade.php` complet (partial + contenu additionnel hors partial), pas seulement `_details.blade.php`.
2. **Chart.js "historique de prix" de `Relation`** (§1.1 Vue 1) — confirmé : tableau simple (sous-tableau `RelationValue` Date/Valeur), sans graphique.
3. **Granularité du graphe "Système d'information"** — confirmé supprimée (graphe toujours complet), cf. §1.2.
4. **`SecurityControl`** — confirmé : ne pas afficher ses relations inverses (`Application`/`Process`), rester fidèle à `show.blade.php` actuel (name/description uniquement).
5. **Bugs listés en §0.2** — items 1–8, 12, 13 corrigés directement dans le code le 2026-07-05 (cf. §0.2, tests complets rejoués : 1587 passed). Items 9–11 : décision utilisateur du 2026-07-05, laissés pour la Phase 2 (pas de patch de secours de l'ancien `CartographyController`).
6. **Cellules fusionnées** (`Cluster.logical_servers+Routers`, `Bay` 6-relations-en-une-cellule) — confirmé : séparer en lignes distinctes dans le nouveau rapport (une ligne par relation).

---

## 2. Modèle de document Word personnalisable (2026-07-07)

**Demande utilisateur** : permettre à l'administration d'uploader un modèle `.docx` personnalisé (page de garde, en-têtes, marges, styles Heading1/2/3 libres) dans lequel le corps du rapport de cartographie généré par `ReportBuilder` est fusionné, à l'emplacement d'un tag `:content:`. Modèle par défaut si aucun n'est uploadé ; dernier modèle uploadé utilisé globalement par tous les utilisateurs.

### 2.0 Inspection préalable

Avant toute implémentation, lecture complète de `ReportBuilder.php`, `WordHelper::addCoverPageAndToc()`, `CartographyController.php`, `routes/web.php`, `resources/views/doc/report.blade.php`, `app/Models/Parameter.php`, `app/Support/MercatorSettings.php`, et génération d'un rapport réel suivi d'une inspection manuelle (`unzip`) de son `word/document.xml`/`_rels`/`styles.xml`/`numbering.xml`/`settings.xml`/`docProps/core.xml`, pour fonder la conception sur l'état réel du zip OOXML plutôt que sur des hypothèses. Deux écarts trouvés entre la demande initiale et le code réel :

- **Pas de section paysage** : la demande évoquait la préservation d'un `sectPr` intermédiaire "paysage pour les graphes" — en réalité `ReportBuilder` n'a jamais qu'**une seule** `Section` PhpWord (portrait), les vues étant séparées par de simples `addPageBreak()`. Un seul `<w:sectPr>` existe dans le document généré, toujours en dernier enfant de `<w:body>`. Conséquence : l'étape "extraire le corps sans le `sectPr` final, en conservant les `sectPr` intermédiaires" de l'algorithme de fusion se réduit à une règle triviale (retirer le dernier enfant de `<w:body>` s'il s'agit d'un `w:sectPr` direct — les éventuels sauts de section intermédiaires, représentés par un `w:sectPr` **imbriqué dans le `w:pPr` d'un paragraphe**, ne sont de toute façon jamais touchés par cette règle).
- **Images en VML, pas en DrawingML** : confirmé par inspection du zip réel (PhpWord 1.4.0), `WordHelper::insertGraph()`/icônes d'objet produisent `<w:pict><v:shape>...<v:imagedata r:id="rIdX">` (VML historique), **pas** `<w:drawing><wp:inline>...<a:blip r:embed="rIdX">` (DrawingML moderne). `TemplateMergerService` cible donc `v:imagedata`/`r:id`, pas `r:embed` — un changement de writer PhpWord qui basculerait vers DrawingML nécessiterait une mise à jour de ce mécanisme.

### 2.1 `TemplateValidatorService` — ✅ terminée

Détection du tag `:content:` (et validation docx/taille) par concaténation du texte de chaque `<w:p>` (tous ses `<w:t>` descendants, dans l'ordre du document) plutôt qu'en portant `TemplateProcessor::fixBrokenMacros()` (regex sur le XML brut, technique de PhpWord pour son propre usage). Cette approche gère nativement un tag fragmenté par Word sur plusieurs `<w:r>` (ex. après une correction orthographique) sans passe de « normalisation des runs » séparée — la concaténation par paragraphe **est** la normalisation. `App\Services\Report\TemplateValidatorService::validate(string $path): array` retourne un tableau de clés de traduction (`report_template.errors.*`), vide si valide.

**Tests** : `tests/Unit/Services/Report/TemplateValidatorServiceTest.php` (8 tests) avec des fixtures `.docx` dédiées sous `tests/fixtures/templates/` (générées par un script PhpWord jetable, committées comme fichiers binaires) : tag présent une fois, tag fragmenté sur 2 runs, tag absent, tag en double, fichier non-docx, XML mal formé, fichier inexistant, fichier trop volumineux.

### 2.2 `TemplateMergerService` — ✅ terminée

Fusion strictement au niveau ZIP/OOXML (`ZipArchive` + `DOMDocument`, jamais `IOFactory::load()` ni `TemplateProcessor::setComplexBlock()`, qui repasseraient par le modèle objet PhpWord et perdraient toute mise en forme du modèle qu'il ne comprend pas). Algorithme (`merge(string $templatePath, string $bodyReportPath, string $outputPath): void`) :

1. Copie du modèle vers le fichier de sortie, ouverture en écriture (`ZipArchive::open`, pas de flag `CREATE` puisque le fichier existe déjà).
2. Localisation du paragraphe `:content:` dans le modèle (même détection par concaténation qu'en §2.1).
3. Extraction des enfants de `<w:body>` du rapport généré, à l'exclusion de son `<w:sectPr>` final (règle triviale, cf. §2.0).
4. Renumérotation des `w:id` numériques de `w:bookmarkStart`/`w:bookmarkEnd` du fragment, à partir du plus grand id déjà présent dans le modèle + 1 (les **noms** — `getUID()` type `ENTITY_1`, ou les `_TocN` générés par PhpWord — restent inchangés, conformément à la consigne). Note : si le modèle personnalisé a lui aussi son propre titre de couverture avec `addTitle(..., 0)`, PhpWord numérote ses propres `_TocN` indépendamment à partir de 0 — une collision de **nom** (pas d'id) entre le `_Toc0` du modèle et celui du corps fusionné est possible, mais sans conséquence pratique : `<w:updateFields w:val="true"/>` (étape 7) force Word à reconstruire l'intégralité de la table des matières (et ses cibles de navigation) au premier "Mettre à jour les champs", auto-corrigeant toute ambiguïté de nommage.
5. Copie des fichiers `word/media/*` réellement référencés par le fragment (recherche des `<v:imagedata r:id="...">` survivants, pas une copie aveugle de tout `word/media/` du rapport — les images de l'éventuelle propre page de garde du modèle ne sont jamais concernées), renommage en cas de collision de nom de fichier, nouveaux `rId` attribués après le plus grand `rId` existant du modèle, mise à jour de `word/_rels/document.xml.rels` et, si besoin, ajout d'un `<Default Extension="png">` dans `[Content_Types].xml`.
6. Fusion ciblée de `word/styles.xml`/`word/numbering.xml` : seuls les styles du rapport **absents** du modèle (par `styleId`) sont copiés — en cas de conflit, le style du modèle est prioritaire et celui du rapport est simplement ignoré. Seules les définitions `w:numId`/`w:abstractNum` réellement utilisées par ces styles copiés (ou directement par le corps du fragment) sont importées et renumérotées ; un style non copié (déjà présent dans le modèle) n'entraîne l'import d'aucune définition de numérotation associée, pour ne pas polluer `numbering.xml` avec des définitions orphelines.
7. Ajout de `<w:updateFields w:val="true"/>` dans `word/settings.xml` du modèle (élément créé s'il est absent, sinon son `w:val` est simplement basculé à `true`).
8. Mise à jour de `docProps/core.xml` : `dcterms:modified` est **toujours** réécrit à l'heure de la fusion ; `dcterms:created` n'est réécrit que s'il est absent/vide, sinon préservé tel quel (date de création d'origine du modèle).
9. Import des nœuds du fragment dans le DOM du modèle (`DOMDocument::importNode(..., true)`, en s'assurant au préalable que les espaces de noms `w:`/`r:`/`v:`/`o:`/`w10:` sont bien déclarés sur la racine du modèle), insertion juste avant le paragraphe `:content:`, puis suppression de ce paragraphe.

**Choix de champ Word pour la date de génération** : la demande initiale mentionnait `CREATEDATE`, mais ce champ Word est lié à `dcterms:created` — qui reste **figé à la date de fabrication du modèle** dans ce pipeline (préservé, jamais réécrit, cf. étape 8). Un champ `CREATEDATE` sur la page de garde du modèle par défaut aurait donc affiché la même date sur tous les rapports générés, quelle que soit la date réelle de génération. Le modèle par défaut (§2.3) utilise donc un champ **`SAVEDATE`**, lié à `dcterms:modified` — que la fusion réécrit systématiquement à l'heure de génération — pour obtenir le comportement attendu (« date de génération du rapport », comme l'ancien texte codé en dur `Carbon::now()->format(...)`). `PhpOffice\PhpWord\Element\Field` ne liste ni `CREATEDATE` ni `SAVEDATE` dans son type whitelist (seul `DATE`, qui se recalcule à l'heure d'ouverture — pas adapté, on veut une date figée) ; le champ complexe (`fldChar begin/instrText/separate/texte en cache/end`) a donc été écrit à la main lors de la génération du modèle par défaut, en s'inspirant de la structure déjà produite par PhpWord pour ses propres champs `PAGE`/`NUMPAGES`/`PAGEREF`.

**Tests** : `tests/Unit/Services/Report/TemplateMergerServiceTest.php` (6 tests) sur des fixtures `merge-template.docx`/`merge-body.docx` construites pour exercer chaque mécanisme : conflit de style `Heading1` (le modèle gagne), style `Heading2` absent du modèle (copié avec sa numérotation renumérotée), bookmark déjà présent dans le modèle (`id=5`) forçant le renumérotage du fragment à partir de 6, media référencé par le corps copié et son `r:id` correctement réécrit dans le document final, `updateFields`/dates `docProps`, et rejet (avec suppression du fichier de sortie partiellement écrit) si le modèle ne contient pas `:content:`.

### 2.3 Modèle par défaut + bascule du pipeline — ✅ terminée

`resources/reports/default-template.docx` généré une fois via un script PhpWord jetable (non committé) puis retouché à la main (édition directe de `word/document.xml`/`docProps/core.xml` dans le zip) pour deux raisons : (a) remplacer le texte "date de génération" par le champ `SAVEDATE` (cf. §2.2, hors de portée de l'API haut niveau de PhpWord) ; (b) corriger un défaut du writer TOC de PhpWord — `Writer\Word2007\Element\TOC::write()` n'écrit le `fldChar begin`/`instrText`/`separate` que s'il existe au moins un titre déjà présent au moment de la génération (`$element->getTitles()` non vide) ; un modèle autoportant sans aucun titre Heading1/2 au moment de sa fabrication se retrouvait donc avec un champ TOC **non balancé** (un simple `fldChar end` sans `begin`). Corrigé en réécrivant à la main un champ TOC complet et valide (`TOC \o "1-2" \h \z \u`, avec un texte de repli « clic droit → Mettre à jour les champs » avant la première mise à jour). Reproduit fidèlement la mise en page actuelle : titre de couverture centré, `SAVEDATE`, TOC native profondeur 1–2, saut de page, tag `:content:`, marges standard (1 pouce, valeur par défaut PhpWord).

**Décision utilisateur** : la ligne "Version Mercator : X.Y.Z" de l'ancienne page de garde codée en dur (`Carbon::now()`/`app('mercator.version')`) est **supprimée**, pas reportée ailleurs — un modèle Word statique ne peut pas afficher dynamiquement le numéro de version de l'application (pas de champ Word natif pour ça, et la demande limite volontairement le mécanisme de fusion au seul tag `:content:`). La date de génération survit via le champ `SAVEDATE` (cf. §2.2) ; la version n'apparaît plus nulle part sur le rapport.

`ReportBuilder::build()` ne construit plus de page de garde/TOC lui-même (suppression de l'appel à `WordHelper::addCoverPageAndToc()`, méthode devenue morte et supprimée) : le document PhpWord produit ne contient plus que le corps des vues sélectionnées, sauvegardé dans un fichier temporaire (`storage/app/reports/body-{uniqid}.docx`), puis fusionné via `TemplateMergerService::merge()` dans le fichier final retourné à l'appelant ; le fichier corps intermédiaire est supprimé (`finally`) après la fusion, qu'elle réussisse ou échoue. `WordHelper::newDocument()` (styles Heading1-3 + numérotation `hNum`, nécessaires au contenu des vues) est inchangé.

**Vérification visuelle** : conversion du modèle par défaut seul, puis d'une fusion réelle (modèle par défaut + corps généré via une fixture PhpWord), en PDF via LibreOffice headless (`soffice --headless --convert-to pdf`) — aucun avertissement de corruption dans les deux cas, page de garde/TOC/en-tête/pied de page/numérotation de titre (`1. Body Subheading`)/image tous rendus correctement.

**Tests** : suite complète (dont tous les tests `CartographyControllerTest` existants, qui passent sans modification — la fusion est transparente pour eux) plus un nouveau test vérifiant que le corps réel (`Template Merge Entity`) apparaît dans le document final et que `:content:` en a disparu. 1685 passed après cette étape.

### 2.4 Routes + contrôleur + `Parameter` + tests HTTP — ✅ terminée

`App\Support\ReportTemplateSettings` (nouveau, mirroir du pattern déjà établi par `MercatorSettings` : métadonnées structurées — nom d'origine + date d'upload — encodées en JSON dans une seule clé `Parameter` nommée `report_template`) expose `currentTemplatePath()` (modèle uploadé si sa métadonnée existe **et** le fichier est bien présent sur disque, sinon le modèle par défaut), `storagePath()` (`storage/app/report-templates/template.docx`, écrasé à chaque upload — un seul modèle global), `load()`/`save()`. `ReportBuilder::build()` appelle désormais `ReportTemplateSettings::currentTemplatePath()` au lieu du chemin du modèle par défaut codé en dur.

Deux routes ajoutées dans `routes/web.php` (groupe `admin.`, à la suite de `report.cartography`) :
- `GET report/cartography/template/default` → `CartographyController::downloadDefaultTemplate()`, gate `reports_access` (même gate que la génération du rapport elle-même, conformément à la demande « accessible à tout utilisateur autorisé à générer le rapport »).
- `POST report/cartography/template` → `CartographyController::uploadTemplate()`, gate `configure` (même gate que l'ensemble de l'écran `/admin/config/parameters`, retenu car c'est le seul gate existant couvrant déjà une action « affecte tous les utilisateurs globalement », et cet écran utilise déjà des tags `:variable:` de la même famille que `:content:`). Les deux méthodes utilisent `abort_if(Gate::denies(...))`, jamais `$this->authorize()`, conformément aux contraintes du projet.

`uploadTemplate()` : validation Laravel de base (`file`, `mimes:docx`, `max:10240` = 10 Mo, cohérent avec `TemplateValidatorService::MAX_SIZE_BYTES`), puis validation de contenu via `TemplateValidatorService` — rejet (`back()->withErrors(['template' => trans($errors[0])])`) sans toucher au modèle actif si `:content:` est absent/en double ou le fichier corrompu ; sinon déplacement vers `ReportTemplateSettings::storagePath()` et mise à jour du `Parameter`.

**Piège rencontré en testant l'upload** : `Symfony\Component\HttpFoundation\File\UploadedFile::move()`, quand l'objet est construit en **mode test** (5ᵉ argument du constructeur `$test = true`, nécessaire pour simuler un upload HTTP en test Pest), délègue à `File::move()` — un vrai `rename()` du fichier source, pas une copie. Une première version des tests construisait `new UploadedFile(base_path('tests/fixtures/templates/valid-template.docx'), ...)` **directement** sur la fixture committée : le premier test qui atteignait réellement `$file->move(...)` faisait disparaître silencieusement la fixture du dépôt (déplacée vers `storage/app/report-templates/`), cassant tous les tests suivants qui en dépendaient — y compris lors d'exécutions **ultérieures** de la suite complète, jusqu'à régénération manuelle du fichier. Corrigé en copiant systématiquement la fixture vers un fichier temporaire jetable avant de construire l'`UploadedFile` de test (`uploadableTemplateFixture()`, helper ajouté à `CartographyControllerTest.php`).

**Tests** : 5 nouveaux tests dans `CartographyControllerTest.php` (téléchargement du modèle par défaut avec/sans gate, upload refusé sans gate `configure`, upload valide → `Parameter` mis à jour + génération suivante utilisant bien le nouveau modèle, upload sans `:content:` → erreur + modèle actif inchangé). 1690 passed après cette étape (suite complète rejouée deux fois pour confirmer que la fixture survit désormais à une exécution complète).

### 2.5 Écran Blade + traductions — ✅ terminée

Nouvel encart « Modèle de document » (`resources/lang/{en,fr}/report_template.php`, nouveau fichier de traduction dédié plutôt qu'un sous-tableau de `cruds.php`, cohérent avec les autres fichiers de traduction déjà présents à la racine — `auth.php`, `doc.php`, `global.php`, etc. — et nécessaire puisque `TemplateValidatorService` retourne des clés `report_template.errors.*`, donc un fichier `report_template.php` à la racine, pas `cruds.report_template.*`) inséré dans `resources/views/doc/report.blade.php`, entre le formulaire de génération existant et les listes de rapports : lien de téléchargement du modèle par défaut (`target="_new"`, cohérent avec tous les autres liens de rapport de cette page), affichage du nom + date du modèle personnalisé actif (ou message « utilisation du modèle par défaut »), champ d'upload (`accept=".docx"`) + bouton, visible uniquement derrière `@can('configure')`. Aucun JavaScript ajouté (formulaire HTML natif suffisant ; la contrainte « IIFE compatible CSP, pas de handler inline » du besoin initial ne s'applique que s'il y a effectivement du JS à écrire). La route `doc/report` (fermeture anonyme dans `routes/web.php`, jamais un contrôleur dédié) passe désormais `reportTemplate = ReportTemplateSettings::load()` à la vue.

**Vérification visuelle** : rendu de l'écran via le client de test Pest (HTML garanti identique à ce que sert la vraie route), extrait dans `public/` pour être servi par un `php artisan serve` isolé (base de test MySQL `mercator_test` dédiée, distincte de la base de développement réelle — jamais touchée), capture d'écran Chrome headless dans les deux états (modèle par défaut / modèle personnalisé actif avec nom+date affichés) : rendu cohérent avec le reste de la page (mêmes classes Bootstrap `card`/`form-group`), aucune anomalie de mise en page.

**Tests** : `tests/Feature/Controller/ReportScreenTest.php` (3 tests : message "modèle par défaut" + champ d'upload visible pour un utilisateur `configure`, nom/date du modèle personnalisé affichés, champ d'upload invisible pour un utilisateur sans `configure`). 1693 passed après cette étape.

### Bilan

Fonctionnalité complète en 5 étapes (2.1 à 2.5), chacune testée et la suite complète rejouée avant de passer à la suivante. Aucune régression sur les 1647 tests préexistants avant le début de ce chantier. Fichiers ajoutés : `app/Services/Report/TemplateValidatorService.php`, `app/Services/Report/TemplateMergerService.php`, `app/Support/ReportTemplateSettings.php`, `resources/reports/default-template.docx`, `resources/lang/{en,fr}/report_template.php`, `tests/Unit/Services/Report/{TemplateValidatorServiceTest,TemplateMergerServiceTest}.php`, `tests/Feature/Controller/ReportScreenTest.php`, `tests/fixtures/templates/*.docx`. Fichiers modifiés : `ReportBuilder.php` (bascule sur la fusion systématique), `WordHelper.php` (suppression de `addCoverPageAndToc()`, devenue morte), `CartographyController.php` (2 nouvelles actions), `routes/web.php` (2 nouvelles routes), `resources/views/doc/report.blade.php` (encart « Modèle de document »), `CartographyControllerTest.php` (tests de fusion + template).

### 2.6 Correctif post-livraison (2026-07-07) — numérotation des chapitres disparue

**Signalé par l'utilisateur** : « Dans le rapport de cartographie généré, il n'y a plus de numérotation des chapitres qui est imposé dans `addNumberingStyle`. »

**Cause** : `TemplateMergerService::mergeNumbering()` calcule le prochain `w:numId` disponible en partant de `0` puis en prenant le maximum des `w:numId` déjà présents dans `numbering.xml` du modèle, `+1`. Quand le modèle (le modèle par défaut livré, ou tout modèle personnalisé qui n'a lui-même aucune liste numérotée) ne contient **aucun** `<w:num>` existant, cette boucle ne s'exécute jamais et le premier id alloué reste `0`. Or `w:numId="0"` est une valeur **réservée** par OOXML pour signifier « pas de numérotation » — Word l'ignore silencieusement, quelle que soit la définition `w:abstractNum`/`w:num` par ailleurs correcte et bien reliée. Confirmé en extrayant un rapport réel généré après l'implémentation de §2.1–2.5 : `numbering.xml` contenait bien `<w:num w:numId="0"><w:abstractNumId w:val="0"/></w:num>` et les styles `Heading1`/`Heading2`/`Heading3` référençaient bien ce `numId="0"` — définition techniquement complète, mais inopérante à cause de ce seul id.

**Corrigé** : l'allocateur de `numId` part désormais de `1` (`$nextNumId = 1`, au lieu de `0`) ; l'allocateur de `abstractNumId`, lui, reste à `0` (aucune valeur n'y est réservée par la spécification). Vérifié par génération réelle + conversion PDF LibreOffice headless : « 1. Vue de l'écosystème », « 1.1. Entités », « 1.1.1. Numbering Debug Entity », « 2. Système d'information » tous correctement numérotés après correctif (rien avant).

**Test de régression** : le test déjà existant `TemplateMergerServiceTest` (« merge keeps the template's own style on a styleId conflict... ») a été renforcé d'une assertion explicite `expect($numIdMatch[1])->not->toBe('0')` — vérifié qu'il échoue bien sur le code d'avant correctif (`$nextNumId = 0`) et passe avec le correctif. La fixture `merge-template.docx` utilisée par ce test avait déjà un `numbering.xml` vide dès sa création (§2.2), c'est-à-dire qu'elle reproduisait déjà exactement les conditions du bug — l'assertion manquante, pas la fixture, était l'angle mort.

**Découverte concomitante (sans lien avec le bug ci-dessus)** : `resources/reports/default-template.docx` a été remplacé, en dehors de ce chantier, par un modèle par défaut plus riche (page de garde française « Rapport de Cartographie du Système d'Information », image de couverture intégrée, TOC via un bloc de contenu structuré `w:sdt`/`docPartGallery`) — le script PhpWord jetable initial (§2.3) n'est donc plus fidèle au fichier réellement livré. Deux tests `CartographyControllerTest` supposaient à tort un contenu figé de ce fichier (texte de couverture « Information System Mapping Report », nombre exact de médias embarqués) et ont été corrigés pour rester valides quel que soit le modèle par défaut réellement en place : la vérification de fusion contrôle désormais la survie du `<w:sectPr>` du modèle (`headerReference`/`footerReference`) plutôt qu'un texte de couverture précis, et le test de comptage d'images calcule dynamiquement le nombre de PNG déjà présents dans le modèle par défaut actif (`countPngMedia()`, nouvelle fonction utilitaire du fichier de test) avant d'ajouter le delta attendu, au lieu d'une constante en dur.

**Suite complète rejouée** : 1693 passed, 0 failed. `phpstan analyse` (tout le projet) et `pint --test` clean sur tous les fichiers touchés.

### 2.7 Correctif post-livraison, suite (2026-07-07) — numérotation toujours absente

**Signalé par l'utilisateur** (après §2.6) : « Il n'y a toujours pas de numérotation dans les chapitres du rapport. »

**Cause, plus profonde que §2.6** : le modèle par défaut réellement livré (§2.6, découverte concomitante — remplacé par un modèle riche généré/édité dans **LibreOffice**, pas Word ni PhpWord) ne contient **aucune partie `word/numbering.xml` du tout**, ni de relation `.../numbering` dans `word/_rels/document.xml.rels`, ni d'`Override` correspondant dans `[Content_Types].xml` — LibreOffice omet entièrement cette partie quand le document lui-même n'utilise aucune liste numérotée, plutôt que d'en écrire une vide comme le fait PhpWord. Or `TemplateMergerService::mergeNumbering()` avait une garde d'entrée `if (! zipHasEntry($report, 'numbering.xml') || ! zipHasEntry($output, 'numbering.xml')) return [];` — une simplification volontaire documentée dans le code (« templates réels ont quasi toujours ce fichier ») qui s'avère être exactement le cas réel rencontré. Conséquence : la fusion de numérotation était intégralement sautée, mais le style `Heading1`/`Heading2`/`Heading3` copié depuis le corps du rapport gardait son `<w:numPr><w:numId w:val="1"/></w:numPr>` **tel quel** (jamais remappé, puisque `$numIdMap` restait vide) — référence pendante vers une définition de numérotation absente de tout le paquet OOXML. Confirmé en extrayant un rapport fusionné avec le vrai modèle par défaut : styles.xml référence bien `numId="1"`, mais `word/numbering.xml` n'existe pas dans l'archive.

**Corrigé** : `mergeNumbering()` ne saute plus la fusion quand seul le **modèle** manque de `numbering.xml` (il continue de sauter si c'est le **rapport** qui en manque — rien à importer dans ce cas) ; une nouvelle méthode `createNumberingPart()` construit alors un `<w:numbering>` vide et l'enregistre correctement dans le paquet (relation `word/_rels/document.xml.rels` + `Override` dans `[Content_Types].xml`) avant d'y importer les définitions nécessaires, exactement comme si le modèle en avait déjà eu un.

**Piège technique rencontré et résolu pendant ce correctif** : `ZipArchive::getFromName()` ne reflète **jamais** un `addFromString()` déjà effectué dans la même session tant que l'archive n'a pas été fermée (vérifié empiriquement par un test isolé : lire juste après avoir écrit renvoie une chaîne vide, pas le contenu venant d'être écrit). Or `mergeMedia()` et `mergeStylesAndNumbering()`/`mergeNumbering()` lisaient et écrivaient chacun **indépendamment** `word/_rels/document.xml.rels` et `[Content_Types].xml` — avec l'ajout de `createNumberingPart()` (qui doit, lui aussi, modifier ces deux mêmes parties), un second cycle lecture-modification-écriture sur la même partie aurait silencieusement écrasé les modifications du premier (par exemple, la relation image ajoutée par `mergeMedia()` aurait disparu si `createNumberingPart()` était appelé ensuite). Corrigé en restructurant `performMerge()` pour charger `word/_rels/document.xml.rels` et `[Content_Types].xml` **une seule fois**, passer ces deux `DOMDocument` en paramètre à `mergeMedia()` et `mergeStylesAndNumbering()` (qui les mutent en mémoire sans jamais les réécrire eux-mêmes), et les écrire **une seule fois**, à la fin. `ensureContentTypeDefaults()` et le corps de `mergeMedia()` ont perdu leur propre appel `ZipArchive::addFromString` sur ces deux parties en conséquence.

**Vérification** : génération réelle avec le vrai modèle par défaut (logo Mercator, page de garde française) + conversion PDF LibreOffice headless — aucun avertissement de corruption, page de garde/logo/`SAVEDATE` intacts, « 1. Vue de l'écosystème » / « 1.1. Entités » / « 1.1.1. ... » correctement numérotés.

**Test de régression** : nouvelle fixture `tests/fixtures/templates/merge-template-no-numbering.docx` (copie de `merge-template.docx` dont `word/numbering.xml`, sa relation et son `Override` ont été retirés par script, reproduisant fidèlement l'état du vrai modèle par défaut) et nouveau test `TemplateMergerServiceTest` (« merge creates and registers word/numbering.xml when the template has no numbering part at all ») — vérifié qu'il échoue bien sur le code d'avant ce correctif (`Expecting false not to be false`, `numbering.xml` absent de l'archive de sortie) et passe avec le correctif.

**Suite complète rejouée** : 1694 passed, 0 failed. `phpstan analyse` (tout le projet) et `pint --test` clean sur tous les fichiers touchés.

### 2.8 Nouvelles balises `:timestamp:` et `:version:` (2026-07-07)

**Demande utilisateur** : « Dans le rapport de cartographie, ajoute le champ `:timestamp:` qui est remplacé par la date et l'heure de génération du rapport ainsi que le champ `:version:` qui est remplacé par le numéro de version de Mercator. »

**Nature différente de `:content:`** : contrairement à `:content:` (obligatoire, exactement une fois, remplace le **paragraphe entier** qui le contient par le corps du rapport), `:timestamp:` et `:version:` sont des balises **optionnelles, librement répétables, n'importe où dans le modèle** (page de garde, en-tête, pied de page) — substitution de **texte** in situ (le reste du paragraphe est préservé), pas remplacement de paragraphe. Elles ne sont donc pas ajoutées à `TemplateValidatorService` (rien à valider : absentes, présentes une fois ou plusieurs fois sont toutes des utilisations légitimes).

**Implémentation** (`TemplateMergerService::substituteTemplateTags()`, nouvelle méthode, appelée dans `performMerge()` juste après les déclarations d'espaces de noms, **avant** l'insertion du corps du rapport — de sorte qu'elle ne scanne jamais le contenu du corps, qui ne contient jamais ces balises) :
- `:timestamp:` → `Carbon::now()->format('d/m/Y H:i')` (même format que l'ancien texte de page de garde codé en dur avant la réécriture §2, et que le champ `SAVEDATE` du modèle par défaut — cohérent). `updateCoreProperties()` (qui écrit `dcterms:modified`) reçoit désormais ce même `Carbon::now()` en paramètre plutôt que d'appeler une seconde fois `Carbon::now()` en interne, pour garantir que `:timestamp:` et `dcterms:modified`/le champ `SAVEDATE` affichent exactement la même date à la milliseconde près.
- `:version:` → `app('mercator.version')` (même source que l'ancien texte de page de garde).
- Recherche dans `word/document.xml` (déjà chargé) **et** dans chaque partie `word/header*.xml`/`word/footer*.xml` du paquet (parties séparées, chacune avec ses propres paragraphes `<w:p>`, non atteignables depuis le document principal) — trouvées par balayage du répertoire central du zip (`^word/(header|footer)\d*\.xml$`), chargées/modifiées/réécrites individuellement.
- Détection et remplacement robustes à une balise fragmentée par Word sur plusieurs `<w:r>` : pour chaque paragraphe, concaténation de tous les `<w:t>` descendants (même principe que la détection de `:content:`), recherche de la balise dans le texte concaténé, puis découpage du texte de **chaque nœud recouvert** par la position de la balise (texte avant conservé dans le premier nœud touché, texte après conservé dans le dernier, nœuds intermédiaires vidés), le texte de remplacement étant inséré une seule fois, dans le premier nœud touché — le reste du paragraphe (texte avant/après la balise, formatage de chaque run) est préservé intact, contrairement au remplacement de paragraphe entier utilisé pour `:content:`. Répété en boucle par balise/paragraphe pour couvrir plusieurs occurrences dans un même paragraphe (ex. balise présente deux fois dans un même pied de page).

**Vérification** : nouvelle fixture `tests/fixtures/templates/merge-template-with-tags.docx` (en-tête « Version :version: », pied de page « Generated: :timestamp: / :timestamp: » — deux occurrences dans le même paragraphe —, corps avec `:timestamp:` fragmenté sur deux runs) ; fusion réelle avec le vrai modèle par défaut (logo Mercator) suivie d'une conversion PDF LibreOffice headless : en-tête « Version 2026.06.28 », pied de page « Generated: 07/07/2026 19:24 / 07/07/2026 19:24 » (les deux occurrences remplacées), corps « Body 07/07/2026 19:24 value » (balise fragmentée correctement reconstituée, texte environnant intact) — aucune anomalie.

**Écran Blade** : ligne d'aide ajoutée sous le champ d'update (`report_template.tags_helper`, nouvelles clés `en`/`fr`) énumérant les 3 balises prises en charge, puisque rien ne les documentait auparavant dans l'interface (y compris `:content:`, déjà existante).

**Test de régression** : 2 nouveaux tests `TemplateMergerServiceTest` (substitution dans le corps/en-tête/pied de page avec occurrence fragmentée et répétée, via `Carbon::setTestNow()` pour figer l'heure ; non-régression — un modèle qui n'utilise pas ces balises n'en fait jamais apparaître). Suite complète rejouée : 1696 passed, 0 failed. `phpstan analyse` (tout le projet) et `pint --test` clean sur tous les fichiers touchés.

### 2.9 Lien de téléchargement du modèle actif (2026-07-07)

**Demande utilisateur** : « Dans la page `resources/views/doc/report.blade.php`, ajoute un lien sur le nom du modèle de document qui permet de télécharger le document. »

**Réalisé** : nouvelle route `GET report/cartography/template/current` → `CartographyController::downloadCurrentTemplate()` (gate `reports_access`, identique au téléchargement du modèle par défaut ; 404 si aucun modèle personnalisé n'est actif ou si le fichier a disparu du disque malgré une métadonnée `Parameter` présente) qui télécharge `ReportTemplateSettings::storagePath()` sous le nom d'origine (`original_name`, celui affiché à l'écran). Le nom du modèle actif dans `report.blade.php` est désormais un lien (`target="_new"`, cohérent avec tous les autres liens de la page) vers cette route, au lieu d'un texte simple.

**Tests** : 3 nouveaux tests `CartographyControllerTest` (404 sans modèle actif, téléchargement effectif sous le nom d'origine — vérifié via l'en-tête `Content-Disposition` —, refus sans le gate `reports_access`). Vérification visuelle (capture d'écran Chrome headless, écran servi par un `php artisan serve` isolé comme en §2.5) : le nom du modèle (« mon-modele-anssi.docx ») s'affiche bien comme un lien cliquable. Suite complète rejouée : 1699 passed, 0 failed. `phpstan analyse` (tout le projet) et `pint --test` clean sur tous les fichiers touchés.

### 2.10 Incident post-livraison (2026-07-07) — la suite de tests supprimait le modèle réel de l'utilisateur

**Signalé par l'utilisateur** : « `http://127.0.0.1:8000/admin/report/cartography/template/current` renvoie une erreur 404 » alors que le modèle personnalisé s'affichait bien comme actif sur l'écran et que la génération du rapport l'utilisait toujours correctement.

**Diagnostic** : le routage fonctionnait (`curl` confirme un `302` — redirection non authentifiée — cohérent sur `.../template/default` et `.../template/current`, et un `405` sur la route d'upload attaquée en `GET` — preuve que Laravel route bien ces chemins). Le seul point de divergence possible étant l'`abort_if` explicite de `downloadCurrentTemplate()`, inspection directe du disque et de la base réelle (`php artisan tinker`) : la ligne `Parameter` (`report_template`) portait toujours `original_name: "default-template.docx"`, mais `storage/app/report-templates/` était **vide** — `is_file()` renvoyait `false` alors que la métadonnée existait.

**Cause réelle : un effet de bord de ma propre suite de tests sur le vrai disque de l'utilisateur.** `ReportTemplateSettings::storagePath()` renvoie un chemin **filesystem réel** (`storage_path(...)`), qui n'est **pas isolé par environnement** contrairement à la connexion base de données (`RefreshDatabase` + base de test dédiée) — `storage_path()` pointe vers le **même répertoire physique**, que le processus courant soit un test ou l'application réelle. `CartographyControllerTest.php` et `ReportScreenTest.php` avaient chacun un `afterEach(() => @unlink(ReportTemplateSettings::storagePath()))` **inconditionnel** — à chaque exécution de la suite complète (rejouée plusieurs fois pendant ce chantier, §2.6 à §2.9), ce nettoyage supprimait silencieusement tout fichier réellement présent à cet emplacement, y compris un modèle que l'utilisateur avait lui-même uploadé via l'écran réel entre deux exécutions. Le `original_name` retrouvé (« default-template.docx ») correspond exactement au nom de fichier servi par `downloadDefaultTemplate()`, cohérent avec un test manuel de l'utilisateur (téléchargement du modèle par défaut suivi d'un ré-upload pour essayer le formulaire).

**Restauré** : copie de `resources/reports/default-template.docx` vers `storage/app/report-templates/template.docx` (contenu identique à ce que l'utilisateur avait très probablement uploadé, d'après le nom enregistré) — confirmé par `ReportTemplateSettings::currentTemplatePath()` retournant de nouveau le chemin personnalisé.

**Corrigé, dans les deux fichiers de test concernés** :
- `ReportScreenTest.php` : le `afterEach` supprimait un fichier que ce fichier de test **n'écrit jamais** (`ReportTemplateSettings::save()` n'écrit que la métadonnée en base) — nettoyage retiré entièrement, remplacé par un commentaire expliquant pourquoi il n'y en a pas besoin.
- `CartographyControllerTest.php` (describe `cartography report template`, qui lui écrit réellement ce fichier via le flux d'upload testé) : `beforeEach` sauvegarde le contenu réel existant (s'il y en a un) **et** le retire du disque pour la durée du test (chaque test suppose un état de départ « aucun modèle actif », cohérent avec la base de test toujours vierge — un fichier réel laissé en place aurait faussé cette hypothèse, comme le montre le test « rejects a template upload missing the `:content:` tag... » qui vérifie `is_file(...)` à `false` après un upload refusé) ; `afterEach` nettoie ce que le test a laissé puis restaure le contenu réel sauvegardé, s'il y en avait un.

**Vérification** : `md5sum` du fichier réel avant/après une exécution complète de la suite (`1699 passed`) — somme identique, fichier intact.

**Leçon retenue pour la suite du projet** : toute future écriture de test touchant `storage_path()`/chemins filesystem réels (pas seulement `ReportTemplateSettings`) doit être vérifiée pour un défaut d'isolation similaire — `RefreshDatabase` isole la base, **pas** le disque.

## 3. Restructuration du chapitre « Infrastructure physique » (2026-07-09)

**Demande utilisateur** : réorganiser le chapitre Infrastructure physique (§2.6) autour d'une logique hiérarchique — vue globale, puis sites, puis bâtiments (racines puis enfants), puis autres objets — schémas placés au plus près des données qu'ils illustrent, au lieu de la structure §Étape 5 (3 graphes globaux en tête, puis un graphe de localisation par bâtiment sous chaque site, puis une liste plate par type d'objet).

**Décision utilisateur actée en amont** (le graphe des zones de sécurité, `SecurityZoneGraphBuilder`, n'apparaît pas dans la structure cible à 5 points fournie par la mission) : déplacé de sa position globale unique vers la section `Zone` de l'étape 5 ("autres objets"), scopé à chaque zone individuellement, inséré uniquement si `zone->childZones` n'est pas vide.

**Aucune extension du service de graphes commun n'a été nécessaire** : `PhysicalInfrastructureGraphBuilder::buildLocationDot()`/`buildConnectivityDot()` et `SecurityZoneGraphBuilder::buildDot()` acceptaient déjà des collections arbitraires (pas de site/building/object figé en dur) — toute la logique de périmètre (site, sous-arbre de bâtiment, baie, zone) est un filtrage en mémoire dans `PhysicalInfrastructureSection`, sans requête SQL supplémentaire (les collections restent celles déjà chargées par `Cartographer::scopedQuery(...)->with(...)->get()` en tête de `build()`).

**Nouvelle structure de `PhysicalInfrastructureSection::build()`** :
1. `addGlobalGraphs()` — schéma réseau + vue infrastructure physique, périmètre complet (tous sites/bâtiments/baies/équipements), sans filtre — reprend exactement l'appel non filtré des écrans interactifs.
2. `addSites()` — par site (alphabétique) : schéma réseau + infra physique **limités au site** (nouvelle méthode `siteScopedContext()`, filtre les 12 collections du contexte par appartenance directe ou via bâtiment/baie), puis les données du site (inchangées).
3. `addBuildingsSection()` — un seul sous-titre « Bâtiment », puis les bâtiments racines (alphabétique), puis les bâtiments enfants (alphabétique). Nouvelle méthode `buildingSubtreeContext()` (réutilise `buildingSubtree()`, déjà existante) : filtre le contexte au sous-arbre d'un bâtiment (lui-même + descendants récursifs, leurs baies, leurs équipements). Racines : schéma réseau + infra physique, **seulement si** le sous-arbre a plus d'un bâtiment ou contient au moins une baie/un équipement (`$hasContent`). Enfants (`building_id` non nul) : infra physique seulement (jamais de schéma réseau), toujours tenté — un bâtiment enfant sans baie ni équipement produit un graphe réduit au seul nœud du bâtiment, filtré comme « vide » par `WordHelper::insertGraph()` (moins de 2 nœuds), sans garde explicite nécessaire côté enfant.
4. `addBays()` (déplacée avant les autres objets, comme dans l'ordre actuel du rapport) : schéma infra physique scopé à la baie seule, uniquement si au moins une des 6 relations d'équipement monté n'est pas vide.
5. `addZones()` : schéma de zone de sécurité scopé (zone + `childZones` directs, `buildings`/`adminUsers` de l'ensemble), uniquement si `childZones` n'est pas vide — remplace le graphe global unique de §Étape 5.
6. Les 13 autres types d'objets (`PhysicalServer` → `Lan`) : inchangés, jamais de schéma (aucune relation de containment dans le vocabulaire des builders communs).

**Suppression de code mort** : `addSiteLocationGraphs()`, `addSiteConnectivityGraphs()`, `addFilteredConnectivityGraph()`, `belongsToBuildingOrBay()` (logique remplacée par `siteScopedContext()`/`buildingSubtreeContext()`) ; `buildingSubtree()` conservée telle quelle (toujours utile pour les deux nouvelles méthodes de scope).

**`WordHelper::insertGraph()` inchangée** : son garde-fou existant (`countGraphNodes() < 2` → graphe ignoré) couvre déjà l'exigence « aucun schéma vide, aucun nœud isolé n'est inséré » — aucune logique de détection de périmètre vide n'a dû être dupliquée dans `PhysicalInfrastructureSection`, seules les conditions *explicites* du §3/§5 (root building `$hasContent`, bay `$hasEquipment`, zone `childZones`) ont été ajoutées, par cohérence avec le texte de la mission et pour éviter des rasterisations `dot` inutiles à grande échelle.

**Tests** (`tests/Feature/Report/PhysicalInfrastructureSectionTest.php`, 10 tests, dont 6 nouveaux/réécrits) : ordre des sections (global → sites A/B → bâtiments racines A/Z → bâtiments enfants A/Z → Bay, vérifié via la position du **bookmark** `w:bookmarkStart[...]w:name="UID"` de chaque objet — chercher l'UID nu se serait heurté aux liens hypertexte `w:anchor` pointant vers ce même UID, apparus plus tôt dans le document, ex. la ligne « bâtiments » de la table d'un Site) ; comptage des PNG dans `word/media/` pour les 4 scénarios d'inclusion de schéma (bâtiment racine vide/peuplé, bâtiment enfant, baie vide/peuplée, zone avec/sans enfants) — **piège de test rencontré et corrigé** : les factories `PhysicalServer`/`StorageDevice`/`Bay`/`Workstation` (`app/Factories/*.php`) définissent `site_id`/`building_id`/`bay_id` par défaut via des sous-factories imbriquées (`Site::factory()`, `Building::factory()`, `Bay::factory()`) — omettre de surcharger explicitement un seul de ces trois champs dans un test fait apparaître un site/bâtiment/baie fantôme supplémentaire (chaînes non reliées entre elles), gonflant silencieusement le nombre de graphes/nœuds attendus. Chaque scénario isolé surcharge désormais les trois FK explicitement (`null` ou une valeur réelle). Suite complète rejouée : 1707 passed, 0 failed. `phpstan analyse` (tout le projet) et `pint --test` clean sur tous les fichiers touchés.

**Non-régression** : les 6 autres chapitres du rapport (`EcosystemSection` → `GdprSection`) et les écrans interactifs (`PhysicalInfrastructureView`, `NetworkInfrastructureView`, `SecurityZoneView`) sont inchangés — seule `PhysicalInfrastructureSection` (+ son fichier de test) a été modifiée.
