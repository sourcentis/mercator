# Authentification Keycloak avec Fallback Local

## Vue d'ensemble

Cette implémentation ajoute un système de **fallback vers l'authentification locale** pour Keycloak, similaire à celui
existant pour LDAP.

## Fonctionnalités

### 1. Auto-Provisioning (`KEYCLOAK_AUTO_PROVISION`)

Crée automatiquement un utilisateur local lors de la première connexion SSO réussie.

```env
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_AUTO_PROVISION_ROLE=User
```

**Comportement :**

- ✅ **true** : Utilisateur créé automatiquement avec le rôle par défaut
- ❌ **false** : Seuls les utilisateurs existants peuvent se connecter via SSO

### 2. Fallback Local (`KEYCLOAK_FALLBACK_LOCAL`)

Permet de basculer vers l'authentification locale en cas d'échec SSO.

```env
KEYCLOAK_FALLBACK_LOCAL=true
```

**Scénarios déclenchant le fallback :**

1. Échec de communication avec Keycloak
2. Token invalide ou expiré
3. Utilisateur non provisionné localement (si `AUTO_PROVISION=false`)

**Comportement :**

- ✅ **true** : Redirige vers `/login` avec message informatif
- ❌ **false** : Affiche une erreur et bloque l'accès

### 3. Synchronisation des Rôles (`KEYCLOAK_SYNC_ROLES`)

Synchronise les rôles Keycloak avec les rôles locaux correspondants.

```env
KEYCLOAK_SYNC_ROLES=true
```

**Mécanisme :**

- Compare `realm_access.roles` de Keycloak avec `Role::title` local
- Utilise `syncWithoutDetaching()` pour préserver les rôles locaux existants
- Les rôles manquants dans Keycloak sont conservés

## Matrice de Décision

| Scénario                       | `AUTO_PROVISION` | `FALLBACK_LOCAL` | Résultat                            |
|--------------------------------|------------------|------------------|-------------------------------------|
| SSO OK, utilisateur existant   | ✓/✗              | ✓/✗              | ✅ Login SSO réussi                  |
| SSO OK, utilisateur inexistant | ✓                | ✓/✗              | ✅ Auto-provision + Login            |
| SSO OK, utilisateur inexistant | ✗                | ✓                | ⚠️ Redirect login local             |
| SSO OK, utilisateur inexistant | ✗                | ✗                | ❌ Erreur : utilisateur invalide     |
| SSO échoue                     | ✓/✗              | ✓                | ⚠️ Redirect login local             |
| SSO échoue                     | ✓/✗              | ✗                | ❌ Erreur : authentification échouée |

## Comparaison LDAP vs Keycloak

### Similarités

| Fonctionnalité    | LDAP                         | Keycloak                         |
|-------------------|------------------------------|----------------------------------|
| Auto-provisioning | ✅ `LDAP_AUTO_PROVISION`      | ✅ `KEYCLOAK_AUTO_PROVISION`      |
| Fallback local    | ✅ `LDAP_FALLBACK_LOCAL`      | ✅ `KEYCLOAK_FALLBACK_LOCAL`      |
| Rôle par défaut   | ✅ `LDAP_AUTO_PROVISION_ROLE` | ✅ `KEYCLOAK_AUTO_PROVISION_ROLE` |
| Audit logs        | ✅                            | ✅                                |

### Différences

| Aspect                    | LDAP                                         | Keycloak                            |
|---------------------------|----------------------------------------------|-------------------------------------|
| **Flow**                  | Bind direct avec credentials                 | OAuth2 redirect flow                |
| **Fallback**              | Même requête, credentials disponibles        | Redirect séparé, pas de credentials |
| **Sync rôles**            | Via groupes LDAP                             | Via `realm_access.roles`            |
| **Recherche utilisateur** | Multiple attributs (`uid`, `sAMAccountName`) | Login ou email                      |

## Cas d'Usage Recommandés

### Configuration Stricte (Production)

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_AUTO_PROVISION=false
KEYCLOAK_FALLBACK_LOCAL=false
KEYCLOAK_SYNC_ROLES=true
```

- ✅ Sécurité maximale
- ✅ Contrôle total sur les utilisateurs
- ❌ Nécessite pré-provisioning manuel

### Configuration Permissive (Dev/Test)

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_FALLBACK_LOCAL=true
KEYCLOAK_SYNC_ROLES=true
```

- ✅ Onboarding automatique
- ✅ Fallback en cas de problème
- ⚠️ Moins de contrôle

### Configuration Hybride (Recommandé)

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_FALLBACK_LOCAL=true
KEYCLOAK_SYNC_ROLES=true
KEYCLOAK_AUTO_PROVISION_ROLE=User
```

- ✅ Auto-provisioning avec rôle limité
- ✅ Fallback pour continuité de service
- ✅ Sync rôles pour gouvernance

## Logs et Debugging

Les événements suivants sont loggés :

```php
// Échec Keycloak
Log::warning('Keycloak authentication failed', ['error' => ...]);

// Utilisateur non trouvé
Log::info('Keycloak user not found locally and auto-provision disabled', ['login' => ...]);

// Auto-provision réussi
Log::info('Keycloak user auto-provisioned', ['user_id' => ..., 'login' => ...]);

// Login réussi
Log::info('Keycloak login successful', ['user_id' => ..., 'login' => ...]);
```

Les audit logs enregistrent :

- Action : `SSO Login (Keycloak)`
- User agent, IP, URL
- Rôles Keycloak reçus

## Migration depuis l'ancien code

### Changements requis dans `config/services.php` :

```php
'keycloak' => [
    'enabled' => env('KEYCLOAK_ENABLED', false),
    'client_id' => env('KEYCLOAK_CLIENT_ID'),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
    'redirect' => env('KEYCLOAK_REDIRECT_URI'),
    'base_url' => env('KEYCLOAK_BASE_URL'),
    'realms' => env('KEYCLOAK_REALM'),
    'btn_label' => env('KEYCLOAK_BTN_LABEL', 'Keycloak'),
    
    // Nouvelles options
    'auto_provision' => env('KEYCLOAK_AUTO_PROVISION', false),
    'auto_provision_role' => env('KEYCLOAK_AUTO_PROVISION_ROLE', 'User'),
    'fallback_local' => env('KEYCLOAK_FALLBACK_LOCAL', false),
    'sync_roles' => env('KEYCLOAK_SYNC_ROLES', false),
],
```

### Variable `KEYCLOAK` obsolète

L'ancienne variable `KEYCLOAK=enable` n'est plus utilisée : le bouton de connexion s'affiche dès que
`KEYCLOAK_ENABLED=true`. Les messages de fallback (`info`, `error`) sont affichés nativement sur la page de
connexion.

## Tests

### Test du fallback SSO → Local

1. Désactiver temporairement Keycloak :
   ```env
   KEYCLOAK_ENABLED=false
   KEYCLOAK_FALLBACK_LOCAL=true
   ```

2. Tenter SSO → doit rediriger vers login local

### Test auto-provisioning

1. Configuration :
   ```env
   KEYCLOAK_ENABLED=true
   KEYCLOAK_AUTO_PROVISION=true
   KEYCLOAK_AUTO_PROVISION_ROLE=User
   ```

2. Connexion avec un utilisateur Keycloak inexistant localement
3. Vérifier création dans la table `users` avec le rôle `User`

### Test sync des rôles

1. Créer des rôles locaux : `Admin`, `Editor`, `User`
2. Attribuer `Admin` et `Editor` dans Keycloak
3. Se connecter via SSO
4. Vérifier dans `role_user` que les rôles sont synchronisés

## Sécurité

### Recommandations

1. **Production** : `FALLBACK_LOCAL=false` pour forcer SSO
2. **Mots de passe** : Les utilisateurs auto-provisionnés reçoivent un mot de passe aléatoire inutilisable
3. **Rôles** : Utiliser `syncWithoutDetaching()` pour préserver les rôles locaux custom
4. **Audit** : Tous les logins SSO sont audités avec les rôles Keycloak reçus

### Vérifications

```php
// Le mot de passe généré est inutilisable
Hash::make(Str::random(32))

// Les rôles locaux sont préservés
$user->roles()->syncWithoutDetaching($keycloakRoles)
```

## Support

Pour toute question ou amélioration, créer une issue sur le dépôt GitHub mercator.