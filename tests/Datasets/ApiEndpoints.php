<?php

use App\Models\Activity;
use App\Models\Actor;
use App\Models\AdminUser;
use App\Models\Annuaire;
use App\Models\Application;
use App\Models\ApplicationBlock;
use App\Models\ApplicationFlow;
use App\Models\ApplicationModule;
use App\Models\ApplicationService;
use App\Models\Bay;
use App\Models\Building;
use App\Models\Certificate;
use App\Models\Cluster;
use App\Models\Container;
use App\Models\Database;
use App\Models\DataProcessing;
use App\Models\Document;
use App\Models\Domain;
use App\Models\Entity;
use App\Models\ExternalConnectedEntity;
use App\Models\ForestAd;
use App\Models\Gateway;
use App\Models\Information;
use App\Models\Lan;
use App\Models\LogicalFlow;
use App\Models\LogicalServer;
use App\Models\MacroProcessus;
use App\Models\Man;
use App\Models\Network;
use App\Models\NetworkSwitch;
use App\Models\Operation;
use App\Models\Peripheral;
use App\Models\Phone;
use App\Models\PhysicalLink;
use App\Models\PhysicalRouter;
use App\Models\PhysicalSecurityDevice;
use App\Models\PhysicalServer;
use App\Models\PhysicalSwitch;
use App\Models\Process;
use App\Models\Relation;
use App\Models\Router;
use App\Models\SecurityControl;
use App\Models\SecurityDevice;
use App\Models\Site;
use App\Models\StorageDevice;
use App\Models\Subnetwork;
use App\Models\Task;
use App\Models\Vlan;
use App\Models\Wan;
use App\Models\WifiTerminal;
use App\Models\Workstation;
use App\Models\ZoneAdmin;

dataset('api_endpoints', [
    'data-processings' => [
        [
            'route' => 'data-processings',
            'model' => DataProcessing::class,
        ],
    ],
    'security-controls' => [
        [
            'route' => 'security-controls',
            'model' => SecurityControl::class,
        ],
    ],
    'entities' => [
        [
            'route' => 'entities',
            'model' => Entity::class,
        ],
    ],
    'relations' => [
        [
            'route' => 'relations',
            'model' => Relation::class,
        ],
    ],
    'macro-processuses' => [
        [
            'route' => 'macro-processuses',
            'model' => MacroProcessus::class,
        ],
    ],
    'processes' => [
        [
            'route' => 'processes',
            'model' => Process::class,
        ],
    ],
    'operations' => [
        [
            'route' => 'operations',
            'model' => Operation::class,
        ],
    ],
    'actors' => [
        [
            'route' => 'actors',
            'model' => Actor::class,
        ],
    ],
    'activities' => [
        [
            'route' => 'activities',
            'model' => Activity::class,
        ],
    ],
    'tasks' => [
        [
            'route' => 'tasks',
            'model' => Task::class,
        ],
    ],
    'information' => [
        [
            'route' => 'information',
            'model' => Information::class,
        ],
    ],
    'applications' => [
        [
            'route' => 'applications',
            'model' => Application::class,
        ],
    ],
    'application-blocks' => [
        [
            'route' => 'application-blocks',
            'model' => ApplicationBlock::class,
        ],
    ],
    'application-modules' => [
        [
            'route' => 'application-modules',
            'model' => ApplicationModule::class,
        ],
    ],
    'application-services' => [
        [
            'route' => 'application-services',
            'model' => ApplicationService::class,
        ],
    ],
    'databases' => [
        [
            'route' => 'databases',
            'model' => Database::class,
        ],
    ],
    'application-flows' => [
        [
            'route' => 'application-flows',
            'model' => ApplicationFlow::class,
        ],
    ],
    'zone-admins' => [
        [
            'route' => 'zone-admins',
            'model' => ZoneAdmin::class,
        ],
    ],
    'annuaires' => [
        [
            'route' => 'annuaires',
            'model' => Annuaire::class,
        ],
    ],
    'forest-ads' => [
        [
            'route' => 'forest-ads',
            'model' => ForestAd::class,
        ],
    ],
    'domains' => [
        [
            'route' => 'domains',
            'model' => Domain::class,
        ],
    ],
    'admin-users' => [
        [
            'route' => 'admin-users',
            'model' => AdminUser::class,
            // Pas de colonne 'name' : champ libellé utilisé pour les tests de filtre/tri
            'label' => 'user_id',
        ],
    ],
    'networks' => [
        [
            'route' => 'networks',
            'model' => Network::class,
        ],
    ],
    'subnetworks' => [
        [
            'route' => 'subnetworks',
            'model' => Subnetwork::class,
        ],
    ],
    'gateways' => [
        [
            'route' => 'gateways',
            'model' => Gateway::class,
        ],
    ],
    'external-connected-entities' => [
        [
            'route' => 'external-connected-entities',
            'model' => ExternalConnectedEntity::class,
        ],
    ],
    'network-switches' => [
        [
            'route' => 'network-switches',
            'model' => NetworkSwitch::class,
        ],
    ],
    'routers' => [
        [
            'route' => 'routers',
            'model' => Router::class,
        ],
    ],
    'security-devices' => [
        [
            'route' => 'security-devices',
            'model' => SecurityDevice::class,
        ],
    ],
    'clusters' => [
        [
            'route' => 'clusters',
            'model' => Cluster::class,
        ],
    ],
    'containers' => [
        [
            'route' => 'containers',
            'model' => Container::class,
        ],
    ],
    'logical-servers' => [
        [
            'route' => 'logical-servers',
            'model' => LogicalServer::class,
        ],
    ],
    'logical-flows' => [
        [
            'route' => 'logical-flows',
            'model' => LogicalFlow::class,
        ],
    ],
    'certificates' => [
        [
            'route' => 'certificates',
            'model' => Certificate::class,
        ],
    ],
    'sites' => [
        [
            'route' => 'sites',
            'model' => Site::class,
        ],
    ],
    'buildings' => [
        [
            'route' => 'buildings',
            'model' => Building::class,
        ],
    ],
    'bays' => [
        [
            'route' => 'bays',
            'model' => Bay::class,
        ],
    ],
    'physical-servers' => [
        [
            'route' => 'physical-servers',
            'model' => PhysicalServer::class,
        ],
    ],
    'workstations' => [
        [
            'route' => 'workstations',
            'model' => Workstation::class,
        ],
    ],
    'storage-devices' => [
        [
            'route' => 'storage-devices',
            'model' => StorageDevice::class,
        ],
    ],
    'peripherals' => [
        [
            'route' => 'peripherals',
            'model' => Peripheral::class,
        ],
    ],
    'phones' => [
        [
            'route' => 'phones',
            'model' => Phone::class,
        ],
    ],
    'physical-switches' => [
        [
            'route' => 'physical-switches',
            'model' => PhysicalSwitch::class,
        ],
    ],
    'physical-routers' => [
        [
            'route' => 'physical-routers',
            'model' => PhysicalRouter::class,
        ],
    ],
    'wifi-terminals' => [
        [
            'route' => 'wifi-terminals',
            'model' => WifiTerminal::class,
        ],
    ],
    'physical-security-devices' => [
        [
            'route' => 'physical-security-devices',
            'model' => PhysicalSecurityDevice::class,
        ],
    ],
    'wans' => [
        [
            'route' => 'wans',
            'model' => Wan::class,
        ],
    ],
    'mans' => [
        [
            'route' => 'mans',
            'model' => Man::class,
        ],
    ],
    'lans' => [
        [
            'route' => 'lans',
            'model' => Lan::class,
        ],
    ],
    'vlans' => [
        [
            'route' => 'vlans',
            'model' => Vlan::class,
        ],
    ],
    'physical-links' => [
        [
            'route' => 'physical-links',
            'model' => PhysicalLink::class,
            // Pas de colonne 'name' : champ libellé utilisé pour les tests de filtre/tri
            'label' => 'src_port',
        ],
    ],
    'documents' => [
        [
            'route' => 'documents',
            'model' => Document::class,
            // Pas de colonne 'name' : champ libellé utilisé pour les tests de filtre/tri
            'label' => 'filename',
        ],
    ],
]);
