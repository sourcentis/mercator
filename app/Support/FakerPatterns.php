<?php

namespace App\Support;

use Faker\Provider\Base;

/**
 * Catalogue d'expressions régulières utilisées par les factories
 * (`app/Factories/*Factory.php`) pour générer des données de test plus
 * plausibles que `faker->word()` / `faker->name()` / `faker->randomNumber()` :
 * un routeur ou un switch n'a pas un nom de famille pour identifiant, une
 * version n'est pas un mot du dictionnaire, une adresse MAC n'est pas une
 * adresse postale.
 *
 * Consommé via `$this->faker->regexify(FakerPatterns::XXX)`. Le moteur de
 * regexify de Faker ne supporte qu'un sous-ensemble du regex (classes de
 * caractères avec bornes, `\d`/`\w`, quantificateurs `{n,m}`/`?`/`*`/`+`,
 * alternatives `(a|b|c)` — pas de lookaround ni de rétro-références), d'où
 * des motifs volontairement simples. En particulier, les groupes imbriqués
 * du type `(a|(b|c))` ne sont PAS supportés : le remplacement des alternatives
 * se fait par une seule passe regex non récursive sur `\(...\)`, qui casse
 * silencieusement dès qu'un groupe en contient un autre (sortie du type
 * "b|c)" au lieu d'une seule alternative). Toujours écrire les alternatives
 * à plat, jamais imbriquées.
 *
 * @see Base::regexify()
 */
class FakerPatterns
{
    /**
     * Identifiant d'équipement technique (routeur, switch, serveur, baie de
     * stockage, etc.) : préfixe-site-numéro, ex. "sw-nancy-04", "fw-lyon-12".
     */
    public const HOSTNAME = '[a-z]{2,4}-[a-z]{3,6}-\d{2,3}';

    /**
     * Nom de segment réseau (LAN/WAN/MAN/VLAN/DMZ), ex. "LAN-PARI-12".
     */
    public const NETWORK_SEGMENT = '(LAN|WAN|MAN|VLAN|DMZ)-[A-Z]{2,4}-\d{1,3}';

    /**
     * Nom d'entité organisationnelle / métier (bloc applicatif, entité,
     * fournisseur, ...), ex. "Kelis Solutions".
     */
    public const ORG_NAME = '[A-Z][a-z]{3,8} (Solutions|Systems|Group|Consulting|Technologies|Services)';

    /**
     * Nom de système applicatif (application, base de données), ex.
     * "crm-billing-prod".
     */
    public const SYSTEM_NAME = '[a-z]{3,6}-(core|portal|crm|erp|billing|auth|catalog|gateway)-(prod|staging|dev)';

    /**
     * Numéro de version sémantique, ex. "12.4.187".
     */
    public const SEMVER = '[1-9]\d?\.\d{1,2}\.\d{1,3}';

    /**
     * Adresse MAC, ex. "3F:A1:0C:9E:22:7B".
     */
    public const MAC_ADDRESS = '[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}';

    /**
     * Identifiant de VLAN plausible (2-3 chiffres, hors plage réservée).
     */
    public const VLAN_ID = '[1-9]\d{1,2}';

    /**
     * Port réseau parmi les ports standards les plus courants.
     */
    public const PORT = '(21|22|23|25|53|80|110|143|389|443|445|636|993|995|1433|3306|3389|5432|8080|8443)';

    /**
     * Numéro de série d'équipement, ex. "AX93K48271".
     */
    public const SERIAL_NUMBER = '[A-Z]{2}\d{2}[A-Z]\d{5}';

    /**
     * Référence documentaire / de commande générique, ex. "REF-482913".
     */
    public const REFERENCE_CODE = 'REF-\d{6}';

    /**
     * Nom de domaine Active Directory, ex. "corp.local".
     */
    public const DOMAIN_FQDN = '[a-z]{3,6}\.(local|corp|lan)';

    /**
     * Nom de bâtiment, ex. "Batiment C 42". Le numéro (1 à 999) porte l'espace
     * des noms à ~26 000 : avec la seule lettre, deux bâtiments d'un même test
     * recevaient le même nom une fois sur 26 (échec sur la règle d'unicité).
     */
    public const BUILDING_NAME = 'Batiment [A-Z] [1-9][0-9]{0,2}';

    /**
     * Nom de baie / rack, ex. "Baie 14".
     */
    public const BAY_NAME = 'Baie \d{1,2}';

    /**
     * Plage de sous-réseau privée en notation CIDR, ex. "172.16.42.0/24".
     */
    public const SUBNET_CIDR = '(10\.\d{1,2}\.\d{1,2}\.0\/24|172\.16\.\d{1,2}\.0\/24|172\.20\.\d{1,2}\.0\/24|172\.31\.\d{1,2}\.0\/24|192\.168\.\d{1,2}\.0\/24)';

    /**
     * Zone de sécurité réseau (filtrage pare-feu).
     */
    public const SECURITY_ZONE = '(DMZ|INTERNAL|EXTERNAL|GUEST|MANAGEMENT)';

    /**
     * Réponse booléenne en français, telle qu'affichée dans l'UI Mercator.
     */
    public const YES_NO_FR = '(Oui|Non)';

    /**
     * Mode d'allocation d'une adresse IP.
     */
    public const IP_ALLOCATION_TYPE = '(DHCP|Statique|Reserve)';

    /**
     * Identifiant de poste de travail (PC ou portable), ex. "PC-JDUP04".
     */
    public const WORKSTATION_NAME = '(PC|LT)-[A-Z]{2,4}\d{2,3}';

    /**
     * Identifiant de téléphone, ex. "TEL-JDUP04".
     */
    public const PHONE_NAME = 'TEL-[A-Z]{2,4}\d{2,3}';

    /**
     * Nom de local (salle/bureau), ex. "LOCAL042".
     */
    public const LOCAL_NAME = 'LOCAL[0-9]{3}';
}
