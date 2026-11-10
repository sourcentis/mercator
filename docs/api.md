# API

Cartography can be modified or updated via a REST API.

A REST API ([Representational State Transfer](https://fr.wikipedia.org/wiki/Representational_state_transfer))
is an application programming interface that respects the constraints of the REST
architecture and enables interaction with RESTful web services.

### Install the API on Mercator

To install the API in Mercator, you need to install Passport by running this command:

```bash
php artisan passport:install
```

- The Docker environment supports this functionality natively, via the entrypoint.

## 📚 Mapping APIs

### Descriptions
Each entity in the mapping data model exposes a REST endpoint following Laravel conventions.  
Routes are defined in: `/routes/api.php`.

---

#### **GET /api/{resource}**  
Returns a **collection** of `{resource}` objects.

**Response:**  
- Laravel `ResourceCollection`  
- Includes only the main attributes (simplified view)

**Example:**  
`GET /api/processes`

---

#### **GET /api/{resource}/{id}**  
Returns the full **Resource** for the specified ID.

**Parameters:**  
- `id` — unique identifier of the object

**Response:**  
- Laravel `Resource`  
- Includes all attributes of the object

**Example:**  
`GET /api/processes/1`

---

#### 📌 Notes
- List endpoints return a **ResourceCollection**, which exposes a partial view.  
- To retrieve the full representation of an object, use its individual endpoint.  
- To view the data model associated with an API, click its name in the interface.

### Endpoints for GDPR

- [<img src="/mercator/images/get.png" width="30"> /api/data-processings](./model.md#register)
- [<img src="/mercator/images/get.png" width="30"> /api/security-controls](./model.md#security-measures)

### Endpoints for Ecosystem

- [<img src="/mercator/images/get.png" width="30"> /api/entities](./model.md#entities)
- [<img src="/mercator/images/get.png" width="30"> /api/relations](./model.md#relationships)

### Endpoints for Information system business

- [<img src="/mercator/images/get.png" width="30"> /api/macro-processuses](./model.md#macro-processes)
- [<img src="/mercator/images/get.png" width="30"> /api/processes](./model.md#processes)
- [<img src="/mercator/images/get.png" width="30"> /api/activities](./model.md#activities)
- [<img src="/mercator/images/get.png" width="30"> /api/operations](./model.md#operations)
- [<img src="/mercator/images/get.png" width="30"> /api/tasks](./model.md#tasks)
- [<img src="/mercator/images/get.png" width="30"> /api/actors](./model.md#actors)
- [<img src="/mercator/images/get.png" width="30"> /api/information](./model.md#information)

### Endpoints for Application

- [<img src="/mercator/images/get.png" width="30"> /api/application-blocks](./model.md#applications-blocks)
- [<img src="/mercator/images/get.png" width="30"> /api/applications](./model.md#applications)
- [<img src="/mercator/images/get.png" width="30"> /api/application-services](./model.md#applications-services)
- [<img src="/mercator/images/get.png" width="30"> /api/application-modules](./model.md#application-modules)
- [<img src="/mercator/images/get.png" width="30"> /api/databases](./model.md#databases)
- [<img src="/mercator/images/get.png" width="30"> /api/application-flows](./model.md#application-flows)

### Endpoints for Administration view

- [<img src="/mercator/images/get.png" width="30"> /api/zone-admins](./model.md#administration-areas)
- [<img src="/mercator/images/get.png" width="30"> /api/annuaires](./model.md#administration-directory-services)
- [<img src="/mercator/images/get.png" width="30"> /api/forest-ads](./model.md#active-directory-forests-ldap-tree-structure)
- [<img src="/mercator/images/get.png" width="30"> /api/domains](./model.md#active-directory-domains-ldap)
- [<img src="/mercator/images/get.png" width="30"> /api/admin-users](./model.md#users)

### Endpoints for Logical infrastructure

- [<img src="/mercator/images/get.png" width="30"> /api/networks](./model.md#networks)
- [<img src="/mercator/images/get.png" width="30"> /api/subnetworks](./model.md#subnetworks)
- [<img src="/mercator/images/get.png" width="30"> /api/gateways](./model.md#external-input-gateways)
- [<img src="/mercator/images/get.png" width="30"> /api/external-connected-entities](./model.md#connected-external-entities)
- [<img src="/mercator/images/get.png" width="30"> /api/network-switches](./model.md#network-switches)
- [<img src="/mercator/images/get.png" width="30"> /api/routers](./model.md#logical-routers)
- [<img src="/mercator/images/get.png" width="30"> /api/security-devices](model.md#security-devices)
- [<img src="/mercator/images/get.png" width="30"> /api/dhcp-servers *(usage not recommended)*](./model.md#dhcp-servers)
- [<img src="/mercator/images/get.png" width="30"> /api/dnsservers *(usage not recommended)*](./model.md#dns-servers)
- [<img src="/mercator/images/get.png" width="30"> /api/clusters](./model.md#clusters)
- [<img src="/mercator/images/get.png" width="30"> /api/logical-servers](./model.md#logical-servers)
- [<img src="/mercator/images/get.png" width="30"> /api/backups](./model.md#backup-plans) ==> linked to logical-servers and storage-devices
- [<img src="/mercator/images/get.png" width="30"> /api/containers](./model.md#containers)
- [<img src="/mercator/images/get.png" width="30"> /api/logical-flows](./model.md#logical-flows)
- [<img src="/mercator/images/get.png" width="30"> /api/certificates](./model.md#certificates)
- [<img src="/mercator/images/get.png" width="30"> /api/vlans](./model.md#vlans)

### Endpoints for Physical infrastructure

- [<img src="/mercator/images/get.png" width="30"> /api/sites](./model.md#sites)
- [<img src="/mercator/images/get.png" width="30"> /api/buildings](./model.md#buildings-rooms)
- [<img src="/mercator/images/get.png" width="30"> /api/bays](./model.md#racks)
- [<img src="/mercator/images/get.png" width="30"> /api/zones](./model.md#security-zones)
- [<img src="/mercator/images/get.png" width="30"> /api/physical-servers](./model.md#physical-servers)
- [<img src="/mercator/images/get.png" width="30"> /api/workstations](./model.md#workstations)
- [<img src="/mercator/images/get.png" width="30"> /api/storage-devices](./model.md#storage-infrastructures) (recommended for backups)
- [<img src="/mercator/images/get.png" width="30"> /api/peripherals](./model.md#peripherals)
- [<img src="/mercator/images/get.png" width="30"> /api/phones](./model.md#phones)
- [<img src="/mercator/images/get.png" width="30"> /api/physical-switches](./model.md#physical-switches)
- [<img src="/mercator/images/get.png" width="30"> /api/physical-routers](./model.md#physical-routers)
- [<img src="/mercator/images/get.png" width="30"> /api/wifi-terminals](./model.md#wifi-terminals)
- [<img src="/mercator/images/get.png" width="30"> /api/physical-security-devices](./model.md#physical-security-devices)
- [<img src="/mercator/images/get.png" width="30"> /api/wans](./model.md#wans)
- [<img src="/mercator/images/get.png" width="30"> /api/mans](./model.md#mans)
- [<img src="/mercator/images/get.png" width="30"> /api/lans](./model.md#lans)
- [<img src="/mercator/images/get.png" width="30"> /api/physical-links](./model.md#physical-links)

## Configuration APIs

- [<img src="/mercator/images/get.png" width="30"> /api/users](./model.md#users)
- [<img src="/mercator/images/get.png" width="30"> /api/roles](./model.md#roles)

The role contains permissions: *permission_roles*.
*permission_roles* does not exist as an API endpoint. It is a pivot table internally managed by Laravel.
To retrieve the role↔permission associations, you must use **/api/roles/{id}?include=permissions** — the permissions linked to each role will be embedded in the role’s response.
The list of permissions and their corresponding {id} values is available via the **/api/permissions** API.

- [<img src="/mercator/images/get.png" width="30"> /api/perimeters](./model.md#perimeters)

Perimeters (`name`: 2–32 characters, unique) require the **configure** permission for every action, like the administration screen. The default perimeter (id 1) and any perimeter still referenced by a role cannot be deleted (`422`). **/api/perimeters/{id}** also returns the ids of the roles attached to the perimeter (`roles`).

- [<img src="/mercator/images/get.png" width="30"> /api/cartographers](./model.md#cartography)
- [<img src="/mercator/images/get.png" width="30"> /api/graphs](./model.md#tools)

Graphs hold both maps (`class` = 1) and BPMN diagrams (`class` = 2). The `content` field is the raw XML of the diagram and is stored as is (it is not stripped of its tags like other text fields).

- [<img src="/mercator/images/get.png" width="30"> /api/permissions](./model.md#permissions) `READ ONLY`
- [<img src="/mercator/images/get.png" width="30"> /api/documents](./model.md#documents)

The unique feature of the endpoint **documents** is that it allows you to either upload or download a document.

#### Example for a document in Mercator:

- Add a document in the database
```bash
RESPONSE=$(http_call -X POST "$API/api/documents" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json" \
    -F "file=@./rapport.pdf")

# Display clean JSON (jq uncode, but isn't yet in $() )
echo "$RESPONSE" | jq .

DOC_ID=$(echo "$RESPONSE" | jq -r '.id // empty' 2>/dev/null)
```

- Example of downloading a document from mercator:
```bash
 OUTFILE="./downloaded_${DOC_ID}.pdf"
    curl -s -X GET "$API/api/documents/$DOC_ID/download" \
        -H "Authorization: Bearer $TOKEN" \
        -H "Accept: application/octet-stream" \
        -o "$OUTFILE" \
        -w "HTTP %{http_code}\n"
```
---

## Tools APIs

### Maps and BPMN API

TBD

## Queries API

- [<img src="/mercator/images/get.png" width="30"> /api/queries](./model.md#queries)
- <img src="/mercator/images/get.png" width="30"> /api/queries/***id***

### Requests can also be executed via the API
- <img src="/mercator/images/get.png" width="30"> /api/queries/execute/1  
    - The **query ID** must be provided.  
    - The query must be of **list type**.
    - The result is returned as a **CSV** file.

### A query can also be executed without saving it
- **POST** /api/queries/execute
    - The DSL is sent as **JSON** in the request body.
    - Requires the `query_create` or `query_edit` permission, plus the **show** permission on every object read by the query.
    - The result is returned as **JSON**: `columns`, `rows` and `meta` for a list, `nodes`, `edges` and `meta` for a graph.

```bash
curl -X POST https://mercator/api/queries/execute \
     -H "Authorization: Bearer <token>" \
     -H "Content-Type: application/json" \
     -d '{"from": "applications", "fields": ["name", "logical_servers.name"], "filters": [{"field": "name", "operator": "like", "value": "A%"}], "output": "list", "limit": 100}'
```

---

## Reports APIs

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

### Excel reports can be extracted as csv. 

- Example for report extracted with excel format
```bash
curl -s -X GET http://localhost:8081/api/report/cve \
    -H "Authorization: Bearer ${TOKEN}" \
    -H "Accept: application/octet-stream" \
    -o "rapport_cve_$(date +%Y%m%d).xlsx"
```
- Example for a report extracted with CSV format
```bash
curl -s -X GET "http://localhost:8081/api/report/cve?format=csv" \
     -H "Authorization: Bearer ${TOKEN}" \
     -H "Accept: text/csv" \
     -o "rapport_cve_$(date +%Y%m%d).csv"
```

## Actions managed by the resource controller

Requests and URIs for each API are shown in the table below.

| Request   | URI                       | Action                                       |
| --------- | ------------------------- | -------------------------------------------- |
| GET       | /api/objects              | returns the list of objects                  |
| GET       | /api/objects/{id}         | returns the object with ID `{id}`            |
| POST      | /api/objects              | creates a new object                         |
| PUT/PATCH | /api/objects/{id}         | updates the object with ID `{id}`            |
| DELETE    | /api/objects/{id}         | deletes the object with ID `{id}`            |
| POST      | /api/objects/mass-store   | creates multiple objects in a single request |
| PUT/PATCH | /api/objects/mass-update  | updates multiple objects at once             |
| DELETE    | /api/objects/mass-destroy | deletes multiple objects at once             |


The fields to be supplied are those described in the [data model](model.md).

To get access to advanced filters, feel free to check the related page: [API Advanced (filters)](./apifilters.md)

## Access rights

To access the APIs, you must identify yourself as a Mercator application user.
This user must have a role in Mercator that allows him/her to access/modify the objects
objects accessed via the API.

When authentication is successful, the API sends an "access_token", which must be passed in the "Authorization" header of the API request.

📌 Examples of connections are presented in the [Examples](#examples) section.

## Linking objects

Mapping objects can refer to other objects. For example, we can link a process to an application. Suppose we have a
‘process’ that uses two applications, ‘app1’ and ‘app2’. To do this, we follow these steps:

- Step 1: Ensure you have the application_id for the applications you want to link.

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

- Step 2: Link the process to the applications. Either with an update or a store, we can add:

```
{
  "id": 101,
  "name": "process",
  "applications[]": [201, 202]
}
```

The names of all extra fields
are: ['actors', 'tasks', 'activities', 'entities', 'applications', 'informations', 'processes', 'databases', 'logical_servers', 'modules', 'domainesForestAds', 'servers', 'vlans', 'lans', 'mans', 'wans', 'operations', 'domaines', 'applicationServices', 'certificates', 'peripherals', 'physicalServers', 'networkSwitches', 'physicalSwitches', 'physicalRouters']

---

## Examples

Please find below few examples with different languages

### PHP
Here are a few examples of how to use the API with PHP:

#### Authentication

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
            set_error_handler("Login to API failed status 403");
            error_log($responseInfo['http_code']);
            error_log("No login API status 403");

        }
    }

    var_dump($response);
```

#### Users list

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

#### Get a user

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

#### Update a user

```php
<?php
   $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://127.0.0.1:8000/api/users/8",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_POST => true,
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
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    var_dump($response);
```

### Python

Here's an example of how to use the API in Python :

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
print(response.json())
print(response.status_code)

```

### Bash

Here's an example of using the API on the command line with [CURL](https://curl.se/docs/manpage.html)
and [JQ](https://stedolan.github.io/jq/)

```
# valid login and password
data='{"login":"admin@admin.com","password":"password"}'

# get a token after correct login
token=$(curl -s -d ${data} -H "Content-Type: application/json" http://localhost:8000/api/login | jq -r .access_token)

# query users and decode JSON data with JQ.
curl -s -H "Content-Type: application/json" -H "Authorization: Bearer ${token}" "http://127.0.0.1:8000/api/users" | jq .

```

Other Bash example

```
#!/usr/bin/bash

API_URL=http://127.0.0.1:8000/api

# valid login and password

data='{"login":"admin@admin.com","password":"password"}'

# Get a token after correct login

TOKEN=$(curl -s -d ${data} -H "Content-Type: application/json" ${API_URL}/login | jq -r .access_token)

# Récupération de l'objet

OBJECT_ID=10

RESPONSE=$(curl -s -X GET "${API_URL}/logical-servers/${OBJECT_ID}" \
 -H "Authorization: Bearer ${TOKEN}" \
 -H "Accept: application/json")

echo "Objet récupéré: ${RESPONSE}"

# Mise à jour d'une valeur avec une requête PUT

RESPONSE=$(echo "$RESPONSE" | jq -c '.data')
RESPONSE=$(echo "$RESPONSE" | jq -r '.operating_system="Linux"')

echo "Objet modifié: ${RESPONSE}"

curl -s -X PUT "${API_URL}/logical-servers/${OBJECT_ID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -H "cache-control: no-cache" \
  -d "$RESPONSE"

# Vérification de la mise à jour

UPDATED_OBJECT=$(curl -s -X GET "${API_URL}/logical-servers/${OBJECT_ID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

echo "Objet mis à jour: ${UPDATED_OBJECT}"
```

### PowerShell

The following **PowerShell** script demonstrates how to authenticate with the API and retrieve the list of logical
servers.

#### Step 1 — Authenticate and obtain an access token

```powershell
# Define the login endpoint and credentials
$loginUri = "http://127.0.0.1:8000/api/login"
$loginBody = @{
    login = "admin@admin.com"
    password = "password"
}

# Send the authentication request
try {
    $loginResponse = Invoke-RestMethod -Uri $loginUri -Method Post -Body $loginBody -ContentType "application/x-www-form-urlencoded"
    $token = $loginResponse.access_token
    Write-Host "Access token successfully retrieved."
} catch {
    Write-Error "Authentication failed: $_"
    return
}
```

#### Step 2 — Use the token to query logical servers

```powershell
# Define the endpoint and authorization headers
$endPoint = "logical-servers"
$apiUri = "http://127.0.0.1:8000/api/$endPoint"
$headers = @{
    'Authorization' = "Bearer $token"
    'Accept'        = 'application/json'
}

# Send the GET request
try {
    $servers = Invoke-RestMethod -Uri $apiUri -Method Get -Headers $headers
    $servers | Format-Table id, name, operating_system, description
} catch {
    Write-Error "Request failed: $_"
}
```
