CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "media"(
  "id" integer primary key autoincrement not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  "collection_name" varchar not null,
  "name" varchar not null,
  "file_name" varchar not null,
  "mime_type" varchar,
  "disk" varchar not null,
  "size" integer not null,
  "manipulations" text not null,
  "custom_properties" text not null,
  "responsive_images" text not null,
  "order_column" integer,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "media_model_type_model_id_index" on "media"(
  "model_type",
  "model_id"
);
CREATE TABLE IF NOT EXISTS "password_resets"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime
);
CREATE INDEX "password_resets_email_index" on "password_resets"("email");
CREATE TABLE IF NOT EXISTS "permissions"(
  "id" integer primary key autoincrement not null,
  "title" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "module" varchar
  ,
  "ext_refs" varchar
);
CREATE TABLE IF NOT EXISTS "activity_operation"(
  "activity_id" integer not null,
  "operation_id" integer not null,
  foreign key("activity_id") references "activities"("id") on delete CASCADE on update NO ACTION,
  foreign key("operation_id") references "operations"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "activity_id_fk_1472704" on "activity_operation"("activity_id");
CREATE INDEX "operation_id_fk_1472704" on "activity_operation"("operation_id");
CREATE TABLE IF NOT EXISTS "activity_process"(
  "process_id" integer not null,
  "activity_id" integer not null,
  foreign key("activity_id") references "activities"("id") on delete CASCADE on update NO ACTION,
  foreign key("process_id") references "processes"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "activity_id_fk_1627616" on "activity_process"("activity_id");
CREATE INDEX "process_id_fk_1627616" on "activity_process"("process_id");
CREATE TABLE IF NOT EXISTS "actor_operation"(
  "operation_id" integer not null,
  "actor_id" integer not null,
  foreign key("actor_id") references "actors"("id") on delete CASCADE on update NO ACTION,
  foreign key("operation_id") references "operations"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "actor_id_fk_1472680" on "actor_operation"("actor_id");
CREATE INDEX "operation_id_fk_1472680" on "actor_operation"("operation_id");
CREATE TABLE IF NOT EXISTS "application_module_application_service"(
  "application_service_id" integer not null,
  "application_module_id" integer not null,
  foreign key("application_module_id") references "application_modules"("id") on delete CASCADE on update NO ACTION,
  foreign key("application_service_id") references "application_services"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "application_module_id_fk_1492414" on "application_module_application_service"(
  "application_module_id"
);
CREATE INDEX "application_service_id_fk_1492414" on "application_module_application_service"(
  "application_service_id"
);
CREATE TABLE IF NOT EXISTS "application_application_service"(
  "application_id" integer not null,
  "application_service_id" integer not null,
  foreign key("application_service_id") references "application_services"("id") on delete CASCADE on update NO ACTION,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "application_service_id_fk_1482585" on "application_application_service"(
  "application_service_id"
);
CREATE INDEX "m_application_id_fk_1482585" on "application_application_service"(
  "application_id"
);
CREATE TABLE IF NOT EXISTS "bay_wifi_terminal"(
  "wifi_terminal_id" integer not null,
  "bay_id" integer not null,
  foreign key("bay_id") references "bays"("id") on delete CASCADE on update NO ACTION,
  foreign key("wifi_terminal_id") references "wifi_terminals"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "bay_id_fk_1485509" on "bay_wifi_terminal"("bay_id");
CREATE INDEX "wifi_terminal_id_fk_1485509" on "bay_wifi_terminal"(
  "wifi_terminal_id"
);
CREATE TABLE IF NOT EXISTS "database_entity"(
  "database_id" integer not null,
  "entity_id" integer not null,
  foreign key("database_id") references "databases"("id") on delete CASCADE on update NO ACTION,
  foreign key("entity_id") references "entities"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "database_id_fk_1485563" on "database_entity"("database_id");
CREATE INDEX "entity_id_fk_1485563" on "database_entity"("entity_id");
CREATE TABLE IF NOT EXISTS "database_information"(
  "database_id" integer not null,
  "information_id" integer not null,
  foreign key("database_id") references "databases"("id") on delete CASCADE on update NO ACTION,
  foreign key("information_id") references "information"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "database_id_fk_1485570" on "database_information"("database_id");
CREATE INDEX "information_id_fk_1485570" on "database_information"(
  "information_id"
);
CREATE TABLE IF NOT EXISTS "application_database"(
  "application_id" integer not null,
  "database_id" integer not null,
  foreign key("database_id") references "databases"("id") on delete CASCADE on update NO ACTION,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "database_id_fk_1482586" on "application_database"("database_id");
CREATE INDEX "m_application_id_fk_1482586" on "application_database"(
  "application_id"
);
CREATE TABLE IF NOT EXISTS "application_entity"(
  "application_id" integer not null,
  "entity_id" integer not null,
  foreign key("entity_id") references "entities"("id") on delete CASCADE on update NO ACTION,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "entity_id_fk_1488611" on "application_entity"("entity_id");
CREATE INDEX "m_application_id_fk_1488611" on "application_entity"(
  "application_id"
);
CREATE TABLE IF NOT EXISTS "entity_process"(
  "process_id" integer not null,
  "entity_id" integer not null,
  foreign key("entity_id") references "entities"("id") on delete CASCADE on update NO ACTION,
  foreign key("process_id") references "processes"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "entity_id_fk_1627958" on "entity_process"("entity_id");
CREATE INDEX "process_id_fk_1627958" on "entity_process"("process_id");
CREATE TABLE IF NOT EXISTS "information_process"(
  "information_id" integer not null,
  "process_id" integer not null,
  foreign key("information_id") references "information"("id") on delete CASCADE on update NO ACTION,
  foreign key("process_id") references "processes"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "information_id_fk_1473025" on "information_process"(
  "information_id"
);
CREATE INDEX "process_id_fk_1473025" on "information_process"("process_id");
CREATE TABLE IF NOT EXISTS "lan_man"(
  "man_id" integer not null,
  "lan_id" integer not null,
  foreign key("lan_id") references "lans"("id") on delete CASCADE on update NO ACTION,
  foreign key("man_id") references "mans"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "lan_id_fk_1490345" on "lan_man"("lan_id");
CREATE INDEX "man_id_fk_1490345" on "lan_man"("man_id");
CREATE TABLE IF NOT EXISTS "lan_wan"(
  "wan_id" integer not null,
  "lan_id" integer not null,
  foreign key("lan_id") references "lans"("id") on delete CASCADE on update NO ACTION,
  foreign key("wan_id") references "wans"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "lan_id_fk_1490368" on "lan_wan"("lan_id");
CREATE INDEX "wan_id_fk_1490368" on "lan_wan"("wan_id");
CREATE TABLE IF NOT EXISTS "application_logical_server"(
  "application_id" integer not null,
  "logical_server_id" integer not null,
  foreign key("logical_server_id") references "logical_servers"("id") on delete CASCADE on update NO ACTION,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "logical_server_id_fk_1488616" on "application_logical_server"(
  "logical_server_id"
);
CREATE INDEX "m_application_id_fk_1488616" on "application_logical_server"(
  "application_id"
);
CREATE TABLE IF NOT EXISTS "logical_server_physical_server"(
  "logical_server_id" integer not null,
  "physical_server_id" integer not null,
  foreign key("logical_server_id") references "logical_servers"("id") on delete CASCADE on update NO ACTION,
  foreign key("physical_server_id") references "physical_servers"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "logical_server_id_fk_1657961" on "logical_server_physical_server"(
  "logical_server_id"
);
CREATE INDEX "physical_server_id_fk_1657961" on "logical_server_physical_server"(
  "physical_server_id"
);
CREATE TABLE IF NOT EXISTS "application_process"(
  "application_id" integer not null,
  "process_id" integer not null,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION,
  foreign key("process_id") references "processes"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "m_application_id_fk_1482573" on "application_process"(
  "application_id"
);
CREATE INDEX "process_id_fk_1482573" on "application_process"("process_id");
CREATE TABLE IF NOT EXISTS "man_wan"(
  "wan_id" integer not null,
  "man_id" integer not null,
  foreign key("man_id") references "mans"("id") on delete CASCADE on update NO ACTION,
  foreign key("wan_id") references "wans"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "man_id_fk_1490367" on "man_wan"("man_id");
CREATE INDEX "wan_id_fk_1490367" on "man_wan"("wan_id");
CREATE TABLE IF NOT EXISTS "operation_task"(
  "operation_id" integer not null,
  "task_id" integer not null,
  foreign key("operation_id") references "operations"("id") on delete CASCADE on update NO ACTION,
  foreign key("task_id") references "tasks"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "operation_id_fk_1472749" on "operation_task"("operation_id");
CREATE INDEX "task_id_fk_1472749" on "operation_task"("task_id");
CREATE TABLE IF NOT EXISTS "permission_role"(
  "role_id" integer not null,
  "permission_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete CASCADE on update NO ACTION,
  foreign key("role_id") references "roles"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "permission_id_fk_1470794" on "permission_role"("permission_id");
CREATE INDEX "role_id_fk_1470794" on "permission_role"("role_id");
CREATE TABLE IF NOT EXISTS "physical_router_vlan"(
  "physical_router_id" integer not null,
  "vlan_id" integer not null,
  foreign key("physical_router_id") references "physical_routers"("id") on delete CASCADE on update NO ACTION,
  foreign key("vlan_id") references "vlans"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "physical_router_id_fk_1658250" on "physical_router_vlan"(
  "physical_router_id"
);
CREATE INDEX "vlan_id_fk_1658250" on "physical_router_vlan"("vlan_id");
CREATE TABLE IF NOT EXISTS "role_user"(
  "user_id" integer not null,
  "role_id" integer not null,
  foreign key("role_id") references "roles"("id") on delete CASCADE on update NO ACTION,
  foreign key("user_id") references "users"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "role_id_fk_1470803" on "role_user"("role_id");
CREATE INDEX "user_id_fk_1470803" on "role_user"("user_id");
CREATE TABLE IF NOT EXISTS "certificate_logical_server"(
  "certificate_id" integer not null,
  "logical_server_id" integer not null,
  foreign key("certificate_id") references "certificates"("id") on delete CASCADE on update NO ACTION,
  foreign key("logical_server_id") references "logical_servers"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "certificate_id_fk_9483453" on "certificate_logical_server"(
  "certificate_id"
);
CREATE INDEX "logical_server_id_fk_9483453" on "certificate_logical_server"(
  "logical_server_id"
);
CREATE TABLE IF NOT EXISTS "application_certificate"(
  "certificate_id" integer not null,
  "application_id" integer not null,
  foreign key("certificate_id") references "certificates"("id") on delete CASCADE on update NO ACTION,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "certificate_id_fk_4584393" on "application_certificate"(
  "certificate_id"
);
CREATE INDEX "m_application_id_fk_4584393s" on "application_certificate"(
  "application_id"
);
CREATE TABLE IF NOT EXISTS "application_events"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "application_id" integer not null,
  "message" text not null,
  "created_at" datetime,
  "updated_at" datetime,
  "ext_refs" varchar,
  foreign key("user_id") references "users"("id"),
  foreign key("application_id") references "applications"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "application_workstation"(
  "application_id" integer not null,
  "workstation_id" integer not null,
  foreign key("workstation_id") references "workstations"("id") on delete CASCADE on update NO ACTION,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "m_application_id_fk_1486547" on "application_workstation"(
  "application_id"
);
CREATE INDEX "workstation_id_fk_1486547" on "application_workstation"(
  "workstation_id"
);
CREATE TABLE IF NOT EXISTS "database_logical_server"(
  "database_id" integer not null,
  "logical_server_id" integer not null
);
CREATE INDEX "database_id_fk_1542475" on "database_logical_server"(
  "database_id"
);
CREATE INDEX "logical_server_id_fk_1542475" on "database_logical_server"(
  "logical_server_id"
);
CREATE TABLE IF NOT EXISTS "activity_document"(
  "activity_id" integer not null,
  "document_id" integer not null,
  foreign key("activity_id") references "activities"("id") on delete CASCADE on update NO ACTION,
  foreign key("document_id") references "documents"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "activity_id_fk_1472714" on "activity_document"("activity_id");
CREATE INDEX "operation_id_fk_1472714" on "activity_document"("document_id");
CREATE TABLE IF NOT EXISTS "entity_document"(
  "entity_id" integer not null,
  "document_id" integer not null,
  foreign key("entity_id") references "entities"("id") on delete CASCADE on update NO ACTION,
  foreign key("document_id") references "documents"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "activity_id_fk_4325433" on "entity_document"("entity_id");
CREATE INDEX "operation_id_fk_5837593" on "entity_document"("document_id");
CREATE TABLE IF NOT EXISTS "cpe_vendors"(
  "id" integer primary key autoincrement not null,
  "part" varchar not null,
  "name" varchar not null
);
CREATE UNIQUE INDEX "cpe_vendors_part_name_unique" on "cpe_vendors"(
  "part",
  "name"
);
CREATE TABLE IF NOT EXISTS "cpe_products"(
  "id" integer primary key autoincrement not null,
  "cpe_vendor_id" integer not null,
  "name" varchar not null,
  foreign key("cpe_vendor_id") references "cpe_vendors"("id") on delete NO ACTION on update NO ACTION
);
CREATE INDEX "cpe_product_fk_1485479" on "cpe_products"("cpe_vendor_id");
CREATE UNIQUE INDEX "cpe_products_cpe_vendor_id_name_unique" on "cpe_products"(
  "cpe_vendor_id",
  "name"
);
CREATE TABLE IF NOT EXISTS "cpe_versions"(
  "id" integer primary key autoincrement not null,
  "cpe_product_id" integer not null,
  "name" varchar not null,
  foreign key("cpe_product_id") references "cpe_products"("id") on delete NO ACTION on update NO ACTION
);
CREATE INDEX "cpe_version_fk_1485479" on "cpe_versions"("cpe_product_id");
CREATE UNIQUE INDEX "cpe_versions_cpe_product_id_name_unique" on "cpe_versions"(
  "cpe_product_id",
  "name"
);
CREATE TABLE IF NOT EXISTS "data_processing_document"(
  "data_processing_id" integer not null,
  "document_id" integer not null,
  foreign key("data_processing_id") references "data_processing"("id") on delete CASCADE on update NO ACTION,
  foreign key("document_id") references "documents"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "data_processing_id_fk_6930583" on "data_processing_document"(
  "data_processing_id"
);
CREATE INDEX "operation_id_fk_4355431" on "data_processing_document"(
  "document_id"
);
CREATE TABLE IF NOT EXISTS "data_processing_process"(
  "data_processing_id" integer not null,
  "process_id" integer not null,
  foreign key("data_processing_id") references "data_processing"("id") on delete CASCADE on update NO ACTION,
  foreign key("process_id") references "processes"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "data_processing_id_fk_5435435" on "data_processing_process"(
  "data_processing_id"
);
CREATE INDEX "process_id_fk_594358" on "data_processing_process"("process_id");
CREATE TABLE IF NOT EXISTS "application_data_processing"(
  "data_processing_id" integer not null,
  "application_id" integer not null,
  foreign key("data_processing_id") references "data_processing"("id") on delete CASCADE on update NO ACTION,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "data_processing_id_fk_6948435" on "application_data_processing"(
  "data_processing_id"
);
CREATE INDEX "m_applications_id_fk_4384483" on "application_data_processing"(
  "application_id"
);
CREATE TABLE IF NOT EXISTS "data_processing_information"(
  "data_processing_id" integer not null,
  "information_id" integer not null,
  foreign key("data_processing_id") references "data_processing"("id") on delete CASCADE on update NO ACTION,
  foreign key("information_id") references "information"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "data_processing_id_fk_58305863" on "data_processing_information"(
  "data_processing_id"
);
CREATE INDEX "information_id_fk_4384483" on "data_processing_information"(
  "information_id"
);
CREATE TABLE IF NOT EXISTS "application_security_control"(
  "security_control_id" integer not null,
  "application_id" integer not null,
  foreign key("security_control_id") references "security_controls"("id") on delete CASCADE on update NO ACTION,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "m_application_id_fk_5837573" on "application_security_control"(
  "application_id"
);
CREATE INDEX "security_control_id_fk_5920381" on "application_security_control"(
  "security_control_id"
);
CREATE TABLE IF NOT EXISTS "security_control_process"(
  "security_control_id" integer not null,
  "process_id" integer not null,
  foreign key("security_control_id") references "security_controls"("id") on delete CASCADE on update NO ACTION,
  foreign key("process_id") references "processes"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "process_id_fk_5837573" on "security_control_process"(
  "process_id"
);
CREATE INDEX "security_control_id_fk_54354353" on "security_control_process"(
  "security_control_id"
);
CREATE TABLE IF NOT EXISTS "application_physical_server"(
  "application_id" integer not null,
  "physical_server_id" integer not null,
  foreign key("physical_server_id") references "physical_servers"("id") on delete CASCADE on update NO ACTION,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "m_application_id_fk_5483543" on "application_physical_server"(
  "application_id"
);
CREATE INDEX "physical_server_id_fk_4543543" on "application_physical_server"(
  "physical_server_id"
);
CREATE TABLE IF NOT EXISTS "document_logical_server"(
  "logical_server_id" integer not null,
  "document_id" integer not null,
  foreign key("logical_server_id") references "logical_servers"("id") on delete CASCADE on update NO ACTION,
  foreign key("document_id") references "documents"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "logical_server_id_fk_43832473" on "document_logical_server"(
  "logical_server_id"
);
CREATE INDEX "document_id_fk_1284334" on "document_logical_server"(
  "document_id"
);
CREATE TABLE IF NOT EXISTS "application_peripheral"(
  "application_id" integer not null,
  "peripheral_id" integer not null,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION,
  foreign key("peripheral_id") references "peripherals"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "m_application_id_fk_9878654" on "application_peripheral"(
  "application_id"
);
CREATE INDEX "peripheral_id_fk_6454564" on "application_peripheral"(
  "peripheral_id"
);
CREATE TABLE IF NOT EXISTS "relation_values"(
  "relation_id" integer not null,
  "date_price" date,
  "price" numeric,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("relation_id") references "relations"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "relation_id_fk_43243244" on "relation_values"("relation_id");
CREATE TABLE IF NOT EXISTS "document_relation"(
  "relation_id" integer not null,
  "document_id" integer not null,
  foreign key("relation_id") references "relations"("id") on delete CASCADE on update NO ACTION,
  foreign key("document_id") references "documents"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "relation_id_fk_6948334" on "document_relation"("relation_id");
CREATE INDEX "document_id_fk_5492844" on "document_relation"("document_id");
CREATE TABLE IF NOT EXISTS "physical_router_router"(
  "router_id" integer not null,
  "physical_router_id" integer not null,
  foreign key("physical_router_id") references "physical_routers"("id") on delete CASCADE on update NO ACTION,
  foreign key("router_id") references "routers"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "router_id_fk_958343" on "physical_router_router"("router_id");
CREATE INDEX "physical_router_id_fk_124983" on "physical_router_router"(
  "physical_router_id"
);
CREATE TABLE IF NOT EXISTS "network_switch_physical_switch"(
  "network_switch_id" integer not null,
  "physical_switch_id" integer not null,
  foreign key("network_switch_id") references "network_switches"("id") on delete CASCADE on update NO ACTION,
  foreign key("physical_switch_id") references "physical_switches"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "network_switch_id_fk_543323" on "network_switch_physical_switch"(
  "network_switch_id"
);
CREATE INDEX "physical_switch_id_fk_4543143" on "network_switch_physical_switch"(
  "physical_switch_id"
);
CREATE TABLE IF NOT EXISTS "audit_logs"(
  "id" integer primary key autoincrement not null,
  "description" varchar not null,
  "subject_id" integer,
  "subject_type" varchar,
  "user_id" integer,
  "properties" text,
  "host" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "container_logical_server"(
  "container_id" integer not null,
  "logical_server_id" integer not null,
  foreign key("container_id") references "containers"("id") on delete CASCADE on update NO ACTION,
  foreign key("logical_server_id") references "logical_servers"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "container_id_fk_54933032" on "container_logical_server"(
  "container_id"
);
CREATE INDEX "logical_server_id_fk_4394832" on "container_logical_server"(
  "logical_server_id"
);
CREATE TABLE IF NOT EXISTS "application_container"(
  "container_id" integer not null,
  "application_id" integer not null,
  foreign key("container_id") references "containers"("id") on delete CASCADE on update NO ACTION,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "container_id_fk_549854345" on "application_container"(
  "container_id"
);
CREATE INDEX "m_application_id_fk_344234340" on "application_container"(
  "application_id"
);
CREATE TABLE IF NOT EXISTS "activity_application"(
  "application_id" integer not null,
  "activity_id" integer not null,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION,
  foreign key("activity_id") references "activities"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "application_id_fk_0394834858" on "activity_application"(
  "application_id"
);
CREATE INDEX "process_id_fk_394823838" on "activity_application"(
  "activity_id"
);
CREATE TABLE IF NOT EXISTS "container_database"(
  "database_id" integer not null,
  "container_id" integer not null,
  foreign key("database_id") references "databases"("id") on delete cascade,
  foreign key("container_id") references "containers"("id") on delete cascade,
  primary key("database_id", "container_id")
);
CREATE TABLE IF NOT EXISTS "admin_user_application"(
  "admin_user_id" integer not null,
  "application_id" integer not null,
  foreign key("admin_user_id") references "admin_users"("id") on delete cascade,
  foreign key("application_id") references "applications"("id") on delete cascade,
  primary key("admin_user_id", "application_id")
);
CREATE TABLE IF NOT EXISTS "activity_impact"(
  "id" integer primary key autoincrement not null,
  "activity_id" integer not null,
  "impact_type" varchar not null,
  "severity" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("activity_id") references "activities"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "physical_security_device_security_device"(
  "security_device_id" integer not null,
  "physical_security_device_id" integer not null,
  foreign key("security_device_id") references "security_devices"("id") on delete CASCADE on update NO ACTION,
  foreign key("physical_security_device_id") references "physical_security_devices"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "physical_security_device_id_fk_6549543" on "physical_security_device_security_device"(
  "physical_security_device_id"
);
CREATE INDEX "security_device_id_fk_43329392" on "physical_security_device_security_device"(
  "security_device_id"
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar,
  "email" varchar,
  "email_verified_at" datetime,
  "password" varchar,
  "remember_token" varchar,
  "granularity" integer,
  "language" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "login" varchar not null
  ,
  "flow_label" varchar
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email", "deleted_at");
CREATE TABLE IF NOT EXISTS "document_external_connected_entity"(
  "external_connected_entity_id" integer not null,
  "document_id" integer not null,
  foreign key("external_connected_entity_id") references "external_connected_entities"("id") on delete cascade,
  foreign key("document_id") references "documents"("id") on delete cascade
);
CREATE INDEX "external_connected_entity_idx_2143243" on "document_external_connected_entity"(
  "external_connected_entity_id"
);
CREATE INDEX "document_idx_434934839" on "document_external_connected_entity"(
  "document_id"
);
CREATE TABLE IF NOT EXISTS "external_connected_entity_subnetwork"(
  "external_connected_entity_id" integer not null,
  "subnetwork_id" integer not null,
  foreign key("external_connected_entity_id") references "external_connected_entities"("id") on delete cascade,
  foreign key("subnetwork_id") references "subnetworks"("id") on delete cascade
);
CREATE INDEX "external_connected_entity_idx_59458458" on "external_connected_entity_subnetwork"(
  "external_connected_entity_id"
);
CREATE INDEX "subnetwork_idx_4343848" on "external_connected_entity_subnetwork"(
  "subnetwork_id"
);
CREATE TABLE IF NOT EXISTS "cluster_logical_server"(
  "cluster_id" integer not null,
  "logical_server_id" integer not null,
  foreign key("cluster_id") references "clusters"("id") on delete cascade,
  foreign key("logical_server_id") references "logical_servers"("id") on delete cascade,
  primary key("cluster_id", "logical_server_id")
);
CREATE INDEX "cluster_logical_server_logical_server_id_index" on "cluster_logical_server"(
  "logical_server_id"
);
CREATE TABLE IF NOT EXISTS "cluster_physical_server"(
  "cluster_id" integer not null,
  "physical_server_id" integer not null,
  foreign key("cluster_id") references "clusters"("id") on delete cascade,
  foreign key("physical_server_id") references "physical_servers"("id") on delete cascade,
  primary key("cluster_id", "physical_server_id")
);
CREATE INDEX "cluster_physical_server_physical_server_id_index" on "cluster_physical_server"(
  "physical_server_id"
);
CREATE TABLE IF NOT EXISTS "cluster_router"(
  "cluster_id" integer not null,
  "router_id" integer not null,
  foreign key("cluster_id") references "clusters"("id") on delete cascade,
  foreign key("router_id") references "routers"("id") on delete cascade,
  primary key("cluster_id", "router_id")
);
CREATE INDEX "cluster_router_router_id_index" on "cluster_router"("router_id");
CREATE TABLE IF NOT EXISTS "application_security_device"(
  "security_device_id" integer not null,
  "application_id" integer not null,
  foreign key("security_device_id") references "security_devices"("id") on delete CASCADE on update NO ACTION,
  foreign key("application_id") references "applications"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "m_application_id_fk_41923483" on "application_security_device"(
  "application_id"
);
CREATE INDEX "security_device_id_fk_304832731" on "application_security_device"(
  "security_device_id"
);
CREATE TABLE IF NOT EXISTS "network_switch_vlan"(
  "network_switch_id" integer not null,
  "vlan_id" integer not null,
  foreign key("network_switch_id") references "network_switches"("id") on delete cascade,
  foreign key("vlan_id") references "vlans"("id") on delete cascade,
  primary key("network_switch_id", "vlan_id")
);
CREATE INDEX "network_switch_vlan_network_switch_id_index" on "network_switch_vlan"(
  "network_switch_id"
);
CREATE INDEX "network_switch_vlan_vlan_id_index" on "network_switch_vlan"(
  "vlan_id"
);
CREATE TABLE IF NOT EXISTS "oauth_device_codes"(
  "id" varchar not null,
  "user_id" integer,
  "client_id" varchar not null,
  "user_code" varchar not null,
  "scopes" text not null,
  "revoked" tinyint(1) not null,
  "user_approved_at" datetime,
  "last_polled_at" datetime,
  "expires_at" datetime,
  primary key("id")
);
CREATE INDEX "oauth_device_codes_user_id_index" on "oauth_device_codes"(
  "user_id"
);
CREATE INDEX "oauth_device_codes_client_id_index" on "oauth_device_codes"(
  "client_id"
);
CREATE UNIQUE INDEX "oauth_device_codes_user_code_unique" on "oauth_device_codes"(
  "user_code"
);
CREATE TABLE IF NOT EXISTS "mercator_modules"(
  "name" varchar not null,
  "label" varchar not null,
  "version" varchar not null,
  "enabled" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  primary key("name")
);
CREATE TABLE IF NOT EXISTS "application_flow_information"(
  "id" integer primary key autoincrement not null,
  "flux_id" integer not null,
  "information_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("flux_id") references "application_flows"("id") on delete cascade,
  foreign key("information_id") references "information"("id") on delete cascade
);
CREATE UNIQUE INDEX "flux_information_flux_id_information_id_unique" on "application_flow_information"(
  "flux_id",
  "information_id"
);
CREATE TABLE IF NOT EXISTS "oauth_clients"(
  "id" varchar not null,
  "user_id" integer,
  "name" varchar not null,
  "secret" varchar,
  "provider" varchar,
  "redirect" text not null,
  "personal_access_client" integer not null,
  "password_client" integer not null,
  "revoked" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  primary key("id")
);
CREATE INDEX "oauth_clients_user_id_index" on "oauth_clients"("user_id");
CREATE TABLE IF NOT EXISTS "oauth_access_tokens"(
  "id" varchar not null,
  "user_id" integer,
  "client_id" varchar not null,
  "name" varchar,
  "scopes" text,
  "revoked" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  "expires_at" datetime,
  primary key("id")
);
CREATE INDEX "oauth_access_tokens_user_id_index" on "oauth_access_tokens"(
  "user_id"
);
CREATE TABLE IF NOT EXISTS "oauth_auth_codes"(
  "id" varchar not null,
  "user_id" integer not null,
  "client_id" varchar not null,
  "scopes" text,
  "revoked" integer not null,
  "expires_at" datetime,
  primary key("id")
);
CREATE INDEX "oauth_auth_codes_user_id_index" on "oauth_auth_codes"("user_id");
CREATE TABLE IF NOT EXISTS "oauth_refresh_tokens"(
  "id" varchar not null,
  "access_token_id" varchar not null,
  "revoked" integer not null,
  "expires_at" datetime,
  primary key("id")
);
CREATE INDEX "oauth_refresh_tokens_access_token_id_index" on "oauth_refresh_tokens"(
  "access_token_id"
);
CREATE TABLE IF NOT EXISTS "oauth_personal_access_clients"(
  "id" integer primary key autoincrement not null,
  "client_id" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "information_information"(
  "information_id" integer not null,
  "child_information_id" integer not null,
  foreign key("information_id") references "information"("id") on delete cascade,
  foreign key("child_information_id") references "information"("id") on delete cascade,
  primary key("information_id", "child_information_id")
);
CREATE TABLE IF NOT EXISTS "application_module_entity"(
  "application_module_id" integer not null,
  "entity_id" integer not null,
  foreign key("application_module_id") references "application_modules"("id") on delete cascade,
  foreign key("entity_id") references "entities"("id") on delete cascade,
  primary key("application_module_id", "entity_id")
);
CREATE TABLE IF NOT EXISTS "saved_queries"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "query" text not null,
  "is_public" tinyint(1) not null default '0',
  "user_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE INDEX "saved_queries_user_id_is_public_index" on "saved_queries"(
  "user_id",
  "is_public"
);
CREATE TABLE IF NOT EXISTS "domain_forest_ad"(
  "forest_ad_id" integer not null,
  "domain_id" integer not null,
  foreign key("forest_ad_id") references forest_ads("id") on delete cascade on update no action,
  foreign key("domain_id") references domains("id") on delete cascade on update no action,
  foreign key("domain_id") references "domains"("id") on delete CASCADE on update NO ACTION
);
CREATE INDEX "domaine_ad_id_fk_1492084" on "domain_forest_ad"("domain_id");
CREATE INDEX "forest_ad_id_fk_1492084" on "domain_forest_ad"("forest_ad_id");
CREATE TABLE IF NOT EXISTS "parameters"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "value" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "parameters_name_unique" on "parameters"("name");
CREATE TABLE IF NOT EXISTS "zone_zone"(
  "zone_id" integer not null,
  "related_zone_id" integer not null,
  foreign key("zone_id") references "zones"("id") on delete cascade,
  foreign key("related_zone_id") references "zones"("id") on delete cascade,
  primary key("zone_id", "related_zone_id")
);
CREATE TABLE IF NOT EXISTS "building_zone"(
  "building_id" integer not null,
  "zone_id" integer not null,
  foreign key("building_id") references "buildings"("id") on delete cascade,
  foreign key("zone_id") references "zones"("id") on delete cascade,
  primary key("building_id", "zone_id")
);
CREATE TABLE IF NOT EXISTS "admin_user_zone"(
  "admin_user_id" integer not null,
  "zone_id" integer not null,
  foreign key("admin_user_id") references "admin_users"("id") on delete cascade,
  foreign key("zone_id") references "zones"("id") on delete cascade,
  primary key("admin_user_id", "zone_id")
);
CREATE TABLE IF NOT EXISTS "backup_logical_server"(
  "backup_id" integer not null,
  "logical_server_id" integer not null,
  foreign key("backup_id") references "backups"("id") on delete cascade,
  foreign key("logical_server_id") references "logical_servers"("id") on delete cascade,
  primary key("backup_id", "logical_server_id")
);
CREATE TABLE IF NOT EXISTS "backup_storage_device"(
  "backup_id" integer not null,
  "storage_device_id" integer not null,
  foreign key("backup_id") references "backups"("id") on delete cascade,
  foreign key("storage_device_id") references "storage_devices"("id") on delete cascade,
  primary key("backup_id", "storage_device_id")
);
CREATE TABLE IF NOT EXISTS "cartographers"(
  "id" integer primary key autoincrement not null,
  "cartographiable_type" varchar not null,
  "cartographiable_id" integer not null,
  "user_id" integer,
  "role_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE INDEX "cartographers_cartographiable_type_cartographiable_id_index" on "cartographers"(
  "cartographiable_type",
  "cartographiable_id"
);
CREATE INDEX "cartographers_user_id_index" on "cartographers"("user_id");
CREATE INDEX "cartographers_role_id_index" on "cartographers"("role_id");
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "application_document"(
  "application_id" integer not null,
  "document_id" integer not null
);
CREATE INDEX "application_id_fk_7812341" on "application_document"(
  "application_id"
);
CREATE INDEX "document_id_fk_7812342" on "application_document"("document_id");
CREATE TABLE IF NOT EXISTS "monarc_sync_items"(
  "id" integer primary key autoincrement not null,
  "model" varchar not null,
  "mercator_id" integer not null,
  "object_uuid" varchar not null,
  "anr_id" integer not null,
  "sent_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "monarc_sync_items_unique" on "monarc_sync_items"(
  "anr_id",
  "model",
  "mercator_id"
);
CREATE TABLE IF NOT EXISTS "perimeters"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "roles"(
  "id" integer primary key autoincrement not null,
  "title" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "perimeter_id" integer not null default('1'),
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update NO ACTION
);
CREATE INDEX "roles_perimeter_id_index" on "roles"("perimeter_id");
CREATE INDEX "saved_queries_name_index" on "saved_queries"("name");
CREATE UNIQUE INDEX "perimeters_name_unique" on "perimeters"("name");
CREATE TABLE IF NOT EXISTS "activities"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "drp" text,
  "drp_link" varchar,
  "recovery_time_objective" integer,
  "maximum_tolerable_downtime" integer,
  "recovery_point_objective" integer,
  "maximum_tolerable_data_loss" integer,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "activities_name_index" on "activities"("name");
CREATE INDEX "activities_perimeter_id_index" on "activities"("perimeter_id");
CREATE TABLE IF NOT EXISTS "actors"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "nature" varchar,
  "type" varchar,
  "contact" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "actors_name_index" on "actors"("name");
CREATE INDEX "actors_perimeter_id_index" on "actors"("perimeter_id");
CREATE TABLE IF NOT EXISTS "admin_users"(
  "id" integer primary key autoincrement not null,
  "user_id" varchar not null,
  "firstname" varchar,
  "lastname" varchar,
  "type" varchar,
  "description" text,
  "domain_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "icon_id" integer,
  "attributes" varchar,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("domain_id") references "domains"("id") on delete cascade on update no action,
  foreign key("domain_id") references "domains"("id") on delete cascade on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "admin_users_perimeter_id_index" on "admin_users"("perimeter_id");
CREATE INDEX "document_id_fk_129487" on "admin_users"("icon_id");
CREATE INDEX "domain_id_fk_69385935" on "admin_users"("domain_id");
CREATE UNIQUE INDEX "domain_id_user_id_unique" on "admin_users"(
  "domain_id",
  "user_id",
  "deleted_at"
);
CREATE TABLE IF NOT EXISTS "annuaires"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "solution" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "zone_admin_id" integer,
  "ext_refs" varchar,
  "application_id" integer,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("zone_admin_id") references "zone_admins"("id") on delete no action on update no action,
  foreign key("application_id") references "applications"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "annuaires_name_index" on "annuaires"("name");
CREATE INDEX "annuaires_perimeter_id_index" on "annuaires"("perimeter_id");
CREATE INDEX "zone_admin_fk_1482666" on "annuaires"("zone_admin_id");
CREATE TABLE IF NOT EXISTS "application_blocks"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "responsible" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "application_blocks_name_index" on "application_blocks"("name");
CREATE INDEX "application_blocks_perimeter_id_index" on "application_blocks"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "application_flows"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "application_source_id" integer,
  "service_source_id" integer,
  "module_source_id" integer,
  "database_source_id" integer,
  "application_dest_id" integer,
  "service_dest_id" integer,
  "module_dest_id" integer,
  "database_dest_id" integer,
  "crypted" tinyint(1),
  "bidirectional" tinyint(1),
  "type" varchar,
  "attributes" varchar,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("application_dest_id") references "applications"("id") on delete no action on update no action,
  foreign key("application_source_id") references "applications"("id") on delete no action on update no action,
  foreign key("database_dest_id") references "databases"("id") on delete no action on update no action,
  foreign key("database_source_id") references "databases"("id") on delete no action on update no action,
  foreign key("module_dest_id") references "application_modules"("id") on delete no action on update no action,
  foreign key("module_source_id") references "application_modules"("id") on delete no action on update no action,
  foreign key("service_dest_id") references "application_services"("id") on delete no action on update no action,
  foreign key("service_source_id") references "application_services"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "application_dest_fk_1485549" on "application_flows"(
  "application_dest_id"
);
CREATE INDEX "application_flows_name_index" on "application_flows"("name");
CREATE INDEX "application_flows_perimeter_id_index" on "application_flows"(
  "perimeter_id"
);
CREATE INDEX "application_source_fk_1485545" on "application_flows"(
  "application_source_id"
);
CREATE INDEX "database_dest_fk_1485552" on "application_flows"(
  "database_dest_id"
);
CREATE INDEX "database_source_fk_1485548" on "application_flows"(
  "database_source_id"
);
CREATE INDEX "module_dest_fk_1485551" on "application_flows"("module_dest_id");
CREATE INDEX "module_source_fk_1485547" on "application_flows"(
  "module_source_id"
);
CREATE INDEX "service_dest_fk_1485550" on "application_flows"(
  "service_dest_id"
);
CREATE INDEX "service_source_fk_1485546" on "application_flows"(
  "service_source_id"
);
CREATE TABLE IF NOT EXISTS "application_modules"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "vendor" varchar,
  "product" varchar,
  "version" varchar,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "comments" text,
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "application_modules_name_index" on "application_modules"("name");
CREATE INDEX "application_modules_perimeter_id_index" on "application_modules"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "application_services"(
  "id" integer primary key autoincrement not null,
  "description" text,
  "exposition" varchar,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "comments" text,
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "application_services_name_index" on "application_services"(
  "name"
);
CREATE INDEX "application_services_perimeter_id_index" on "application_services"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "applications"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "security_need_c" integer,
  "responsible" varchar,
  "type" varchar,
  "technology" varchar,
  "external" varchar,
  "users" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "entity_resp_id" integer,
  "application_block_id" integer,
  "documentation" varchar,
  "security_need_i" integer,
  "security_need_a" integer,
  "security_need_t" integer,
  "version" varchar,
  "functional_referent" varchar,
  "editor" varchar,
  "install_date" date,
  "update_date" date,
  "rto" integer,
  "rpo" integer,
  "vendor" varchar,
  "product" varchar,
  "attributes" varchar,
  "patching_frequency" integer,
  "next_update" date,
  "icon_id" integer,
  "security_need_auth" integer,
  "ext_refs" varchar,
  "status" varchar,
  "prod_date" date,
  "hosting" varchar,
  "urls" varchar,
  "perimeter_id" integer not null default('1'),
  "comments" text,
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("application_block_id") references "application_blocks"("id") on delete no action on update no action,
  foreign key("entity_resp_id") references "entities"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "application_block_fk_1644592" on "applications"(
  "application_block_id"
);
CREATE INDEX "applications_name_index" on "applications"("name");
CREATE INDEX "applications_perimeter_id_index" on "applications"(
  "perimeter_id"
);
CREATE INDEX "document_id_fk_4394343" on "applications"("icon_id");
CREATE INDEX "entity_resp_fk_1488612" on "applications"("entity_resp_id");
CREATE TABLE IF NOT EXISTS "backups"(
  "id" integer primary key autoincrement not null,
  "backup_frequency" integer,
  "backup_cycle" integer,
  "backup_retention" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "name" varchar not null,
  "type" varchar,
  "attributes" text,
  "description" text,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "backups_name_index" on "backups"("name");
CREATE INDEX "backups_perimeter_id_index" on "backups"("perimeter_id");
CREATE TABLE IF NOT EXISTS "bays"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "building_id" integer,
  "ext_refs" varchar,
  "site_id" integer,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("building_id") references "buildings"("id") on delete no action on update no action,
  foreign key("building_id") references "buildings"("id") on delete no action on update no action,
  foreign key("site_id") references "sites"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "bays_name_index" on "bays"("name");
CREATE INDEX "bays_perimeter_id_index" on "bays"("perimeter_id");
CREATE INDEX "room_fk_1483441" on "bays"("building_id");
CREATE INDEX "site_id_fk_1483442" on "bays"("site_id");
CREATE TABLE IF NOT EXISTS "buildings"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "site_id" integer,
  "attributes" varchar,
  "type" varchar,
  "building_id" integer,
  "icon_id" integer,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("site_id") references "sites"("id") on delete no action on update no action,
  foreign key("building_id") references "buildings"("id") on delete set null on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "buildings_name_index" on "buildings"("name");
CREATE INDEX "buildings_perimeter_id_index" on "buildings"("perimeter_id");
CREATE INDEX "document_id_fk_49574431" on "buildings"("icon_id");
CREATE INDEX "site_fk_1483431" on "buildings"("site_id");
CREATE TABLE IF NOT EXISTS "certificates"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "type" varchar,
  "description" text,
  "start_validity" date,
  "end_validity" date,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "status" integer,
  "last_notification" datetime,
  "ext_refs" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "certificates_name_index" on "certificates"("name");
CREATE INDEX "certificates_perimeter_id_index" on "certificates"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "clusters"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "type" varchar,
  "description" text,
  "address_ip" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "attributes" varchar,
  "icon_id" integer,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "clusters_name_index" on "clusters"("name");
CREATE INDEX "clusters_perimeter_id_index" on "clusters"("perimeter_id");
CREATE INDEX "document_id_fk_495432841" on "clusters"("icon_id");
CREATE TABLE IF NOT EXISTS "containers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "type" varchar,
  "description" text,
  "icon_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "containers_perimeter_id_index" on "containers"("perimeter_id");
CREATE INDEX "document_id_fk_43948593" on "containers"("icon_id");
CREATE TABLE IF NOT EXISTS "data_processing"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "responsible" text,
  "purpose" text,
  "categories" text,
  "recipients" text,
  "transfert" text,
  "retention" text,
  "controls" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "legal_basis" varchar,
  "lawfulness" text,
  "lawfulness_legitimate_interest" tinyint(1),
  "lawfulness_public_interest" tinyint(1),
  "lawfulness_vital_interest" tinyint(1),
  "lawfulness_legal_obligation" tinyint(1),
  "lawfulness_contract" tinyint(1),
  "lawfulness_consent" tinyint(1),
  "data_source" text,
  "data_collection_obligation" text,
  "data_subject_rights" text,
  "automated_decision_making" text,
  "update_date" date,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "data_processing_name_index" on "data_processing"("name");
CREATE INDEX "data_processing_perimeter_id_index" on "data_processing"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "databases"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "responsible" varchar,
  "type" varchar,
  "security_need_c" integer,
  "external" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "entity_resp_id" integer,
  "security_need_i" integer,
  "security_need_a" integer,
  "security_need_t" integer,
  "security_need_auth" integer,
  "icon_id" integer,
  "ext_refs" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("entity_resp_id") references "entities"("id") on delete no action on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "databases_name_index" on "databases"("name");
CREATE INDEX "databases_perimeter_id_index" on "databases"("perimeter_id");
CREATE INDEX "entity_resp_fk_1485569" on "databases"("entity_resp_id");
CREATE TABLE IF NOT EXISTS "dhcp_servers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "address_ip" varchar,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "dhcp_servers_name_index" on "dhcp_servers"("name");
CREATE INDEX "dhcp_servers_perimeter_id_index" on "dhcp_servers"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "dnsservers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "address_ip" varchar,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "dnsservers_name_index" on "dnsservers"("name");
CREATE INDEX "dnsservers_perimeter_id_index" on "dnsservers"("perimeter_id");
CREATE TABLE IF NOT EXISTS "documents"(
  "id" integer primary key autoincrement not null,
  "filename" varchar not null,
  "mimetype" varchar not null,
  "size" integer not null,
  "hash" varchar not null,
  "deleted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "ext_refs" varchar,
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "domains"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "domain_ctrl_cnt" integer,
  "user_count" integer,
  "machine_count" integer,
  "relation_inter_domaine" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "domains_name_index" on "domains"("name");
CREATE INDEX "domains_perimeter_id_index" on "domains"("perimeter_id");
CREATE TABLE IF NOT EXISTS "entities"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "security_level" text,
  "contact_point" text,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "type" varchar,
  "attributes" varchar,
  "reference" varchar,
  "parent_entity_id" integer,
  "icon_id" integer,
  "external_ref_id" varchar,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("parent_entity_id") references "entities"("id") on delete set null on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "document_id_fk_129486" on "entities"("icon_id");
CREATE INDEX "entities_name_index" on "entities"("name");
CREATE INDEX "entities_perimeter_id_index" on "entities"("perimeter_id");
CREATE INDEX "entity_id_fk_4398013" on "entities"("parent_entity_id");
CREATE INDEX "type" on "entities"("type");
CREATE TABLE IF NOT EXISTS "external_connected_entities"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "contacts" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "description" text,
  "type" varchar,
  "entity_id" integer,
  "network_id" integer,
  "src" varchar,
  "dest" varchar,
  "src_desc" varchar,
  "dest_desc" varchar,
  "security" text,
  "ext_refs" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("entity_id") references "entities"("id") on delete cascade on update no action,
  foreign key("network_id") references "networks"("id") on delete cascade on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "entity_id_fk_1295034" on "external_connected_entities"(
  "entity_id"
);
CREATE INDEX "external_connected_entities_name_index" on "external_connected_entities"(
  "name"
);
CREATE INDEX "external_connected_entities_perimeter_id_index" on "external_connected_entities"(
  "perimeter_id"
);
CREATE INDEX "network_id_fk_8596554" on "external_connected_entities"(
  "network_id"
);
CREATE TABLE IF NOT EXISTS "forest_ads"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "zone_admin_id" integer,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("zone_admin_id") references "zone_admins"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "forest_ads_name_index" on "forest_ads"("name");
CREATE INDEX "forest_ads_perimeter_id_index" on "forest_ads"("perimeter_id");
CREATE INDEX "zone_admin_fk_1482667" on "forest_ads"("zone_admin_id");
CREATE TABLE IF NOT EXISTS "gateways"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "ip" varchar,
  "authentification" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "gateways_name_index" on "gateways"("name");
CREATE INDEX "gateways_perimeter_id_index" on "gateways"("perimeter_id");
CREATE TABLE IF NOT EXISTS "graphs"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "type" varchar,
  "content" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "class" integer,
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "graphs_name_index" on "graphs"("name");
CREATE TABLE IF NOT EXISTS "information"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "owner" varchar,
  "administrator" varchar,
  "storage" varchar,
  "security_need_c" integer,
  "sensitivity" varchar,
  "constraints" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "security_need_i" integer,
  "security_need_a" integer,
  "security_need_t" integer,
  "retention" varchar,
  "security_need_auth" integer,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "icon_id" integer,
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "document_id_fk_61928374" on "information"("icon_id");
CREATE INDEX "information_name_index" on "information"("name");
CREATE INDEX "information_perimeter_id_index" on "information"("perimeter_id");
CREATE TABLE IF NOT EXISTS "lans"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "lans_name_index" on "lans"("name");
CREATE INDEX "lans_perimeter_id_index" on "lans"("perimeter_id");
CREATE TABLE IF NOT EXISTS "logical_flows"(
  "id" integer primary key autoincrement not null,
  "name" varchar,
  "source_ip_range" varchar,
  "dest_ip_range" varchar,
  "source_port" varchar,
  "dest_port" varchar,
  "protocol" varchar,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "router_id" integer,
  "priority" integer,
  "action" varchar,
  "users" varchar,
  "interface" varchar,
  "schedule" varchar,
  "logical_server_source_id" integer,
  "peripheral_source_id" integer,
  "physical_server_source_id" integer,
  "storage_device_source_id" integer,
  "workstation_source_id" integer,
  "physical_security_device_source_id" integer,
  "logical_server_dest_id" integer,
  "peripheral_dest_id" integer,
  "physical_server_dest_id" integer,
  "storage_device_dest_id" integer,
  "workstation_dest_id" integer,
  "physical_security_device_dest_id" integer,
  "chain" varchar,
  "subnetwork_source_id" integer,
  "subnetwork_dest_id" integer,
  "security_device_source_id" integer,
  "security_device_dest_id" integer,
  "cluster_source_id" integer,
  "cluster_dest_id" integer,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("security_device_dest_id") references "security_devices"("id") on delete set null on update no action,
  foreign key("security_device_source_id") references "security_devices"("id") on delete set null on update no action,
  foreign key("physical_security_device_dest_id") references "physical_security_devices"("id") on delete cascade on update no action,
  foreign key("workstation_dest_id") references "workstations"("id") on delete cascade on update no action,
  foreign key("storage_device_dest_id") references "storage_devices"("id") on delete cascade on update no action,
  foreign key("physical_server_dest_id") references "physical_servers"("id") on delete cascade on update no action,
  foreign key("peripheral_dest_id") references "peripherals"("id") on delete cascade on update no action,
  foreign key("logical_server_dest_id") references "logical_servers"("id") on delete cascade on update no action,
  foreign key("physical_security_device_source_id") references "physical_security_devices"("id") on delete cascade on update no action,
  foreign key("workstation_source_id") references "workstations"("id") on delete cascade on update no action,
  foreign key("storage_device_source_id") references "storage_devices"("id") on delete cascade on update no action,
  foreign key("physical_server_source_id") references "physical_servers"("id") on delete cascade on update no action,
  foreign key("peripheral_source_id") references "peripherals"("id") on delete cascade on update no action,
  foreign key("logical_server_source_id") references "logical_servers"("id") on delete cascade on update no action,
  foreign key("router_id") references "routers"("id") on delete cascade on update no action,
  foreign key("subnetwork_source_id") references "subnetworks"("id") on delete set null on update no action,
  foreign key("subnetwork_dest_id") references "subnetworks"("id") on delete set null on update no action,
  foreign key("cluster_source_id") references "clusters"("id") on delete set null on update no action,
  foreign key("cluster_dest_id") references "clusters"("id") on delete set null on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "logical_flows_name_index" on "logical_flows"("name");
CREATE INDEX "logical_flows_perimeter_id_index" on "logical_flows"(
  "perimeter_id"
);
CREATE INDEX "logical_flows_subnetwork_dest_id_index" on "logical_flows"(
  "subnetwork_dest_id"
);
CREATE INDEX "logical_flows_subnetwork_source_id_index" on "logical_flows"(
  "subnetwork_source_id"
);
CREATE INDEX "router_id_fk_4382393" on "logical_flows"("router_id");
CREATE TABLE IF NOT EXISTS "logical_servers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "net_services" varchar,
  "configuration" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "operating_system" varchar,
  "address_ip" varchar,
  "cpu" varchar,
  "memory" varchar,
  "environment" varchar,
  "disk" integer,
  "install_date" date,
  "update_date" date,
  "disk_used" integer,
  "attributes" varchar,
  "patching_frequency" integer,
  "next_update" date,
  "cluster_id" integer,
  "domain_id" integer,
  "type" varchar,
  "active" tinyint(1),
  "icon_id" integer,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("domain_id") references "domains"("id") on delete set null on update no action,
  foreign key("domain_id") references "domains"("id") on delete set null on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "cluster_id_fk_5435359" on "logical_servers"("cluster_id");
CREATE INDEX "document_id_fk_51303394" on "logical_servers"("icon_id");
CREATE INDEX "domain_id_fk_493844" on "logical_servers"("domain_id");
CREATE INDEX "logical_servers_active" on "logical_servers"("active");
CREATE INDEX "logical_servers_name_index" on "logical_servers"("name");
CREATE INDEX "logical_servers_perimeter_id_index" on "logical_servers"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "macro_processuses"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "io_elements" text,
  "security_need_c" integer,
  "owner" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "security_need_i" integer,
  "security_need_a" integer,
  "security_need_t" integer,
  "security_need_auth" integer,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "macro_processuses_name_index" on "macro_processuses"("name");
CREATE INDEX "macro_processuses_perimeter_id_index" on "macro_processuses"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "mans"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "description" text,
  "parent_man_id" integer,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("parent_man_id") references "mans"("id") on delete set null on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "man_id_fk_4385454" on "mans"("parent_man_id");
CREATE INDEX "mans_name_index" on "mans"("name");
CREATE INDEX "mans_perimeter_id_index" on "mans"("perimeter_id");
CREATE TABLE IF NOT EXISTS "network_switches"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "ip" varchar,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "network_switches_name_index" on "network_switches"("name");
CREATE INDEX "network_switches_perimeter_id_index" on "network_switches"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "networks"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "protocol_type" varchar,
  "responsible" varchar,
  "responsible_sec" varchar,
  "security_need_c" integer,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "security_need_i" integer,
  "security_need_a" integer,
  "security_need_t" integer,
  "security_need_auth" integer,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "networks_name_index" on "networks"("name");
CREATE INDEX "networks_perimeter_id_index" on "networks"("perimeter_id");
CREATE TABLE IF NOT EXISTS "operations"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "process_id" integer,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("process_id") references "processes"("id") on delete cascade on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "operations_name_index" on "operations"("name");
CREATE INDEX "operations_perimeter_id_index" on "operations"("perimeter_id");
CREATE INDEX "process_id_fk_7945129" on "operations"("process_id");
CREATE TABLE IF NOT EXISTS "peripherals"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "type" varchar,
  "description" text,
  "responsible" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "site_id" integer,
  "building_id" integer,
  "bay_id" integer,
  "vendor" varchar,
  "product" varchar,
  "version" varchar,
  "address_ip" varchar,
  "provider_id" integer,
  "icon_id" integer,
  "ext_refs" varchar,
  "domain_id" integer,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("site_id") references "sites"("id") on delete no action on update no action,
  foreign key("building_id") references "buildings"("id") on delete no action on update no action,
  foreign key("bay_id") references "bays"("id") on delete no action on update no action,
  foreign key("provider_id") references "entities"("id") on delete no action on update no action,
  foreign key("domain_id") references "domains"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "bay_fk_1485451" on "peripherals"("bay_id");
CREATE INDEX "building_fk_1485450" on "peripherals"("building_id");
CREATE INDEX "document_id_fk_129484" on "peripherals"("icon_id");
CREATE INDEX "entity_id_fk_4383234" on "peripherals"("provider_id");
CREATE INDEX "peripherals_name_index" on "peripherals"("name");
CREATE INDEX "peripherals_perimeter_id_index" on "peripherals"("perimeter_id");
CREATE INDEX "site_fk_1485449" on "peripherals"("site_id");
CREATE TABLE IF NOT EXISTS "phones"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "type" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "site_id" integer,
  "building_id" integer,
  "physical_switch_id" integer,
  "vendor" varchar,
  "product" varchar,
  "version" varchar,
  "address_ip" varchar,
  "ext_refs" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("building_id") references "buildings"("id") on delete no action on update no action,
  foreign key("physical_switch_id") references "physical_switches"("id") on delete no action on update no action,
  foreign key("site_id") references "sites"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "building_fk_1485480" on "phones"("building_id");
CREATE INDEX "phones_name_index" on "phones"("name");
CREATE INDEX "phones_perimeter_id_index" on "phones"("perimeter_id");
CREATE INDEX "physical_switch_fk_5738332" on "phones"("physical_switch_id");
CREATE INDEX "site_fk_1485479" on "phones"("site_id");
CREATE TABLE IF NOT EXISTS "physical_links"(
  "id" integer primary key autoincrement not null,
  "src_port" varchar,
  "dest_port" varchar,
  "peripheral_src_id" integer,
  "phone_src_id" integer,
  "physical_router_src_id" integer,
  "physical_security_device_src_id" integer,
  "physical_server_src_id" integer,
  "physical_switch_src_id" integer,
  "storage_device_src_id" integer,
  "wifi_terminal_src_id" integer,
  "workstation_src_id" integer,
  "peripheral_dest_id" integer,
  "phone_dest_id" integer,
  "physical_router_dest_id" integer,
  "physical_security_device_dest_id" integer,
  "physical_server_dest_id" integer,
  "physical_switch_dest_id" integer,
  "storage_device_dest_id" integer,
  "wifi_terminal_dest_id" integer,
  "workstation_dest_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "router_src_id" integer,
  "router_dest_id" integer,
  "network_switch_src_id" integer,
  "network_switch_dest_id" integer,
  "logical_server_src_id" integer,
  "logical_server_dest_id" integer,
  "type" varchar,
  "color" varchar,
  "ext_refs" varchar,
  "attributes" varchar,
  "description" text,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("workstation_dest_id") references "workstations"("id") on delete cascade on update no action,
  foreign key("wifi_terminal_dest_id") references "wifi_terminals"("id") on delete cascade on update no action,
  foreign key("storage_device_dest_id") references "storage_devices"("id") on delete cascade on update no action,
  foreign key("physical_switch_dest_id") references "physical_switches"("id") on delete cascade on update no action,
  foreign key("physical_server_dest_id") references "physical_servers"("id") on delete cascade on update no action,
  foreign key("physical_security_device_dest_id") references "physical_security_devices"("id") on delete cascade on update no action,
  foreign key("physical_router_dest_id") references "physical_routers"("id") on delete cascade on update no action,
  foreign key("phone_dest_id") references "phones"("id") on delete cascade on update no action,
  foreign key("peripheral_dest_id") references "peripherals"("id") on delete cascade on update no action,
  foreign key("workstation_src_id") references "workstations"("id") on delete cascade on update no action,
  foreign key("wifi_terminal_src_id") references "wifi_terminals"("id") on delete cascade on update no action,
  foreign key("storage_device_src_id") references "storage_devices"("id") on delete cascade on update no action,
  foreign key("physical_switch_src_id") references "physical_switches"("id") on delete cascade on update no action,
  foreign key("physical_server_src_id") references "physical_servers"("id") on delete cascade on update no action,
  foreign key("physical_security_device_src_id") references "physical_security_devices"("id") on delete cascade on update no action,
  foreign key("physical_router_src_id") references "physical_routers"("id") on delete cascade on update no action,
  foreign key("phone_src_id") references "phones"("id") on delete cascade on update no action,
  foreign key("peripheral_src_id") references "peripherals"("id") on delete cascade on update no action,
  foreign key("router_src_id") references "routers"("id") on delete cascade on update no action,
  foreign key("router_dest_id") references "routers"("id") on delete cascade on update no action,
  foreign key("network_switch_src_id") references "network_switches"("id") on delete cascade on update no action,
  foreign key("network_switch_dest_id") references "network_switches"("id") on delete cascade on update no action,
  foreign key("logical_server_src_id") references "logical_servers"("id") on delete cascade on update no action,
  foreign key("logical_server_dest_id") references "logical_servers"("id") on delete cascade on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "logical_server_dest_id_fk" on "physical_links"(
  "logical_server_dest_id"
);
CREATE INDEX "logical_server_src_id_fk" on "physical_links"(
  "logical_server_src_id"
);
CREATE INDEX "network_switches_dest_id_fk" on "physical_links"(
  "network_switch_dest_id"
);
CREATE INDEX "network_switches_src_id_fk" on "physical_links"(
  "network_switch_src_id"
);
CREATE INDEX "peripheral_dest_id_fk" on "physical_links"("peripheral_dest_id");
CREATE INDEX "peripheral_src_id_fk" on "physical_links"("peripheral_src_id");
CREATE INDEX "phone_dest_id_fk" on "physical_links"("phone_dest_id");
CREATE INDEX "phone_src_id_fk" on "physical_links"("phone_src_id");
CREATE INDEX "physical_links_perimeter_id_index" on "physical_links"(
  "perimeter_id"
);
CREATE INDEX "physical_router_dest_id_fk" on "physical_links"(
  "physical_router_dest_id"
);
CREATE INDEX "physical_router_src_id_fk" on "physical_links"(
  "physical_router_src_id"
);
CREATE INDEX "physical_security_device_dest_id_fk" on "physical_links"(
  "physical_security_device_dest_id"
);
CREATE INDEX "physical_security_device_src_id_fk" on "physical_links"(
  "physical_security_device_src_id"
);
CREATE INDEX "physical_server_dest_id_fk" on "physical_links"(
  "physical_server_dest_id"
);
CREATE INDEX "physical_server_src_id_fk" on "physical_links"(
  "physical_server_src_id"
);
CREATE INDEX "physical_switch_dest_id_fk" on "physical_links"(
  "physical_switch_dest_id"
);
CREATE INDEX "physical_switch_src_id_fk" on "physical_links"(
  "physical_switch_src_id"
);
CREATE INDEX "router_dest_id_fk" on "physical_links"("router_dest_id");
CREATE INDEX "router_src_id_fk" on "physical_links"("router_src_id");
CREATE INDEX "storage_device_dest_id_fk" on "physical_links"(
  "storage_device_dest_id"
);
CREATE INDEX "storage_device_src_id_fk" on "physical_links"(
  "storage_device_src_id"
);
CREATE INDEX "wifi_terminal_dest_id_fk" on "physical_links"(
  "wifi_terminal_dest_id"
);
CREATE INDEX "wifi_terminal_src_id_fk" on "physical_links"(
  "wifi_terminal_src_id"
);
CREATE INDEX "workstation_dest_id_fk" on "physical_links"(
  "workstation_dest_id"
);
CREATE INDEX "workstation_src_id_fk" on "physical_links"("workstation_src_id");
CREATE TABLE IF NOT EXISTS "physical_routers"(
  "id" integer primary key autoincrement not null,
  "description" text,
  "type" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "site_id" integer,
  "building_id" integer,
  "bay_id" integer,
  "name" varchar not null,
  "vendor" varchar,
  "product" varchar,
  "version" varchar,
  "ext_refs" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("site_id") references "sites"("id") on delete no action on update no action,
  foreign key("building_id") references "buildings"("id") on delete no action on update no action,
  foreign key("bay_id") references "bays"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "bay_fk_1485499" on "physical_routers"("bay_id");
CREATE INDEX "building_fk_1485498" on "physical_routers"("building_id");
CREATE INDEX "physical_routers_name_index" on "physical_routers"("name");
CREATE INDEX "physical_routers_perimeter_id_index" on "physical_routers"(
  "perimeter_id"
);
CREATE INDEX "site_fk_1485497" on "physical_routers"("site_id");
CREATE TABLE IF NOT EXISTS "physical_security_devices"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "type" varchar,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "site_id" integer,
  "building_id" integer,
  "bay_id" integer,
  "address_ip" varchar,
  "attributes" varchar,
  "icon_id" integer,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("site_id") references "sites"("id") on delete no action on update no action,
  foreign key("building_id") references "buildings"("id") on delete no action on update no action,
  foreign key("bay_id") references "bays"("id") on delete no action on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "bay_fk_1485519" on "physical_security_devices"("bay_id");
CREATE INDEX "building_fk_1485518" on "physical_security_devices"(
  "building_id"
);
CREATE INDEX "document_id_fk_493827312" on "physical_security_devices"(
  "icon_id"
);
CREATE INDEX "physical_security_devices_name_index" on "physical_security_devices"(
  "name"
);
CREATE INDEX "physical_security_devices_perimeter_id_index" on "physical_security_devices"(
  "perimeter_id"
);
CREATE INDEX "site_fk_1485517" on "physical_security_devices"("site_id");
CREATE TABLE IF NOT EXISTS "physical_servers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "responsible" varchar,
  "configuration" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "site_id" integer,
  "building_id" integer,
  "bay_id" integer,
  "physical_switch_id" integer,
  "type" varchar,
  "vendor" varchar,
  "product" varchar,
  "version" varchar,
  "address_ip" varchar,
  "cpu" varchar,
  "memory" varchar,
  "disk" varchar,
  "disk_used" varchar,
  "operating_system" varchar,
  "install_date" datetime,
  "update_date" datetime,
  "patching_group" varchar,
  "patching_frequency" integer,
  "next_update" date,
  "icon_id" integer,
  "ext_refs" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("site_id") references "sites"("id") on delete no action on update no action,
  foreign key("physical_switch_id") references "physical_switches"("id") on delete no action on update no action,
  foreign key("building_id") references "buildings"("id") on delete no action on update no action,
  foreign key("bay_id") references "bays"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "bay_fk_1485324" on "physical_servers"("bay_id");
CREATE INDEX "building_fk_1485323" on "physical_servers"("building_id");
CREATE INDEX "document_id_fk_5328384" on "physical_servers"("icon_id");
CREATE INDEX "physical_servers_name_index" on "physical_servers"("name");
CREATE INDEX "physical_servers_perimeter_id_index" on "physical_servers"(
  "perimeter_id"
);
CREATE INDEX "physical_switch_fk_8732342" on "physical_servers"(
  "physical_switch_id"
);
CREATE INDEX "site_fk_1485322" on "physical_servers"("site_id");
CREATE TABLE IF NOT EXISTS "physical_switches"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "type" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "site_id" integer,
  "building_id" integer,
  "bay_id" integer,
  "vendor" varchar,
  "product" varchar,
  "version" varchar,
  "icon_id" integer,
  "ext_refs" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("site_id") references "sites"("id") on delete no action on update no action,
  foreign key("building_id") references "buildings"("id") on delete no action on update no action,
  foreign key("bay_id") references "bays"("id") on delete no action on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "bay_fk_1485493" on "physical_switches"("bay_id");
CREATE INDEX "building_fk_1485489" on "physical_switches"("building_id");
CREATE INDEX "physical_switches_name_index" on "physical_switches"("name");
CREATE INDEX "physical_switches_perimeter_id_index" on "physical_switches"(
  "perimeter_id"
);
CREATE INDEX "site_fk_1485488" on "physical_switches"("site_id");
CREATE TABLE IF NOT EXISTS "processes"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "owner" varchar,
  "security_need_c" integer,
  "in_out" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "macroprocess_id" integer,
  "security_need_i" integer,
  "security_need_a" integer,
  "security_need_t" integer,
  "security_need_auth" integer,
  "icon_id" integer,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("macroprocess_id") references "macro_processuses"("id") on delete no action on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "document_id_fk_5938654" on "processes"("icon_id");
CREATE INDEX "process_fk_4342342" on "processes"("macroprocess_id");
CREATE INDEX "processes_name_index" on "processes"("name");
CREATE INDEX "processes_perimeter_id_index" on "processes"("perimeter_id");
CREATE TABLE IF NOT EXISTS "relations"(
  "id" integer primary key autoincrement not null,
  "importance" integer,
  "name" varchar not null,
  "type" varchar,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "source_id" integer not null,
  "destination_id" integer not null,
  "attributes" varchar,
  "reference" varchar,
  "responsible" varchar,
  "order_number" varchar,
  "active" tinyint(1) not null default('1'),
  "start_date" date,
  "end_date" date,
  "comments" text,
  "security_need_c" integer,
  "security_need_i" integer,
  "security_need_a" integer,
  "security_need_t" integer,
  "security_need_auth" integer,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("destination_id") references "entities"("id") on delete no action on update no action,
  foreign key("source_id") references "entities"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "destination_fk_1494373" on "relations"("destination_id");
CREATE INDEX "relations_name_index" on "relations"("name");
CREATE INDEX "relations_perimeter_id_index" on "relations"("perimeter_id");
CREATE INDEX "source_fk_1494372" on "relations"("source_id");
CREATE TABLE IF NOT EXISTS "routers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "rules" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ip_addresses" text,
  "type" varchar,
  "cluster_id" integer,
  "ext_refs" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "cluster_id_fk_4398834" on "routers"("cluster_id");
CREATE INDEX "routers_name_index" on "routers"("name");
CREATE INDEX "routers_perimeter_id_index" on "routers"("perimeter_id");
CREATE TABLE IF NOT EXISTS "security_controls"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "security_controls_name_index" on "security_controls"("name");
CREATE INDEX "security_controls_perimeter_id_index" on "security_controls"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "security_devices"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "vendor" varchar,
  "product" varchar,
  "version" varchar,
  "type" varchar,
  "attributes" varchar,
  "icon_id" integer,
  "address_ip" varchar,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "document_id_fk_432938439" on "security_devices"("icon_id");
CREATE INDEX "security_devices_name_index" on "security_devices"("name");
CREATE INDEX "security_devices_perimeter_id_index" on "security_devices"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "sites"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "icon_id" integer,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "document_id_fk_129485" on "sites"("icon_id");
CREATE INDEX "sites_name_index" on "sites"("name");
CREATE INDEX "sites_perimeter_id_index" on "sites"("perimeter_id");
CREATE TABLE IF NOT EXISTS "storage_devices"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "site_id" integer,
  "building_id" integer,
  "bay_id" integer,
  "physical_switch_id" integer,
  "vendor" varchar,
  "product" varchar,
  "version" varchar,
  "address_ip" varchar,
  "type" varchar,
  "ext_refs" varchar,
  "icon_id" integer,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("site_id") references "sites"("id") on delete no action on update no action,
  foreign key("physical_switch_id") references "physical_switches"("id") on delete no action on update no action,
  foreign key("building_id") references "buildings"("id") on delete no action on update no action,
  foreign key("bay_id") references "bays"("id") on delete no action on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "bay_fk_1485363" on "storage_devices"("bay_id");
CREATE INDEX "building_fk_1485362" on "storage_devices"("building_id");
CREATE INDEX "document_id_fk_53812733" on "storage_devices"("icon_id");
CREATE INDEX "physical_switch_fk_4025543" on "storage_devices"(
  "physical_switch_id"
);
CREATE INDEX "site_fk_1485361" on "storage_devices"("site_id");
CREATE INDEX "storage_devices_name_index" on "storage_devices"("name");
CREATE INDEX "storage_devices_perimeter_id_index" on "storage_devices"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "subnetworks"(
  "id" integer primary key autoincrement not null,
  "description" text,
  "address" varchar,
  "ip_allocation_type" varchar,
  "responsible_exp" varchar,
  "dmz" varchar,
  "wifi" varchar,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "connected_subnets_id" integer,
  "gateway_id" integer,
  "zone" varchar,
  "vlan_id" integer,
  "network_id" integer,
  "default_gateway" varchar,
  "subnetwork_id" integer,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("network_id") references "networks"("id") on delete no action on update no action,
  foreign key("vlan_id") references "vlans"("id") on delete no action on update no action,
  foreign key("connected_subnets_id") references "subnetworks"("id") on delete no action on update no action,
  foreign key("gateway_id") references "gateways"("id") on delete no action on update no action,
  foreign key("subnetwork_id") references "subnetworks"("id") on delete set null on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "connected_subnets_fk_1483256" on "subnetworks"(
  "connected_subnets_id"
);
CREATE INDEX "gateway_fk_1492376" on "subnetworks"("gateway_id");
CREATE INDEX "network_fk_5476544" on "subnetworks"("network_id");
CREATE INDEX "subnetworks_name_index" on "subnetworks"("name");
CREATE INDEX "subnetworks_perimeter_id_index" on "subnetworks"("perimeter_id");
CREATE INDEX "subnetworks_subnetwork_id_index" on "subnetworks"(
  "subnetwork_id"
);
CREATE INDEX "vlan_fk_6844934" on "subnetworks"("vlan_id");
CREATE TABLE IF NOT EXISTS "tasks"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "tasks_name_index" on "tasks"("name");
CREATE INDEX "tasks_perimeter_id_index" on "tasks"("perimeter_id");
CREATE TABLE IF NOT EXISTS "vlans"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "vlan_id" integer,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "vlans_name_index" on "vlans"("name");
CREATE INDEX "vlans_perimeter_id_index" on "vlans"("perimeter_id");
CREATE TABLE IF NOT EXISTS "wans"(
  "id" integer primary key autoincrement not null,
  "name" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "wans_name_index" on "wans"("name");
CREATE INDEX "wans_perimeter_id_index" on "wans"("perimeter_id");
CREATE TABLE IF NOT EXISTS "wifi_terminals"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "type" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "site_id" integer,
  "building_id" integer,
  "physical_switch_id" integer,
  "vendor" varchar,
  "product" varchar,
  "version" varchar,
  "address_ip" varchar,
  "ext_refs" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("building_id") references "buildings"("id") on delete no action on update no action,
  foreign key("physical_switch_id") references "physical_switches"("id") on delete no action on update no action,
  foreign key("site_id") references "sites"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "building_fk_1485508" on "wifi_terminals"("building_id");
CREATE INDEX "physical_switch_fk_593584" on "wifi_terminals"(
  "physical_switch_id"
);
CREATE INDEX "site_fk_1485507" on "wifi_terminals"("site_id");
CREATE INDEX "wifi_terminals_name_index" on "wifi_terminals"("name");
CREATE INDEX "wifi_terminals_perimeter_id_index" on "wifi_terminals"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "workstations"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "site_id" integer,
  "building_id" integer,
  "physical_switch_id" integer,
  "type" varchar,
  "operating_system" varchar,
  "address_ip" varchar,
  "cpu" varchar,
  "memory" varchar,
  "disk" integer,
  "vendor" varchar,
  "product" varchar,
  "version" varchar,
  "icon_id" integer,
  "entity_id" integer,
  "user_id" integer,
  "other_user" varchar,
  "status" varchar,
  "manufacturer" varchar,
  "model" varchar,
  "serial_number" varchar,
  "last_inventory_date" date,
  "warranty_end_date" date,
  "domain_id" integer,
  "warranty" varchar,
  "warranty_start_date" date,
  "warranty_period" varchar,
  "agent_version" varchar,
  "update_source" varchar,
  "network_id" integer,
  "network_port_type" varchar,
  "mac_address" varchar,
  "purchase_date" date,
  "fin_value" numeric,
  "ext_refs" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("network_id") references "networks"("id") on delete no action on update no action,
  foreign key("domain_id") references "domains"("id") on delete no action on update no action,
  foreign key("user_id") references "admin_users"("id") on delete no action on update no action,
  foreign key("entity_id") references "entities"("id") on delete no action on update no action,
  foreign key("site_id") references "sites"("id") on delete no action on update no action,
  foreign key("physical_switch_id") references "physical_switches"("id") on delete no action on update no action,
  foreign key("building_id") references "buildings"("id") on delete no action on update no action,
  foreign key("icon_id") references "documents"("id") on delete no action on update no action,
  foreign key("domain_id") references "domains"("id") on delete no action on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "building_fk_1485333" on "workstations"("building_id");
CREATE INDEX "document_id_fk_129483" on "workstations"("icon_id");
CREATE INDEX "physical_switch_fk_0938434" on "workstations"(
  "physical_switch_id"
);
CREATE INDEX "site_fk_1485332" on "workstations"("site_id");
CREATE INDEX "workstations_name_index" on "workstations"("name");
CREATE INDEX "workstations_perimeter_id_index" on "workstations"(
  "perimeter_id"
);
CREATE TABLE IF NOT EXISTS "zone_admins"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "type" varchar,
  "attributes" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "zone_admins_name_index" on "zone_admins"("name");
CREATE INDEX "zone_admins_perimeter_id_index" on "zone_admins"("perimeter_id");
CREATE TABLE IF NOT EXISTS "zones"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "type" varchar,
  "attributes" varchar,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "ext_refs" varchar,
  "perimeter_id" integer not null default('1'),
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  foreign key("perimeter_id") references "perimeters"("id") on delete restrict on update no action,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE INDEX "zones_perimeter_id_index" on "zones"("perimeter_id");

INSERT INTO migrations VALUES(1,'2016_06_01_000001_create_oauth_auth_codes_table',1);
INSERT INTO migrations VALUES(2,'2016_06_01_000002_create_oauth_access_tokens_table',1);
INSERT INTO migrations VALUES(3,'2016_06_01_000003_create_oauth_refresh_tokens_table',1);
INSERT INTO migrations VALUES(4,'2016_06_01_000004_create_oauth_clients_table',1);
INSERT INTO migrations VALUES(5,'2016_06_01_000005_create_oauth_personal_access_clients_table',1);
INSERT INTO migrations VALUES(6,'2021_05_08_191249_create_activities_table',1);
INSERT INTO migrations VALUES(7,'2021_05_08_191249_create_activity_operation_table',1);
INSERT INTO migrations VALUES(8,'2021_05_08_191249_create_activity_process_table',1);
INSERT INTO migrations VALUES(9,'2021_05_08_191249_create_actor_operation_table',1);
INSERT INTO migrations VALUES(10,'2021_05_08_191249_create_actors_table',1);
INSERT INTO migrations VALUES(11,'2021_05_08_191249_create_annuaires_table',1);
INSERT INTO migrations VALUES(12,'2021_05_08_191249_create_application_blocks_table',1);
INSERT INTO migrations VALUES(13,'2021_05_08_191249_create_application_module_application_service_table',1);
INSERT INTO migrations VALUES(14,'2021_05_08_191249_create_application_modules_table',1);
INSERT INTO migrations VALUES(15,'2021_05_08_191249_create_application_service_m_application_table',1);
INSERT INTO migrations VALUES(16,'2021_05_08_191249_create_application_services_table',1);
INSERT INTO migrations VALUES(17,'2021_05_08_191249_create_audit_logs_table',1);
INSERT INTO migrations VALUES(18,'2021_05_08_191249_create_bay_wifi_terminal_table',1);
INSERT INTO migrations VALUES(19,'2021_05_08_191249_create_bays_table',1);
INSERT INTO migrations VALUES(20,'2021_05_08_191249_create_buildings_table',1);
INSERT INTO migrations VALUES(21,'2021_05_08_191249_create_database_entity_table',1);
INSERT INTO migrations VALUES(22,'2021_05_08_191249_create_database_information_table',1);
INSERT INTO migrations VALUES(23,'2021_05_08_191249_create_database_m_application_table',1);
INSERT INTO migrations VALUES(24,'2021_05_08_191249_create_databases_table',1);
INSERT INTO migrations VALUES(25,'2021_05_08_191249_create_dhcp_servers_table',1);
INSERT INTO migrations VALUES(26,'2021_05_08_191249_create_dnsservers_table',1);
INSERT INTO migrations VALUES(27,'2021_05_08_191249_create_domaine_ad_forest_ad_table',1);
INSERT INTO migrations VALUES(28,'2021_05_08_191249_create_domaine_ads_table',1);
INSERT INTO migrations VALUES(29,'2021_05_08_191249_create_entities_table',1);
INSERT INTO migrations VALUES(30,'2021_05_08_191249_create_entity_m_application_table',1);
INSERT INTO migrations VALUES(31,'2021_05_08_191249_create_entity_process_table',1);
INSERT INTO migrations VALUES(32,'2021_05_08_191249_create_external_connected_entities_table',1);
INSERT INTO migrations VALUES(33,'2021_05_08_191249_create_external_connected_entity_network_table',1);
INSERT INTO migrations VALUES(34,'2021_05_08_191249_create_fluxes_table',1);
INSERT INTO migrations VALUES(35,'2021_05_08_191249_create_forest_ads_table',1);
INSERT INTO migrations VALUES(36,'2021_05_08_191249_create_gateways_table',1);
INSERT INTO migrations VALUES(37,'2021_05_08_191249_create_information_process_table',1);
INSERT INTO migrations VALUES(38,'2021_05_08_191249_create_information_table',1);
INSERT INTO migrations VALUES(39,'2021_05_08_191249_create_lan_man_table',1);
INSERT INTO migrations VALUES(40,'2021_05_08_191249_create_lan_wan_table',1);
INSERT INTO migrations VALUES(41,'2021_05_08_191249_create_lans_table',1);
INSERT INTO migrations VALUES(42,'2021_05_08_191249_create_logical_server_m_application_table',1);
INSERT INTO migrations VALUES(43,'2021_05_08_191249_create_logical_server_physical_server_table',1);
INSERT INTO migrations VALUES(44,'2021_05_08_191249_create_logical_servers_table',1);
INSERT INTO migrations VALUES(45,'2021_05_08_191249_create_m_application_process_table',1);
INSERT INTO migrations VALUES(46,'2021_05_08_191249_create_m_applications_table',1);
INSERT INTO migrations VALUES(47,'2021_05_08_191249_create_macro_processuses_table',1);
INSERT INTO migrations VALUES(48,'2021_05_08_191249_create_man_wan_table',1);
INSERT INTO migrations VALUES(49,'2021_05_08_191249_create_mans_table',1);
INSERT INTO migrations VALUES(50,'2021_05_08_191249_create_media_table',1);
INSERT INTO migrations VALUES(51,'2021_05_08_191249_create_network_subnetword_table',1);
INSERT INTO migrations VALUES(52,'2021_05_08_191249_create_network_switches_table',1);
INSERT INTO migrations VALUES(53,'2021_05_08_191249_create_networks_table',1);
INSERT INTO migrations VALUES(54,'2021_05_08_191249_create_operation_task_table',1);
INSERT INTO migrations VALUES(55,'2021_05_08_191249_create_operations_table',1);
INSERT INTO migrations VALUES(56,'2021_05_08_191249_create_password_resets_table',1);
INSERT INTO migrations VALUES(57,'2021_05_08_191249_create_peripherals_table',1);
INSERT INTO migrations VALUES(58,'2021_05_08_191249_create_permission_role_table',1);
INSERT INTO migrations VALUES(59,'2021_05_08_191249_create_permissions_table',1);
INSERT INTO migrations VALUES(60,'2021_05_08_191249_create_phones_table',1);
INSERT INTO migrations VALUES(61,'2021_05_08_191249_create_physical_router_vlan_table',1);
INSERT INTO migrations VALUES(62,'2021_05_08_191249_create_physical_routers_table',1);
INSERT INTO migrations VALUES(63,'2021_05_08_191249_create_physical_security_devices_table',1);
INSERT INTO migrations VALUES(64,'2021_05_08_191249_create_physical_servers_table',1);
INSERT INTO migrations VALUES(65,'2021_05_08_191249_create_physical_switches_table',1);
INSERT INTO migrations VALUES(66,'2021_05_08_191249_create_processes_table',1);
INSERT INTO migrations VALUES(67,'2021_05_08_191249_create_relations_table',1);
INSERT INTO migrations VALUES(68,'2021_05_08_191249_create_role_user_table',1);
INSERT INTO migrations VALUES(69,'2021_05_08_191249_create_roles_table',1);
INSERT INTO migrations VALUES(70,'2021_05_08_191249_create_routers_table',1);
INSERT INTO migrations VALUES(71,'2021_05_08_191249_create_security_devices_table',1);
INSERT INTO migrations VALUES(72,'2021_05_08_191249_create_sites_table',1);
INSERT INTO migrations VALUES(73,'2021_05_08_191249_create_storage_devices_table',1);
INSERT INTO migrations VALUES(74,'2021_05_08_191249_create_subnetworks_table',1);
INSERT INTO migrations VALUES(75,'2021_05_08_191249_create_tasks_table',1);
INSERT INTO migrations VALUES(76,'2021_05_08_191249_create_users_table',1);
INSERT INTO migrations VALUES(77,'2021_05_08_191249_create_vlans_table',1);
INSERT INTO migrations VALUES(78,'2021_05_08_191249_create_wans_table',1);
INSERT INTO migrations VALUES(79,'2021_05_08_191249_create_wifi_terminals_table',1);
INSERT INTO migrations VALUES(80,'2021_05_08_191249_create_workstations_table',1);
INSERT INTO migrations VALUES(81,'2021_05_08_191249_create_zone_admins_table',1);
INSERT INTO migrations VALUES(82,'2021_05_08_191251_add_foreign_keys_to_activity_operation_table',1);
INSERT INTO migrations VALUES(83,'2021_05_08_191251_add_foreign_keys_to_activity_process_table',1);
INSERT INTO migrations VALUES(84,'2021_05_08_191251_add_foreign_keys_to_actor_operation_table',1);
INSERT INTO migrations VALUES(85,'2021_05_08_191251_add_foreign_keys_to_annuaires_table',1);
INSERT INTO migrations VALUES(86,'2021_05_08_191251_add_foreign_keys_to_application_module_application_service_table',1);
INSERT INTO migrations VALUES(87,'2021_05_08_191251_add_foreign_keys_to_application_service_m_application_table',1);
INSERT INTO migrations VALUES(88,'2021_05_08_191251_add_foreign_keys_to_bay_wifi_terminal_table',1);
INSERT INTO migrations VALUES(89,'2021_05_08_191251_add_foreign_keys_to_bays_table',1);
INSERT INTO migrations VALUES(90,'2021_05_08_191251_add_foreign_keys_to_buildings_table',1);
INSERT INTO migrations VALUES(91,'2021_05_08_191251_add_foreign_keys_to_database_entity_table',1);
INSERT INTO migrations VALUES(92,'2021_05_08_191251_add_foreign_keys_to_database_information_table',1);
INSERT INTO migrations VALUES(93,'2021_05_08_191251_add_foreign_keys_to_database_m_application_table',1);
INSERT INTO migrations VALUES(94,'2021_05_08_191251_add_foreign_keys_to_databases_table',1);
INSERT INTO migrations VALUES(95,'2021_05_08_191251_add_foreign_keys_to_domaine_ad_forest_ad_table',1);
INSERT INTO migrations VALUES(96,'2021_05_08_191251_add_foreign_keys_to_entity_m_application_table',1);
INSERT INTO migrations VALUES(97,'2021_05_08_191251_add_foreign_keys_to_entity_process_table',1);
INSERT INTO migrations VALUES(98,'2021_05_08_191251_add_foreign_keys_to_external_connected_entity_network_table',1);
INSERT INTO migrations VALUES(99,'2021_05_08_191251_add_foreign_keys_to_fluxes_table',1);
INSERT INTO migrations VALUES(100,'2021_05_08_191251_add_foreign_keys_to_forest_ads_table',1);
INSERT INTO migrations VALUES(101,'2021_05_08_191251_add_foreign_keys_to_information_process_table',1);
INSERT INTO migrations VALUES(102,'2021_05_08_191251_add_foreign_keys_to_lan_man_table',1);
INSERT INTO migrations VALUES(103,'2021_05_08_191251_add_foreign_keys_to_lan_wan_table',1);
INSERT INTO migrations VALUES(104,'2021_05_08_191251_add_foreign_keys_to_logical_server_m_application_table',1);
INSERT INTO migrations VALUES(105,'2021_05_08_191251_add_foreign_keys_to_logical_server_physical_server_table',1);
INSERT INTO migrations VALUES(106,'2021_05_08_191251_add_foreign_keys_to_m_application_process_table',1);
INSERT INTO migrations VALUES(107,'2021_05_08_191251_add_foreign_keys_to_m_applications_table',1);
INSERT INTO migrations VALUES(108,'2021_05_08_191251_add_foreign_keys_to_man_wan_table',1);
INSERT INTO migrations VALUES(109,'2021_05_08_191251_add_foreign_keys_to_network_subnetword_table',1);
INSERT INTO migrations VALUES(110,'2021_05_08_191251_add_foreign_keys_to_operation_task_table',1);
INSERT INTO migrations VALUES(111,'2021_05_08_191251_add_foreign_keys_to_peripherals_table',1);
INSERT INTO migrations VALUES(112,'2021_05_08_191251_add_foreign_keys_to_permission_role_table',1);
INSERT INTO migrations VALUES(113,'2021_05_08_191251_add_foreign_keys_to_phones_table',1);
INSERT INTO migrations VALUES(114,'2021_05_08_191251_add_foreign_keys_to_physical_router_vlan_table',1);
INSERT INTO migrations VALUES(115,'2021_05_08_191251_add_foreign_keys_to_physical_routers_table',1);
INSERT INTO migrations VALUES(116,'2021_05_08_191251_add_foreign_keys_to_physical_security_devices_table',1);
INSERT INTO migrations VALUES(117,'2021_05_08_191251_add_foreign_keys_to_physical_servers_table',1);
INSERT INTO migrations VALUES(118,'2021_05_08_191251_add_foreign_keys_to_physical_switches_table',1);
INSERT INTO migrations VALUES(119,'2021_05_08_191251_add_foreign_keys_to_processes_table',1);
INSERT INTO migrations VALUES(120,'2021_05_08_191251_add_foreign_keys_to_relations_table',1);
INSERT INTO migrations VALUES(121,'2021_05_08_191251_add_foreign_keys_to_role_user_table',1);
INSERT INTO migrations VALUES(122,'2021_05_08_191251_add_foreign_keys_to_storage_devices_table',1);
INSERT INTO migrations VALUES(123,'2021_05_08_191251_add_foreign_keys_to_subnetworks_table',1);
INSERT INTO migrations VALUES(124,'2021_05_08_191251_add_foreign_keys_to_wifi_terminals_table',1);
INSERT INTO migrations VALUES(125,'2021_05_08_191251_add_foreign_keys_to_workstations_table',1);
INSERT INTO migrations VALUES(126,'2021_05_13_180642_add_cidt_criteria',1);
INSERT INTO migrations VALUES(127,'2021_05_19_161123_rename_subnetwork',1);
INSERT INTO migrations VALUES(128,'2021_06_22_170555_add_type',1);
INSERT INTO migrations VALUES(129,'2021_07_14_071311_create_certificates_table',1);
INSERT INTO migrations VALUES(130,'2021_08_08_125856_config_right',1);
INSERT INTO migrations VALUES(131,'2021_08_11_201624_certificate_application_link',1);
INSERT INTO migrations VALUES(132,'2021_08_18_171048_network_redesign',1);
INSERT INTO migrations VALUES(133,'2021_08_20_034757_default_gateway',1);
INSERT INTO migrations VALUES(134,'2021_08_28_152910_cleanup',1);
INSERT INTO migrations VALUES(135,'2021_09_19_125048_relation-inportance',1);
INSERT INTO migrations VALUES(136,'2021_09_21_161028_add_router_ip',1);
INSERT INTO migrations VALUES(137,'2021_09_22_114706_add_security_ciat',1);
INSERT INTO migrations VALUES(138,'2021_09_23_192127_rename_descrition',1);
INSERT INTO migrations VALUES(139,'2021_09_28_205405_add_direction_to_flows',1);
INSERT INTO migrations VALUES(140,'2021_10_12_210233_physical_router_name_type',1);
INSERT INTO migrations VALUES(141,'2021_10_19_102610_add_address_ip',1);
INSERT INTO migrations VALUES(142,'2021_11_23_204551_add_app_version',1);
INSERT INTO migrations VALUES(143,'2022_02_08_210603_create_cartographer_m_application_table',1);
INSERT INTO migrations VALUES(144,'2022_02_22_32654_add_cert_status',1);
INSERT INTO migrations VALUES(145,'2022_02_27_162738_add_functional_referent_to_m_application',1);
INSERT INTO migrations VALUES(146,'2022_02_27_163129_add_editor_to_m_application',1);
INSERT INTO migrations VALUES(147,'2022_02_27_192155_add_date_fields_to_m_application',1);
INSERT INTO migrations VALUES(148,'2022_02_28_205630_create_m_application_event_table',1);
INSERT INTO migrations VALUES(149,'2022_05_02_123756_add_update_to_logical_servers',1);
INSERT INTO migrations VALUES(150,'2022_05_18_140331_add_is_external_column_to_entities',1);
INSERT INTO migrations VALUES(151,'2022_05_21_103208_add_type_property_to_entities',1);
INSERT INTO migrations VALUES(152,'2022_06_27_061444_application_workstation',1);
INSERT INTO migrations VALUES(153,'2022_07_28_105153_add_link_operation_process',1);
INSERT INTO migrations VALUES(154,'2022_08_11_165441_add_vpn_fields',1);
INSERT INTO migrations VALUES(155,'2022_09_13_204845_cert_last_notification',1);
INSERT INTO migrations VALUES(156,'2022_12_17_115624_rto_rpo',1);
INSERT INTO migrations VALUES(157,'2023_01_03_205224_database_logical_server',1);
INSERT INTO migrations VALUES(158,'2023_01_08_123726_add_physical_link',1);
INSERT INTO migrations VALUES(159,'2023_01_27_165009_add_flux_nature',1);
INSERT INTO migrations VALUES(160,'2023_01_28_145242_add_logical_devices_link',1);
INSERT INTO migrations VALUES(161,'2023_02_09_164940_gdpr',1);
INSERT INTO migrations VALUES(162,'2023_03_16_123031_create_documents_table',1);
INSERT INTO migrations VALUES(163,'2023_03_22_185812_create_cpe',1);
INSERT INTO migrations VALUES(164,'2023_04_18_123308_add_gdpr_tables',1);
INSERT INTO migrations VALUES(165,'2023_05_29_161406_security_controls_links',1);
INSERT INTO migrations VALUES(166,'2023_06_14_120958_add_physical_address_ip',1);
INSERT INTO migrations VALUES(167,'2023_08_06_100128_add_physicalserver_size',1);
INSERT INTO migrations VALUES(168,'2023_08_07_183714_application_physical_server',1);
INSERT INTO migrations VALUES(169,'2023_09_04_111440_add_application_patching',1);
INSERT INTO migrations VALUES(170,'2023_09_26_074104_iot',1);
INSERT INTO migrations VALUES(171,'2023_10_28_124418_add_cluster',1);
INSERT INTO migrations VALUES(172,'2023_11_30_070804_fix_migration_typo',1);
INSERT INTO migrations VALUES(173,'2024_02_21_085107_application_patching',1);
INSERT INTO migrations VALUES(174,'2024_02_29_134239_patching_attributes',1);
INSERT INTO migrations VALUES(175,'2024_03_14_165211_router_ip_lenght',1);
INSERT INTO migrations VALUES(176,'2024_03_19_195927_contracts',1);
INSERT INTO migrations VALUES(177,'2024_04_06_161307_nomalization',1);
INSERT INTO migrations VALUES(178,'2024_04_08_105719_network_flow',1);
INSERT INTO migrations VALUES(179,'2024_04_14_072101_add_parent_entity',1);
INSERT INTO migrations VALUES(180,'2024_04_28_075916_normalize_process_name',1);
INSERT INTO migrations VALUES(181,'2024_05_09_180526_improve_network_flow',1);
INSERT INTO migrations VALUES(182,'2024_05_15_212326_routers_log_phys',1);
INSERT INTO migrations VALUES(183,'2024_06_03_165236_add_user',1);
INSERT INTO migrations VALUES(184,'2024_06_11_060639_external_connnected_entities_desc',1);
INSERT INTO migrations VALUES(185,'2024_06_18_125723_link_domain_lservers',1);
INSERT INTO migrations VALUES(186,'2024_08_27_200851_normalize_storage_devices',1);
INSERT INTO migrations VALUES(187,'2024_09_22_112404_add_icon',1);
INSERT INTO migrations VALUES(188,'2024_09_24_044657_move_icons_to_docs',1);
INSERT INTO migrations VALUES(189,'2024_09_24_084005_move_icons_to_docs',1);
INSERT INTO migrations VALUES(190,'2024_09_26_160952_building_attributes',1);
INSERT INTO migrations VALUES(191,'2024_10_31_220940_add_vlan_id',1);
INSERT INTO migrations VALUES(192,'2024_11_13_183902_add_type_to_logical_servers',1);
INSERT INTO migrations VALUES(193,'2024_11_26_130914_add_authenticity',1);
INSERT INTO migrations VALUES(194,'2025_01_03_130604_create_graphs_table',1);
INSERT INTO migrations VALUES(195,'2025_01_10_123601_logical_server_disabled',1);
INSERT INTO migrations VALUES(196,'2025_01_16_120601_add_icon_to_process',1);
INSERT INTO migrations VALUES(197,'2025_01_17_133444_add_table_containers',1);
INSERT INTO migrations VALUES(198,'2025_02_18_073549_application_assignation',1);
INSERT INTO migrations VALUES(199,'2025_03_17_094654_datetime_to_date',1);
INSERT INTO migrations VALUES(200,'2025_03_24_132409_remove_unique_name_deleted_at_indexes',1);
INSERT INTO migrations VALUES(201,'2025_03_26_133906_external_nullable',1);
INSERT INTO migrations VALUES(202,'2025_04_27_084635_add_icon_to_servers',1);
INSERT INTO migrations VALUES(203,'2025_04_27_123200_add_legal_basis',1);
INSERT INTO migrations VALUES(204,'2025_06_19_072710_add_databases_to_containers',1);
INSERT INTO migrations VALUES(205,'2025_06_28_155312_add_glpi_fields',1);
INSERT INTO migrations VALUES(206,'2025_07_01_065432_admin_user_fields',1);
INSERT INTO migrations VALUES(207,'2025_07_02_123433_rename_permission',1);
INSERT INTO migrations VALUES(208,'2025_07_07_070846_add_admin_user_application',1);
INSERT INTO migrations VALUES(209,'2025_07_08_065626_add_references_to_logical_flows',1);
INSERT INTO migrations VALUES(210,'2025_07_17_145227_activities_bia',1);
INSERT INTO migrations VALUES(211,'2025_07_17_150249_create_activity_impact_table',1);
INSERT INTO migrations VALUES(212,'2025_08_23_111003_add_pra',1);
INSERT INTO migrations VALUES(213,'2025_08_27_135552_physical_logical_security_devices',1);
INSERT INTO migrations VALUES(214,'2025_09_05_141822_add_external_ref_id_to_entities',1);
INSERT INTO migrations VALUES(215,'2025_09_21_134035_add_cluster_router',1);
INSERT INTO migrations VALUES(216,'2025_09_23_113715_add_user_login',1);
INSERT INTO migrations VALUES(217,'2025_09_29_193233_external_connected_entities_complement',1);
INSERT INTO migrations VALUES(218,'2025_10_02_205209_add_permissions',1);
INSERT INTO migrations VALUES(219,'2025_10_04_153231_add_attribut_on_fluxes',1);
INSERT INTO migrations VALUES(220,'2025_10_08_100558_more_buildings',1);
INSERT INTO migrations VALUES(221,'2025_10_17_073218_add_cluster_logical_server',1);
INSERT INTO migrations VALUES(222,'2025_10_17_085635_add_fields_to_cluster',1);
INSERT INTO migrations VALUES(223,'2025_10_18_174616_add_cluster_physical_server',1);
INSERT INTO migrations VALUES(224,'2025_10_18_183453_add_cluster_router',1);
INSERT INTO migrations VALUES(225,'2025_10_21_104026_add_lawfullness',1);
INSERT INTO migrations VALUES(226,'2025_10_27_095802_add_link_applications_security_devices',1);
INSERT INTO migrations VALUES(227,'2025_10_27_104704_add_fields_security_device',1);
INSERT INTO migrations VALUES(228,'2025_10_30_081134_change_active_nullable_on_lservers',1);
INSERT INTO migrations VALUES(229,'2025_11_01_123622_drop_unique_graphs_name_unique_on_graphs_table',1);
INSERT INTO migrations VALUES(230,'2025_11_09_103559_add_network_switch_vlan',1);
INSERT INTO migrations VALUES(231,'2025_11_10_090632_add_subnetwork_link',1);
INSERT INTO migrations VALUES(232,'2024_06_01_000001_create_oauth_device_codes_table',2);
INSERT INTO migrations VALUES(233,'2025_11_17_110452_add_graph_class',2);
INSERT INTO migrations VALUES(234,'2025_11_21_123505_add_chain_to_logical_flow',2);
INSERT INTO migrations VALUES(235,'2025_11_24_152357_create_mercator_modules_table',2);
INSERT INTO migrations VALUES(236,'2025_11_30_123016_add_module_to_permissions_table',2);
INSERT INTO migrations VALUES(237,'2025_12_03_105527_add_ip_to_security_devices',2);
INSERT INTO migrations VALUES(238,'2025_12_03_111641_add_security_device_to_logical_flows',2);
INSERT INTO migrations VALUES(239,'2025_12_04_135457_add_manage_module_role',2);
INSERT INTO migrations VALUES(240,'2026_01_16_114842_add_icon_to_databases',2);
INSERT INTO migrations VALUES(241,'2026_01_24_091900_add_information_flow_table',2);
INSERT INTO migrations VALUES(242,'2026_02_13_134714_add_cpe_to_application_modules',2);
INSERT INTO migrations VALUES(243,'2026_02_18_085500_add_type_color_to_physical_links',2);
INSERT INTO migrations VALUES(244,'2026_03_02_000001_upgrade_oauth_clients_uuid',2);
INSERT INTO migrations VALUES(245,'2026_03_03_184523_add_parent_man',2);
INSERT INTO migrations VALUES(246,'2026_03_05_114042_add_icon_to_physical_switches',2);
INSERT INTO migrations VALUES(247,'2026_03_11_134833_add_cluster_to_logical_flows',2);
INSERT INTO migrations VALUES(248,'2026_03_11_175208_add_information_information',2);
INSERT INTO migrations VALUES(249,'2026_03_22_121031_add_data_processing_fields',2);
INSERT INTO migrations VALUES(250,'2026_03_30_095429_add_document_permissions',2);
INSERT INTO migrations VALUES(251,'2026_04_05_180615_add_table_backups',2);
INSERT INTO migrations VALUES(252,'2026_04_15_100554_add_application_module_entity',2);
INSERT INTO migrations VALUES(253,'2026_04_17_211419_add_query_table',2);
INSERT INTO migrations VALUES(254,'2026_05_03_184011_rename_m_applications_to_applications',3);
INSERT INTO migrations VALUES(255,'2026_05_03_185943_rename_m_application_events_to_application_events',4);
INSERT INTO migrations VALUES(256,'2026_05_03_192017_rename_m_application_id_in_application_events',5);
INSERT INTO migrations VALUES(257,'2026_05_03_193002_rename_m_application_permissions',5);
INSERT INTO migrations VALUES(258,'2026_05_03_230714_consolidate_application_permissions',5);
INSERT INTO migrations VALUES(259,'2026_05_03_234356_drop_application_cartographer_table',6);
INSERT INTO migrations VALUES(260,'2026_05_07_215942_rename_fluxes_to_application_flows',7);
INSERT INTO migrations VALUES(261,'2026_05_08_120804_rename_flux_information_to_application_flow_information',8);
INSERT INTO migrations VALUES(262,'2026_05_08_212557_rename_domaines_to_domains',8);
INSERT INTO migrations VALUES(263,'2026_05_11_000001_create_parameters_table',9);
INSERT INTO migrations VALUES(264,'2026_05_12_113635_drop_unique_name_from_annuaires',9);
INSERT INTO migrations VALUES(265,'2026_05_12_150000_create_zones_table',9);
INSERT INTO migrations VALUES(266,'2026_05_12_150001_add_zone_permissions',9);
INSERT INTO migrations VALUES(267,'2026_05_20_000000_restructure_backups_table',9);
INSERT INTO migrations VALUES(268,'2026_05_31_000000_create_cartographers_table',9);
INSERT INTO migrations VALUES(269,'2026_05_31_000001_rename_irregular_permissions',9);
INSERT INTO migrations VALUES(270,'2026_06_04_090735_create_jobs_table',9);
INSERT INTO migrations VALUES(271,'2026_06_07_094101_add_soft_deletes_to_cartographers_table',9);
INSERT INTO migrations VALUES(272,'2026_06_11_000000_create_application_document_table',9);
INSERT INTO migrations VALUES(273,'2026_06_18_000000_add_ext_refs_to_mapped_objects',9);
INSERT INTO migrations VALUES(274,'2026_06_21_000000_move_mercator_config_to_parameters',9);
INSERT INTO migrations VALUES(275,'2026_06_22_000000_rename_room_id_to_building_id_in_bays',9);
INSERT INTO migrations VALUES(276,'2026_06_22_000001_add_type_and_attributes_columns',9);
INSERT INTO migrations VALUES(277,'2026_06_22_000002_add_description_and_attributes_to_physical_links',9);
INSERT INTO migrations VALUES(278,'2026_06_28_000000_drop_unique_name_from_backups_table',9);
INSERT INTO migrations VALUES(279,'2026_07_05_000000_change_peripheral_domain_to_domain_id',9);
INSERT INTO migrations VALUES(280,'2026_07_18_000000_create_monarc_sync_items_table',9);
INSERT INTO migrations VALUES(281,'2026_07_24_000000_add_status_hosting_fields_to_applications',9);
INSERT INTO migrations VALUES(282,'2026_08_04_000000_add_application_id_to_annuaires',9);
INSERT INTO migrations VALUES(283,'2026_08_04_000000_fix_physical_servers_patching_typo',9);
INSERT INTO migrations VALUES(284,'2026_08_11_000000_add_cairn_access_permission',9);
INSERT INTO migrations VALUES(285,'2026_09_02_000000_add_flow_label_to_users_table',9);
INSERT INTO migrations VALUES(286,'2026_09_08_000000_add_icon_to_storage_devices',9);
INSERT INTO migrations VALUES(287,'2026_09_09_000000_add_type_attributes_to_cartography_objects',9);
INSERT INTO migrations VALUES(288,'2026_09_09_000001_rename_nature_to_type_on_application_flows',9);
INSERT INTO migrations VALUES(289,'2026_09_10_000000_rename_entity_type_to_type_on_entities',9);
INSERT INTO migrations VALUES(290,'2026_09_10_000001_move_is_external_to_attributes_on_entities',9);
INSERT INTO migrations VALUES(291,'2026_09_14_100000_create_perimeters_table',9);
INSERT INTO migrations VALUES(292,'2026_09_14_100001_add_perimeter_id_to_roles_table',9);
INSERT INTO migrations VALUES(293,'2026_09_15_000000_add_perimeter_id_to_mapped_objects',9);
INSERT INTO migrations VALUES(294,'2026_09_15_113000_add_comments_to_applications',9);
INSERT INTO migrations VALUES(295,'2026_09_16_000000_drop_unique_name_indexes_on_mapped_objects',9);
INSERT INTO migrations VALUES(296,'2026_09_16_000001_add_perimeter_id_to_admin_users_table',9);
INSERT INTO migrations VALUES(297,'2026_09_16_100000_add_name_indexes_to_frequently_sorted_tables',9);
INSERT INTO migrations VALUES(298,'2026_09_21_000000_rename_nom_to_name_in_perimeters_table',9);
INSERT INTO migrations VALUES(299,'2026_09_24_000000_add_comments_to_application_services',9);
INSERT INTO migrations VALUES(300,'2026_09_24_000001_add_comments_to_application_modules',9);
INSERT INTO migrations VALUES(301,'2026_10_10_000000_sanitize_stored_html_fields',9);
INSERT INTO migrations VALUES(302,'2026_10_28_000000_add_icon_to_information',9);
INSERT INTO migrations VALUES(304,'2026_11_06_000000_add_blame_columns_to_audited_tables',10);
