# Authentification Keycloak

## Vue d'ensemble

Mercator peut déléguer l'authentification des utilisateurs à **Keycloak** via OpenID Connect (flux *authorization
code*). Un bouton « Keycloak » est ajouté à la page de connexion ; le formulaire de connexion locale reste disponible.

À chaque connexion SSO, Mercator :

1. redirige l'utilisateur vers Keycloak (`/realms/<realm>/protocol/openid-connect/auth`, scope `openid`) ;
2. échange le code d'autorisation contre un token à l'aide du secret client ;
3. lit l'identité de l'utilisateur via l'endpoint `userinfo` de Keycloak ;
4. retrouve le compte local correspondant (par login, puis par email), ou le crée si l'auto-provisioning est activé ;
5. ajoute les rôles locaux correspondant aux rôles du realm Keycloak (si la synchronisation des rôles est activée) ;
6. ouvre la session Mercator et écrit une entrée dans le journal d'audit.

Pour connecter Mercator à Microsoft Entra ID au travers de Keycloak, voir [ENTRA_ID.fr.md](ENTRA_ID.fr.md).

## Configuration

### Keycloak

Dans le realm utilisé par Mercator :

1. **Clients → Create client**

   | Champ                 | Valeur                                                 |
   |-----------------------|--------------------------------------------------------|
   | Client type           | OpenID Connect                                         |
   | Client ID             | `mercator`                                             |
   | Client authentication | ON (client confidentiel)                               |
   | Authentication flow   | Standard flow                                          |
   | Valid redirect URIs   | `https://mercator.example.com/login/keycloak/callback` |
   | Web origins           | `https://mercator.example.com`                         |

   Récupérez le secret dans l'onglet **Credentials**.

2. **Realm roles** (uniquement si la synchronisation des rôles est utilisée) : créez des rôles dont les noms sont
   **strictement identiques** aux titres des rôles Mercator (**Administration → Rôles**), majuscules comprises, par
   exemple `Admin`, `User`, `Auditor`, `Cartographer`.

3. **Client scopes → roles → Mappers → realm roles** : activez **Add to userinfo**. Mercator lit les rôles via
   l'endpoint `userinfo`, où Keycloak ne les place pas par défaut. Sans ce réglage, la connexion fonctionne mais
   aucun rôle n'est transmis.

### Mercator

Dans le fichier `.env` :

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_CLIENT_ID=mercator
KEYCLOAK_CLIENT_SECRET="<secret du client Keycloak>"
KEYCLOAK_REDIRECT_URI=https://mercator.example.com/login/keycloak/callback
KEYCLOAK_BASE_URL=https://keycloak.example.com
KEYCLOAK_REALM=mercator
KEYCLOAK_BTN_LABEL="Connexion SSO"
KEYCLOAK_FALLBACK_LOCAL=true
KEYCLOAK_AUTO_PROVISION=false
KEYCLOAK_AUTO_PROVISION_ROLE=User
KEYCLOAK_SYNC_ROLES=false
```

| Variable                       | Défaut     | Description                                                              |
|--------------------------------|------------|--------------------------------------------------------------------------|
| `KEYCLOAK_ENABLED`             | `false`    | Active le SSO et affiche le bouton sur la page de connexion              |
| `KEYCLOAK_CLIENT_ID`           |            | Identifiant du client Keycloak                                           |
| `KEYCLOAK_CLIENT_SECRET`       |            | Secret du client Keycloak                                                |
| `KEYCLOAK_REDIRECT_URI`        |            | URL de retour, identique à celle déclarée dans Keycloak                  |
| `KEYCLOAK_BASE_URL`            |            | URL racine de Keycloak, **sans** `/realms/...`                           |
| `KEYCLOAK_REALM`               |            | Nom du realm                                                             |
| `KEYCLOAK_BTN_LABEL`           | `Keycloak` | Libellé du bouton de connexion                                           |
| `KEYCLOAK_FALLBACK_LOCAL`      | `false`    | Message affiché en cas d'échec SSO (voir plus bas)                       |
| `KEYCLOAK_AUTO_PROVISION`      | `false`    | Crée le compte Mercator à la première connexion SSO                      |
| `KEYCLOAK_AUTO_PROVISION_ROLE` | `User`     | Rôle attribué aux comptes créés automatiquement                          |
| `KEYCLOAK_SYNC_ROLES`          | `false`    | Ajoute les rôles Mercator correspondant aux rôles du realm Keycloak      |

Rechargez ensuite la configuration :

```bash
php artisan config:clear
```

Avec Docker, redémarrez le conteneur après modification des variables d'environnement.

> L'ancienne variable `KEYCLOAK=enable` n'est plus utilisée : seule `KEYCLOAK_ENABLED` compte.

## Fonctionnalités

### 1. Auto-Provisioning (`KEYCLOAK_AUTO_PROVISION`)

Crée automatiquement un utilisateur local lors de la première connexion SSO réussie.

```env
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_AUTO_PROVISION_ROLE=User
```

**Comportement :**

- ✅ **true** : utilisateur créé avec le `preferred_username` Keycloak comme login, le nom et l'email Keycloak, le
  rôle par défaut et un mot de passe aléatoire inutilisable
- ❌ **false** : seuls les utilisateurs existants peuvent se connecter via SSO

Si `KEYCLOAK_AUTO_PROVISION_ROLE` ne correspond à aucun rôle Mercator existant, le compte est créé sans rôle.

### 2. Fallback Local (`KEYCLOAK_FALLBACK_LOCAL`)

Détermine la manière dont un échec SSO est signalé à l'utilisateur.

```env
KEYCLOAK_FALLBACK_LOCAL=true
```

**Situations concernées :**

1. Échec de communication avec Keycloak, secret client invalide, code invalide ou expiré
2. Utilisateur introuvable localement (si `KEYCLOAK_AUTO_PROVISION=false`)

**Comportement :** dans tous les cas, l'utilisateur est redirigé vers `/login`.

- ✅ **true** : un message d'information l'invite à se connecter avec ses identifiants locaux
- ❌ **false** : un message d'erreur est affiché

> Cette option ne désactive **pas** le formulaire de connexion locale : il reste disponible quelle que soit sa
> valeur. Elle modifie uniquement le message affiché après un échec SSO.

### 3. Synchronisation des Rôles (`KEYCLOAK_SYNC_ROLES`)

Ajoute à l'utilisateur les rôles locaux correspondant à ses rôles de realm Keycloak.

```env
KEYCLOAK_SYNC_ROLES=true
```

**Mécanisme :**

- Compare `realm_access.roles` reçu du `userinfo` avec `Role::title` local (insensible à la casse avec MySQL/MariaDB, sensible avec PostgreSQL : respectez toujours la casse exacte)
- Les rôles Keycloak sans équivalent local (par exemple `offline_access`, `default-roles-<realm>`) sont ignorés
- Utilise `syncWithoutDetaching()` : les rôles sont uniquement **ajoutés**, jamais retirés. Un rôle retiré dans
  Keycloak reste dans Mercator jusqu'à ce qu'il soit retiré manuellement.

## Matrice de Décision

| Scénario                            | `AUTO_PROVISION` | `FALLBACK_LOCAL` | Résultat                                                                         |
|-------------------------------------|------------------|------------------|----------------------------------------------------------------------------------|
| SSO OK, utilisateur existant        | ✓/✗              | ✓/✗              | ✅ Connexion SSO réussie                                                          |
| SSO OK, utilisateur inexistant      | ✓                | ✓/✗              | ✅ Auto-provisioning + connexion                                                  |
| SSO OK, utilisateur inexistant      | ✗                | ✓                | ⚠️ Retour sur `/login` : `User "…" not found. Please use local credentials.`      |
| SSO OK, utilisateur inexistant      | ✗                | ✗                | ❌ Retour sur `/login` : `User "…" is not a valid Mercator user.`                 |
| Échec SSO                           | ✓/✗              | ✓                | ⚠️ Retour sur `/login` : `SSO authentication failed. Please use local credentials.` |
| Échec SSO                           | ✓/✗              | ✗                | ❌ Retour sur `/login` : `Authentication failed.`                                 |

## Comparaison LDAP vs Keycloak

### Similarités

| Fonctionnalité    | LDAP                         | Keycloak                         |
|-------------------|------------------------------|----------------------------------|
| Auto-provisioning | ✅ `LDAP_AUTO_PROVISION`      | ✅ `KEYCLOAK_AUTO_PROVISION`      |
| Fallback local    | ✅ `LDAP_FALLBACK_LOCAL`      | ✅ `KEYCLOAK_FALLBACK_LOCAL`      |
| Rôle par défaut   | ✅ `LDAP_AUTO_PROVISION_ROLE` | ✅ `KEYCLOAK_AUTO_PROVISION_ROLE` |
| Logs d'audit      | ✅                            | ✅                                |

### Différences

| Aspect                    | LDAP                                                           | Keycloak                                         |
|---------------------------|----------------------------------------------------------------|--------------------------------------------------|
| **Flux**                  | Bind direct avec les identifiants                              | Redirection OAuth2 / OpenID Connect              |
| **Fallback**              | Même requête : mot de passe local vérifié si LDAP échoue       | Redirection séparée : message sur la page de connexion |
| **Contrôle d'accès**      | `LDAP_GROUP` restreint la connexion aux membres d'un groupe    | Côté Keycloak (client, fournisseur d'identité)   |
| **Sync rôles**            | Aucune (rôle par défaut au provisioning uniquement)            | Via `realm_access.roles`                         |
| **Recherche utilisateur** | Plusieurs attributs (`uid`, `sAMAccountName`)                  | Login, puis email                                |

## Cas d'Usage Recommandés

### Configuration Stricte (Production)

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_AUTO_PROVISION=false
KEYCLOAK_FALLBACK_LOCAL=false
KEYCLOAK_SYNC_ROLES=true
```

- ✅ Contrôle total sur les utilisateurs : seuls les comptes créés dans Mercator peuvent se connecter
- ❌ Nécessite un pré-provisioning manuel

### Configuration Permissive (Dev/Test)

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_FALLBACK_LOCAL=true
KEYCLOAK_SYNC_ROLES=true
```

- ✅ Onboarding automatique
- ⚠️ Tout utilisateur du realm autorisé sur le client obtient un compte Mercator

### Configuration Hybride (Recommandé)

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_FALLBACK_LOCAL=true
KEYCLOAK_SYNC_ROLES=true
KEYCLOAK_AUTO_PROVISION_ROLE=User
```

- ✅ Auto-provisioning avec un rôle limité
- ✅ Message explicite invitant à la connexion locale en cas de problème SSO
- ✅ Rôles gérés dans Keycloak

## Logs et Debugging

Les événements suivants sont journalisés dans `storage/logs/laravel.log` :

```php
// Échec Keycloak (l'erreur contient la réponse de Keycloak, par exemple 401 unauthorized_client)
Log::warning('Keycloak authentication failed', ['error' => ...]);

// Utilisateur non trouvé
Log::info('Keycloak user not found locally and auto-provision disabled', ['login' => ...]);

// Auto-provisioning réussi
Log::info('Keycloak user auto-provisioned', ['user_id' => ..., 'login' => ...]);

// Connexion réussie
Log::info('Keycloak login successful', ['user_id' => ..., 'login' => ...]);
```

Le journal d'audit (**Administration → Journal d'audit**) enregistre :

- Action : `SSO Login (Keycloak)`
- User agent, IP, URL
- Rôles Keycloak reçus (`keycloak_roles`)

## Configuration de Référence

Paramètres Keycloak dans `config/services.php` :

```php
'keycloak' => [
    'enabled' => env('KEYCLOAK_ENABLED', false),
    'client_id' => env('KEYCLOAK_CLIENT_ID'),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
    'redirect' => env('KEYCLOAK_REDIRECT_URI'),
    'base_url' => env('KEYCLOAK_BASE_URL'),
    'realms' => env('KEYCLOAK_REALM'),
    'btn_label' => env('KEYCLOAK_BTN_LABEL', 'Keycloak'),
    'fallback_local' => env('KEYCLOAK_FALLBACK_LOCAL', false),
    'auto_provision' => env('KEYCLOAK_AUTO_PROVISION', false),
    'auto_provision_role' => env('KEYCLOAK_AUTO_PROVISION_ROLE', 'User'),
    'sync_roles' => env('KEYCLOAK_SYNC_ROLES', false),
],
```

La clé `realms` (au pluriel) est celle attendue par le driver `socialiteproviders/keycloak`.

## Tests

### Test du message d'échec SSO

1. Renseigner un `KEYCLOAK_CLIENT_SECRET` invalide et `KEYCLOAK_FALLBACK_LOCAL=true`
2. Se connecter via SSO → retour sur `/login` avec `SSO authentication failed. Please use local credentials.`
3. `storage/logs/laravel.log` contient `Keycloak authentication failed`

### Test auto-provisioning

1. Configuration :
   ```env
   KEYCLOAK_ENABLED=true
   KEYCLOAK_AUTO_PROVISION=true
   KEYCLOAK_AUTO_PROVISION_ROLE=User
   ```

2. Connexion avec un utilisateur Keycloak inexistant localement
3. Vérifier dans **Administration → Utilisateurs** que le compte existe avec le rôle `User`

### Test sync des rôles

1. Dans Keycloak, attribuer le rôle de realm `Cartographer` à un utilisateur
2. Vérifier que **Add to userinfo** est activé sur le mapper `realm roles`
3. Se connecter via SSO
4. Vérifier dans **Administration → Utilisateurs** que l'utilisateur a le rôle `Cartographer`, et dans le journal
   d'audit que `keycloak_roles` contient `Cartographer`

## Dépannage

| Symptôme                                                  | Cause probable                                                              | Solution                                                                        |
|-----------------------------------------------------------|-----------------------------------------------------------------------------|---------------------------------------------------------------------------------|
| Le bouton SSO n'apparaît pas                              | `KEYCLOAK_ENABLED` absent ou différent de `true`, configuration en cache    | Vérifiez la variable, exécutez `php artisan config:clear` (ou redémarrez le conteneur) |
| Keycloak affiche `Invalid parameter: redirect_uri`        | `KEYCLOAK_REDIRECT_URI` absent des *Valid redirect URIs* du client          | Alignez les deux valeurs (protocole, hôte, chemin)                              |
| Keycloak renvoie `Realm does not exist`                   | `KEYCLOAK_REALM` erroné                                                     | Corrigez le `.env`                                                              |
| Keycloak renvoie `HTTP 404 Not Found`                     | `KEYCLOAK_BASE_URL` contenant `/realms/...`                                 | Ne conservez que l'URL racine de Keycloak                                       |
| Retour sur la page de connexion avec une erreur d'authentification | Secret client erroné, Keycloak injoignable depuis Mercator, horloge décalée | Consultez `storage/logs/laravel.log` (`Keycloak authentication failed`)   |
| Connexion réussie mais aucun rôle attribué                | Rôles absents du `userinfo`, ou noms de rôles différents                    | Activez *Add to userinfo* et vérifiez la casse des rôles                        |
| Un second compte est créé pour un utilisateur existant    | Le `preferred_username` et l'email Keycloak diffèrent du compte Mercator    | Alignez le login Mercator sur le nom d'utilisateur Keycloak                     |

## Sécurité

### Recommandations

1. **Compte de secours** : conservez au moins un compte administrateur local Mercator, au mot de passe robuste,
   pour intervenir en cas d'indisponibilité de Keycloak.
2. **Mots de passe** : les utilisateurs auto-provisionnés reçoivent un mot de passe aléatoire inutilisable ; ils ne
   peuvent se connecter que par SSO (sauf si un administrateur leur définit un mot de passe).
3. **Rôles** : la synchronisation ne fait qu'ajouter des rôles. Pour révoquer un accès, retirez le rôle dans
   Mercator, ou désactivez l'utilisateur dans Keycloak pour bloquer toute connexion SSO.
4. **Audit** : toutes les connexions SSO sont auditées avec les rôles Keycloak reçus.
5. **Déconnexion** : la déconnexion de Mercator ferme la session Mercator, mais pas la session Keycloak. Sur un
   poste partagé, l'utilisateur doit également se déconnecter de Keycloak.
6. **Secrets** : ne versionnez jamais le fichier `.env`.

## Support

Pour toute question ou amélioration, créer une issue sur le dépôt GitHub mercator.
