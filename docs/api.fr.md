# API

La cartographie peut être modifiée ou mise à jour via une REST API.

Une API REST ([Representational State Transfer](https://fr.wikipedia.org/wiki/Representational_state_transfer))
est une interface de programmation d'application qui respecte les contraintes du style d'architecture REST
et permet d'interagir avec les services web RESTful.

## Installer l'API sur Mercator

Pour installer l'API dans Mercator, il est nécessaire d'installer Passport en lançant cette commande :

```bash
php artisan passport:install
```

- l'environnement Docker prend en charge cette fonctionnalité nativement, via l'entrypoint.

## 📚 APIs de la cartographie

### Descriptions
Chaque entité du modèle de données de la cartographie expose un endpoint REST conforme aux conventions Laravel.  
Les routes sont définies dans : `/routes/api.php`.

---

#### **GET /api/{resource}**  
Retourne une **collection** d’objets `{resource}`.

**Réponse :**  
- `ResourceCollection` Laravel  
- Contient uniquement les attributs principaux (vue simplifiée)

**Exemple :**  
`GET /api/processes`

---

#### **GET /api/{resource}/{id}**  
Retourne la **Resource** complète correspondant à l’ID fourni.

**Paramètres :**  
- `id` — identifiant unique de l’objet

**Réponse :**  
- `Resource` Laravel  
- Contient l’ensemble des attributs de l’objet

**Exemple :**  
`GET /api/processes/1`

---

#### 📌 Notes
- Les endpoints de liste renvoient une **ResourceCollection**, donc une vue partielle.  
- Pour obtenir la représentation complète d’un objet, utiliser son endpoint individuel.  
- Pour visualiser le modèle de données associé à une API, cliquer sur son nom dans l’interface.

### Les points de terminaison du RGPD

- [<img src="/mercator/images/get.png" width="30"> /api/data-processings](./model.fr.md#registre)
- [<img src="/mercator/images/get.png" width="30"> /api/security-controls](./model.fr.md#mesures-de-securite)

### Les points de terminaison de l'écosystème

- [<img src="/mercator/images/get.png" width="30"> /api/entities](./model.fr.md#entites)
- [<img src="/mercator/images/get.png" width="30"> /api/relations](./model.fr.md#relations)

### Les points de terminaison du métier du système d'information

- [<img src="/mercator/images/get.png" width="30"> /api/macro-processuses](./model.fr.md#macro-processus)
- [<img src="/mercator/images/get.png" width="30"> /api/processes](./model.fr.md#processus)
- [<img src="/mercator/images/get.png" width="30"> /api/activities](./model.fr.md#activites)
- [<img src="/mercator/images/get.png" width="30"> /api/operations](./model.fr.md#operations)
- [<img src="/mercator/images/get.png" width="30"> /api/tasks](./model.fr.md#taches)
- [<img src="/mercator/images/get.png" width="30"> /api/actors](./model.fr.md#acteurs)
- [<img src="/mercator/images/get.png" width="30"> /api/information](./model.fr.md#information)

### Les points de terminaison des applications

- [<img src="/mercator/images/get.png" width="30"> /api/application-blocks](./model.fr.md#blocs-applicatif)
- [<img src="/mercator/images/get.png" width="30"> /api/applications](./model.fr.md#applications)
- [<img src="/mercator/images/get.png" width="30"> /api/application-services](./model.fr.md#services-applicatif)
- [<img src="/mercator/images/get.png" width="30"> /api/application-modules](./model.fr.md#modules-applicatif)
- [<img src="/mercator/images/get.png" width="30"> /api/databases](./model.fr.md#bases-de-donnees)
- [<img src="/mercator/images/get.png" width="30"> /api/application-flows](./model.fr.md#flux-applicatifs)

### Les points de terminaison de l'administration

- [<img src="/mercator/images/get.png" width="30"> /api/zone-admins](./model.fr.md#zones-dadministration)
- [<img src="/mercator/images/get.png" width="30"> /api/annuaires](./model.fr.md#services-dannuaire-dadministration)
- [<img src="/mercator/images/get.png" width="30"> /api/forest-ads](./model.fr.md#forets-active-directory-arborescence-ldap)
- [<img src="/mercator/images/get.png" width="30"> /api/domains](./model.fr.md#domaines-active-directory-ldap)
- [<img src="/mercator/images/get.png" width="30"> /api/admin-users](./model.fr.md#utilisateurs)

### Les points de terminaison de l'infrastructure logique

- [<img src="/mercator/images/get.png" width="30"> /api/networks](./model.fr.md#reseaux)
- [<img src="/mercator/images/get.png" width="30"> /api/subnetworks](./model.fr.md#sous-reseaux)
- [<img src="/mercator/images/get.png" width="30"> /api/gateways](./model.fr.md#passerelles-dentrees-depuis-lexterieur)
- [<img src="/mercator/images/get.png" width="30"> /api/external-connected-entities](./model.fr.md#entites-exterieures-connectees)
- [<img src="/mercator/images/get.png" width="30"> /api/network-switches](./model.fr.md#commutateurs-reseau)
- [<img src="/mercator/images/get.png" width="30"> /api/routers](./model.fr.md#routeurs-logiques)
- [<img src="/mercator/images/get.png" width="30"> /api/security-devices](./model.fr.md#equipements-de-securite)
- [<img src="/mercator/images/get.png" width="30"> /api/dhcp-servers *(usage non recommandé)*](./model.fr.md#serveurs-dhcp)
- [<img src="/mercator/images/get.png" width="30"> /api/dnsservers *(usage non recommandé)*](./model.fr.md#serveurs-dns)
- [<img src="/mercator/images/get.png" width="30"> /api/clusters](./model.fr.md#clusters)
- [<img src="/mercator/images/get.png" width="30"> /api/logical-servers](./model.fr.md#serveurs-logiques)
- [<img src="/mercator/images/get.png" width="30"> /api/backups](./model.fr.md#plans-de-sauvegarde) ==> lié à logical-servers et storage-devices
- [<img src="/mercator/images/get.png" width="30"> /api/logical-flows](./model.fr.md#flux-logiques)
- [<img src="/mercator/images/get.png" width="30"> /api/containers](./model.fr.md#conteneurs)
- [<img src="/mercator/images/get.png" width="30"> /api/certificates](./model.fr.md#certificats)
- [<img src="/mercator/images/get.png" width="30"> /api/vlans](./model.fr.md#vlans)

### Les points de terminaison de l'infrastructure physique

- [<img src="/mercator/images/get.png" width="30"> /api/sites](./model.fr.md#sites)
- [<img src="/mercator/images/get.png" width="30"> /api/buildings](./model.fr.md#batiments-salles)
- [<img src="/mercator/images/get.png" width="30"> /api/bays](./model.fr.md#baies)
- [<img src="/mercator/images/get.png" width="30"> /api/zones](./model.fr.md#zones-de-securite)
- [<img src="/mercator/images/get.png" width="30"> /api/physical-servers](./model.fr.md#serveurs-physiques)
- [<img src="/mercator/images/get.png" width="30"> /api/workstations](./model.fr.md#postes-de-travail)
- [<img src="/mercator/images/get.png" width="30"> /api/storage-devices](./model.fr.md#infrastructures-de-stockage) (recommandé pour backups)
- [<img src="/mercator/images/get.png" width="30"> /api/peripherals](./model.fr.md#peripheriques)
- [<img src="/mercator/images/get.png" width="30"> /api/phones](./model.fr.md#telephones)
- [<img src="/mercator/images/get.png" width="30"> /api/physical-switches](./model.fr.md#commutateurs-physiques)
- [<img src="/mercator/images/get.png" width="30"> /api/physical-routers](./model.fr.md#routeurs-physiques)
- [<img src="/mercator/images/get.png" width="30"> /api/wifi-terminals](./model.fr.md#bornes-wifi)
- [<img src="/mercator/images/get.png" width="30"> /api/physical-security-devices](./model.fr.md#equipements-de-securite-physique)
- [<img src="/mercator/images/get.png" width="30"> /api/physical-links](./model.fr.md#liens-physiques)
- [<img src="/mercator/images/get.png" width="30"> /api/wans](./model.fr.md#wans)
- [<img src="/mercator/images/get.png" width="30"> /api/mans](./model.fr.md#mans)
- [<img src="/mercator/images/get.png" width="30"> /api/lans](./model.fr.md#lans)


## Les API de la Configuration

- [<img src="/mercator/images/get.png" width="30"> /api/users](./model.fr.md#utilisateurs)
- [<img src="/mercator/images/get.png" width="30"> /api/roles](./model.fr.md#roles)

Le rôle contient des permissions: *permission_roles*
*permission_roles* n'existe pas comme endpoint API. C'est une table pivot gérée en interne par Laravel. Pour récupérer les associations rôle↔permission, on doit passer par **/api/roles/{id}?include=permissions** — les permissions liées à chaque rôle seront imbriquées dans la réponse du rôle.
La liste des permissions de de leurs {id} se trouve par l'api **/api/permissions**


- [<img src="/mercator/images/get.png" width="30"> /api/perimeters](./model.fr.md#perimetres)

Les périmètres (`name` : 2 à 32 caractères, unique) exigent la permission **configure** pour toute action, comme l'écran d'administration. Le périmètre par défaut (id 1) et tout périmètre encore référencé par un rôle ne peuvent pas être supprimés (`422`). **/api/perimeters/{id}** renvoie aussi les ids des rôles rattachés au périmètre (`roles`).

- [<img src="/mercator/images/get.png" width="30"> /api/cartographers](./model.fr.md#cartographie)
- [<img src="/mercator/images/get.png" width="30"> /api/graphs](./model.fr.md#outils)

Les graphes regroupent les cartes (`class` = 1) et les diagrammes BPMN (`class` = 2). Le champ `content` contient le XML brut du diagramme et est enregistré tel quel (ses balises ne sont pas supprimées comme pour les autres champs texte).

- [<img src="/mercator/images/get.png" width="30"> /api/permissions](./model.fr.md#permissions) `LECTURE UNIQUEMENT`
- [<img src="/mercator/images/get.png" width="30"> /api/documents](./model.fr.md#documents)

La particularité du point de terminaison **documents** est qu'il permet d'ajouter ou de télécharger un document.

#### Exemple pour un document dans Mercator:

- Ajout d'un document dans la base
```bash
RESPONSE=$(http_call -X POST "$API/api/documents" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json" \
    -F "file=@./rapport.pdf")

# Affichage JSON propre (jq décode, mais n'est plus dans $() )
echo "$RESPONSE" | jq .

DOC_ID=$(echo "$RESPONSE" | jq -r '.id // empty' 2>/dev/null)
```

- Téléchargement d'un document de Mercator:
```bash
 OUTFILE="./downloaded_${DOC_ID}.pdf"
    curl -s -X GET "$API/api/documents/$DOC_ID/download" \
        -H "Authorization: Bearer $TOKEN" \
        -H "Accept: application/octet-stream" \
        -o "$OUTFILE" \
        -w "HTTP %{http_code}\n"
```
---
## Les API des Outils

### Cartes et BPMN

N/C

### requêtes
- [<img src="/mercator/images/get.png" width="30"> /api/queries](./model.fr#requetes)
- <img src="/mercator/images/get.png" width="30"> /api/queries/***id***

#### Les requêtes peuvent aussi être exécutées par api
- <img src="/mercator/images/get.png" width="30"> /api/queries/execute/1
    - l'id de la requête doit être fourni.
    - La requête doit être de type liste.
    - Le résultat est renvoyé au format **CSV**.

#### Une requête peut aussi être exécutée sans être enregistrée
- **POST** /api/queries/execute
    - Le DSL est envoyé en **JSON** dans le corps de la requête.
    - Nécessite le droit `query_create` ou `query_edit`, ainsi que le droit **show** sur chaque objet lu par la requête.
    - Le résultat est renvoyé en **JSON** : `columns`, `rows` et `meta` pour une liste, `nodes`, `edges` et `meta` pour un graphe.

```bash
curl -X POST https://mercator/api/queries/execute \
     -H "Authorization: Bearer <token>" \
     -H "Content-Type: application/json" \
     -d '{"from": "applications", "fields": ["name", "logical_servers.name"], "filters": [{"field": "name", "operator": "like", "value": "A%"}], "output": "list", "limit": 100}'
```

---

## Les APIs des rapports
- <img src="/mercator/images/get.png" width="30"> /api/report/cartography
- <img src="/mercator/images/get.png" width="30"> /api/report/entities
- <img src="/mercator/images/get.png" width="30"> /api/report/applicationsByBlocks
- <img src="/mercator/images/get.png" width="30"> /api/report/directory
- <img src="/mercator/images/get.png" width="30"> /api/report/logicalServers
- <img src="/mercator/images/get.png" width="30"> /api/report/securityNeeds
- <img src="/mercator/images/get.png" width="30"> /api/report/logicalServerConfigs
- <img src="/mercator/images/get.png" width="30"> /api/report/externalAccess
- <img src="/mercator/images/get.png" width="30"> /api/report/physicalInventory
- <img src="/mercator/images/get.png" width="30"> /api/report/vlans
- <img src="/mercator/images/get.png" width="30"> /api/report/workstations
- <img src="/mercator/images/get.png" width="30"> /api/report/cve
- <img src="/mercator/images/get.png" width="30"> /api/report/activityList
- <img src="/mercator/images/get.png" width="30"> /api/report/activityReport
- <img src="/mercator/images/get.png" width="30"> /api/report/impacts
- <img src="/mercator/images/get.png" width="30"> /api/report/rto


### Les rapports Excel peuvent aussi être extraits en format CSV.

- Exemple de sortie Excel
```bash
curl -s -X GET http://localhost:8081/api/report/cve \
    -H "Authorization: Bearer ${TOKEN}" \
    -H "Accept: application/octet-stream" \
    -o "rapport_cve_$(date +%Y%m%d).xlsx"
```
- Exemple de sortie csv
```bash
curl -s -X GET "http://localhost:8081/api/report/cve?format=csv" \
     -H "Authorization: Bearer ${TOKEN}" \
     -H "Accept: text/csv" \
     -o "rapport_cve_$(date +%Y%m%d).csv"
```


## Actions gérées par le contrôleur de ressources

Les requêtes et URI de chaque api est représentée dans le tableau ci-dessous.

| Requête   | URI                      | Action 	                              |
|-----------|--------------------------|---------------------------------------|
| GET       | /api/objets              | renvoie la liste des objets           |
| GET       | /api/objets/{id}         | renvoie l'objet {id}                  |
| POST 	    | /api/objets              | sauve un nouvel objet                 |
| PUT/PATCH | /api/objets/{id}         | met à jour l'objet {id}               |
| DELETE 	  | /api/objets/{id}         | supprime l'objet {id}                 |
| POST      | /api/objets/mass-store   | crée plusieurs objets en une requête  |
| PUT/PATCH | /api/objets/mass-update  | met à jour plusieurs objets à la fois |
| DELETE    | /api/objets/mass-destroy | supprime plusieurs objets à la fois   |

Les champs à fournir sont ceux décrits dans le [modèle de données](model.fr.md).

Pour voir les fonctions avancées de filtres : voir la page [API avancée (filtres)](apifilters.fr.md)

## Droits d'accès

Il faut s'identifier avec un utilisateur de l'application Mercator pour pouvoir accéder aux API.
Cet utilisateur doit disposer d'un rôle dans Mercator qui lui permet d'accéder / modifier les objets
accédés par l'API.

Lorsque l'authentification réussit, l'API envoie un "access_token" qui doit être passé dans
l'entête "Authorization" de la requête de l'API.

📌 Des exemples de connections sont présentés dans le chapitre [Exemples.](#exemples)

## Liaison entre les objets

Les objets de la cartographie peuvent faire référence à d'autres objets. Par exemple, nous pouvons lier un processus à
une application. Supposons que nous ayons un "processus" qui utilise deux applications "app1" et "app2". Pour ce faire,
nous suivons ces étapes :

- Étape 1 : Assurez-vous d'avoir l'application_id pour les applications que vous souhaitez lier.

```
{
  "id": 201,
  "name": "app1",
  "description": "desc1"
}
{
  "id": 202,
  "name": "app2",
  "description": "desc2"
}
```

- Étape 2 : Liez le processus aux applications. Soit avec une mise à jour, soit avec un enregistrement, nous pouvons
  ajouter :

```
{
  "id": 101,
  "name": "processus",
  "application_id[]": [201, 202]
}
```

Les noms de tous les champs supplémentaires
sont : ['actors', 'tasks', 'activities', 'entities', 'applications', 'informations', 'processes', 'databases', 'logical_servers', 'modules', 'domainesForestAds', 'servers', 'vlans', 'lans', 'mans', 'wans', 'operations', 'domains', 'applicationServices', 'certificates', 'peripherals', 'physicalServers', 'physicalRouters', 'networkSwitches', 'routers', 'physicalSwitches' ].

## Exemples

Voici quelques exemples d'utilisation de l'API avec différents langages :

### PHP

#### Authentification

```php
<?php
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://127.0.0.1:8000/api/login",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query(
            array("login" => "admin@admin.com",
                  "password" => "password")),
        CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "content-type: application/x-www-form-urlencoded",
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    $info = curl_getinfo($curl);

    curl_close($curl);

    if ($err) {
        set_error_handler($err);
    } else {
        if ($info['http_code'] == 200) {
            $access_token = json_decode($response)->access_token;

        } else {
            set_error_handler("Login to api faild status 403");
            error_log($responseInfo['http_code']);
            error_log("No login api status 403");

        }
    }

    var_dump($response);
```

#### Liste des utilisateurs

```php
<?php
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://127.0.0.1:8000/api/users",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => null, // here you can send parameters
        CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "Authorization: " . "Bearer" . " " . $access_token . "",
            "cache-control: no-cache",
            "content-type: application/json",
        ),
    ));


    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    var_dump($response);

```

#### Récupérer un utilisateur

```php
<?php
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://127.0.0.1:8000/api/users/1",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => null, // here you can send parameters
        CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "Authorization: " . "Bearer" . " " . $access_token . "",
            "cache-control: no-cache",
            "content-type: application/json",
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    var_dump($response);
```

#### Mettre à jour un utilisateur

```php
<?php
   $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://127.0.0.1:8000/api/users/8",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => http_build_query(
            array(
                'name' => 'Henri',
                'login' => 'henri@test.fr',
                'language' => 'fr',
                'roles[0]' => 1,
                'roles[1]' => 3,
                'granularity' => '3')
            ),
        CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "Authorization: " . "Bearer" . " " . $access_token . "",
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded",
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    var_dump($response);
```

### Python

Voici un exemple d'utilisation de l'API en Python

```python
#!/usr/bin/python3

import requests

vheaders = {}
vheaders['accept'] = 'application/json'
vheaders['content-type'] = 'application/x-www-form-urlencoded'
vheaders['cache-control'] = 'no-cache'

print("Login")
response = requests.post("http://127.0.0.1:8000/api/login",
    headers=vheaders,
    data= {'login':'admin@admin.com', 'password':'password'} )
print(response.status_code)

vheaders['Authorization'] = "Bearer " + response.json()['access_token']

print("Get workstations")
response = requests.get("http://127.0.0.1:8000/api/workstations", headers=vheaders)
data=response.json()
print(data)
print(response.status_code)

```

### bash

Voici un exemple d'utilisation de l'API en ligne de commande avec [CURL](https://curl.se/docs/manpage.html)
et [JQ](https://stedolan.github.io/jq/)

```
#!/usr/bin/bash

API_URL=http://127.0.0.1:8000/api
OBJECT=applications
OBJECT_ID=45

# valid login and password

data='{"login":"admin@admin.com","password":"password"}'

# Get a token after correct login

TOKEN=$(curl -s -d ${data} -H "Content-Type: application/json" ${API_URL}/login | jq -r .access_token)

# Récupération de l'objet
RESPONSE=$(curl -s -X GET "${API_URL}/${OBJECT}/${OBJECT_ID}" \
 -H "Authorization: Bearer ${TOKEN}" \
 -H "Accept: application/json")

echo "Objet récupéré: ${RESPONSE}"

# Mise à jour d'une valeur avec une requête PUT

RESPONSE=$(echo "$RESPONSE" | jq -c '.data')
RESPONSE=$(echo "$RESPONSE" | jq -r '.activities=[1]')

echo "Objet modifié: ${RESPONSE}"

curl -s -X PUT "${API_URL}/${OBJECT}/${OBJECT_ID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -H "cache-control: no-cache" \
  -d "$RESPONSE"

# Vérification de la mise à jour

UPDATED_OBJECT=$(curl -s -X GET "${API_URL}/${OBJECT}/${OBJECT_ID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

echo "Objet mis à jour: ${UPDATED_OBJECT}"

```

### PowerShell

Le script PowerShell ci-dessous montre comment s’authentifier auprès de l’API et récupérer la liste des serveurs
logiques.

#### Étape 1 — Authentification et obtention du jeton d’accès

```powershell
# Définir l’URL d’authentification et les identifiants
$loginUri = "http://127.0.0.1:8000/api/login"
$loginBody = @{
    login = "admin@admin.com"
    password = "password"
}

# Envoyer la requête d’authentification
try {
    $loginResponse = Invoke-RestMethod -Uri $loginUri -Method Post -Body $loginBody -ContentType "application/x-www-form-urlencoded"
    $token = $loginResponse.access_token
    Write-Host "Jeton d’accès récupéré avec succès."
} catch {
    Write-Error "Échec de l’authentification : $_"
    return
}
```

#### Étape 2 — Utilisation du jeton pour interroger les serveurs logiques

```powershell
# Définir l’endpoint et les en-têtes d’autorisation
$endPoint = "logical-servers"
$apiUri = "https://127.0.0.1:8000/api/$endPoint"
$headers = @{
    'Authorization' = "Bearer $token"
    'Accept'        = 'application/json'
}

# Envoyer la requête GET
try {
    $servers = Invoke-RestMethod -Uri $apiUri -Method Get -Headers $headers
    $servers | Format-Table id, name, operating_system, description
} catch {
    Write-Error "Échec de la requête : $_"
}
```
