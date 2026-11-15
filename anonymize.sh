#!/bin/bash
# =============================================================================
#  Mercator – Script d'anonymisation de la base de données
#  Généré à partir du schéma réel – chaque colonne vérifiée
#
#  Usage  : ./anonymize.sh
#  Config : fichier .env dans le même répertoire (variables DB_*)
#
#  Fonctions de génération (MySQL natif, sans extension) :
#    rand_word(n)   → chaîne hex aléatoire de n caractères
#    rand_text()    → paragraphe de ~3 phrases (~150 chars)
#    rand_person()  → "Prénom Nom" fictif
#    rand_short()   → identifiant court aléatoire (~16 chars)
# =============================================================================

set -euo pipefail

# ---------------------------------------------------------------------------
# Chargement du .env (parsing robuste : seulement les variables DB_*)
# ---------------------------------------------------------------------------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${SCRIPT_DIR}/.env"

if [[ -f "$ENV_FILE" ]]; then
    echo "[INFO] Chargement de la configuration depuis $ENV_FILE"
    while IFS= read -r line; do
        line="${line%%#*}"
        line="${line%"${line##*[![:space:]]}"}"
        [[ -z "$line" ]] && continue
        [[ "$line" != *=* ]] && continue
        varname="${line%%=*}"
        [[ "$varname" =~ ^DB_ ]] || continue
        varvalue="${line#*=}"
        varvalue="${varvalue#\"}" ; varvalue="${varvalue%\"}"
        varvalue="${varvalue#\'}" ; varvalue="${varvalue%\'}"
        export "$varname"="$varvalue"
    done < "$ENV_FILE"
else
    echo "[WARN] Aucun fichier .env trouvé – utilisation des variables d'environnement existantes"
fi

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_DATABASE:-mercator}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD:-}"

if ! command -v mysql &>/dev/null; then
    echo "[ERREUR] Le client mysql n'est pas installé ou pas dans le PATH." >&2
    exit 1
fi

MYSQL_CMD="mysql -h${DB_HOST} -P${DB_PORT} -u${DB_USER}"
[[ -n "$DB_PASS" ]] && MYSQL_CMD="${MYSQL_CMD} -p${DB_PASS}"
MYSQL_CMD="${MYSQL_CMD} ${DB_NAME}"

echo "[INFO] Connexion à ${DB_HOST}:${DB_PORT}/${DB_NAME} en tant que ${DB_USER}..."
if ! $MYSQL_CMD -e "SELECT 1;" &>/dev/null; then
    echo "[ERREUR] Impossible de se connecter à la base. Vérifiez le fichier .env." >&2
    exit 1
fi
echo "[OK] Connexion établie."

# ---------------------------------------------------------------------------
# Création des fonctions utilitaires (supprimées en fin de script)
# ---------------------------------------------------------------------------
# Suppression garantie même en cas d'échec (set -e) : sinon les fonctions
# restent dans la base et se retrouvent dans les dumps de schéma
# (database/schema/*.sql) avec un DEFINER propre à cette machine.
drop_rand_functions() {
    $MYSQL_CMD -e "
DROP FUNCTION IF EXISTS rand_word;
DROP FUNCTION IF EXISTS rand_text;
DROP FUNCTION IF EXISTS rand_person;
DROP FUNCTION IF EXISTS rand_short;
" || true
}
trap drop_rand_functions EXIT

$MYSQL_CMD <<'SQL'
DROP FUNCTION IF EXISTS rand_word;
DROP FUNCTION IF EXISTS rand_text;
DROP FUNCTION IF EXISTS rand_person;
DROP FUNCTION IF EXISTS rand_short;

DELIMITER $$

CREATE FUNCTION rand_word(n INT)
RETURNS VARCHAR(255) DETERMINISTIC
BEGIN
    RETURN SUBSTRING(MD5(RAND()), 1, n);
END$$

CREATE FUNCTION rand_text()
RETURNS TEXT DETERMINISTIC
BEGIN
    RETURN CONCAT(
        'Lorem ', SUBSTRING(MD5(RAND()),1,8), ' ipsum ',   SUBSTRING(MD5(RAND()),1,12), ' dolor sit amet. ',
        'Sed ',   SUBSTRING(MD5(RAND()),1,6), ' ut ',      SUBSTRING(MD5(RAND()),1,10), ' consequat arcu. ',
        'Nulla ', SUBSTRING(MD5(RAND()),1,8), ' finibus ', SUBSTRING(MD5(RAND()),1,8),  ' purus eu felis.'
    );
END$$

CREATE FUNCTION rand_person()
RETURNS VARCHAR(100) DETERMINISTIC
BEGIN
    DECLARE prenom VARCHAR(20);
    DECLARE nom    VARCHAR(20);
    SET prenom = ELT(1 + FLOOR(RAND() * 10),
        'Alice','Bruno','Claire','Denis','Emma',
        'Fabien','Gaëlle','Hugo','Inès','Jules');
    SET nom = ELT(1 + FLOOR(RAND() * 10),
        'Martin','Bernard','Dupont','Moreau','Simon',
        'Laurent','Lefebvre','Michel','Garcia','Roux');
    RETURN CONCAT(prenom, ' ', nom);
END$$

CREATE FUNCTION rand_short()
RETURNS VARCHAR(64) DETERMINISTIC
BEGIN
    RETURN CONCAT(SUBSTRING(MD5(RAND()),1,8), '-', SUBSTRING(MD5(RAND()),1,8));
END$$

DELIMITER ;
SQL

run_sql() {
    local label="$1"
    local sql="$2"
    echo "  → $label"
    $MYSQL_CMD -e "$sql"
}

# =============================================================================
echo ""
echo "============================================================"
echo "  AVERTISSEMENT : cette opération va écraser de façon"
echo "  irréversible toutes les données sensibles de la base :"
echo "  BASE : ${DB_NAME}  |  HÔTE : ${DB_HOST}"
echo "============================================================"
echo ""
read -r -p "Confirmer l'anonymisation ? [o/N] : " confirm
confirm="${confirm:-N}"
if [[ ! "$confirm" =~ ^[oO]$ ]]; then
    echo "[ANNULÉ] Aucune modification effectuée."
    exit 0
fi
echo ""
echo "  Début de l'anonymisation – $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# ---------------------------------------------------------------------------
# COUCHE MÉTIER
# ---------------------------------------------------------------------------

echo "[01] Macro-processus (macro_processuses)"
# Colonnes : id|name|description|io_elements|...|owner
run_sql "name, description, io_elements, owner" "
UPDATE macro_processuses SET
    name        = CONCAT('MacroProcessus_', id),
    description = rand_text(),
    io_elements = rand_text(),
    owner       = rand_person();
"

echo "[02] Processus (processes)"
# Colonnes : id|name|description|owner|in_out|...
run_sql "name, description, owner, in_out" "
UPDATE processes SET
    name        = CONCAT('Processus_', id),
    description = rand_text(),
    owner       = rand_person(),
    in_out      = rand_text();
"

echo "[03] Activités (activities)"
# Colonnes : id|name|description|drp|drp_link
run_sql "name, description, drp, drp_link" "
UPDATE activities SET
    name        = CONCAT('Activité_', id),
    description = rand_text(),
    drp         = rand_text(),
    drp_link    = CONCAT('https://docs.example.com/', rand_word(8));
"

echo "[04] Opérations (operations)"
# Colonnes : id|name|description|process_id
run_sql "name, description" "
UPDATE operations SET
    name        = CONCAT('Opération_', id),
    description = rand_text();
"

echo "[05] Tâches (tasks)"
# Colonnes : id|name|description
run_sql "name, description" "
UPDATE tasks SET
    name        = CONCAT('Tâche_', id),
    description = rand_text();
"

echo "[06] Acteurs (actors)"
# Colonnes : id|name|nature|type|contact
run_sql "name, contact" "
UPDATE actors SET
    name    = rand_person(),
    contact = CONCAT(rand_word(6), '@example.com');
"

# ---------------------------------------------------------------------------
# COUCHE APPLICATIVE
# ---------------------------------------------------------------------------

echo "[07] Applications (applications)"
# Colonnes : id|name|description|vendor|product|responsible|functional_referent|
#            users|editor|documentation|version|attributes
run_sql "name, description, responsible, functional_referent, users, editor, documentation, attributes" "
UPDATE applications SET
    name                = CONCAT('Application_', id),
    description         = rand_text(),
    responsible         = rand_person(),
    functional_referent = rand_person(),
    users               = rand_short(),
    editor              = rand_person(),
    documentation       = CONCAT('https://docs.example.com/', rand_word(8)),
    attributes          = rand_short();
"

echo "[08] Blocs applicatifs (application_blocks)"
# Colonnes : id|name|description|responsible
run_sql "name, description, responsible" "
UPDATE application_blocks SET
    name        = CONCAT('Bloc_', id),
    description = rand_text(),
    responsible = rand_person();
"

echo "[09] Services applicatifs (application_services)"
# Colonnes : id|description|exposition|name
run_sql "name, description, exposition" "
UPDATE application_services SET
    name        = CONCAT('Service_', id),
    description = rand_text(),
    exposition  = rand_short();
"

echo "[10] Modules applicatifs (application_modules)"
# Colonnes : id|name|description|vendor|product|version
run_sql "name, description" "
UPDATE application_modules SET
    name        = CONCAT('Module_', id),
    description = rand_text();
"

echo "[11] Flux applicatifs (application_flows)"
# Colonnes : id|name|nature|attributes|description
run_sql "name, description, attributes" "
UPDATE application_flows SET
    name        = CONCAT('Flux_', id),
    description = rand_text(),
    attributes  = rand_short();
"

echo "[12] Informations (information)"
# Colonnes : id|name|description|owner|administrator|storage|sensitivity|constraints|retention
run_sql "name, description, owner, administrator, storage, sensitivity, constraints, retention" "
UPDATE information SET
    name          = CONCAT('Information_', id),
    description   = rand_text(),
    owner         = rand_person(),
    administrator = rand_person(),
    storage       = CONCAT('/storage/', rand_word(8)),
    sensitivity   = rand_short(),
    constraints   = rand_text(),
    retention     = rand_short();
"

echo "[13] Traitements de données (data_processing)"
# Colonnes : id|name|legal_basis|description|responsible|purpose|categories|
#            data_source|recipients|transfert|retention|data_subject_rights|controls
run_sql "name, description, responsible, purpose, categories, data_source, recipients, transfert, retention, data_subject_rights, controls" "
UPDATE data_processing SET
    name               = CONCAT('Traitement_', id),
    description        = rand_text(),
    responsible        = rand_person(),
    purpose            = rand_text(),
    categories         = rand_short(),
    data_source        = rand_short(),
    recipients         = rand_person(),
    transfert          = rand_short(),
    retention          = rand_short(),
    data_subject_rights= rand_text(),
    controls           = rand_text();
"

# ---------------------------------------------------------------------------
# COUCHE INFRASTRUCTURE LOGIQUE
# ---------------------------------------------------------------------------

echo "[14] Serveurs logiques (logical_servers)"
# Colonnes : id|name|description|net_services|configuration|operating_system|
#            address_ip|cpu|memory|environment|attributes
run_sql "name, description, configuration, net_services, address_ip, attributes" "
UPDATE logical_servers SET
    name          = CONCAT('srv-log-', id),
    description   = rand_text(),
    configuration = rand_text(),
    net_services  = CONCAT(rand_word(6), ' ', rand_word(6)),
    address_ip    = CONCAT('10.0.', FLOOR(id/254), '.', (id MOD 254)+1),
    attributes    = rand_short();
"

echo "[15] Clusters (clusters)"
# Colonnes : id|name|type|attributes|description|address_ip
run_sql "name, description, address_ip, attributes" "
UPDATE clusters SET
    name        = CONCAT('cluster-', id),
    description = rand_text(),
    address_ip  = CONCAT('10.6.', FLOOR(id/254), '.', (id MOD 254)+1),
    attributes  = rand_short();
"

echo "[16] Containers (containers)"
# Colonnes : id|name|type|description
run_sql "name, description" "
UPDATE containers SET
    name        = CONCAT('container-', id),
    description = rand_text();
"

echo "[17] Bases de données (databases)"
# Colonnes : id|name|type|description|responsible|external
run_sql "name, description, responsible" "
UPDATE \`databases\` SET
    name        = CONCAT('db-', id),
    description = rand_text(),
    responsible = rand_person();
"

echo "[18] Flux logiques (logical_flows)"
# Colonnes : id|name|description|chain|interface|source_ip_range|dest_ip_range|
#            source_port|dest_port|users|schedule|action
run_sql "name, description, source_ip_range, dest_ip_range, chain, action" "
UPDATE logical_flows SET
    name            = CONCAT('flux-', id),
    description     = rand_text(),
    source_ip_range = CONCAT('10.0.', FLOOR(id/254), '.0/24'),
    dest_ip_range   = CONCAT('10.1.', FLOOR(id/254), '.0/24'),
    chain           = rand_short(),
    action          = rand_short();
"

echo "[19] Réseaux (networks)"
# Colonnes : id|name|description|protocol_type|responsible|responsible_sec
run_sql "name, description, responsible, responsible_sec" "
UPDATE networks SET
    name            = CONCAT('network-', id),
    description     = rand_text(),
    responsible     = rand_person(),
    responsible_sec = rand_person();
"

echo "[20] Sous-réseaux (subnetworks)"
# Colonnes : id|name|description|address|responsible_exp|default_gateway|zone
run_sql "name, description, address, responsible_exp, default_gateway" "
UPDATE subnetworks SET
    name            = CONCAT('subnet-', id),
    description     = rand_text(),
    address         = CONCAT('192.168.', id, '.0/24'),
    responsible_exp = rand_person(),
    default_gateway = CONCAT('192.168.', id, '.1');
"

echo "[21] VLANs (vlans)"
# Colonnes : id|name|description|vlan_id
run_sql "name, description" "
UPDATE vlans SET
    name        = CONCAT('vlan-', id),
    description = rand_text();
"

echo "[22] LANs (lans)"
# Colonnes : id|name|description
run_sql "name, description" "
UPDATE lans SET
    name        = CONCAT('lan-', id),
    description = rand_text();
"

echo "[23] MANs (mans)"
# Colonnes : id|name|description|parent_man_id
run_sql "name, description" "
UPDATE mans SET
    name        = CONCAT('man-', id),
    description = rand_text();
"

echo "[24] WANs (wans)"
# Colonnes : id|name  (pas de description dans le schéma)
run_sql "name" "
UPDATE wans SET
    name = CONCAT('wan-', id);
"

echo "[25] Passerelles (gateways)"
# Colonnes : id|name|description|ip|authentification
run_sql "name, description, ip, authentification" "
UPDATE gateways SET
    name             = CONCAT('gw-', id),
    description      = rand_text(),
    ip               = CONCAT('172.16.', FLOOR(id/254), '.', (id MOD 254)+1),
    authentification = rand_short();
"

echo "[26] Routeurs logiques (routers)"
# Colonnes : id|name|type|description|rules|ip_addresses
run_sql "name, description, rules, ip_addresses" "
UPDATE routers SET
    name        = CONCAT('router-', id),
    description = rand_text(),
    rules       = rand_text(),
    ip_addresses= CONCAT('172.17.', FLOOR(id/254), '.', (id MOD 254)+1);
"

echo "[27] Serveurs DNS (dnsservers)"
# Colonnes : id|name|description|address_ip
run_sql "name, description, address_ip" "
UPDATE dnsservers SET
    name        = CONCAT('dns-', id),
    description = rand_text(),
    address_ip  = CONCAT('10.3.0.', (id MOD 254)+1);
"

echo "[28] Serveurs DHCP (dhcp_servers)"
# Colonnes : id|name|description|address_ip
run_sql "name, description, address_ip" "
UPDATE dhcp_servers SET
    name        = CONCAT('dhcp-', id),
    description = rand_text(),
    address_ip  = CONCAT('10.3.1.', (id MOD 254)+1);
"

echo "[29] Network switches (network_switches)"
# Colonnes : id|name|ip|description
run_sql "name, description, ip" "
UPDATE network_switches SET
    name        = CONCAT('nsw-', id),
    description = rand_text(),
    ip          = CONCAT('10.7.0.', (id MOD 254)+1);
"

# ---------------------------------------------------------------------------
# COUCHE INFRASTRUCTURE PHYSIQUE
# ---------------------------------------------------------------------------

echo "[30] Serveurs physiques (physical_servers)"
# Colonnes : id|name|type|description|vendor|product|version|responsible|
#            configuration|address_ip|patching_group
run_sql "name, description, configuration, address_ip, responsible" "
UPDATE physical_servers SET
    name          = CONCAT('srv-phy-', id),
    description   = rand_text(),
    configuration = rand_text(),
    address_ip    = CONCAT('10.1.', FLOOR(id/254), '.', (id MOD 254)+1),
    responsible   = rand_person();
"

echo "[31] Commutateurs physiques (physical_switches)"
# Colonnes : id|name|type|description|vendor|product|version
run_sql "name, description" "
UPDATE physical_switches SET
    name        = CONCAT('psw-', id),
    description = rand_text();
"

echo "[32] Routeurs physiques (physical_routers)"
# Colonnes : id|name|description|vendor|product|version|type
run_sql "name, description" "
UPDATE physical_routers SET
    name        = CONCAT('pr-', id),
    description = rand_text();
"

echo "[33] Équipements de sécurité physiques (physical_security_devices)"
# Colonnes : id|name|type|attributes|description|address_ip
run_sql "name, description, address_ip, attributes" "
UPDATE physical_security_devices SET
    name        = CONCAT('psd-', id),
    description = rand_text(),
    address_ip  = CONCAT('10.8.0.', (id MOD 254)+1),
    attributes  = rand_short();
"

echo "[34] Équipements de sécurité logiques (security_devices)"
# Colonnes : id|name|type|attributes|description|vendor|product|version|address_ip
run_sql "name, description, address_ip, attributes" "
UPDATE security_devices SET
    name        = CONCAT('sd-', id),
    description = rand_text(),
    address_ip  = CONCAT('10.9.0.', (id MOD 254)+1),
    attributes  = rand_short();
"

echo "[35] Périphériques (peripherals)"
# Colonnes : id|name|type|description|vendor|product|version|responsible|
#            address_ip|domain|provider_id
run_sql "name, description, address_ip, responsible, domain" "
UPDATE peripherals SET
    name        = CONCAT('periph-', id),
    description = rand_text(),
    address_ip  = CONCAT('10.5.0.', (id MOD 254)+1),
    responsible = rand_person(),
    domain      = CONCAT('domain-', rand_word(6));
"

echo "[36] Postes de travail (workstations)"
# Colonnes : id|name|description|address_ip|operating_system|cpu|memory|
#            user_id|other_user|serial_number|mac_address|manufacturer|model
run_sql "name, description, address_ip, other_user, serial_number, mac_address" "
UPDATE workstations SET
    name          = CONCAT('ws-', id),
    description   = rand_text(),
    address_ip    = CONCAT('10.2.', FLOOR(id/254), '.', (id MOD 254)+1),
    other_user    = rand_person(),
    serial_number = rand_word(12),
    mac_address   = CONCAT(
        rand_word(2),':',rand_word(2),':',rand_word(2),':',
        rand_word(2),':',rand_word(2),':',rand_word(2));
"

echo "[37] Téléphones (phones)"
# Colonnes : id|name|type|description|vendor|product|version|address_ip
run_sql "name, description, address_ip" "
UPDATE phones SET
    name        = CONCAT('phone-', id),
    description = rand_text(),
    address_ip  = CONCAT('10.4.0.', (id MOD 254)+1);
"

echo "[38] Équipements WiFi (wifi_terminals)"
# Colonnes : id|name|type|description|vendor|product|version|address_ip
run_sql "name, description, address_ip" "
UPDATE wifi_terminals SET
    name        = CONCAT('wifi-', id),
    description = rand_text(),
    address_ip  = CONCAT('10.10.0.', (id MOD 254)+1);
"

echo "[39] Stockage (storage_devices)"
# Colonnes : id|name|type|description|vendor|product|version|address_ip
run_sql "name, description, address_ip" "
UPDATE storage_devices SET
    name        = CONCAT('storage-', id),
    description = rand_text(),
    address_ip  = CONCAT('10.11.0.', (id MOD 254)+1);
"

echo "[40] Baies (bays)"
# Colonnes : id|name|description|room_id
run_sql "name, description" "
UPDATE bays SET
    name        = CONCAT('baie-', id),
    description = rand_text();
"

echo "[41] Bâtiments (buildings)"
# Colonnes : id|name|type|attributes|description
run_sql "name, description, attributes" "
UPDATE buildings SET
    name        = CONCAT('bâtiment-', id),
    description = rand_text(),
    attributes  = rand_short();
"

echo "[42] Sites (sites)"
# Colonnes : id|name|description
run_sql "name, description" "
UPDATE sites SET
    name        = CONCAT('site-', id),
    description = rand_text();
"

# ---------------------------------------------------------------------------
# COUCHE ORGANISATIONS / ENTITÉS
# ---------------------------------------------------------------------------

echo "[43] Entités (entities)"
# Colonnes : id|name|security_level|contact_point|description|attributes|
#            reference|external_ref_id
run_sql "name, description, security_level, contact_point, reference, external_ref_id, attributes" "
UPDATE entities SET
    name            = CONCAT('Entité_', id),
    description     = rand_text(),
    security_level  = rand_short(),
    contact_point   = rand_person(),
    reference       = CONCAT('REF-', rand_word(6)),
    external_ref_id = CONCAT('EXT-', rand_word(8)),
    attributes      = rand_short();
"

echo "[44] Entités externes connectées (external_connected_entities)"
# Colonnes : id|name|description|security|src|src_desc|dest|dest_desc|contacts
run_sql "name, description, security, contacts, src, src_desc, dest, dest_desc" "
UPDATE external_connected_entities SET
    name        = CONCAT('Ext_', id),
    description = rand_text(),
    security    = rand_short(),
    contacts    = CONCAT(rand_person(), ' <', rand_word(6), '@example.com>'),
    src         = CONCAT('10.0.', FLOOR(id/254), '.', (id MOD 254)+1),
    src_desc    = CONCAT('Source-', rand_word(8)),
    dest        = CONCAT('10.1.', FLOOR(id/254), '.', (id MOD 254)+1),
    dest_desc   = CONCAT('Dest-', rand_word(8));
"

echo "[45] Domaines AD (domains)"
# Colonnes : id|name|description|relation_inter_domaine
run_sql "name, description, relation_inter_domaine" "
UPDATE domains SET
    name                  = CONCAT('domain-', id),
    description           = rand_text(),
    relation_inter_domaine= rand_short();
"

echo "[46] Forêts AD (forest_ads)"
# Colonnes : id|name|description
run_sql "name, description" "
UPDATE forest_ads SET
    name        = CONCAT('forest-', id),
    description = rand_text();
"

echo "[47] Annuaires (annuaires)"
# Colonnes : id|name|description|solution
run_sql "name, description, solution" "
UPDATE annuaires SET
    name        = CONCAT('annuaire-', id),
    description = rand_text(),
    solution    = rand_short();
"

echo "[48] Zones d'administration (zone_admins)"
# Colonnes : id|name|description
run_sql "name, description" "
UPDATE zone_admins SET
    name        = CONCAT('zone-', id),
    description = rand_text();
"

# ---------------------------------------------------------------------------
# COUCHE SÉCURITÉ / CONFORMITÉ
# ---------------------------------------------------------------------------

echo "[49] Contrôles de sécurité (security_controls)"
# Colonnes : id|name|description
run_sql "name, description" "
UPDATE security_controls SET
    name        = CONCAT('Contrôle_', id),
    description = rand_text();
"

echo "[50] Certificats (certificates)"
# Colonnes : id|name|type|description
run_sql "name, description" "
UPDATE certificates SET
    name        = CONCAT('cert-', id),
    description = rand_text();
"

echo "[51] Relations (relations)"
# Colonnes : id|name|type|description|attributes|reference|responsible|comments
run_sql "name, description, attributes, reference, responsible, comments" "
UPDATE relations SET
    name        = CONCAT('relation-', id),
    description = rand_text(),
    attributes  = rand_short(),
    reference   = CONCAT('REL-', rand_word(6)),
    responsible = rand_person(),
    comments    = rand_text();
"

echo "[52] Sauvegardes (backups)"
# Colonnes : id|logical_server_id|storage_device_id|backup_frequency|backup_cycle|backup_retention
# (pas de champs libres texte à anonymiser, on passe)
echo "  → pas de champ textuel à anonymiser"

# ---------------------------------------------------------------------------
# COUCHE UTILISATEURS / ADMIN
# ---------------------------------------------------------------------------

echo "[53] Utilisateurs (users)"
# Colonnes : id|login|name|email|password|remember_token
run_sql "login, name, email, password, remember_token" "
UPDATE users SET
    login          = CONCAT('user_', id),
    name           = rand_person(),
    email          = CONCAT('user_', id, '@example.com'),
    password       = '\$2y\$10\$anonymizedpasswordhashXXXXXXXXXXXXXXXXXXXXXXXXXXX',
    remember_token = rand_word(16);
"

echo "[54] Administrateurs (admin_users)"
# Colonnes : id|user_id|firstname|lastname|type|attributes|description
run_sql "firstname, lastname, description, attributes" "
UPDATE admin_users SET
    firstname   = ELT(1 + FLOOR(RAND() * 5), 'Alice','Bruno','Claire','Denis','Emma'),
    lastname    = ELT(1 + FLOOR(RAND() * 5), 'Martin','Bernard','Dupont','Moreau','Simon'),
    description = rand_text(),
    attributes  = rand_short();
"

echo "[55] Requêtes sauvegardées (saved_queries)"
# Colonnes : id|name|description|query|is_public|user_id
run_sql "name, description" "
UPDATE saved_queries SET
    name        = CONCAT('query-', id),
    description = rand_text();
"

echo "[56] Graphes (graphs)"
# Colonnes : id|class|name|type|content
run_sql "name, content" "
UPDATE graphs SET
    name    = CONCAT('graph-', id),
    content = rand_text();
"

# ---------------------------------------------------------------------------
# NETTOYAGE
# ---------------------------------------------------------------------------
echo ""
echo "[57] Purge des logs d'audit (audit_logs)"
run_sql "TRUNCATE" "TRUNCATE TABLE audit_logs;"

echo "[58] Suppression des tokens OAuth"
run_sql "oauth_access_tokens" "TRUNCATE TABLE oauth_access_tokens;"
run_sql "oauth_auth_codes"    "TRUNCATE TABLE oauth_auth_codes;"
run_sql "oauth_refresh_tokens" "TRUNCATE TABLE oauth_refresh_tokens;"
run_sql "oauth_device_codes"  "TRUNCATE TABLE oauth_device_codes;"

echo "[59] Suppression des fonctions temporaires"
drop_rand_functions

echo ""
echo "============================================================"
echo "  Anonymisation terminée – $(date '+%Y-%m-%d %H:%M:%S')"
echo "============================================================"
