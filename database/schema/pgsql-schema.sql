--
-- PostgreSQL database dump
--

\restrict QDnbTDWFYi6iaYCOuDqk67XlmwoWbZkeHVO5UKhQCOMvTdtmvJcBzZp2lIoYqTX

-- Dumped from database version 16.15
-- Dumped by pg_dump version 16.15

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: activities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activities (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    drp text,
    drp_link character varying(255),
    recovery_time_objective integer,
    maximum_tolerable_downtime integer,
    recovery_point_objective integer,
    maximum_tolerable_data_loss integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: activities_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activities_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.activities_id_seq OWNED BY public.activities.id;


--
-- Name: activity_application; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity_application (
    application_id integer NOT NULL,
    activity_id integer NOT NULL
);


--
-- Name: activity_document; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity_document (
    activity_id integer NOT NULL,
    document_id integer NOT NULL
);


--
-- Name: activity_impact; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity_impact (
    id bigint NOT NULL,
    activity_id integer NOT NULL,
    impact_type character varying(255) NOT NULL,
    severity smallint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: activity_impact_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activity_impact_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activity_impact_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.activity_impact_id_seq OWNED BY public.activity_impact.id;


--
-- Name: activity_operation; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity_operation (
    activity_id integer NOT NULL,
    operation_id integer NOT NULL
);


--
-- Name: activity_process; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity_process (
    process_id integer NOT NULL,
    activity_id integer NOT NULL
);


--
-- Name: actor_operation; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.actor_operation (
    operation_id integer NOT NULL,
    actor_id integer NOT NULL
);


--
-- Name: actors; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.actors (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    nature character varying(255),
    type character varying(255),
    contact character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: actors_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.actors_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: actors_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.actors_id_seq OWNED BY public.actors.id;


--
-- Name: admin_user_application; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.admin_user_application (
    admin_user_id integer NOT NULL,
    application_id integer NOT NULL
);


--
-- Name: admin_user_zone; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.admin_user_zone (
    admin_user_id integer NOT NULL,
    zone_id integer NOT NULL
);


--
-- Name: admin_users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.admin_users (
    id integer NOT NULL,
    user_id character varying(255) NOT NULL,
    firstname character varying(255),
    lastname character varying(255),
    type character varying(255),
    icon_id integer,
    attributes character varying(255),
    description text,
    domain_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: admin_users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.admin_users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: admin_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.admin_users_id_seq OWNED BY public.admin_users.id;


--
-- Name: annuaires; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.annuaires (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    solution character varying(255),
    zone_admin_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    application_id integer,
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: annuaires_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.annuaires_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: annuaires_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.annuaires_id_seq OWNED BY public.annuaires.id;


--
-- Name: application_application_service; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_application_service (
    application_id integer NOT NULL,
    application_service_id integer NOT NULL
);


--
-- Name: application_blocks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_blocks (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    responsible character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: application_blocks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.application_blocks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: application_blocks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.application_blocks_id_seq OWNED BY public.application_blocks.id;


--
-- Name: application_certificate; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_certificate (
    certificate_id integer NOT NULL,
    application_id integer NOT NULL
);


--
-- Name: application_container; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_container (
    container_id integer NOT NULL,
    application_id integer NOT NULL
);


--
-- Name: application_data_processing; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_data_processing (
    data_processing_id integer NOT NULL,
    application_id integer NOT NULL
);


--
-- Name: application_database; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_database (
    application_id integer NOT NULL,
    database_id integer NOT NULL
);


--
-- Name: application_document; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_document (
    application_id integer NOT NULL,
    document_id integer NOT NULL
);


--
-- Name: application_entity; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_entity (
    application_id integer NOT NULL,
    entity_id integer NOT NULL
);


--
-- Name: application_events; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_events (
    id integer NOT NULL,
    user_id integer NOT NULL,
    application_id integer NOT NULL,
    message text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    ext_refs character varying(255)
);


--
-- Name: application_flow_information; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_flow_information (
    id bigint NOT NULL,
    flux_id integer NOT NULL,
    information_id integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: application_flows; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_flows (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    attributes character varying(255),
    description text,
    application_source_id integer,
    service_source_id integer,
    module_source_id integer,
    database_source_id integer,
    application_dest_id integer,
    service_dest_id integer,
    module_dest_id integer,
    database_dest_id integer,
    crypted boolean,
    bidirectional boolean,
    type character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: application_logical_server; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_logical_server (
    application_id integer NOT NULL,
    logical_server_id integer NOT NULL
);


--
-- Name: application_module_application_service; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_module_application_service (
    application_service_id integer NOT NULL,
    application_module_id integer NOT NULL
);


--
-- Name: application_module_entity; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_module_entity (
    application_module_id integer NOT NULL,
    entity_id integer NOT NULL
);


--
-- Name: application_modules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_modules (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    vendor character varying(255),
    product character varying(255),
    version character varying(255),
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    comments text,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: application_modules_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.application_modules_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: application_modules_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.application_modules_id_seq OWNED BY public.application_modules.id;


--
-- Name: application_peripheral; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_peripheral (
    application_id integer NOT NULL,
    peripheral_id integer NOT NULL
);


--
-- Name: application_physical_server; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_physical_server (
    application_id integer NOT NULL,
    physical_server_id integer NOT NULL
);


--
-- Name: application_process; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_process (
    application_id integer NOT NULL,
    process_id integer NOT NULL
);


--
-- Name: application_security_control; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_security_control (
    security_control_id integer NOT NULL,
    application_id integer NOT NULL
);


--
-- Name: application_security_device; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_security_device (
    security_device_id integer NOT NULL,
    application_id integer NOT NULL
);


--
-- Name: application_services; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_services (
    id integer NOT NULL,
    description text,
    exposition character varying(255),
    name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    comments text,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: application_services_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.application_services_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: application_services_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.application_services_id_seq OWNED BY public.application_services.id;


--
-- Name: application_workstation; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_workstation (
    application_id integer NOT NULL,
    workstation_id integer NOT NULL
);


--
-- Name: applications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.applications (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    icon_id integer,
    description text,
    responsible character varying(255),
    technology character varying(255),
    external character varying(255),
    users character varying(255),
    entity_resp_id integer,
    application_block_id integer,
    documentation character varying(255),
    security_need_c integer,
    security_need_i integer,
    security_need_a integer,
    security_need_t integer,
    version character varying(255),
    functional_referent character varying(255),
    editor character varying(255),
    install_date date,
    update_date date,
    rto integer,
    rpo integer,
    vendor character varying(255),
    product character varying(255),
    attributes character varying(255),
    patching_frequency integer,
    next_update date,
    security_need_auth integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    status character varying(255),
    prod_date date,
    hosting character varying(255),
    urls character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    comments text,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_logs (
    id integer NOT NULL,
    description character varying(255) NOT NULL,
    subject_id integer,
    subject_type character varying(255),
    user_id integer,
    properties text,
    host character varying(45),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.audit_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- Name: backup_logical_server; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.backup_logical_server (
    backup_id integer NOT NULL,
    logical_server_id integer NOT NULL
);


--
-- Name: backup_storage_device; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.backup_storage_device (
    backup_id integer NOT NULL,
    storage_device_id integer NOT NULL
);


--
-- Name: backups; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.backups (
    id integer NOT NULL,
    backup_frequency smallint,
    backup_cycle smallint,
    backup_retention smallint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    name character varying(255) NOT NULL,
    type character varying(100),
    attributes text,
    description text,
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: COLUMN backups.backup_frequency; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.backups.backup_frequency IS '1=daily,2=weekly,3=monthly';


--
-- Name: COLUMN backups.backup_cycle; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.backups.backup_cycle IS 'Ex: 1 full/day + 1 weekly/month';


--
-- Name: COLUMN backups.backup_retention; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.backups.backup_retention IS 'Retention in days';


--
-- Name: backups_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.backups_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: backups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.backups_id_seq OWNED BY public.backups.id;


--
-- Name: bay_wifi_terminal; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.bay_wifi_terminal (
    wifi_terminal_id integer NOT NULL,
    bay_id integer NOT NULL
);


--
-- Name: bays; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.bays (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    building_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    site_id integer,
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: bays_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.bays_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: bays_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.bays_id_seq OWNED BY public.bays.id;


--
-- Name: building_zone; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.building_zone (
    building_id integer NOT NULL,
    zone_id integer NOT NULL
);


--
-- Name: buildings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.buildings (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    attributes character varying(255),
    type character varying(255),
    description text,
    site_id integer,
    building_id integer,
    icon_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: buildings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.buildings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: buildings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.buildings_id_seq OWNED BY public.buildings.id;


--
-- Name: cartographers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cartographers (
    id bigint NOT NULL,
    cartographiable_type character varying(255) NOT NULL,
    cartographiable_id bigint NOT NULL,
    user_id integer,
    role_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


--
-- Name: cartographers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cartographers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cartographers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cartographers_id_seq OWNED BY public.cartographers.id;


--
-- Name: certificate_logical_server; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.certificate_logical_server (
    certificate_id integer NOT NULL,
    logical_server_id integer NOT NULL
);


--
-- Name: certificates; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.certificates (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    description text,
    start_validity date,
    end_validity date,
    status integer,
    last_notification timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: certificates_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.certificates_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: certificates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.certificates_id_seq OWNED BY public.certificates.id;


--
-- Name: cluster_logical_server; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cluster_logical_server (
    cluster_id integer NOT NULL,
    logical_server_id integer NOT NULL
);


--
-- Name: cluster_physical_server; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cluster_physical_server (
    cluster_id integer NOT NULL,
    physical_server_id integer NOT NULL
);


--
-- Name: cluster_router; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cluster_router (
    cluster_id integer NOT NULL,
    router_id integer NOT NULL
);


--
-- Name: clusters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.clusters (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    attributes character varying(255),
    icon_id integer,
    description text,
    address_ip character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: clusters_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.clusters_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: clusters_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.clusters_id_seq OWNED BY public.clusters.id;


--
-- Name: container_database; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.container_database (
    database_id integer NOT NULL,
    container_id integer NOT NULL
);


--
-- Name: container_logical_server; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.container_logical_server (
    container_id integer NOT NULL,
    logical_server_id integer NOT NULL
);


--
-- Name: containers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.containers (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    description text,
    icon_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: containers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.containers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: containers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.containers_id_seq OWNED BY public.containers.id;


--
-- Name: cpe_products; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cpe_products (
    id integer NOT NULL,
    cpe_vendor_id integer NOT NULL,
    name character varying(255) NOT NULL
);


--
-- Name: cpe_products_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cpe_products_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cpe_products_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cpe_products_id_seq OWNED BY public.cpe_products.id;


--
-- Name: cpe_vendors; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cpe_vendors (
    id integer NOT NULL,
    part character(1) NOT NULL,
    name character varying(255) NOT NULL
);


--
-- Name: cpe_vendors_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cpe_vendors_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cpe_vendors_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cpe_vendors_id_seq OWNED BY public.cpe_vendors.id;


--
-- Name: cpe_versions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cpe_versions (
    id integer NOT NULL,
    cpe_product_id integer NOT NULL,
    name character varying(255) NOT NULL
);


--
-- Name: cpe_versions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cpe_versions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cpe_versions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cpe_versions_id_seq OWNED BY public.cpe_versions.id;


--
-- Name: data_processing; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.data_processing (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    responsible text,
    purpose text,
    categories text,
    recipients text,
    transfert text,
    retention text,
    controls text,
    legal_basis character varying(255),
    lawfulness text,
    lawfulness_legitimate_interest boolean,
    lawfulness_public_interest boolean,
    lawfulness_vital_interest boolean,
    lawfulness_legal_obligation boolean,
    lawfulness_contract boolean,
    lawfulness_consent boolean,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    data_source text,
    data_collection_obligation text,
    data_subject_rights text,
    automated_decision_making text,
    update_date date,
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: data_processing_document; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.data_processing_document (
    data_processing_id integer NOT NULL,
    document_id integer NOT NULL
);


--
-- Name: data_processing_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.data_processing_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: data_processing_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.data_processing_id_seq OWNED BY public.data_processing.id;


--
-- Name: data_processing_information; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.data_processing_information (
    data_processing_id integer NOT NULL,
    information_id integer NOT NULL
);


--
-- Name: data_processing_process; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.data_processing_process (
    data_processing_id integer NOT NULL,
    process_id integer NOT NULL
);


--
-- Name: database_entity; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.database_entity (
    database_id integer NOT NULL,
    entity_id integer NOT NULL
);


--
-- Name: database_information; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.database_information (
    database_id integer NOT NULL,
    information_id integer NOT NULL
);


--
-- Name: database_logical_server; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.database_logical_server (
    database_id integer NOT NULL,
    logical_server_id integer NOT NULL
);


--
-- Name: databases; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.databases (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    description text,
    responsible character varying(255),
    external character varying(255),
    entity_resp_id integer,
    security_need_c integer,
    security_need_i integer,
    security_need_a integer,
    security_need_t integer,
    security_need_auth integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    icon_id integer,
    ext_refs character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: databases_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.databases_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: databases_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.databases_id_seq OWNED BY public.databases.id;


--
-- Name: dhcp_servers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.dhcp_servers (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    address_ip character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: dhcp_servers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.dhcp_servers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: dhcp_servers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.dhcp_servers_id_seq OWNED BY public.dhcp_servers.id;


--
-- Name: dnsservers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.dnsservers (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    address_ip character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: dnsservers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.dnsservers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: dnsservers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.dnsservers_id_seq OWNED BY public.dnsservers.id;


--
-- Name: document_external_connected_entity; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.document_external_connected_entity (
    external_connected_entity_id integer NOT NULL,
    document_id integer NOT NULL
);


--
-- Name: document_logical_server; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.document_logical_server (
    logical_server_id integer NOT NULL,
    document_id integer NOT NULL
);


--
-- Name: document_relation; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.document_relation (
    relation_id integer NOT NULL,
    document_id integer NOT NULL
);


--
-- Name: documents; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.documents (
    id integer NOT NULL,
    filename character varying(255) NOT NULL,
    mimetype character varying(255) NOT NULL,
    size integer NOT NULL,
    hash character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: documents_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.documents_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.documents_id_seq OWNED BY public.documents.id;


--
-- Name: domain_forest_ad; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.domain_forest_ad (
    forest_ad_id integer NOT NULL,
    domain_id integer NOT NULL
);


--
-- Name: domains; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.domains (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    domain_ctrl_cnt integer,
    user_count integer,
    machine_count integer,
    relation_inter_domaine character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: domaine_ads_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.domaine_ads_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: domaine_ads_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.domaine_ads_id_seq OWNED BY public.domains.id;


--
-- Name: entities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.entities (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    attributes character varying(255),
    icon_id integer,
    description text,
    security_level text,
    contact_point text,
    type character varying(255),
    reference character varying(255),
    parent_entity_id integer,
    external_ref_id character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: entities_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.entities_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: entities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.entities_id_seq OWNED BY public.entities.id;


--
-- Name: entity_document; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.entity_document (
    entity_id integer NOT NULL,
    document_id integer NOT NULL
);


--
-- Name: entity_process; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.entity_process (
    process_id integer NOT NULL,
    entity_id integer NOT NULL
);


--
-- Name: external_connected_entities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.external_connected_entities (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    description text,
    entity_id integer,
    network_id integer,
    src character varying(255),
    dest character varying(255),
    src_desc character varying(255),
    dest_desc character varying(255),
    security text,
    contacts character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: external_connected_entities_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.external_connected_entities_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: external_connected_entities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.external_connected_entities_id_seq OWNED BY public.external_connected_entities.id;


--
-- Name: external_connected_entity_subnetwork; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.external_connected_entity_subnetwork (
    external_connected_entity_id integer NOT NULL,
    subnetwork_id integer NOT NULL
);


--
-- Name: flux_information_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.flux_information_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: flux_information_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.flux_information_id_seq OWNED BY public.application_flow_information.id;


--
-- Name: fluxes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.fluxes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: fluxes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.fluxes_id_seq OWNED BY public.application_flows.id;


--
-- Name: forest_ads; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.forest_ads (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    zone_admin_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: forest_ads_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.forest_ads_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: forest_ads_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.forest_ads_id_seq OWNED BY public.forest_ads.id;


--
-- Name: gateways; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.gateways (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    ip character varying(255),
    authentification character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: gateways_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.gateways_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gateways_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.gateways_id_seq OWNED BY public.gateways.id;


--
-- Name: graphs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.graphs (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    class integer,
    type character varying(255),
    content text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: graphs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.graphs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: graphs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.graphs_id_seq OWNED BY public.graphs.id;


--
-- Name: information; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.information (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    owner character varying(255),
    administrator character varying(255),
    storage character varying(255),
    sensitivity character varying(255),
    constraints text,
    security_need_c integer,
    security_need_i integer,
    security_need_a integer,
    security_need_t integer,
    retention character varying(255),
    security_need_auth integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    icon_id integer,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: information_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.information_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: information_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.information_id_seq OWNED BY public.information.id;


--
-- Name: information_information; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.information_information (
    information_id integer NOT NULL,
    child_information_id integer NOT NULL
);


--
-- Name: information_process; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.information_process (
    information_id integer NOT NULL,
    process_id integer NOT NULL
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: lan_man; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.lan_man (
    man_id integer NOT NULL,
    lan_id integer NOT NULL
);


--
-- Name: lan_wan; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.lan_wan (
    wan_id integer NOT NULL,
    lan_id integer NOT NULL
);


--
-- Name: lans; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.lans (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: lans_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.lans_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: lans_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.lans_id_seq OWNED BY public.lans.id;


--
-- Name: logical_flows; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.logical_flows (
    id integer NOT NULL,
    name character varying(255),
    source_ip_range character varying(255),
    dest_ip_range character varying(255),
    source_port character varying(255),
    dest_port character varying(255),
    protocol character varying(255),
    description text,
    router_id integer,
    priority integer,
    action character varying(255),
    users character varying(255),
    interface character varying(255),
    schedule character varying(255),
    logical_server_source_id integer,
    peripheral_source_id integer,
    physical_server_source_id integer,
    storage_device_source_id integer,
    workstation_source_id integer,
    physical_security_device_source_id integer,
    logical_server_dest_id integer,
    peripheral_dest_id integer,
    physical_server_dest_id integer,
    storage_device_dest_id integer,
    workstation_dest_id integer,
    physical_security_device_dest_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    chain character varying(255),
    subnetwork_source_id integer,
    subnetwork_dest_id integer,
    security_device_source_id integer,
    security_device_dest_id integer,
    cluster_source_id integer,
    cluster_dest_id integer,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: logical_flows_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.logical_flows_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: logical_flows_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.logical_flows_id_seq OWNED BY public.logical_flows.id;


--
-- Name: logical_server_physical_server; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.logical_server_physical_server (
    logical_server_id integer NOT NULL,
    physical_server_id integer NOT NULL
);


--
-- Name: logical_servers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.logical_servers (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    icon_id integer,
    description text,
    net_services character varying(255),
    configuration text,
    operating_system character varying(255),
    address_ip character varying(255),
    cpu character varying(255),
    memory character varying(255),
    environment character varying(255),
    disk integer,
    install_date date,
    update_date date,
    disk_used integer,
    attributes character varying(255),
    patching_frequency integer,
    next_update date,
    domain_id integer,
    active boolean,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: logical_servers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.logical_servers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: logical_servers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.logical_servers_id_seq OWNED BY public.logical_servers.id;


--
-- Name: m_application_events_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.m_application_events_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: m_application_events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.m_application_events_id_seq OWNED BY public.application_events.id;


--
-- Name: m_applications_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.m_applications_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: m_applications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.m_applications_id_seq OWNED BY public.applications.id;


--
-- Name: macro_processuses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.macro_processuses (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    io_elements text,
    security_need_c integer,
    security_need_i integer,
    security_need_a integer,
    security_need_t integer,
    security_need_auth integer,
    owner character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: macro_processuses_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.macro_processuses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: macro_processuses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.macro_processuses_id_seq OWNED BY public.macro_processuses.id;


--
-- Name: man_wan; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.man_wan (
    wan_id integer NOT NULL,
    man_id integer NOT NULL
);


--
-- Name: mans; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.mans (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    description text,
    parent_man_id integer,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: mans_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.mans_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: mans_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.mans_id_seq OWNED BY public.mans.id;


--
-- Name: media; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.media (
    id integer NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL,
    collection_name character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    file_name character varying(255) NOT NULL,
    mime_type character varying(255),
    disk character varying(255) NOT NULL,
    size integer NOT NULL,
    manipulations json NOT NULL,
    custom_properties json NOT NULL,
    responsive_images json NOT NULL,
    order_column integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: media_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.media_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: media_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.media_id_seq OWNED BY public.media.id;


--
-- Name: mercator_modules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.mercator_modules (
    name character varying(255) NOT NULL,
    label character varying(255) NOT NULL,
    version character varying(255) NOT NULL,
    enabled boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: monarc_sync_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.monarc_sync_items (
    id bigint NOT NULL,
    model character varying(255) NOT NULL,
    mercator_id integer NOT NULL,
    object_uuid uuid NOT NULL,
    anr_id integer NOT NULL,
    sent_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: monarc_sync_items_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.monarc_sync_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: monarc_sync_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.monarc_sync_items_id_seq OWNED BY public.monarc_sync_items.id;


--
-- Name: network_switch_physical_switch; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.network_switch_physical_switch (
    network_switch_id integer NOT NULL,
    physical_switch_id integer NOT NULL
);


--
-- Name: network_switch_vlan; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.network_switch_vlan (
    network_switch_id integer NOT NULL,
    vlan_id integer NOT NULL
);


--
-- Name: network_switches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.network_switches (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    ip character varying(255),
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: network_switches_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.network_switches_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: network_switches_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.network_switches_id_seq OWNED BY public.network_switches.id;


--
-- Name: networks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.networks (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    protocol_type character varying(255),
    responsible character varying(255),
    responsible_sec character varying(255),
    security_need_c integer,
    security_need_i integer,
    security_need_a integer,
    security_need_t integer,
    security_need_auth integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: networks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.networks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: networks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.networks_id_seq OWNED BY public.networks.id;


--
-- Name: oauth_access_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.oauth_access_tokens (
    id character varying(100) NOT NULL,
    user_id bigint,
    client_id character varying(100) NOT NULL,
    name character varying(255),
    scopes text,
    revoked boolean NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone
);


--
-- Name: oauth_auth_codes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.oauth_auth_codes (
    id character varying(100) NOT NULL,
    user_id bigint NOT NULL,
    client_id character varying(100) NOT NULL,
    scopes text,
    revoked boolean NOT NULL,
    expires_at timestamp(0) without time zone
);


--
-- Name: oauth_clients; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.oauth_clients (
    id character varying(100) NOT NULL,
    user_id bigint,
    name character varying(255) NOT NULL,
    secret character varying(100),
    provider character varying(255),
    redirect text NOT NULL,
    personal_access_client boolean NOT NULL,
    password_client boolean NOT NULL,
    revoked boolean NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: oauth_clients_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.oauth_clients_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: oauth_clients_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.oauth_clients_id_seq OWNED BY public.oauth_clients.id;


--
-- Name: oauth_device_codes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.oauth_device_codes (
    id character(80) NOT NULL,
    user_id bigint,
    client_id uuid NOT NULL,
    user_code character(8) NOT NULL,
    scopes text NOT NULL,
    revoked boolean NOT NULL,
    user_approved_at timestamp(0) without time zone,
    last_polled_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone
);


--
-- Name: oauth_personal_access_clients; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.oauth_personal_access_clients (
    id bigint NOT NULL,
    client_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: oauth_personal_access_clients_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.oauth_personal_access_clients_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: oauth_personal_access_clients_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.oauth_personal_access_clients_id_seq OWNED BY public.oauth_personal_access_clients.id;


--
-- Name: oauth_refresh_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.oauth_refresh_tokens (
    id character varying(100) NOT NULL,
    access_token_id character varying(100) NOT NULL,
    revoked boolean NOT NULL,
    expires_at timestamp(0) without time zone
);


--
-- Name: operation_task; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.operation_task (
    operation_id integer NOT NULL,
    task_id integer NOT NULL
);


--
-- Name: operations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.operations (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    process_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: operations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.operations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: operations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.operations_id_seq OWNED BY public.operations.id;


--
-- Name: parameters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.parameters (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    value text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: parameters_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.parameters_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: parameters_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.parameters_id_seq OWNED BY public.parameters.id;


--
-- Name: password_resets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_resets (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: perimeters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.perimeters (
    id integer NOT NULL,
    name character varying(32) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: perimeters_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.perimeters_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: perimeters_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.perimeters_id_seq OWNED BY public.perimeters.id;


--
-- Name: peripherals; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.peripherals (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    icon_id integer,
    description text,
    responsible character varying(255),
    site_id integer,
    building_id integer,
    bay_id integer,
    vendor character varying(255),
    product character varying(255),
    version character varying(255),
    address_ip character varying(255),
    provider_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    domain_id integer,
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: peripherals_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.peripherals_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: peripherals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.peripherals_id_seq OWNED BY public.peripherals.id;


--
-- Name: permission_role; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.permission_role (
    role_id integer NOT NULL,
    permission_id integer NOT NULL
);


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.permissions (
    id integer NOT NULL,
    title character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    module character varying(255),
    ext_refs character varying(255)
);


--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.permissions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: phones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.phones (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    description text,
    site_id integer,
    building_id integer,
    physical_switch_id integer,
    vendor character varying(255),
    product character varying(255),
    version character varying(255),
    address_ip character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: phones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.phones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: phones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.phones_id_seq OWNED BY public.phones.id;


--
-- Name: physical_links; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.physical_links (
    id integer NOT NULL,
    src_port character varying(255),
    dest_port character varying(255),
    peripheral_src_id integer,
    phone_src_id integer,
    physical_router_src_id integer,
    physical_security_device_src_id integer,
    physical_server_src_id integer,
    physical_switch_src_id integer,
    storage_device_src_id integer,
    wifi_terminal_src_id integer,
    workstation_src_id integer,
    peripheral_dest_id integer,
    phone_dest_id integer,
    physical_router_dest_id integer,
    physical_security_device_dest_id integer,
    physical_server_dest_id integer,
    physical_switch_dest_id integer,
    storage_device_dest_id integer,
    wifi_terminal_dest_id integer,
    workstation_dest_id integer,
    router_src_id integer,
    router_dest_id integer,
    network_switch_src_id integer,
    network_switch_dest_id integer,
    logical_server_src_id integer,
    logical_server_dest_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    type character varying(255),
    color character varying(255),
    ext_refs character varying(255),
    attributes character varying(255),
    description text,
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: physical_links_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.physical_links_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: physical_links_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.physical_links_id_seq OWNED BY public.physical_links.id;


--
-- Name: physical_router_router; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.physical_router_router (
    router_id integer NOT NULL,
    physical_router_id integer NOT NULL
);


--
-- Name: physical_router_vlan; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.physical_router_vlan (
    physical_router_id integer NOT NULL,
    vlan_id integer NOT NULL
);


--
-- Name: physical_routers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.physical_routers (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    description text,
    site_id integer,
    building_id integer,
    bay_id integer,
    vendor character varying(255),
    product character varying(255),
    version character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: physical_routers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.physical_routers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: physical_routers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.physical_routers_id_seq OWNED BY public.physical_routers.id;


--
-- Name: physical_security_device_security_device; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.physical_security_device_security_device (
    security_device_id integer NOT NULL,
    physical_security_device_id integer NOT NULL
);


--
-- Name: physical_security_devices; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.physical_security_devices (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    icon_id integer,
    description text,
    site_id integer,
    building_id integer,
    bay_id integer,
    address_ip character varying(255),
    attributes character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: physical_security_devices_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.physical_security_devices_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: physical_security_devices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.physical_security_devices_id_seq OWNED BY public.physical_security_devices.id;


--
-- Name: physical_servers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.physical_servers (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    icon_id integer,
    description text,
    responsible character varying(255),
    configuration text,
    site_id integer,
    building_id integer,
    bay_id integer,
    physical_switch_id integer,
    vendor character varying(255),
    product character varying(255),
    version character varying(255),
    address_ip character varying(255),
    cpu character varying(255),
    memory character varying(255),
    disk character varying(255),
    disk_used character varying(255),
    operating_system character varying(255),
    install_date timestamp(0) without time zone,
    update_date timestamp(0) without time zone,
    patching_group character varying(255),
    patching_frequency integer,
    next_update date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: physical_servers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.physical_servers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: physical_servers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.physical_servers_id_seq OWNED BY public.physical_servers.id;


--
-- Name: physical_switches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.physical_switches (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    type character varying(255),
    site_id integer,
    building_id integer,
    bay_id integer,
    vendor character varying(255),
    product character varying(255),
    version character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    icon_id integer,
    ext_refs character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: physical_switches_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.physical_switches_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: physical_switches_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.physical_switches_id_seq OWNED BY public.physical_switches.id;


--
-- Name: processes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.processes (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    icon_id integer,
    description text,
    in_out text,
    owner character varying(255),
    security_need_c integer,
    security_need_i integer,
    security_need_a integer,
    security_need_t integer,
    security_need_auth integer,
    macroprocess_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: processes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.processes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: processes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.processes_id_seq OWNED BY public.processes.id;


--
-- Name: relation_values; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.relation_values (
    relation_id integer NOT NULL,
    date_price date,
    price numeric(12,2),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: relations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.relations (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    attributes character varying(255),
    description text,
    source_id integer NOT NULL,
    destination_id integer NOT NULL,
    reference character varying(255),
    responsible character varying(255),
    order_number character varying(255),
    active boolean DEFAULT true NOT NULL,
    start_date date,
    end_date date,
    comments text,
    importance integer,
    security_need_c integer,
    security_need_i integer,
    security_need_a integer,
    security_need_t integer,
    security_need_auth integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: relations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.relations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: relations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.relations_id_seq OWNED BY public.relations.id;


--
-- Name: role_user; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_user (
    user_id integer NOT NULL,
    role_id integer NOT NULL
);


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id integer NOT NULL,
    title character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    perimeter_id integer DEFAULT 1 NOT NULL
);


--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.roles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: routers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.routers (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    description text,
    rules text,
    ip_addresses text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: routers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.routers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: routers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.routers_id_seq OWNED BY public.routers.id;


--
-- Name: saved_queries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.saved_queries (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    query json NOT NULL,
    is_public boolean DEFAULT false NOT NULL,
    user_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: saved_queries_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.saved_queries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: saved_queries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.saved_queries_id_seq OWNED BY public.saved_queries.id;


--
-- Name: security_control_process; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.security_control_process (
    security_control_id integer NOT NULL,
    process_id integer NOT NULL
);


--
-- Name: security_controls; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.security_controls (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: security_controls_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.security_controls_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: security_controls_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.security_controls_id_seq OWNED BY public.security_controls.id;


--
-- Name: security_devices; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.security_devices (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    icon_id integer,
    description text,
    vendor character varying(255),
    product character varying(255),
    version character varying(255),
    attributes character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    address_ip character varying(255),
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: security_devices_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.security_devices_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: security_devices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.security_devices_id_seq OWNED BY public.security_devices.id;


--
-- Name: sites; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sites (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    icon_id integer,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: sites_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.sites_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: sites_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.sites_id_seq OWNED BY public.sites.id;


--
-- Name: storage_devices; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.storage_devices (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    description text,
    site_id integer,
    building_id integer,
    bay_id integer,
    vendor character varying(255),
    product character varying(255),
    version character varying(255),
    address_ip character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    icon_id integer,
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: storage_devices_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.storage_devices_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: storage_devices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.storage_devices_id_seq OWNED BY public.storage_devices.id;


--
-- Name: subnetworks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.subnetworks (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    address character varying(255),
    ip_allocation_type character varying(255),
    responsible_exp character varying(255),
    dmz character varying(255),
    wifi character varying(255),
    connected_subnets_id integer,
    gateway_id integer,
    zone character varying(255),
    vlan_id integer,
    network_id integer,
    default_gateway character varying(255),
    subnetwork_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: subnetworks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.subnetworks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: subnetworks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.subnetworks_id_seq OWNED BY public.subnetworks.id;


--
-- Name: tasks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tasks (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tasks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tasks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tasks_id_seq OWNED BY public.tasks.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id integer NOT NULL,
    login character varying(255) NOT NULL,
    name character varying(255),
    email character varying(255),
    email_verified_at timestamp(0) without time zone,
    password character varying(255),
    remember_token character varying(255),
    granularity integer,
    language character varying(2),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    flow_label character varying(16)
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: vlans; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vlans (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description character varying(255),
    vlan_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: vlans_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.vlans_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: vlans_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.vlans_id_seq OWNED BY public.vlans.id;


--
-- Name: wans; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.wans (
    id integer NOT NULL,
    name character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: wans_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.wans_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: wans_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.wans_id_seq OWNED BY public.wans.id;


--
-- Name: wifi_terminals; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.wifi_terminals (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    description text,
    site_id integer,
    building_id integer,
    vendor character varying(255),
    product character varying(255),
    version character varying(255),
    address_ip character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: wifi_terminals_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.wifi_terminals_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: wifi_terminals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.wifi_terminals_id_seq OWNED BY public.wifi_terminals.id;


--
-- Name: workstations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workstations (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    site_id integer,
    building_id integer,
    physical_switch_id integer,
    type character varying(255),
    operating_system character varying(255),
    address_ip character varying(255),
    cpu character varying(255),
    memory character varying(255),
    disk integer,
    vendor character varying(255),
    product character varying(255),
    version character varying(255),
    icon_id integer,
    entity_id integer,
    user_id integer,
    other_user character varying(255),
    status character varying(255),
    manufacturer character varying(255),
    model character varying(255),
    serial_number character varying(255),
    last_inventory_date date,
    warranty_end_date date,
    domain_id integer,
    warranty character varying(255),
    warranty_start_date date,
    warranty_period character varying(255),
    agent_version character varying(255),
    update_source character varying(255),
    network_id integer,
    network_port_type character varying(255),
    mac_address character varying(255),
    purchase_date date,
    fin_value numeric(12,2),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: workstations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.workstations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: workstations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.workstations_id_seq OWNED BY public.workstations.id;


--
-- Name: zone_admins; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.zone_admins (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    type character varying(255),
    attributes character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: zone_admins_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.zone_admins_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: zone_admins_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.zone_admins_id_seq OWNED BY public.zone_admins.id;


--
-- Name: zone_zone; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.zone_zone (
    zone_id integer NOT NULL,
    related_zone_id integer NOT NULL
);


--
-- Name: zones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.zones (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255),
    attributes character varying(255),
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    ext_refs character varying(255),
    perimeter_id integer DEFAULT 1 NOT NULL,
    created_by integer,
    updated_by integer,
    deleted_by integer
);


--
-- Name: zones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.zones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: zones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.zones_id_seq OWNED BY public.zones.id;


--
-- Name: activities id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities ALTER COLUMN id SET DEFAULT nextval('public.activities_id_seq'::regclass);


--
-- Name: activity_impact id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_impact ALTER COLUMN id SET DEFAULT nextval('public.activity_impact_id_seq'::regclass);


--
-- Name: actors id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actors ALTER COLUMN id SET DEFAULT nextval('public.actors_id_seq'::regclass);


--
-- Name: admin_users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users ALTER COLUMN id SET DEFAULT nextval('public.admin_users_id_seq'::regclass);


--
-- Name: annuaires id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.annuaires ALTER COLUMN id SET DEFAULT nextval('public.annuaires_id_seq'::regclass);


--
-- Name: application_blocks id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_blocks ALTER COLUMN id SET DEFAULT nextval('public.application_blocks_id_seq'::regclass);


--
-- Name: application_events id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_events ALTER COLUMN id SET DEFAULT nextval('public.m_application_events_id_seq'::regclass);


--
-- Name: application_flow_information id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flow_information ALTER COLUMN id SET DEFAULT nextval('public.flux_information_id_seq'::regclass);


--
-- Name: application_flows id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows ALTER COLUMN id SET DEFAULT nextval('public.fluxes_id_seq'::regclass);


--
-- Name: application_modules id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_modules ALTER COLUMN id SET DEFAULT nextval('public.application_modules_id_seq'::regclass);


--
-- Name: application_services id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_services ALTER COLUMN id SET DEFAULT nextval('public.application_services_id_seq'::regclass);


--
-- Name: applications id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.applications ALTER COLUMN id SET DEFAULT nextval('public.m_applications_id_seq'::regclass);


--
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- Name: backups id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backups ALTER COLUMN id SET DEFAULT nextval('public.backups_id_seq'::regclass);


--
-- Name: bays id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bays ALTER COLUMN id SET DEFAULT nextval('public.bays_id_seq'::regclass);


--
-- Name: buildings id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.buildings ALTER COLUMN id SET DEFAULT nextval('public.buildings_id_seq'::regclass);


--
-- Name: cartographers id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cartographers ALTER COLUMN id SET DEFAULT nextval('public.cartographers_id_seq'::regclass);


--
-- Name: certificates id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.certificates ALTER COLUMN id SET DEFAULT nextval('public.certificates_id_seq'::regclass);


--
-- Name: clusters id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clusters ALTER COLUMN id SET DEFAULT nextval('public.clusters_id_seq'::regclass);


--
-- Name: containers id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.containers ALTER COLUMN id SET DEFAULT nextval('public.containers_id_seq'::regclass);


--
-- Name: cpe_products id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cpe_products ALTER COLUMN id SET DEFAULT nextval('public.cpe_products_id_seq'::regclass);


--
-- Name: cpe_vendors id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cpe_vendors ALTER COLUMN id SET DEFAULT nextval('public.cpe_vendors_id_seq'::regclass);


--
-- Name: cpe_versions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cpe_versions ALTER COLUMN id SET DEFAULT nextval('public.cpe_versions_id_seq'::regclass);


--
-- Name: data_processing id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_processing ALTER COLUMN id SET DEFAULT nextval('public.data_processing_id_seq'::regclass);


--
-- Name: databases id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.databases ALTER COLUMN id SET DEFAULT nextval('public.databases_id_seq'::regclass);


--
-- Name: dhcp_servers id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dhcp_servers ALTER COLUMN id SET DEFAULT nextval('public.dhcp_servers_id_seq'::regclass);


--
-- Name: dnsservers id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dnsservers ALTER COLUMN id SET DEFAULT nextval('public.dnsservers_id_seq'::regclass);


--
-- Name: documents id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents ALTER COLUMN id SET DEFAULT nextval('public.documents_id_seq'::regclass);


--
-- Name: domains id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.domains ALTER COLUMN id SET DEFAULT nextval('public.domaine_ads_id_seq'::regclass);


--
-- Name: entities id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entities ALTER COLUMN id SET DEFAULT nextval('public.entities_id_seq'::regclass);


--
-- Name: external_connected_entities id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.external_connected_entities ALTER COLUMN id SET DEFAULT nextval('public.external_connected_entities_id_seq'::regclass);


--
-- Name: forest_ads id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.forest_ads ALTER COLUMN id SET DEFAULT nextval('public.forest_ads_id_seq'::regclass);


--
-- Name: gateways id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gateways ALTER COLUMN id SET DEFAULT nextval('public.gateways_id_seq'::regclass);


--
-- Name: graphs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.graphs ALTER COLUMN id SET DEFAULT nextval('public.graphs_id_seq'::regclass);


--
-- Name: information id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.information ALTER COLUMN id SET DEFAULT nextval('public.information_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: lans id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lans ALTER COLUMN id SET DEFAULT nextval('public.lans_id_seq'::regclass);


--
-- Name: logical_flows id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows ALTER COLUMN id SET DEFAULT nextval('public.logical_flows_id_seq'::regclass);


--
-- Name: logical_servers id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_servers ALTER COLUMN id SET DEFAULT nextval('public.logical_servers_id_seq'::regclass);


--
-- Name: macro_processuses id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.macro_processuses ALTER COLUMN id SET DEFAULT nextval('public.macro_processuses_id_seq'::regclass);


--
-- Name: mans id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mans ALTER COLUMN id SET DEFAULT nextval('public.mans_id_seq'::regclass);


--
-- Name: media id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.media ALTER COLUMN id SET DEFAULT nextval('public.media_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: monarc_sync_items id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monarc_sync_items ALTER COLUMN id SET DEFAULT nextval('public.monarc_sync_items_id_seq'::regclass);


--
-- Name: network_switches id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.network_switches ALTER COLUMN id SET DEFAULT nextval('public.network_switches_id_seq'::regclass);


--
-- Name: networks id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.networks ALTER COLUMN id SET DEFAULT nextval('public.networks_id_seq'::regclass);


--
-- Name: oauth_personal_access_clients id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oauth_personal_access_clients ALTER COLUMN id SET DEFAULT nextval('public.oauth_personal_access_clients_id_seq'::regclass);


--
-- Name: operations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.operations ALTER COLUMN id SET DEFAULT nextval('public.operations_id_seq'::regclass);


--
-- Name: parameters id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parameters ALTER COLUMN id SET DEFAULT nextval('public.parameters_id_seq'::regclass);


--
-- Name: perimeters id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.perimeters ALTER COLUMN id SET DEFAULT nextval('public.perimeters_id_seq'::regclass);


--
-- Name: peripherals id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.peripherals ALTER COLUMN id SET DEFAULT nextval('public.peripherals_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: phones id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.phones ALTER COLUMN id SET DEFAULT nextval('public.phones_id_seq'::regclass);


--
-- Name: physical_links id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links ALTER COLUMN id SET DEFAULT nextval('public.physical_links_id_seq'::regclass);


--
-- Name: physical_routers id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_routers ALTER COLUMN id SET DEFAULT nextval('public.physical_routers_id_seq'::regclass);


--
-- Name: physical_security_devices id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_security_devices ALTER COLUMN id SET DEFAULT nextval('public.physical_security_devices_id_seq'::regclass);


--
-- Name: physical_servers id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_servers ALTER COLUMN id SET DEFAULT nextval('public.physical_servers_id_seq'::regclass);


--
-- Name: physical_switches id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_switches ALTER COLUMN id SET DEFAULT nextval('public.physical_switches_id_seq'::regclass);


--
-- Name: processes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.processes ALTER COLUMN id SET DEFAULT nextval('public.processes_id_seq'::regclass);


--
-- Name: relations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.relations ALTER COLUMN id SET DEFAULT nextval('public.relations_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: routers id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routers ALTER COLUMN id SET DEFAULT nextval('public.routers_id_seq'::regclass);


--
-- Name: saved_queries id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saved_queries ALTER COLUMN id SET DEFAULT nextval('public.saved_queries_id_seq'::regclass);


--
-- Name: security_controls id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_controls ALTER COLUMN id SET DEFAULT nextval('public.security_controls_id_seq'::regclass);


--
-- Name: security_devices id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_devices ALTER COLUMN id SET DEFAULT nextval('public.security_devices_id_seq'::regclass);


--
-- Name: sites id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sites ALTER COLUMN id SET DEFAULT nextval('public.sites_id_seq'::regclass);


--
-- Name: storage_devices id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storage_devices ALTER COLUMN id SET DEFAULT nextval('public.storage_devices_id_seq'::regclass);


--
-- Name: subnetworks id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subnetworks ALTER COLUMN id SET DEFAULT nextval('public.subnetworks_id_seq'::regclass);


--
-- Name: tasks id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tasks ALTER COLUMN id SET DEFAULT nextval('public.tasks_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: vlans id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vlans ALTER COLUMN id SET DEFAULT nextval('public.vlans_id_seq'::regclass);


--
-- Name: wans id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wans ALTER COLUMN id SET DEFAULT nextval('public.wans_id_seq'::regclass);


--
-- Name: wifi_terminals id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wifi_terminals ALTER COLUMN id SET DEFAULT nextval('public.wifi_terminals_id_seq'::regclass);


--
-- Name: workstations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations ALTER COLUMN id SET DEFAULT nextval('public.workstations_id_seq'::regclass);


--
-- Name: zone_admins id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zone_admins ALTER COLUMN id SET DEFAULT nextval('public.zone_admins_id_seq'::regclass);


--
-- Name: zones id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zones ALTER COLUMN id SET DEFAULT nextval('public.zones_id_seq'::regclass);


--
-- Name: activities activities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_pkey PRIMARY KEY (id);


--
-- Name: activity_impact activity_impact_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_impact
    ADD CONSTRAINT activity_impact_pkey PRIMARY KEY (id);


--
-- Name: actors actors_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actors
    ADD CONSTRAINT actors_pkey PRIMARY KEY (id);


--
-- Name: admin_user_application admin_user_m_application_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_user_application
    ADD CONSTRAINT admin_user_m_application_pkey PRIMARY KEY (admin_user_id, application_id);


--
-- Name: admin_user_zone admin_user_zone_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_user_zone
    ADD CONSTRAINT admin_user_zone_pkey PRIMARY KEY (admin_user_id, zone_id);


--
-- Name: admin_users admin_users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_pkey PRIMARY KEY (id);


--
-- Name: annuaires annuaires_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.annuaires
    ADD CONSTRAINT annuaires_pkey PRIMARY KEY (id);


--
-- Name: application_blocks application_blocks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_blocks
    ADD CONSTRAINT application_blocks_pkey PRIMARY KEY (id);


--
-- Name: application_module_entity application_module_entity_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_module_entity
    ADD CONSTRAINT application_module_entity_pkey PRIMARY KEY (application_module_id, entity_id);


--
-- Name: application_modules application_modules_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_modules
    ADD CONSTRAINT application_modules_pkey PRIMARY KEY (id);


--
-- Name: application_services application_services_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_services
    ADD CONSTRAINT application_services_pkey PRIMARY KEY (id);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: backup_logical_server backup_logical_server_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_logical_server
    ADD CONSTRAINT backup_logical_server_pkey PRIMARY KEY (backup_id, logical_server_id);


--
-- Name: backup_storage_device backup_storage_device_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_storage_device
    ADD CONSTRAINT backup_storage_device_pkey PRIMARY KEY (backup_id, storage_device_id);


--
-- Name: backups backups_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backups
    ADD CONSTRAINT backups_pkey PRIMARY KEY (id);


--
-- Name: bays bays_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bays
    ADD CONSTRAINT bays_pkey PRIMARY KEY (id);


--
-- Name: building_zone building_zone_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.building_zone
    ADD CONSTRAINT building_zone_pkey PRIMARY KEY (building_id, zone_id);


--
-- Name: buildings buildings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.buildings
    ADD CONSTRAINT buildings_pkey PRIMARY KEY (id);


--
-- Name: cartographers cartographers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cartographers
    ADD CONSTRAINT cartographers_pkey PRIMARY KEY (id);


--
-- Name: certificates certificates_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_pkey PRIMARY KEY (id);


--
-- Name: cluster_logical_server cluster_logical_server_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cluster_logical_server
    ADD CONSTRAINT cluster_logical_server_pkey PRIMARY KEY (cluster_id, logical_server_id);


--
-- Name: cluster_physical_server cluster_physical_server_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cluster_physical_server
    ADD CONSTRAINT cluster_physical_server_pkey PRIMARY KEY (cluster_id, physical_server_id);


--
-- Name: cluster_router cluster_router_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cluster_router
    ADD CONSTRAINT cluster_router_pkey PRIMARY KEY (cluster_id, router_id);


--
-- Name: clusters clusters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clusters
    ADD CONSTRAINT clusters_pkey PRIMARY KEY (id);


--
-- Name: container_database container_database_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.container_database
    ADD CONSTRAINT container_database_pkey PRIMARY KEY (database_id, container_id);


--
-- Name: containers containers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.containers
    ADD CONSTRAINT containers_pkey PRIMARY KEY (id);


--
-- Name: cpe_products cpe_products_cpe_vendor_id_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cpe_products
    ADD CONSTRAINT cpe_products_cpe_vendor_id_name_unique UNIQUE (cpe_vendor_id, name);


--
-- Name: cpe_products cpe_products_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cpe_products
    ADD CONSTRAINT cpe_products_pkey PRIMARY KEY (id);


--
-- Name: cpe_vendors cpe_vendors_part_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cpe_vendors
    ADD CONSTRAINT cpe_vendors_part_name_unique UNIQUE (part, name);


--
-- Name: cpe_vendors cpe_vendors_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cpe_vendors
    ADD CONSTRAINT cpe_vendors_pkey PRIMARY KEY (id);


--
-- Name: cpe_versions cpe_versions_cpe_product_id_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cpe_versions
    ADD CONSTRAINT cpe_versions_cpe_product_id_name_unique UNIQUE (cpe_product_id, name);


--
-- Name: cpe_versions cpe_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cpe_versions
    ADD CONSTRAINT cpe_versions_pkey PRIMARY KEY (id);


--
-- Name: data_processing data_processing_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_processing
    ADD CONSTRAINT data_processing_pkey PRIMARY KEY (id);


--
-- Name: databases databases_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.databases
    ADD CONSTRAINT databases_pkey PRIMARY KEY (id);


--
-- Name: dhcp_servers dhcp_servers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dhcp_servers
    ADD CONSTRAINT dhcp_servers_pkey PRIMARY KEY (id);


--
-- Name: dnsservers dnsservers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dnsservers
    ADD CONSTRAINT dnsservers_pkey PRIMARY KEY (id);


--
-- Name: documents documents_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_pkey PRIMARY KEY (id);


--
-- Name: admin_users domain_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT domain_id_user_id_unique UNIQUE (domain_id, user_id, deleted_at);


--
-- Name: domains domaine_ads_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.domains
    ADD CONSTRAINT domaine_ads_pkey PRIMARY KEY (id);


--
-- Name: entities entities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entities
    ADD CONSTRAINT entities_pkey PRIMARY KEY (id);


--
-- Name: external_connected_entities external_connected_entities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.external_connected_entities
    ADD CONSTRAINT external_connected_entities_pkey PRIMARY KEY (id);


--
-- Name: application_flow_information flux_information_flux_id_information_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flow_information
    ADD CONSTRAINT flux_information_flux_id_information_id_unique UNIQUE (flux_id, information_id);


--
-- Name: application_flow_information flux_information_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flow_information
    ADD CONSTRAINT flux_information_pkey PRIMARY KEY (id);


--
-- Name: application_flows fluxes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT fluxes_pkey PRIMARY KEY (id);


--
-- Name: forest_ads forest_ads_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.forest_ads
    ADD CONSTRAINT forest_ads_pkey PRIMARY KEY (id);


--
-- Name: gateways gateways_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gateways
    ADD CONSTRAINT gateways_pkey PRIMARY KEY (id);


--
-- Name: graphs graphs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.graphs
    ADD CONSTRAINT graphs_pkey PRIMARY KEY (id);


--
-- Name: information_information information_information_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.information_information
    ADD CONSTRAINT information_information_pkey PRIMARY KEY (information_id, child_information_id);


--
-- Name: information information_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.information
    ADD CONSTRAINT information_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: lans lans_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lans
    ADD CONSTRAINT lans_pkey PRIMARY KEY (id);


--
-- Name: logical_flows logical_flows_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_pkey PRIMARY KEY (id);


--
-- Name: logical_servers logical_servers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_servers
    ADD CONSTRAINT logical_servers_pkey PRIMARY KEY (id);


--
-- Name: application_events m_application_events_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_events
    ADD CONSTRAINT m_application_events_pkey PRIMARY KEY (id);


--
-- Name: applications m_applications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.applications
    ADD CONSTRAINT m_applications_pkey PRIMARY KEY (id);


--
-- Name: macro_processuses macro_processuses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.macro_processuses
    ADD CONSTRAINT macro_processuses_pkey PRIMARY KEY (id);


--
-- Name: mans mans_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mans
    ADD CONSTRAINT mans_pkey PRIMARY KEY (id);


--
-- Name: media media_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_pkey PRIMARY KEY (id);


--
-- Name: mercator_modules mercator_modules_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mercator_modules
    ADD CONSTRAINT mercator_modules_pkey PRIMARY KEY (name);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: monarc_sync_items monarc_sync_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monarc_sync_items
    ADD CONSTRAINT monarc_sync_items_pkey PRIMARY KEY (id);


--
-- Name: monarc_sync_items monarc_sync_items_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.monarc_sync_items
    ADD CONSTRAINT monarc_sync_items_unique UNIQUE (anr_id, model, mercator_id);


--
-- Name: network_switch_vlan network_switch_vlan_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.network_switch_vlan
    ADD CONSTRAINT network_switch_vlan_pkey PRIMARY KEY (network_switch_id, vlan_id);


--
-- Name: network_switches network_switches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.network_switches
    ADD CONSTRAINT network_switches_pkey PRIMARY KEY (id);


--
-- Name: networks networks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.networks
    ADD CONSTRAINT networks_pkey PRIMARY KEY (id);


--
-- Name: oauth_access_tokens oauth_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oauth_access_tokens
    ADD CONSTRAINT oauth_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: oauth_auth_codes oauth_auth_codes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oauth_auth_codes
    ADD CONSTRAINT oauth_auth_codes_pkey PRIMARY KEY (id);


--
-- Name: oauth_clients oauth_clients_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oauth_clients
    ADD CONSTRAINT oauth_clients_pkey PRIMARY KEY (id);


--
-- Name: oauth_device_codes oauth_device_codes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oauth_device_codes
    ADD CONSTRAINT oauth_device_codes_pkey PRIMARY KEY (id);


--
-- Name: oauth_device_codes oauth_device_codes_user_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oauth_device_codes
    ADD CONSTRAINT oauth_device_codes_user_code_unique UNIQUE (user_code);


--
-- Name: oauth_personal_access_clients oauth_personal_access_clients_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oauth_personal_access_clients
    ADD CONSTRAINT oauth_personal_access_clients_pkey PRIMARY KEY (id);


--
-- Name: oauth_refresh_tokens oauth_refresh_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.oauth_refresh_tokens
    ADD CONSTRAINT oauth_refresh_tokens_pkey PRIMARY KEY (id);


--
-- Name: operations operations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.operations
    ADD CONSTRAINT operations_pkey PRIMARY KEY (id);


--
-- Name: parameters parameters_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parameters
    ADD CONSTRAINT parameters_name_unique UNIQUE (name);


--
-- Name: parameters parameters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parameters
    ADD CONSTRAINT parameters_pkey PRIMARY KEY (id);


--
-- Name: perimeters perimeters_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.perimeters
    ADD CONSTRAINT perimeters_name_unique UNIQUE (name);


--
-- Name: perimeters perimeters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.perimeters
    ADD CONSTRAINT perimeters_pkey PRIMARY KEY (id);


--
-- Name: peripherals peripherals_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.peripherals
    ADD CONSTRAINT peripherals_pkey PRIMARY KEY (id);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: phones phones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.phones
    ADD CONSTRAINT phones_pkey PRIMARY KEY (id);


--
-- Name: physical_links physical_links_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_links_pkey PRIMARY KEY (id);


--
-- Name: physical_routers physical_routers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_routers
    ADD CONSTRAINT physical_routers_pkey PRIMARY KEY (id);


--
-- Name: physical_security_devices physical_security_devices_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_security_devices
    ADD CONSTRAINT physical_security_devices_pkey PRIMARY KEY (id);


--
-- Name: physical_servers physical_servers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_servers
    ADD CONSTRAINT physical_servers_pkey PRIMARY KEY (id);


--
-- Name: physical_switches physical_switches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_switches
    ADD CONSTRAINT physical_switches_pkey PRIMARY KEY (id);


--
-- Name: processes processes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.processes
    ADD CONSTRAINT processes_pkey PRIMARY KEY (id);


--
-- Name: relations relations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.relations
    ADD CONSTRAINT relations_pkey PRIMARY KEY (id);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: routers routers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routers
    ADD CONSTRAINT routers_pkey PRIMARY KEY (id);


--
-- Name: saved_queries saved_queries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saved_queries
    ADD CONSTRAINT saved_queries_pkey PRIMARY KEY (id);


--
-- Name: security_controls security_controls_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_controls
    ADD CONSTRAINT security_controls_pkey PRIMARY KEY (id);


--
-- Name: security_devices security_devices_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_devices
    ADD CONSTRAINT security_devices_pkey PRIMARY KEY (id);


--
-- Name: sites sites_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sites
    ADD CONSTRAINT sites_pkey PRIMARY KEY (id);


--
-- Name: storage_devices storage_devices_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storage_devices
    ADD CONSTRAINT storage_devices_pkey PRIMARY KEY (id);


--
-- Name: subnetworks subnetworks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subnetworks
    ADD CONSTRAINT subnetworks_pkey PRIMARY KEY (id);


--
-- Name: tasks tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email, deleted_at);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: vlans vlans_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vlans
    ADD CONSTRAINT vlans_pkey PRIMARY KEY (id);


--
-- Name: wans wans_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wans
    ADD CONSTRAINT wans_pkey PRIMARY KEY (id);


--
-- Name: wifi_terminals wifi_terminals_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wifi_terminals
    ADD CONSTRAINT wifi_terminals_pkey PRIMARY KEY (id);


--
-- Name: workstations workstations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT workstations_pkey PRIMARY KEY (id);


--
-- Name: zone_admins zone_admins_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zone_admins
    ADD CONSTRAINT zone_admins_pkey PRIMARY KEY (id);


--
-- Name: zone_zone zone_zone_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zone_zone
    ADD CONSTRAINT zone_zone_pkey PRIMARY KEY (zone_id, related_zone_id);


--
-- Name: zones zones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zones
    ADD CONSTRAINT zones_pkey PRIMARY KEY (id);


--
-- Name: activities_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activities_name_index ON public.activities USING btree (name);


--
-- Name: activities_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activities_perimeter_id_index ON public.activities USING btree (perimeter_id);


--
-- Name: activity_id_fk_1472704; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activity_id_fk_1472704 ON public.activity_operation USING btree (activity_id);


--
-- Name: activity_id_fk_1472714; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activity_id_fk_1472714 ON public.activity_document USING btree (activity_id);


--
-- Name: activity_id_fk_1627616; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activity_id_fk_1627616 ON public.activity_process USING btree (activity_id);


--
-- Name: activity_id_fk_4325433; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activity_id_fk_4325433 ON public.entity_document USING btree (entity_id);


--
-- Name: actor_id_fk_1472680; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX actor_id_fk_1472680 ON public.actor_operation USING btree (actor_id);


--
-- Name: actors_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX actors_name_index ON public.actors USING btree (name);


--
-- Name: actors_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX actors_perimeter_id_index ON public.actors USING btree (perimeter_id);


--
-- Name: admin_users_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX admin_users_perimeter_id_index ON public.admin_users USING btree (perimeter_id);


--
-- Name: annuaires_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX annuaires_name_index ON public.annuaires USING btree (name);


--
-- Name: annuaires_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX annuaires_perimeter_id_index ON public.annuaires USING btree (perimeter_id);


--
-- Name: application_block_fk_1644592; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_block_fk_1644592 ON public.applications USING btree (application_block_id);


--
-- Name: application_blocks_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_blocks_name_index ON public.application_blocks USING btree (name);


--
-- Name: application_blocks_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_blocks_perimeter_id_index ON public.application_blocks USING btree (perimeter_id);


--
-- Name: application_dest_fk_1485549; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_dest_fk_1485549 ON public.application_flows USING btree (application_dest_id);


--
-- Name: application_flows_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_flows_name_index ON public.application_flows USING btree (name);


--
-- Name: application_flows_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_flows_perimeter_id_index ON public.application_flows USING btree (perimeter_id);


--
-- Name: application_id_fk_0394834858; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_id_fk_0394834858 ON public.activity_application USING btree (application_id);


--
-- Name: application_id_fk_7812341; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_id_fk_7812341 ON public.application_document USING btree (application_id);


--
-- Name: application_module_id_fk_1492414; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_module_id_fk_1492414 ON public.application_module_application_service USING btree (application_module_id);


--
-- Name: application_modules_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_modules_name_index ON public.application_modules USING btree (name);


--
-- Name: application_modules_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_modules_perimeter_id_index ON public.application_modules USING btree (perimeter_id);


--
-- Name: application_service_id_fk_1482585; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_service_id_fk_1482585 ON public.application_application_service USING btree (application_service_id);


--
-- Name: application_service_id_fk_1492414; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_service_id_fk_1492414 ON public.application_module_application_service USING btree (application_service_id);


--
-- Name: application_services_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_services_name_index ON public.application_services USING btree (name);


--
-- Name: application_services_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_services_perimeter_id_index ON public.application_services USING btree (perimeter_id);


--
-- Name: application_source_fk_1485545; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_source_fk_1485545 ON public.application_flows USING btree (application_source_id);


--
-- Name: applications_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX applications_name_index ON public.applications USING btree (name);


--
-- Name: applications_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX applications_perimeter_id_index ON public.applications USING btree (perimeter_id);


--
-- Name: backups_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backups_name_index ON public.backups USING btree (name);


--
-- Name: backups_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backups_perimeter_id_index ON public.backups USING btree (perimeter_id);


--
-- Name: bay_fk_1485324; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX bay_fk_1485324 ON public.physical_servers USING btree (bay_id);


--
-- Name: bay_fk_1485363; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX bay_fk_1485363 ON public.storage_devices USING btree (bay_id);


--
-- Name: bay_fk_1485451; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX bay_fk_1485451 ON public.peripherals USING btree (bay_id);


--
-- Name: bay_fk_1485493; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX bay_fk_1485493 ON public.physical_switches USING btree (bay_id);


--
-- Name: bay_fk_1485499; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX bay_fk_1485499 ON public.physical_routers USING btree (bay_id);


--
-- Name: bay_fk_1485519; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX bay_fk_1485519 ON public.physical_security_devices USING btree (bay_id);


--
-- Name: bay_id_fk_1485509; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX bay_id_fk_1485509 ON public.bay_wifi_terminal USING btree (bay_id);


--
-- Name: bays_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX bays_name_index ON public.bays USING btree (name);


--
-- Name: bays_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX bays_perimeter_id_index ON public.bays USING btree (perimeter_id);


--
-- Name: building_fk_1485323; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX building_fk_1485323 ON public.physical_servers USING btree (building_id);


--
-- Name: building_fk_1485333; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX building_fk_1485333 ON public.workstations USING btree (building_id);


--
-- Name: building_fk_1485362; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX building_fk_1485362 ON public.storage_devices USING btree (building_id);


--
-- Name: building_fk_1485450; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX building_fk_1485450 ON public.peripherals USING btree (building_id);


--
-- Name: building_fk_1485480; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX building_fk_1485480 ON public.phones USING btree (building_id);


--
-- Name: building_fk_1485489; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX building_fk_1485489 ON public.physical_switches USING btree (building_id);


--
-- Name: building_fk_1485498; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX building_fk_1485498 ON public.physical_routers USING btree (building_id);


--
-- Name: building_fk_1485508; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX building_fk_1485508 ON public.wifi_terminals USING btree (building_id);


--
-- Name: building_fk_1485518; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX building_fk_1485518 ON public.physical_security_devices USING btree (building_id);


--
-- Name: building_id_fk_94821232; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX building_id_fk_94821232 ON public.buildings USING btree (building_id);


--
-- Name: buildings_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX buildings_name_index ON public.buildings USING btree (name);


--
-- Name: buildings_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX buildings_perimeter_id_index ON public.buildings USING btree (perimeter_id);


--
-- Name: cartographers_cartographiable_type_cartographiable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cartographers_cartographiable_type_cartographiable_id_index ON public.cartographers USING btree (cartographiable_type, cartographiable_id);


--
-- Name: cartographers_role_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cartographers_role_id_index ON public.cartographers USING btree (role_id);


--
-- Name: cartographers_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cartographers_user_id_index ON public.cartographers USING btree (user_id);


--
-- Name: certificate_id_fk_4584393; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX certificate_id_fk_4584393 ON public.application_certificate USING btree (certificate_id);


--
-- Name: certificate_id_fk_9483453; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX certificate_id_fk_9483453 ON public.certificate_logical_server USING btree (certificate_id);


--
-- Name: certificates_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX certificates_name_index ON public.certificates USING btree (name);


--
-- Name: certificates_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX certificates_perimeter_id_index ON public.certificates USING btree (perimeter_id);


--
-- Name: cluster_logical_server_logical_server_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cluster_logical_server_logical_server_id_index ON public.cluster_logical_server USING btree (logical_server_id);


--
-- Name: cluster_physical_server_physical_server_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cluster_physical_server_physical_server_id_index ON public.cluster_physical_server USING btree (physical_server_id);


--
-- Name: cluster_router_router_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cluster_router_router_id_index ON public.cluster_router USING btree (router_id);


--
-- Name: clusters_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX clusters_name_index ON public.clusters USING btree (name);


--
-- Name: clusters_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX clusters_perimeter_id_index ON public.clusters USING btree (perimeter_id);


--
-- Name: connected_subnets_fk_1483256; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX connected_subnets_fk_1483256 ON public.subnetworks USING btree (connected_subnets_id);


--
-- Name: container_id_fk_54933032; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX container_id_fk_54933032 ON public.container_logical_server USING btree (container_id);


--
-- Name: container_id_fk_549854345; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX container_id_fk_549854345 ON public.application_container USING btree (container_id);


--
-- Name: containers_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX containers_perimeter_id_index ON public.containers USING btree (perimeter_id);


--
-- Name: cpe_product_fk_1485479; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cpe_product_fk_1485479 ON public.cpe_products USING btree (cpe_vendor_id);


--
-- Name: cpe_version_fk_1485479; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cpe_version_fk_1485479 ON public.cpe_versions USING btree (cpe_product_id);


--
-- Name: data_processing_id_fk_5435435; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX data_processing_id_fk_5435435 ON public.data_processing_process USING btree (data_processing_id);


--
-- Name: data_processing_id_fk_58305863; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX data_processing_id_fk_58305863 ON public.data_processing_information USING btree (data_processing_id);


--
-- Name: data_processing_id_fk_6930583; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX data_processing_id_fk_6930583 ON public.data_processing_document USING btree (data_processing_id);


--
-- Name: data_processing_id_fk_6948435; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX data_processing_id_fk_6948435 ON public.application_data_processing USING btree (data_processing_id);


--
-- Name: data_processing_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX data_processing_name_index ON public.data_processing USING btree (name);


--
-- Name: data_processing_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX data_processing_perimeter_id_index ON public.data_processing USING btree (perimeter_id);


--
-- Name: database_dest_fk_1485552; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX database_dest_fk_1485552 ON public.application_flows USING btree (database_dest_id);


--
-- Name: database_id_fk_1482586; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX database_id_fk_1482586 ON public.application_database USING btree (database_id);


--
-- Name: database_id_fk_1485563; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX database_id_fk_1485563 ON public.database_entity USING btree (database_id);


--
-- Name: database_id_fk_1485570; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX database_id_fk_1485570 ON public.database_information USING btree (database_id);


--
-- Name: database_id_fk_1542475; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX database_id_fk_1542475 ON public.database_logical_server USING btree (database_id);


--
-- Name: database_source_fk_1485548; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX database_source_fk_1485548 ON public.application_flows USING btree (database_source_id);


--
-- Name: databases_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX databases_name_index ON public.databases USING btree (name);


--
-- Name: databases_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX databases_perimeter_id_index ON public.databases USING btree (perimeter_id);


--
-- Name: destination_fk_1494373; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX destination_fk_1494373 ON public.relations USING btree (destination_id);


--
-- Name: dhcp_servers_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX dhcp_servers_name_index ON public.dhcp_servers USING btree (name);


--
-- Name: dhcp_servers_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX dhcp_servers_perimeter_id_index ON public.dhcp_servers USING btree (perimeter_id);


--
-- Name: dnsservers_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX dnsservers_name_index ON public.dnsservers USING btree (name);


--
-- Name: dnsservers_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX dnsservers_perimeter_id_index ON public.dnsservers USING btree (perimeter_id);


--
-- Name: document_id_fk_1284334; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_1284334 ON public.document_logical_server USING btree (document_id);


--
-- Name: document_id_fk_129483; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_129483 ON public.workstations USING btree (icon_id);


--
-- Name: document_id_fk_129484; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_129484 ON public.peripherals USING btree (icon_id);


--
-- Name: document_id_fk_129485; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_129485 ON public.sites USING btree (icon_id);


--
-- Name: document_id_fk_129486; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_129486 ON public.entities USING btree (icon_id);


--
-- Name: document_id_fk_129487; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_129487 ON public.admin_users USING btree (icon_id);


--
-- Name: document_id_fk_4394343; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_4394343 ON public.applications USING btree (icon_id);


--
-- Name: document_id_fk_43948593; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_43948593 ON public.containers USING btree (icon_id);


--
-- Name: document_id_fk_495432841; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_495432841 ON public.clusters USING btree (icon_id);


--
-- Name: document_id_fk_49574431; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_49574431 ON public.buildings USING btree (icon_id);


--
-- Name: document_id_fk_51303394; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_51303394 ON public.logical_servers USING btree (icon_id);


--
-- Name: document_id_fk_5328384; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_5328384 ON public.physical_servers USING btree (icon_id);


--
-- Name: document_id_fk_53812733; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_53812733 ON public.storage_devices USING btree (icon_id);


--
-- Name: document_id_fk_5492844; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_5492844 ON public.document_relation USING btree (document_id);


--
-- Name: document_id_fk_5938654; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_5938654 ON public.processes USING btree (icon_id);


--
-- Name: document_id_fk_61928374; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_61928374 ON public.information USING btree (icon_id);


--
-- Name: document_id_fk_7812342; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_id_fk_7812342 ON public.application_document USING btree (document_id);


--
-- Name: document_idx_434934839; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_idx_434934839 ON public.document_external_connected_entity USING btree (document_id);


--
-- Name: domain_id_fk_493844; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX domain_id_fk_493844 ON public.logical_servers USING btree (domain_id);


--
-- Name: domain_id_fk_69385935; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX domain_id_fk_69385935 ON public.admin_users USING btree (domain_id);


--
-- Name: domaine_ad_id_fk_1492084; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX domaine_ad_id_fk_1492084 ON public.domain_forest_ad USING btree (domain_id);


--
-- Name: domains_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX domains_name_index ON public.domains USING btree (name);


--
-- Name: domains_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX domains_perimeter_id_index ON public.domains USING btree (perimeter_id);


--
-- Name: entities_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX entities_name_index ON public.entities USING btree (name);


--
-- Name: entities_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX entities_perimeter_id_index ON public.entities USING btree (perimeter_id);


--
-- Name: entity_id_fk_1295034; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX entity_id_fk_1295034 ON public.external_connected_entities USING btree (entity_id);


--
-- Name: entity_id_fk_1485563; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX entity_id_fk_1485563 ON public.database_entity USING btree (entity_id);


--
-- Name: entity_id_fk_1488611; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX entity_id_fk_1488611 ON public.application_entity USING btree (entity_id);


--
-- Name: entity_id_fk_1627958; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX entity_id_fk_1627958 ON public.entity_process USING btree (entity_id);


--
-- Name: entity_id_fk_4383234; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX entity_id_fk_4383234 ON public.peripherals USING btree (provider_id);


--
-- Name: entity_id_fk_4398013; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX entity_id_fk_4398013 ON public.entities USING btree (parent_entity_id);


--
-- Name: entity_resp_fk_1485569; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX entity_resp_fk_1485569 ON public.databases USING btree (entity_resp_id);


--
-- Name: entity_resp_fk_1488612; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX entity_resp_fk_1488612 ON public.applications USING btree (entity_resp_id);


--
-- Name: external_connected_entities_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX external_connected_entities_name_index ON public.external_connected_entities USING btree (name);


--
-- Name: external_connected_entities_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX external_connected_entities_perimeter_id_index ON public.external_connected_entities USING btree (perimeter_id);


--
-- Name: external_connected_entity_idx_2143243; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX external_connected_entity_idx_2143243 ON public.document_external_connected_entity USING btree (external_connected_entity_id);


--
-- Name: external_connected_entity_idx_59458458; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX external_connected_entity_idx_59458458 ON public.external_connected_entity_subnetwork USING btree (external_connected_entity_id);


--
-- Name: forest_ad_id_fk_1492084; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX forest_ad_id_fk_1492084 ON public.domain_forest_ad USING btree (forest_ad_id);


--
-- Name: forest_ads_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX forest_ads_name_index ON public.forest_ads USING btree (name);


--
-- Name: forest_ads_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX forest_ads_perimeter_id_index ON public.forest_ads USING btree (perimeter_id);


--
-- Name: gateway_fk_1492376; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX gateway_fk_1492376 ON public.subnetworks USING btree (gateway_id);


--
-- Name: gateways_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX gateways_name_index ON public.gateways USING btree (name);


--
-- Name: gateways_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX gateways_perimeter_id_index ON public.gateways USING btree (perimeter_id);


--
-- Name: graphs_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX graphs_name_index ON public.graphs USING btree (name);


--
-- Name: information_id_fk_1473025; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX information_id_fk_1473025 ON public.information_process USING btree (information_id);


--
-- Name: information_id_fk_1485570; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX information_id_fk_1485570 ON public.database_information USING btree (information_id);


--
-- Name: information_id_fk_4384483; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX information_id_fk_4384483 ON public.data_processing_information USING btree (information_id);


--
-- Name: information_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX information_name_index ON public.information USING btree (name);


--
-- Name: information_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX information_perimeter_id_index ON public.information USING btree (perimeter_id);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: lan_id_fk_1490345; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX lan_id_fk_1490345 ON public.lan_man USING btree (lan_id);


--
-- Name: lan_id_fk_1490368; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX lan_id_fk_1490368 ON public.lan_wan USING btree (lan_id);


--
-- Name: lans_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX lans_name_index ON public.lans USING btree (name);


--
-- Name: lans_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX lans_perimeter_id_index ON public.lans USING btree (perimeter_id);


--
-- Name: logical_flows_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_flows_name_index ON public.logical_flows USING btree (name);


--
-- Name: logical_flows_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_flows_perimeter_id_index ON public.logical_flows USING btree (perimeter_id);


--
-- Name: logical_flows_subnetwork_dest_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_flows_subnetwork_dest_id_index ON public.logical_flows USING btree (subnetwork_dest_id);


--
-- Name: logical_flows_subnetwork_source_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_flows_subnetwork_source_id_index ON public.logical_flows USING btree (subnetwork_source_id);


--
-- Name: logical_server_dest_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_server_dest_id_fk ON public.physical_links USING btree (logical_server_dest_id);


--
-- Name: logical_server_id_fk_1488616; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_server_id_fk_1488616 ON public.application_logical_server USING btree (logical_server_id);


--
-- Name: logical_server_id_fk_1542475; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_server_id_fk_1542475 ON public.database_logical_server USING btree (logical_server_id);


--
-- Name: logical_server_id_fk_1657961; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_server_id_fk_1657961 ON public.logical_server_physical_server USING btree (logical_server_id);


--
-- Name: logical_server_id_fk_43832473; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_server_id_fk_43832473 ON public.document_logical_server USING btree (logical_server_id);


--
-- Name: logical_server_id_fk_4394832; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_server_id_fk_4394832 ON public.container_logical_server USING btree (logical_server_id);


--
-- Name: logical_server_id_fk_9483453; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_server_id_fk_9483453 ON public.certificate_logical_server USING btree (logical_server_id);


--
-- Name: logical_server_src_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_server_src_id_fk ON public.physical_links USING btree (logical_server_src_id);


--
-- Name: logical_servers_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_servers_active ON public.logical_servers USING btree (active);


--
-- Name: logical_servers_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_servers_name_index ON public.logical_servers USING btree (name);


--
-- Name: logical_servers_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logical_servers_perimeter_id_index ON public.logical_servers USING btree (perimeter_id);


--
-- Name: m_application_id_fk_1482573; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_application_id_fk_1482573 ON public.application_process USING btree (application_id);


--
-- Name: m_application_id_fk_1482585; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_application_id_fk_1482585 ON public.application_application_service USING btree (application_id);


--
-- Name: m_application_id_fk_1482586; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_application_id_fk_1482586 ON public.application_database USING btree (application_id);


--
-- Name: m_application_id_fk_1486547; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_application_id_fk_1486547 ON public.application_workstation USING btree (application_id);


--
-- Name: m_application_id_fk_1488611; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_application_id_fk_1488611 ON public.application_entity USING btree (application_id);


--
-- Name: m_application_id_fk_1488616; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_application_id_fk_1488616 ON public.application_logical_server USING btree (application_id);


--
-- Name: m_application_id_fk_344234340; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_application_id_fk_344234340 ON public.application_container USING btree (application_id);


--
-- Name: m_application_id_fk_41923483; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_application_id_fk_41923483 ON public.application_security_device USING btree (application_id);


--
-- Name: m_application_id_fk_4584393s; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_application_id_fk_4584393s ON public.application_certificate USING btree (application_id);


--
-- Name: m_application_id_fk_5483543; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_application_id_fk_5483543 ON public.application_physical_server USING btree (application_id);


--
-- Name: m_application_id_fk_5837573; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_application_id_fk_5837573 ON public.application_security_control USING btree (application_id);


--
-- Name: m_application_id_fk_9878654; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_application_id_fk_9878654 ON public.application_peripheral USING btree (application_id);


--
-- Name: m_applications_id_fk_4384483; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX m_applications_id_fk_4384483 ON public.application_data_processing USING btree (application_id);


--
-- Name: macro_processuses_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX macro_processuses_name_index ON public.macro_processuses USING btree (name);


--
-- Name: macro_processuses_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX macro_processuses_perimeter_id_index ON public.macro_processuses USING btree (perimeter_id);


--
-- Name: man_id_fk_1490345; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX man_id_fk_1490345 ON public.lan_man USING btree (man_id);


--
-- Name: man_id_fk_1490367; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX man_id_fk_1490367 ON public.man_wan USING btree (man_id);


--
-- Name: man_id_fk_4385454; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX man_id_fk_4385454 ON public.mans USING btree (parent_man_id);


--
-- Name: mans_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX mans_name_index ON public.mans USING btree (name);


--
-- Name: mans_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX mans_perimeter_id_index ON public.mans USING btree (perimeter_id);


--
-- Name: media_model_type_model_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX media_model_type_model_id_index ON public.media USING btree (model_type, model_id);


--
-- Name: module_dest_fk_1485551; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX module_dest_fk_1485551 ON public.application_flows USING btree (module_dest_id);


--
-- Name: module_source_fk_1485547; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX module_source_fk_1485547 ON public.application_flows USING btree (module_source_id);


--
-- Name: network_fk_5476544; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX network_fk_5476544 ON public.subnetworks USING btree (network_id);


--
-- Name: network_id_fk_8596554; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX network_id_fk_8596554 ON public.external_connected_entities USING btree (network_id);


--
-- Name: network_switch_id_fk_543323; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX network_switch_id_fk_543323 ON public.network_switch_physical_switch USING btree (network_switch_id);


--
-- Name: network_switch_vlan_network_switch_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX network_switch_vlan_network_switch_id_index ON public.network_switch_vlan USING btree (network_switch_id);


--
-- Name: network_switch_vlan_vlan_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX network_switch_vlan_vlan_id_index ON public.network_switch_vlan USING btree (vlan_id);


--
-- Name: network_switches_dest_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX network_switches_dest_id_fk ON public.physical_links USING btree (network_switch_dest_id);


--
-- Name: network_switches_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX network_switches_name_index ON public.network_switches USING btree (name);


--
-- Name: network_switches_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX network_switches_perimeter_id_index ON public.network_switches USING btree (perimeter_id);


--
-- Name: network_switches_src_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX network_switches_src_id_fk ON public.physical_links USING btree (network_switch_src_id);


--
-- Name: networks_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX networks_name_index ON public.networks USING btree (name);


--
-- Name: networks_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX networks_perimeter_id_index ON public.networks USING btree (perimeter_id);


--
-- Name: oauth_access_tokens_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX oauth_access_tokens_user_id_index ON public.oauth_access_tokens USING btree (user_id);


--
-- Name: oauth_auth_codes_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX oauth_auth_codes_user_id_index ON public.oauth_auth_codes USING btree (user_id);


--
-- Name: oauth_clients_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX oauth_clients_user_id_index ON public.oauth_clients USING btree (user_id);


--
-- Name: oauth_device_codes_client_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX oauth_device_codes_client_id_index ON public.oauth_device_codes USING btree (client_id);


--
-- Name: oauth_device_codes_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX oauth_device_codes_user_id_index ON public.oauth_device_codes USING btree (user_id);


--
-- Name: oauth_refresh_tokens_access_token_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX oauth_refresh_tokens_access_token_id_index ON public.oauth_refresh_tokens USING btree (access_token_id);


--
-- Name: operation_id_fk_1472680; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX operation_id_fk_1472680 ON public.actor_operation USING btree (operation_id);


--
-- Name: operation_id_fk_1472704; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX operation_id_fk_1472704 ON public.activity_operation USING btree (operation_id);


--
-- Name: operation_id_fk_1472714; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX operation_id_fk_1472714 ON public.activity_document USING btree (document_id);


--
-- Name: operation_id_fk_1472749; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX operation_id_fk_1472749 ON public.operation_task USING btree (operation_id);


--
-- Name: operation_id_fk_4355431; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX operation_id_fk_4355431 ON public.data_processing_document USING btree (document_id);


--
-- Name: operation_id_fk_5837593; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX operation_id_fk_5837593 ON public.entity_document USING btree (document_id);


--
-- Name: operations_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX operations_name_index ON public.operations USING btree (name);


--
-- Name: operations_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX operations_perimeter_id_index ON public.operations USING btree (perimeter_id);


--
-- Name: password_resets_email_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX password_resets_email_index ON public.password_resets USING btree (email);


--
-- Name: peripheral_dest_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX peripheral_dest_id_fk ON public.physical_links USING btree (peripheral_dest_id);


--
-- Name: peripheral_id_fk_6454564; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX peripheral_id_fk_6454564 ON public.application_peripheral USING btree (peripheral_id);


--
-- Name: peripheral_src_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX peripheral_src_id_fk ON public.physical_links USING btree (peripheral_src_id);


--
-- Name: peripherals_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX peripherals_name_index ON public.peripherals USING btree (name);


--
-- Name: peripherals_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX peripherals_perimeter_id_index ON public.peripherals USING btree (perimeter_id);


--
-- Name: permission_id_fk_1470794; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX permission_id_fk_1470794 ON public.permission_role USING btree (permission_id);


--
-- Name: phone_dest_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX phone_dest_id_fk ON public.physical_links USING btree (phone_dest_id);


--
-- Name: phone_src_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX phone_src_id_fk ON public.physical_links USING btree (phone_src_id);


--
-- Name: phones_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX phones_name_index ON public.phones USING btree (name);


--
-- Name: phones_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX phones_perimeter_id_index ON public.phones USING btree (perimeter_id);


--
-- Name: physical_links_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_links_perimeter_id_index ON public.physical_links USING btree (perimeter_id);


--
-- Name: physical_router_dest_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_router_dest_id_fk ON public.physical_links USING btree (physical_router_dest_id);


--
-- Name: physical_router_id_fk_124983; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_router_id_fk_124983 ON public.physical_router_router USING btree (physical_router_id);


--
-- Name: physical_router_id_fk_1658250; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_router_id_fk_1658250 ON public.physical_router_vlan USING btree (physical_router_id);


--
-- Name: physical_router_src_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_router_src_id_fk ON public.physical_links USING btree (physical_router_src_id);


--
-- Name: physical_routers_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_routers_name_index ON public.physical_routers USING btree (name);


--
-- Name: physical_routers_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_routers_perimeter_id_index ON public.physical_routers USING btree (perimeter_id);


--
-- Name: physical_security_device_dest_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_security_device_dest_id_fk ON public.physical_links USING btree (physical_security_device_dest_id);


--
-- Name: physical_security_device_id_fk_6549543; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_security_device_id_fk_6549543 ON public.physical_security_device_security_device USING btree (physical_security_device_id);


--
-- Name: physical_security_device_src_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_security_device_src_id_fk ON public.physical_links USING btree (physical_security_device_src_id);


--
-- Name: physical_security_devices_icon_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_security_devices_icon_id_index ON public.physical_security_devices USING btree (icon_id);


--
-- Name: physical_security_devices_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_security_devices_name_index ON public.physical_security_devices USING btree (name);


--
-- Name: physical_security_devices_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_security_devices_perimeter_id_index ON public.physical_security_devices USING btree (perimeter_id);


--
-- Name: physical_server_dest_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_server_dest_id_fk ON public.physical_links USING btree (physical_server_dest_id);


--
-- Name: physical_server_id_fk_1657961; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_server_id_fk_1657961 ON public.logical_server_physical_server USING btree (physical_server_id);


--
-- Name: physical_server_id_fk_4543543; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_server_id_fk_4543543 ON public.application_physical_server USING btree (physical_server_id);


--
-- Name: physical_server_src_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_server_src_id_fk ON public.physical_links USING btree (physical_server_src_id);


--
-- Name: physical_servers_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_servers_name_index ON public.physical_servers USING btree (name);


--
-- Name: physical_servers_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_servers_perimeter_id_index ON public.physical_servers USING btree (perimeter_id);


--
-- Name: physical_switch_dest_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_switch_dest_id_fk ON public.physical_links USING btree (physical_switch_dest_id);


--
-- Name: physical_switch_fk_0938434; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_switch_fk_0938434 ON public.workstations USING btree (physical_switch_id);


--
-- Name: physical_switch_fk_5738332; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_switch_fk_5738332 ON public.phones USING btree (physical_switch_id);


--
-- Name: physical_switch_fk_8732342; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_switch_fk_8732342 ON public.physical_servers USING btree (physical_switch_id);


--
-- Name: physical_switch_id_fk_4543143; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_switch_id_fk_4543143 ON public.network_switch_physical_switch USING btree (physical_switch_id);


--
-- Name: physical_switch_src_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_switch_src_id_fk ON public.physical_links USING btree (physical_switch_src_id);


--
-- Name: physical_switches_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_switches_name_index ON public.physical_switches USING btree (name);


--
-- Name: physical_switches_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX physical_switches_perimeter_id_index ON public.physical_switches USING btree (perimeter_id);


--
-- Name: process_fk_4342342; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX process_fk_4342342 ON public.processes USING btree (macroprocess_id);


--
-- Name: process_id_fk_1473025; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX process_id_fk_1473025 ON public.information_process USING btree (process_id);


--
-- Name: process_id_fk_1482573; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX process_id_fk_1482573 ON public.application_process USING btree (process_id);


--
-- Name: process_id_fk_1627616; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX process_id_fk_1627616 ON public.activity_process USING btree (process_id);


--
-- Name: process_id_fk_1627958; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX process_id_fk_1627958 ON public.entity_process USING btree (process_id);


--
-- Name: process_id_fk_394823838; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX process_id_fk_394823838 ON public.activity_application USING btree (activity_id);


--
-- Name: process_id_fk_5837573; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX process_id_fk_5837573 ON public.security_control_process USING btree (process_id);


--
-- Name: process_id_fk_594358; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX process_id_fk_594358 ON public.data_processing_process USING btree (process_id);


--
-- Name: process_id_fk_7945129; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX process_id_fk_7945129 ON public.operations USING btree (process_id);


--
-- Name: processes_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX processes_name_index ON public.processes USING btree (name);


--
-- Name: processes_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX processes_perimeter_id_index ON public.processes USING btree (perimeter_id);


--
-- Name: relation_id_fk_43243244; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX relation_id_fk_43243244 ON public.relation_values USING btree (relation_id);


--
-- Name: relation_id_fk_6948334; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX relation_id_fk_6948334 ON public.document_relation USING btree (relation_id);


--
-- Name: relations_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX relations_name_index ON public.relations USING btree (name);


--
-- Name: relations_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX relations_perimeter_id_index ON public.relations USING btree (perimeter_id);


--
-- Name: role_id_fk_1470794; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX role_id_fk_1470794 ON public.permission_role USING btree (role_id);


--
-- Name: role_id_fk_1470803; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX role_id_fk_1470803 ON public.role_user USING btree (role_id);


--
-- Name: roles_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX roles_perimeter_id_index ON public.roles USING btree (perimeter_id);


--
-- Name: room_fk_1483441; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX room_fk_1483441 ON public.bays USING btree (building_id);


--
-- Name: router_dest_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX router_dest_id_fk ON public.physical_links USING btree (router_dest_id);


--
-- Name: router_id_fk_4382393; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX router_id_fk_4382393 ON public.logical_flows USING btree (router_id);


--
-- Name: router_id_fk_958343; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX router_id_fk_958343 ON public.physical_router_router USING btree (router_id);


--
-- Name: router_src_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX router_src_id_fk ON public.physical_links USING btree (router_src_id);


--
-- Name: routers_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX routers_name_index ON public.routers USING btree (name);


--
-- Name: routers_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX routers_perimeter_id_index ON public.routers USING btree (perimeter_id);


--
-- Name: saved_queries_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX saved_queries_name_index ON public.saved_queries USING btree (name);


--
-- Name: saved_queries_user_id_is_public_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX saved_queries_user_id_is_public_index ON public.saved_queries USING btree (user_id, is_public);


--
-- Name: security_control_id_fk_54354353; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX security_control_id_fk_54354353 ON public.security_control_process USING btree (security_control_id);


--
-- Name: security_control_id_fk_5920381; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX security_control_id_fk_5920381 ON public.application_security_control USING btree (security_control_id);


--
-- Name: security_controls_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX security_controls_name_index ON public.security_controls USING btree (name);


--
-- Name: security_controls_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX security_controls_perimeter_id_index ON public.security_controls USING btree (perimeter_id);


--
-- Name: security_device_id_fk_304832731; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX security_device_id_fk_304832731 ON public.application_security_device USING btree (security_device_id);


--
-- Name: security_device_id_fk_43329392; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX security_device_id_fk_43329392 ON public.physical_security_device_security_device USING btree (security_device_id);


--
-- Name: security_devices_icon_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX security_devices_icon_id_index ON public.security_devices USING btree (icon_id);


--
-- Name: security_devices_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX security_devices_name_index ON public.security_devices USING btree (name);


--
-- Name: security_devices_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX security_devices_perimeter_id_index ON public.security_devices USING btree (perimeter_id);


--
-- Name: service_dest_fk_1485550; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX service_dest_fk_1485550 ON public.application_flows USING btree (service_dest_id);


--
-- Name: service_source_fk_1485546; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX service_source_fk_1485546 ON public.application_flows USING btree (service_source_id);


--
-- Name: site_fk_1483431; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX site_fk_1483431 ON public.buildings USING btree (site_id);


--
-- Name: site_fk_1485322; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX site_fk_1485322 ON public.physical_servers USING btree (site_id);


--
-- Name: site_fk_1485332; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX site_fk_1485332 ON public.workstations USING btree (site_id);


--
-- Name: site_fk_1485361; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX site_fk_1485361 ON public.storage_devices USING btree (site_id);


--
-- Name: site_fk_1485449; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX site_fk_1485449 ON public.peripherals USING btree (site_id);


--
-- Name: site_fk_1485479; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX site_fk_1485479 ON public.phones USING btree (site_id);


--
-- Name: site_fk_1485488; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX site_fk_1485488 ON public.physical_switches USING btree (site_id);


--
-- Name: site_fk_1485497; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX site_fk_1485497 ON public.physical_routers USING btree (site_id);


--
-- Name: site_fk_1485507; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX site_fk_1485507 ON public.wifi_terminals USING btree (site_id);


--
-- Name: site_fk_1485517; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX site_fk_1485517 ON public.physical_security_devices USING btree (site_id);


--
-- Name: site_id_fk_1483442; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX site_id_fk_1483442 ON public.bays USING btree (site_id);


--
-- Name: sites_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sites_name_index ON public.sites USING btree (name);


--
-- Name: sites_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sites_perimeter_id_index ON public.sites USING btree (perimeter_id);


--
-- Name: source_fk_1494372; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX source_fk_1494372 ON public.relations USING btree (source_id);


--
-- Name: storage_device_dest_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX storage_device_dest_id_fk ON public.physical_links USING btree (storage_device_dest_id);


--
-- Name: storage_device_src_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX storage_device_src_id_fk ON public.physical_links USING btree (storage_device_src_id);


--
-- Name: storage_devices_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX storage_devices_name_index ON public.storage_devices USING btree (name);


--
-- Name: storage_devices_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX storage_devices_perimeter_id_index ON public.storage_devices USING btree (perimeter_id);


--
-- Name: subnetwork_idx_4343848; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX subnetwork_idx_4343848 ON public.external_connected_entity_subnetwork USING btree (subnetwork_id);


--
-- Name: subnetworks_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX subnetworks_name_index ON public.subnetworks USING btree (name);


--
-- Name: subnetworks_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX subnetworks_perimeter_id_index ON public.subnetworks USING btree (perimeter_id);


--
-- Name: subnetworks_subnetwork_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX subnetworks_subnetwork_id_index ON public.subnetworks USING btree (subnetwork_id);


--
-- Name: task_id_fk_1472749; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX task_id_fk_1472749 ON public.operation_task USING btree (task_id);


--
-- Name: tasks_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tasks_name_index ON public.tasks USING btree (name);


--
-- Name: tasks_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tasks_perimeter_id_index ON public.tasks USING btree (perimeter_id);


--
-- Name: type; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX type ON public.entities USING btree (type);


--
-- Name: user_id_fk_1470803; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_id_fk_1470803 ON public.role_user USING btree (user_id);


--
-- Name: vlan_fk_6844934; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX vlan_fk_6844934 ON public.subnetworks USING btree (vlan_id);


--
-- Name: vlan_id_fk_1658250; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX vlan_id_fk_1658250 ON public.physical_router_vlan USING btree (vlan_id);


--
-- Name: vlans_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX vlans_name_index ON public.vlans USING btree (name);


--
-- Name: vlans_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX vlans_perimeter_id_index ON public.vlans USING btree (perimeter_id);


--
-- Name: wan_id_fk_1490367; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wan_id_fk_1490367 ON public.man_wan USING btree (wan_id);


--
-- Name: wan_id_fk_1490368; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wan_id_fk_1490368 ON public.lan_wan USING btree (wan_id);


--
-- Name: wans_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wans_name_index ON public.wans USING btree (name);


--
-- Name: wans_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wans_perimeter_id_index ON public.wans USING btree (perimeter_id);


--
-- Name: wifi_terminal_dest_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wifi_terminal_dest_id_fk ON public.physical_links USING btree (wifi_terminal_dest_id);


--
-- Name: wifi_terminal_id_fk_1485509; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wifi_terminal_id_fk_1485509 ON public.bay_wifi_terminal USING btree (wifi_terminal_id);


--
-- Name: wifi_terminal_src_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wifi_terminal_src_id_fk ON public.physical_links USING btree (wifi_terminal_src_id);


--
-- Name: wifi_terminals_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wifi_terminals_name_index ON public.wifi_terminals USING btree (name);


--
-- Name: wifi_terminals_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wifi_terminals_perimeter_id_index ON public.wifi_terminals USING btree (perimeter_id);


--
-- Name: workstation_dest_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workstation_dest_id_fk ON public.physical_links USING btree (workstation_dest_id);


--
-- Name: workstation_id_fk_1486547; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workstation_id_fk_1486547 ON public.application_workstation USING btree (workstation_id);


--
-- Name: workstation_src_id_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workstation_src_id_fk ON public.physical_links USING btree (workstation_src_id);


--
-- Name: workstations_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workstations_name_index ON public.workstations USING btree (name);


--
-- Name: workstations_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workstations_perimeter_id_index ON public.workstations USING btree (perimeter_id);


--
-- Name: zone_admin_fk_1482666; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX zone_admin_fk_1482666 ON public.annuaires USING btree (zone_admin_id);


--
-- Name: zone_admin_fk_1482667; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX zone_admin_fk_1482667 ON public.forest_ads USING btree (zone_admin_id);


--
-- Name: zone_admins_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX zone_admins_name_index ON public.zone_admins USING btree (name);


--
-- Name: zone_admins_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX zone_admins_perimeter_id_index ON public.zone_admins USING btree (perimeter_id);


--
-- Name: zones_perimeter_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX zones_perimeter_id_index ON public.zones USING btree (perimeter_id);


--
-- Name: activities activities_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: activities activities_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: activities activities_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: activities activities_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: activity_application activity_application_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_application
    ADD CONSTRAINT activity_application_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: activity_operation activity_id_fk_1472704; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_operation
    ADD CONSTRAINT activity_id_fk_1472704 FOREIGN KEY (activity_id) REFERENCES public.activities(id) ON DELETE CASCADE;


--
-- Name: activity_document activity_id_fk_1472784; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_document
    ADD CONSTRAINT activity_id_fk_1472784 FOREIGN KEY (activity_id) REFERENCES public.activities(id) ON DELETE CASCADE;


--
-- Name: activity_process activity_id_fk_1627616; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_process
    ADD CONSTRAINT activity_id_fk_1627616 FOREIGN KEY (activity_id) REFERENCES public.activities(id) ON DELETE CASCADE;


--
-- Name: activity_impact activity_impact_activity_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_impact
    ADD CONSTRAINT activity_impact_activity_id_foreign FOREIGN KEY (activity_id) REFERENCES public.activities(id) ON DELETE CASCADE;


--
-- Name: activity_application activity_m_application_activity_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_application
    ADD CONSTRAINT activity_m_application_activity_id_foreign FOREIGN KEY (activity_id) REFERENCES public.activities(id) ON DELETE CASCADE;


--
-- Name: actor_operation actor_id_fk_1472680; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actor_operation
    ADD CONSTRAINT actor_id_fk_1472680 FOREIGN KEY (actor_id) REFERENCES public.actors(id) ON DELETE CASCADE;


--
-- Name: actors actors_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actors
    ADD CONSTRAINT actors_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: actors actors_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actors
    ADD CONSTRAINT actors_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: actors actors_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actors
    ADD CONSTRAINT actors_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: actors actors_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actors
    ADD CONSTRAINT actors_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: admin_user_application admin_user_application_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_user_application
    ADD CONSTRAINT admin_user_application_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: admin_user_application admin_user_m_application_admin_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_user_application
    ADD CONSTRAINT admin_user_m_application_admin_user_id_foreign FOREIGN KEY (admin_user_id) REFERENCES public.admin_users(id) ON DELETE CASCADE;


--
-- Name: admin_user_zone admin_user_zone_admin_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_user_zone
    ADD CONSTRAINT admin_user_zone_admin_user_id_foreign FOREIGN KEY (admin_user_id) REFERENCES public.admin_users(id) ON DELETE CASCADE;


--
-- Name: admin_user_zone admin_user_zone_zone_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_user_zone
    ADD CONSTRAINT admin_user_zone_zone_id_foreign FOREIGN KEY (zone_id) REFERENCES public.zones(id) ON DELETE CASCADE;


--
-- Name: admin_users admin_users_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: admin_users admin_users_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: admin_users admin_users_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: admin_users admin_users_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: annuaires annuaires_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.annuaires
    ADD CONSTRAINT annuaires_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: annuaires annuaires_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.annuaires
    ADD CONSTRAINT annuaires_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: annuaires annuaires_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.annuaires
    ADD CONSTRAINT annuaires_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: annuaires annuaires_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.annuaires
    ADD CONSTRAINT annuaires_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: annuaires annuaires_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.annuaires
    ADD CONSTRAINT annuaires_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_application_service application_application_service_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_application_service
    ADD CONSTRAINT application_application_service_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: applications application_block_fk_1644592; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.applications
    ADD CONSTRAINT application_block_fk_1644592 FOREIGN KEY (application_block_id) REFERENCES public.application_blocks(id);


--
-- Name: application_blocks application_blocks_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_blocks
    ADD CONSTRAINT application_blocks_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_blocks application_blocks_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_blocks
    ADD CONSTRAINT application_blocks_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_blocks application_blocks_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_blocks
    ADD CONSTRAINT application_blocks_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: application_blocks application_blocks_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_blocks
    ADD CONSTRAINT application_blocks_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_certificate application_certificate_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_certificate
    ADD CONSTRAINT application_certificate_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: application_container application_container_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_container
    ADD CONSTRAINT application_container_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: application_data_processing application_data_processing_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_data_processing
    ADD CONSTRAINT application_data_processing_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: application_database application_database_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_database
    ADD CONSTRAINT application_database_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: application_flows application_dest_fk_1485549; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT application_dest_fk_1485549 FOREIGN KEY (application_dest_id) REFERENCES public.applications(id);


--
-- Name: application_entity application_entity_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_entity
    ADD CONSTRAINT application_entity_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: application_events application_events_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_events
    ADD CONSTRAINT application_events_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: application_flow_information application_flow_information_flux_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flow_information
    ADD CONSTRAINT application_flow_information_flux_id_foreign FOREIGN KEY (flux_id) REFERENCES public.application_flows(id) ON DELETE CASCADE;


--
-- Name: application_flow_information application_flow_information_information_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flow_information
    ADD CONSTRAINT application_flow_information_information_id_foreign FOREIGN KEY (information_id) REFERENCES public.information(id) ON DELETE CASCADE;


--
-- Name: application_flows application_flows_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT application_flows_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_flows application_flows_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT application_flows_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_flows application_flows_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT application_flows_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: application_flows application_flows_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT application_flows_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_document application_id_fk_7812341; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_document
    ADD CONSTRAINT application_id_fk_7812341 FOREIGN KEY (application_id) REFERENCES public.applications(id) ON DELETE CASCADE;


--
-- Name: application_logical_server application_logical_server_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_logical_server
    ADD CONSTRAINT application_logical_server_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: application_module_entity application_module_entity_application_module_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_module_entity
    ADD CONSTRAINT application_module_entity_application_module_id_foreign FOREIGN KEY (application_module_id) REFERENCES public.application_modules(id) ON DELETE CASCADE;


--
-- Name: application_module_entity application_module_entity_entity_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_module_entity
    ADD CONSTRAINT application_module_entity_entity_id_foreign FOREIGN KEY (entity_id) REFERENCES public.entities(id) ON DELETE CASCADE;


--
-- Name: application_module_application_service application_module_id_fk_1492414; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_module_application_service
    ADD CONSTRAINT application_module_id_fk_1492414 FOREIGN KEY (application_module_id) REFERENCES public.application_modules(id) ON DELETE CASCADE;


--
-- Name: application_modules application_modules_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_modules
    ADD CONSTRAINT application_modules_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_modules application_modules_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_modules
    ADD CONSTRAINT application_modules_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_modules application_modules_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_modules
    ADD CONSTRAINT application_modules_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: application_modules application_modules_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_modules
    ADD CONSTRAINT application_modules_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_peripheral application_peripheral_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_peripheral
    ADD CONSTRAINT application_peripheral_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: application_physical_server application_physical_server_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_physical_server
    ADD CONSTRAINT application_physical_server_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: application_process application_process_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_process
    ADD CONSTRAINT application_process_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: application_security_control application_security_control_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_security_control
    ADD CONSTRAINT application_security_control_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: application_security_device application_security_device_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_security_device
    ADD CONSTRAINT application_security_device_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: application_application_service application_service_id_fk_1482585; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_application_service
    ADD CONSTRAINT application_service_id_fk_1482585 FOREIGN KEY (application_service_id) REFERENCES public.application_services(id) ON DELETE CASCADE;


--
-- Name: application_module_application_service application_service_id_fk_1492414; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_module_application_service
    ADD CONSTRAINT application_service_id_fk_1492414 FOREIGN KEY (application_service_id) REFERENCES public.application_services(id) ON DELETE CASCADE;


--
-- Name: application_services application_services_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_services
    ADD CONSTRAINT application_services_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_services application_services_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_services
    ADD CONSTRAINT application_services_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_services application_services_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_services
    ADD CONSTRAINT application_services_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: application_services application_services_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_services
    ADD CONSTRAINT application_services_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_flows application_source_fk_1485545; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT application_source_fk_1485545 FOREIGN KEY (application_source_id) REFERENCES public.applications(id);


--
-- Name: application_workstation application_workstation_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_workstation
    ADD CONSTRAINT application_workstation_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.applications(id);


--
-- Name: applications applications_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.applications
    ADD CONSTRAINT applications_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: applications applications_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.applications
    ADD CONSTRAINT applications_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: applications applications_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.applications
    ADD CONSTRAINT applications_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: applications applications_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.applications
    ADD CONSTRAINT applications_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: backup_logical_server backup_logical_server_backup_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_logical_server
    ADD CONSTRAINT backup_logical_server_backup_id_foreign FOREIGN KEY (backup_id) REFERENCES public.backups(id) ON DELETE CASCADE;


--
-- Name: backup_logical_server backup_logical_server_logical_server_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_logical_server
    ADD CONSTRAINT backup_logical_server_logical_server_id_foreign FOREIGN KEY (logical_server_id) REFERENCES public.logical_servers(id) ON DELETE CASCADE;


--
-- Name: backup_storage_device backup_storage_device_backup_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_storage_device
    ADD CONSTRAINT backup_storage_device_backup_id_foreign FOREIGN KEY (backup_id) REFERENCES public.backups(id) ON DELETE CASCADE;


--
-- Name: backup_storage_device backup_storage_device_storage_device_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_storage_device
    ADD CONSTRAINT backup_storage_device_storage_device_id_foreign FOREIGN KEY (storage_device_id) REFERENCES public.storage_devices(id) ON DELETE CASCADE;


--
-- Name: backups backups_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backups
    ADD CONSTRAINT backups_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: backups backups_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backups
    ADD CONSTRAINT backups_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: backups backups_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backups
    ADD CONSTRAINT backups_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: backups backups_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backups
    ADD CONSTRAINT backups_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_servers bay_fk_1485324; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_servers
    ADD CONSTRAINT bay_fk_1485324 FOREIGN KEY (bay_id) REFERENCES public.bays(id);


--
-- Name: storage_devices bay_fk_1485363; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storage_devices
    ADD CONSTRAINT bay_fk_1485363 FOREIGN KEY (bay_id) REFERENCES public.bays(id);


--
-- Name: peripherals bay_fk_1485451; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.peripherals
    ADD CONSTRAINT bay_fk_1485451 FOREIGN KEY (bay_id) REFERENCES public.bays(id);


--
-- Name: physical_switches bay_fk_1485493; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_switches
    ADD CONSTRAINT bay_fk_1485493 FOREIGN KEY (bay_id) REFERENCES public.bays(id);


--
-- Name: physical_routers bay_fk_1485499; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_routers
    ADD CONSTRAINT bay_fk_1485499 FOREIGN KEY (bay_id) REFERENCES public.bays(id);


--
-- Name: physical_security_devices bay_fk_1485519; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_security_devices
    ADD CONSTRAINT bay_fk_1485519 FOREIGN KEY (bay_id) REFERENCES public.bays(id);


--
-- Name: bay_wifi_terminal bay_id_fk_1485509; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bay_wifi_terminal
    ADD CONSTRAINT bay_id_fk_1485509 FOREIGN KEY (bay_id) REFERENCES public.bays(id) ON DELETE CASCADE;


--
-- Name: bays bays_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bays
    ADD CONSTRAINT bays_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: bays bays_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bays
    ADD CONSTRAINT bays_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: bays bays_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bays
    ADD CONSTRAINT bays_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: bays bays_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bays
    ADD CONSTRAINT bays_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: bays building_fk_1483441; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bays
    ADD CONSTRAINT building_fk_1483441 FOREIGN KEY (building_id) REFERENCES public.buildings(id);


--
-- Name: physical_servers building_fk_1485323; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_servers
    ADD CONSTRAINT building_fk_1485323 FOREIGN KEY (building_id) REFERENCES public.buildings(id);


--
-- Name: workstations building_fk_1485333; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT building_fk_1485333 FOREIGN KEY (building_id) REFERENCES public.buildings(id);


--
-- Name: storage_devices building_fk_1485362; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storage_devices
    ADD CONSTRAINT building_fk_1485362 FOREIGN KEY (building_id) REFERENCES public.buildings(id);


--
-- Name: peripherals building_fk_1485450; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.peripherals
    ADD CONSTRAINT building_fk_1485450 FOREIGN KEY (building_id) REFERENCES public.buildings(id);


--
-- Name: phones building_fk_1485480; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.phones
    ADD CONSTRAINT building_fk_1485480 FOREIGN KEY (building_id) REFERENCES public.buildings(id);


--
-- Name: physical_switches building_fk_1485489; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_switches
    ADD CONSTRAINT building_fk_1485489 FOREIGN KEY (building_id) REFERENCES public.buildings(id);


--
-- Name: physical_routers building_fk_1485498; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_routers
    ADD CONSTRAINT building_fk_1485498 FOREIGN KEY (building_id) REFERENCES public.buildings(id);


--
-- Name: wifi_terminals building_fk_1485508; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wifi_terminals
    ADD CONSTRAINT building_fk_1485508 FOREIGN KEY (building_id) REFERENCES public.buildings(id);


--
-- Name: physical_security_devices building_fk_1485518; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_security_devices
    ADD CONSTRAINT building_fk_1485518 FOREIGN KEY (building_id) REFERENCES public.buildings(id);


--
-- Name: building_zone building_zone_building_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.building_zone
    ADD CONSTRAINT building_zone_building_id_foreign FOREIGN KEY (building_id) REFERENCES public.buildings(id) ON DELETE CASCADE;


--
-- Name: building_zone building_zone_zone_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.building_zone
    ADD CONSTRAINT building_zone_zone_id_foreign FOREIGN KEY (zone_id) REFERENCES public.zones(id) ON DELETE CASCADE;


--
-- Name: buildings buildings_building_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.buildings
    ADD CONSTRAINT buildings_building_id_foreign FOREIGN KEY (building_id) REFERENCES public.buildings(id) ON DELETE SET NULL;


--
-- Name: buildings buildings_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.buildings
    ADD CONSTRAINT buildings_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: buildings buildings_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.buildings
    ADD CONSTRAINT buildings_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: buildings buildings_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.buildings
    ADD CONSTRAINT buildings_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: buildings buildings_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.buildings
    ADD CONSTRAINT buildings_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: cartographers cartographers_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cartographers
    ADD CONSTRAINT cartographers_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: cartographers cartographers_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cartographers
    ADD CONSTRAINT cartographers_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: certificate_logical_server certificate_logical_server_certificate_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.certificate_logical_server
    ADD CONSTRAINT certificate_logical_server_certificate_id_foreign FOREIGN KEY (certificate_id) REFERENCES public.certificates(id) ON DELETE CASCADE;


--
-- Name: certificate_logical_server certificate_logical_server_logical_server_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.certificate_logical_server
    ADD CONSTRAINT certificate_logical_server_logical_server_id_foreign FOREIGN KEY (logical_server_id) REFERENCES public.logical_servers(id) ON DELETE CASCADE;


--
-- Name: application_certificate certificate_m_application_certificate_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_certificate
    ADD CONSTRAINT certificate_m_application_certificate_id_foreign FOREIGN KEY (certificate_id) REFERENCES public.certificates(id) ON DELETE CASCADE;


--
-- Name: certificates certificates_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: certificates certificates_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: certificates certificates_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: certificates certificates_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: cluster_logical_server cluster_logical_server_cluster_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cluster_logical_server
    ADD CONSTRAINT cluster_logical_server_cluster_id_foreign FOREIGN KEY (cluster_id) REFERENCES public.clusters(id) ON DELETE CASCADE;


--
-- Name: cluster_logical_server cluster_logical_server_logical_server_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cluster_logical_server
    ADD CONSTRAINT cluster_logical_server_logical_server_id_foreign FOREIGN KEY (logical_server_id) REFERENCES public.logical_servers(id) ON DELETE CASCADE;


--
-- Name: cluster_physical_server cluster_physical_server_cluster_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cluster_physical_server
    ADD CONSTRAINT cluster_physical_server_cluster_id_foreign FOREIGN KEY (cluster_id) REFERENCES public.clusters(id) ON DELETE CASCADE;


--
-- Name: cluster_physical_server cluster_physical_server_physical_server_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cluster_physical_server
    ADD CONSTRAINT cluster_physical_server_physical_server_id_foreign FOREIGN KEY (physical_server_id) REFERENCES public.physical_servers(id) ON DELETE CASCADE;


--
-- Name: cluster_router cluster_router_cluster_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cluster_router
    ADD CONSTRAINT cluster_router_cluster_id_foreign FOREIGN KEY (cluster_id) REFERENCES public.clusters(id) ON DELETE CASCADE;


--
-- Name: cluster_router cluster_router_router_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cluster_router
    ADD CONSTRAINT cluster_router_router_id_foreign FOREIGN KEY (router_id) REFERENCES public.routers(id) ON DELETE CASCADE;


--
-- Name: clusters clusters_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clusters
    ADD CONSTRAINT clusters_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: clusters clusters_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clusters
    ADD CONSTRAINT clusters_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: clusters clusters_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clusters
    ADD CONSTRAINT clusters_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: clusters clusters_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clusters
    ADD CONSTRAINT clusters_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: subnetworks connected_subnets_fk_1483256; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subnetworks
    ADD CONSTRAINT connected_subnets_fk_1483256 FOREIGN KEY (connected_subnets_id) REFERENCES public.subnetworks(id);


--
-- Name: container_database container_database_container_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.container_database
    ADD CONSTRAINT container_database_container_id_foreign FOREIGN KEY (container_id) REFERENCES public.containers(id) ON DELETE CASCADE;


--
-- Name: container_database container_database_database_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.container_database
    ADD CONSTRAINT container_database_database_id_foreign FOREIGN KEY (database_id) REFERENCES public.databases(id) ON DELETE CASCADE;


--
-- Name: container_logical_server container_logical_server_container_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.container_logical_server
    ADD CONSTRAINT container_logical_server_container_id_foreign FOREIGN KEY (container_id) REFERENCES public.containers(id) ON DELETE CASCADE;


--
-- Name: container_logical_server container_logical_server_logical_server_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.container_logical_server
    ADD CONSTRAINT container_logical_server_logical_server_id_foreign FOREIGN KEY (logical_server_id) REFERENCES public.logical_servers(id) ON DELETE CASCADE;


--
-- Name: application_container container_m_application_container_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_container
    ADD CONSTRAINT container_m_application_container_id_foreign FOREIGN KEY (container_id) REFERENCES public.containers(id) ON DELETE CASCADE;


--
-- Name: containers containers_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.containers
    ADD CONSTRAINT containers_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: containers containers_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.containers
    ADD CONSTRAINT containers_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: containers containers_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.containers
    ADD CONSTRAINT containers_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: containers containers_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.containers
    ADD CONSTRAINT containers_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: cpe_versions cpe_product_fk_1447431; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cpe_versions
    ADD CONSTRAINT cpe_product_fk_1447431 FOREIGN KEY (cpe_product_id) REFERENCES public.cpe_products(id);


--
-- Name: cpe_products cpe_vendor_fk_1454431; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cpe_products
    ADD CONSTRAINT cpe_vendor_fk_1454431 FOREIGN KEY (cpe_vendor_id) REFERENCES public.cpe_vendors(id);


--
-- Name: data_processing data_processing_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_processing
    ADD CONSTRAINT data_processing_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: data_processing data_processing_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_processing
    ADD CONSTRAINT data_processing_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: data_processing_document data_processing_id_fk_42343234; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_processing_document
    ADD CONSTRAINT data_processing_id_fk_42343234 FOREIGN KEY (data_processing_id) REFERENCES public.data_processing(id) ON DELETE CASCADE;


--
-- Name: data_processing_information data_processing_id_fk_493438483; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_processing_information
    ADD CONSTRAINT data_processing_id_fk_493438483 FOREIGN KEY (data_processing_id) REFERENCES public.data_processing(id) ON DELETE CASCADE;


--
-- Name: application_data_processing data_processing_id_fk_49838437; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_data_processing
    ADD CONSTRAINT data_processing_id_fk_49838437 FOREIGN KEY (data_processing_id) REFERENCES public.data_processing(id) ON DELETE CASCADE;


--
-- Name: data_processing_process data_processing_id_fk_764545345; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_processing_process
    ADD CONSTRAINT data_processing_id_fk_764545345 FOREIGN KEY (data_processing_id) REFERENCES public.data_processing(id) ON DELETE CASCADE;


--
-- Name: data_processing data_processing_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_processing
    ADD CONSTRAINT data_processing_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: data_processing data_processing_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_processing
    ADD CONSTRAINT data_processing_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_flows database_dest_fk_1485552; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT database_dest_fk_1485552 FOREIGN KEY (database_dest_id) REFERENCES public.databases(id);


--
-- Name: application_database database_id_fk_1482586; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_database
    ADD CONSTRAINT database_id_fk_1482586 FOREIGN KEY (database_id) REFERENCES public.databases(id) ON DELETE CASCADE;


--
-- Name: database_entity database_id_fk_1485563; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.database_entity
    ADD CONSTRAINT database_id_fk_1485563 FOREIGN KEY (database_id) REFERENCES public.databases(id) ON DELETE CASCADE;


--
-- Name: database_information database_id_fk_1485570; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.database_information
    ADD CONSTRAINT database_id_fk_1485570 FOREIGN KEY (database_id) REFERENCES public.databases(id) ON DELETE CASCADE;


--
-- Name: application_flows database_source_fk_1485548; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT database_source_fk_1485548 FOREIGN KEY (database_source_id) REFERENCES public.databases(id);


--
-- Name: databases databases_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.databases
    ADD CONSTRAINT databases_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: databases databases_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.databases
    ADD CONSTRAINT databases_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: databases databases_icon_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.databases
    ADD CONSTRAINT databases_icon_id_foreign FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: databases databases_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.databases
    ADD CONSTRAINT databases_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: databases databases_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.databases
    ADD CONSTRAINT databases_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: relations destination_fk_1494373; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.relations
    ADD CONSTRAINT destination_fk_1494373 FOREIGN KEY (destination_id) REFERENCES public.entities(id);


--
-- Name: dhcp_servers dhcp_servers_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dhcp_servers
    ADD CONSTRAINT dhcp_servers_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: dhcp_servers dhcp_servers_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dhcp_servers
    ADD CONSTRAINT dhcp_servers_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: dhcp_servers dhcp_servers_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dhcp_servers
    ADD CONSTRAINT dhcp_servers_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: dhcp_servers dhcp_servers_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dhcp_servers
    ADD CONSTRAINT dhcp_servers_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: dnsservers dnsservers_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dnsservers
    ADD CONSTRAINT dnsservers_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: dnsservers dnsservers_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dnsservers
    ADD CONSTRAINT dnsservers_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: dnsservers dnsservers_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dnsservers
    ADD CONSTRAINT dnsservers_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: dnsservers dnsservers_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dnsservers
    ADD CONSTRAINT dnsservers_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: document_logical_server document_id_fk_1284334; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_logical_server
    ADD CONSTRAINT document_id_fk_1284334 FOREIGN KEY (document_id) REFERENCES public.documents(id) ON DELETE CASCADE;


--
-- Name: workstations document_id_fk_129483; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT document_id_fk_129483 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: peripherals document_id_fk_129484; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.peripherals
    ADD CONSTRAINT document_id_fk_129484 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: sites document_id_fk_129485; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sites
    ADD CONSTRAINT document_id_fk_129485 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: entities document_id_fk_129486; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entities
    ADD CONSTRAINT document_id_fk_129486 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: admin_users document_id_fk_129487; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT document_id_fk_129487 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: data_processing_document document_id_fk_3439483; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_processing_document
    ADD CONSTRAINT document_id_fk_3439483 FOREIGN KEY (document_id) REFERENCES public.documents(id) ON DELETE CASCADE;


--
-- Name: containers document_id_fk_434833774; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.containers
    ADD CONSTRAINT document_id_fk_434833774 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: entity_document document_id_fk_4355430; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entity_document
    ADD CONSTRAINT document_id_fk_4355430 FOREIGN KEY (document_id) REFERENCES public.documents(id) ON DELETE CASCADE;


--
-- Name: applications document_id_fk_4394343; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.applications
    ADD CONSTRAINT document_id_fk_4394343 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: document_external_connected_entity document_id_fk_4394384; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_external_connected_entity
    ADD CONSTRAINT document_id_fk_4394384 FOREIGN KEY (document_id) REFERENCES public.documents(id) ON DELETE CASCADE;


--
-- Name: clusters document_id_fk_495432841; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clusters
    ADD CONSTRAINT document_id_fk_495432841 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: buildings document_id_fk_49574431; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.buildings
    ADD CONSTRAINT document_id_fk_49574431 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: logical_servers document_id_fk_51303394; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_servers
    ADD CONSTRAINT document_id_fk_51303394 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: physical_servers document_id_fk_5328384; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_servers
    ADD CONSTRAINT document_id_fk_5328384 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: storage_devices document_id_fk_53812733; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storage_devices
    ADD CONSTRAINT document_id_fk_53812733 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: document_relation document_id_fk_5492844; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_relation
    ADD CONSTRAINT document_id_fk_5492844 FOREIGN KEY (document_id) REFERENCES public.documents(id) ON DELETE CASCADE;


--
-- Name: processes document_id_fk_5938654; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.processes
    ADD CONSTRAINT document_id_fk_5938654 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: information document_id_fk_61928374; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.information
    ADD CONSTRAINT document_id_fk_61928374 FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: application_document document_id_fk_7812342; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_document
    ADD CONSTRAINT document_id_fk_7812342 FOREIGN KEY (document_id) REFERENCES public.documents(id) ON DELETE CASCADE;


--
-- Name: documents documents_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: documents documents_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: documents documents_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: domain_forest_ad domain_id_fk_1492084; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.domain_forest_ad
    ADD CONSTRAINT domain_id_fk_1492084 FOREIGN KEY (domain_id) REFERENCES public.domains(id) ON DELETE CASCADE;


--
-- Name: logical_servers domain_id_fk_493844; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_servers
    ADD CONSTRAINT domain_id_fk_493844 FOREIGN KEY (domain_id) REFERENCES public.domains(id) ON DELETE SET NULL;


--
-- Name: admin_users domain_id_fk_69385935; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT domain_id_fk_69385935 FOREIGN KEY (domain_id) REFERENCES public.domains(id) ON DELETE CASCADE;


--
-- Name: domains domains_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.domains
    ADD CONSTRAINT domains_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: domains domains_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.domains
    ADD CONSTRAINT domains_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: domains domains_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.domains
    ADD CONSTRAINT domains_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: domains domains_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.domains
    ADD CONSTRAINT domains_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: entities entities_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entities
    ADD CONSTRAINT entities_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: entities entities_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entities
    ADD CONSTRAINT entities_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: entities entities_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entities
    ADD CONSTRAINT entities_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: entities entities_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entities
    ADD CONSTRAINT entities_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: external_connected_entities entity_id_fk_1295034; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.external_connected_entities
    ADD CONSTRAINT entity_id_fk_1295034 FOREIGN KEY (entity_id) REFERENCES public.entities(id) ON DELETE CASCADE;


--
-- Name: database_entity entity_id_fk_1485563; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.database_entity
    ADD CONSTRAINT entity_id_fk_1485563 FOREIGN KEY (entity_id) REFERENCES public.entities(id) ON DELETE CASCADE;


--
-- Name: application_entity entity_id_fk_1488611; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_entity
    ADD CONSTRAINT entity_id_fk_1488611 FOREIGN KEY (entity_id) REFERENCES public.entities(id) ON DELETE CASCADE;


--
-- Name: entity_process entity_id_fk_1627958; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entity_process
    ADD CONSTRAINT entity_id_fk_1627958 FOREIGN KEY (entity_id) REFERENCES public.entities(id) ON DELETE CASCADE;


--
-- Name: entity_document entity_id_fk_4325432; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entity_document
    ADD CONSTRAINT entity_id_fk_4325432 FOREIGN KEY (entity_id) REFERENCES public.entities(id) ON DELETE CASCADE;


--
-- Name: peripherals entity_id_fk_4383234; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.peripherals
    ADD CONSTRAINT entity_id_fk_4383234 FOREIGN KEY (provider_id) REFERENCES public.entities(id);


--
-- Name: entities entity_id_fk_4398013; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entities
    ADD CONSTRAINT entity_id_fk_4398013 FOREIGN KEY (parent_entity_id) REFERENCES public.entities(id) ON DELETE SET NULL;


--
-- Name: databases entity_resp_fk_1485569; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.databases
    ADD CONSTRAINT entity_resp_fk_1485569 FOREIGN KEY (entity_resp_id) REFERENCES public.entities(id);


--
-- Name: applications entity_resp_fk_1488612; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.applications
    ADD CONSTRAINT entity_resp_fk_1488612 FOREIGN KEY (entity_resp_id) REFERENCES public.entities(id);


--
-- Name: external_connected_entities external_connected_entities_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.external_connected_entities
    ADD CONSTRAINT external_connected_entities_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: external_connected_entities external_connected_entities_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.external_connected_entities
    ADD CONSTRAINT external_connected_entities_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: external_connected_entities external_connected_entities_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.external_connected_entities
    ADD CONSTRAINT external_connected_entities_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: external_connected_entities external_connected_entities_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.external_connected_entities
    ADD CONSTRAINT external_connected_entities_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: external_connected_entity_subnetwork external_connected_entity_id_fk_4302049; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.external_connected_entity_subnetwork
    ADD CONSTRAINT external_connected_entity_id_fk_4302049 FOREIGN KEY (external_connected_entity_id) REFERENCES public.external_connected_entities(id) ON DELETE CASCADE;


--
-- Name: document_external_connected_entity external_connected_entity_id_fk_434854; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_external_connected_entity
    ADD CONSTRAINT external_connected_entity_id_fk_434854 FOREIGN KEY (external_connected_entity_id) REFERENCES public.external_connected_entities(id) ON DELETE CASCADE;


--
-- Name: domain_forest_ad forest_ad_id_fk_1492084; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.domain_forest_ad
    ADD CONSTRAINT forest_ad_id_fk_1492084 FOREIGN KEY (forest_ad_id) REFERENCES public.forest_ads(id) ON DELETE CASCADE;


--
-- Name: forest_ads forest_ads_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.forest_ads
    ADD CONSTRAINT forest_ads_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: forest_ads forest_ads_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.forest_ads
    ADD CONSTRAINT forest_ads_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: forest_ads forest_ads_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.forest_ads
    ADD CONSTRAINT forest_ads_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: forest_ads forest_ads_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.forest_ads
    ADD CONSTRAINT forest_ads_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: subnetworks gateway_fk_1492376; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subnetworks
    ADD CONSTRAINT gateway_fk_1492376 FOREIGN KEY (gateway_id) REFERENCES public.gateways(id);


--
-- Name: gateways gateways_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gateways
    ADD CONSTRAINT gateways_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: gateways gateways_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gateways
    ADD CONSTRAINT gateways_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: gateways gateways_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gateways
    ADD CONSTRAINT gateways_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: gateways gateways_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gateways
    ADD CONSTRAINT gateways_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: graphs graphs_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.graphs
    ADD CONSTRAINT graphs_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: graphs graphs_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.graphs
    ADD CONSTRAINT graphs_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: graphs graphs_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.graphs
    ADD CONSTRAINT graphs_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: information information_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.information
    ADD CONSTRAINT information_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: information information_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.information
    ADD CONSTRAINT information_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: data_processing_information information_id_fk_0483434; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_processing_information
    ADD CONSTRAINT information_id_fk_0483434 FOREIGN KEY (information_id) REFERENCES public.information(id) ON DELETE CASCADE;


--
-- Name: information_process information_id_fk_1473025; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.information_process
    ADD CONSTRAINT information_id_fk_1473025 FOREIGN KEY (information_id) REFERENCES public.information(id) ON DELETE CASCADE;


--
-- Name: database_information information_id_fk_1485570; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.database_information
    ADD CONSTRAINT information_id_fk_1485570 FOREIGN KEY (information_id) REFERENCES public.information(id) ON DELETE CASCADE;


--
-- Name: information_information information_information_child_information_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.information_information
    ADD CONSTRAINT information_information_child_information_id_foreign FOREIGN KEY (child_information_id) REFERENCES public.information(id) ON DELETE CASCADE;


--
-- Name: information_information information_information_information_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.information_information
    ADD CONSTRAINT information_information_information_id_foreign FOREIGN KEY (information_id) REFERENCES public.information(id) ON DELETE CASCADE;


--
-- Name: information information_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.information
    ADD CONSTRAINT information_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: information information_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.information
    ADD CONSTRAINT information_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: lan_man lan_id_fk_1490345; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lan_man
    ADD CONSTRAINT lan_id_fk_1490345 FOREIGN KEY (lan_id) REFERENCES public.lans(id) ON DELETE CASCADE;


--
-- Name: lan_wan lan_id_fk_1490368; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lan_wan
    ADD CONSTRAINT lan_id_fk_1490368 FOREIGN KEY (lan_id) REFERENCES public.lans(id) ON DELETE CASCADE;


--
-- Name: lans lans_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lans
    ADD CONSTRAINT lans_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: lans lans_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lans
    ADD CONSTRAINT lans_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: lans lans_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lans
    ADD CONSTRAINT lans_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: lans lans_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lans
    ADD CONSTRAINT lans_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: logical_flows logical_flows_cluster_dest_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_cluster_dest_id_foreign FOREIGN KEY (cluster_dest_id) REFERENCES public.clusters(id) ON DELETE SET NULL;


--
-- Name: logical_flows logical_flows_cluster_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_cluster_source_id_foreign FOREIGN KEY (cluster_source_id) REFERENCES public.clusters(id) ON DELETE SET NULL;


--
-- Name: logical_flows logical_flows_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: logical_flows logical_flows_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: logical_flows logical_flows_logical_server_dest_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_logical_server_dest_id_foreign FOREIGN KEY (logical_server_dest_id) REFERENCES public.logical_servers(id) ON DELETE CASCADE;


--
-- Name: logical_flows logical_flows_logical_server_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_logical_server_source_id_foreign FOREIGN KEY (logical_server_source_id) REFERENCES public.logical_servers(id) ON DELETE CASCADE;


--
-- Name: logical_flows logical_flows_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: logical_flows logical_flows_peripheral_dest_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_peripheral_dest_id_foreign FOREIGN KEY (peripheral_dest_id) REFERENCES public.peripherals(id) ON DELETE CASCADE;


--
-- Name: logical_flows logical_flows_peripheral_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_peripheral_source_id_foreign FOREIGN KEY (peripheral_source_id) REFERENCES public.peripherals(id) ON DELETE CASCADE;


--
-- Name: logical_flows logical_flows_physical_security_device_dest_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_physical_security_device_dest_id_foreign FOREIGN KEY (physical_security_device_dest_id) REFERENCES public.physical_security_devices(id) ON DELETE CASCADE;


--
-- Name: logical_flows logical_flows_physical_security_device_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_physical_security_device_source_id_foreign FOREIGN KEY (physical_security_device_source_id) REFERENCES public.physical_security_devices(id) ON DELETE CASCADE;


--
-- Name: logical_flows logical_flows_physical_server_dest_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_physical_server_dest_id_foreign FOREIGN KEY (physical_server_dest_id) REFERENCES public.physical_servers(id) ON DELETE CASCADE;


--
-- Name: logical_flows logical_flows_physical_server_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_physical_server_source_id_foreign FOREIGN KEY (physical_server_source_id) REFERENCES public.physical_servers(id) ON DELETE CASCADE;


--
-- Name: logical_flows logical_flows_security_device_dest_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_security_device_dest_id_foreign FOREIGN KEY (security_device_dest_id) REFERENCES public.security_devices(id) ON DELETE SET NULL;


--
-- Name: logical_flows logical_flows_security_device_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_security_device_source_id_foreign FOREIGN KEY (security_device_source_id) REFERENCES public.security_devices(id) ON DELETE SET NULL;


--
-- Name: logical_flows logical_flows_storage_device_dest_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_storage_device_dest_id_foreign FOREIGN KEY (storage_device_dest_id) REFERENCES public.storage_devices(id) ON DELETE CASCADE;


--
-- Name: logical_flows logical_flows_storage_device_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_storage_device_source_id_foreign FOREIGN KEY (storage_device_source_id) REFERENCES public.storage_devices(id) ON DELETE CASCADE;


--
-- Name: logical_flows logical_flows_subnetwork_dest_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_subnetwork_dest_id_foreign FOREIGN KEY (subnetwork_dest_id) REFERENCES public.subnetworks(id) ON DELETE SET NULL;


--
-- Name: logical_flows logical_flows_subnetwork_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_subnetwork_source_id_foreign FOREIGN KEY (subnetwork_source_id) REFERENCES public.subnetworks(id) ON DELETE SET NULL;


--
-- Name: logical_flows logical_flows_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: logical_flows logical_flows_workstation_dest_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_workstation_dest_id_foreign FOREIGN KEY (workstation_dest_id) REFERENCES public.workstations(id) ON DELETE CASCADE;


--
-- Name: logical_flows logical_flows_workstation_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT logical_flows_workstation_source_id_foreign FOREIGN KEY (workstation_source_id) REFERENCES public.workstations(id) ON DELETE CASCADE;


--
-- Name: physical_links logical_server_dest_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT logical_server_dest_id_fk FOREIGN KEY (logical_server_dest_id) REFERENCES public.logical_servers(id) ON DELETE CASCADE;


--
-- Name: application_logical_server logical_server_id_fk_1488616; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_logical_server
    ADD CONSTRAINT logical_server_id_fk_1488616 FOREIGN KEY (logical_server_id) REFERENCES public.logical_servers(id) ON DELETE CASCADE;


--
-- Name: logical_server_physical_server logical_server_id_fk_1657961; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_server_physical_server
    ADD CONSTRAINT logical_server_id_fk_1657961 FOREIGN KEY (logical_server_id) REFERENCES public.logical_servers(id) ON DELETE CASCADE;


--
-- Name: document_logical_server logical_server_id_fk_43832473; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_logical_server
    ADD CONSTRAINT logical_server_id_fk_43832473 FOREIGN KEY (logical_server_id) REFERENCES public.logical_servers(id) ON DELETE CASCADE;


--
-- Name: physical_links logical_server_src_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT logical_server_src_id_fk FOREIGN KEY (logical_server_src_id) REFERENCES public.logical_servers(id) ON DELETE CASCADE;


--
-- Name: logical_servers logical_servers_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_servers
    ADD CONSTRAINT logical_servers_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: logical_servers logical_servers_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_servers
    ADD CONSTRAINT logical_servers_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: logical_servers logical_servers_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_servers
    ADD CONSTRAINT logical_servers_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: logical_servers logical_servers_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_servers
    ADD CONSTRAINT logical_servers_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_events m_application_events_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_events
    ADD CONSTRAINT m_application_events_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: macro_processuses macro_processuses_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.macro_processuses
    ADD CONSTRAINT macro_processuses_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: macro_processuses macro_processuses_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.macro_processuses
    ADD CONSTRAINT macro_processuses_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: macro_processuses macro_processuses_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.macro_processuses
    ADD CONSTRAINT macro_processuses_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: macro_processuses macro_processuses_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.macro_processuses
    ADD CONSTRAINT macro_processuses_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: lan_man man_id_fk_1490345; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lan_man
    ADD CONSTRAINT man_id_fk_1490345 FOREIGN KEY (man_id) REFERENCES public.mans(id) ON DELETE CASCADE;


--
-- Name: man_wan man_id_fk_1490367; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.man_wan
    ADD CONSTRAINT man_id_fk_1490367 FOREIGN KEY (man_id) REFERENCES public.mans(id) ON DELETE CASCADE;


--
-- Name: mans man_id_fk_4385454; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mans
    ADD CONSTRAINT man_id_fk_4385454 FOREIGN KEY (parent_man_id) REFERENCES public.mans(id) ON DELETE SET NULL;


--
-- Name: mans mans_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mans
    ADD CONSTRAINT mans_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: mans mans_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mans
    ADD CONSTRAINT mans_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: mans mans_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mans
    ADD CONSTRAINT mans_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: mans mans_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mans
    ADD CONSTRAINT mans_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_flows module_dest_fk_1485551; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT module_dest_fk_1485551 FOREIGN KEY (module_dest_id) REFERENCES public.application_modules(id);


--
-- Name: application_flows module_source_fk_1485547; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT module_source_fk_1485547 FOREIGN KEY (module_source_id) REFERENCES public.application_modules(id);


--
-- Name: subnetworks network_fk_5476544; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subnetworks
    ADD CONSTRAINT network_fk_5476544 FOREIGN KEY (network_id) REFERENCES public.networks(id);


--
-- Name: external_connected_entities network_id_fk_8596554; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.external_connected_entities
    ADD CONSTRAINT network_id_fk_8596554 FOREIGN KEY (network_id) REFERENCES public.networks(id) ON DELETE CASCADE;


--
-- Name: physical_links network_switch_dest_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT network_switch_dest_id_fk FOREIGN KEY (network_switch_dest_id) REFERENCES public.network_switches(id) ON DELETE CASCADE;


--
-- Name: network_switch_physical_switch network_switch_id_fk_543323; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.network_switch_physical_switch
    ADD CONSTRAINT network_switch_id_fk_543323 FOREIGN KEY (network_switch_id) REFERENCES public.network_switches(id) ON DELETE CASCADE;


--
-- Name: physical_links network_switch_src_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT network_switch_src_id_fk FOREIGN KEY (network_switch_src_id) REFERENCES public.network_switches(id) ON DELETE CASCADE;


--
-- Name: network_switch_vlan network_switch_vlan_network_switch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.network_switch_vlan
    ADD CONSTRAINT network_switch_vlan_network_switch_id_foreign FOREIGN KEY (network_switch_id) REFERENCES public.network_switches(id) ON DELETE CASCADE;


--
-- Name: network_switch_vlan network_switch_vlan_vlan_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.network_switch_vlan
    ADD CONSTRAINT network_switch_vlan_vlan_id_foreign FOREIGN KEY (vlan_id) REFERENCES public.vlans(id) ON DELETE CASCADE;


--
-- Name: network_switches network_switches_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.network_switches
    ADD CONSTRAINT network_switches_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: network_switches network_switches_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.network_switches
    ADD CONSTRAINT network_switches_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: network_switches network_switches_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.network_switches
    ADD CONSTRAINT network_switches_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: network_switches network_switches_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.network_switches
    ADD CONSTRAINT network_switches_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: networks networks_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.networks
    ADD CONSTRAINT networks_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: networks networks_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.networks
    ADD CONSTRAINT networks_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: networks networks_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.networks
    ADD CONSTRAINT networks_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: networks networks_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.networks
    ADD CONSTRAINT networks_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: actor_operation operation_id_fk_1472680; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actor_operation
    ADD CONSTRAINT operation_id_fk_1472680 FOREIGN KEY (operation_id) REFERENCES public.operations(id) ON DELETE CASCADE;


--
-- Name: activity_operation operation_id_fk_1472704; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_operation
    ADD CONSTRAINT operation_id_fk_1472704 FOREIGN KEY (operation_id) REFERENCES public.operations(id) ON DELETE CASCADE;


--
-- Name: operation_task operation_id_fk_1472749; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.operation_task
    ADD CONSTRAINT operation_id_fk_1472749 FOREIGN KEY (operation_id) REFERENCES public.operations(id) ON DELETE CASCADE;


--
-- Name: activity_document operation_id_fk_1472794; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_document
    ADD CONSTRAINT operation_id_fk_1472794 FOREIGN KEY (document_id) REFERENCES public.documents(id) ON DELETE CASCADE;


--
-- Name: operations operations_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.operations
    ADD CONSTRAINT operations_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: operations operations_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.operations
    ADD CONSTRAINT operations_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: operations operations_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.operations
    ADD CONSTRAINT operations_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: operations operations_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.operations
    ADD CONSTRAINT operations_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_links peripheral_dest_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT peripheral_dest_id_fk FOREIGN KEY (peripheral_dest_id) REFERENCES public.peripherals(id) ON DELETE CASCADE;


--
-- Name: application_peripheral peripheral_id_fk_6454564; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_peripheral
    ADD CONSTRAINT peripheral_id_fk_6454564 FOREIGN KEY (peripheral_id) REFERENCES public.peripherals(id) ON DELETE CASCADE;


--
-- Name: physical_links peripheral_src_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT peripheral_src_id_fk FOREIGN KEY (peripheral_src_id) REFERENCES public.peripherals(id) ON DELETE CASCADE;


--
-- Name: peripherals peripherals_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.peripherals
    ADD CONSTRAINT peripherals_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: peripherals peripherals_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.peripherals
    ADD CONSTRAINT peripherals_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: peripherals peripherals_domain_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.peripherals
    ADD CONSTRAINT peripherals_domain_id_foreign FOREIGN KEY (domain_id) REFERENCES public.domains(id);


--
-- Name: peripherals peripherals_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.peripherals
    ADD CONSTRAINT peripherals_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: peripherals peripherals_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.peripherals
    ADD CONSTRAINT peripherals_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: permission_role permission_id_fk_1470794; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permission_role
    ADD CONSTRAINT permission_id_fk_1470794 FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: physical_links phone_dest_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT phone_dest_id_fk FOREIGN KEY (phone_dest_id) REFERENCES public.phones(id) ON DELETE CASCADE;


--
-- Name: physical_links phone_src_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT phone_src_id_fk FOREIGN KEY (phone_src_id) REFERENCES public.phones(id) ON DELETE CASCADE;


--
-- Name: phones phones_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.phones
    ADD CONSTRAINT phones_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: phones phones_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.phones
    ADD CONSTRAINT phones_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: phones phones_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.phones
    ADD CONSTRAINT phones_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: phones phones_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.phones
    ADD CONSTRAINT phones_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_links physical_links_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_links_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_links physical_links_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_links_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_links physical_links_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_links_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: physical_links physical_links_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_links_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_links physical_router_dest_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_router_dest_id_fk FOREIGN KEY (physical_router_dest_id) REFERENCES public.physical_routers(id) ON DELETE CASCADE;


--
-- Name: physical_router_router physical_router_id_fk_124983; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_router_router
    ADD CONSTRAINT physical_router_id_fk_124983 FOREIGN KEY (physical_router_id) REFERENCES public.physical_routers(id) ON DELETE CASCADE;


--
-- Name: physical_router_vlan physical_router_id_fk_1658250; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_router_vlan
    ADD CONSTRAINT physical_router_id_fk_1658250 FOREIGN KEY (physical_router_id) REFERENCES public.physical_routers(id) ON DELETE CASCADE;


--
-- Name: physical_links physical_router_src_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_router_src_id_fk FOREIGN KEY (physical_router_src_id) REFERENCES public.physical_routers(id) ON DELETE CASCADE;


--
-- Name: physical_routers physical_routers_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_routers
    ADD CONSTRAINT physical_routers_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_routers physical_routers_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_routers
    ADD CONSTRAINT physical_routers_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_routers physical_routers_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_routers
    ADD CONSTRAINT physical_routers_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: physical_routers physical_routers_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_routers
    ADD CONSTRAINT physical_routers_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_links physical_security_device_dest_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_security_device_dest_id_fk FOREIGN KEY (physical_security_device_dest_id) REFERENCES public.physical_security_devices(id) ON DELETE CASCADE;


--
-- Name: physical_security_device_security_device physical_security_device_id_fk_6549543; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_security_device_security_device
    ADD CONSTRAINT physical_security_device_id_fk_6549543 FOREIGN KEY (physical_security_device_id) REFERENCES public.physical_security_devices(id) ON DELETE CASCADE;


--
-- Name: physical_links physical_security_device_src_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_security_device_src_id_fk FOREIGN KEY (physical_security_device_src_id) REFERENCES public.physical_security_devices(id) ON DELETE CASCADE;


--
-- Name: physical_security_devices physical_security_devices_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_security_devices
    ADD CONSTRAINT physical_security_devices_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_security_devices physical_security_devices_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_security_devices
    ADD CONSTRAINT physical_security_devices_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_security_devices physical_security_devices_icon_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_security_devices
    ADD CONSTRAINT physical_security_devices_icon_id_foreign FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: physical_security_devices physical_security_devices_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_security_devices
    ADD CONSTRAINT physical_security_devices_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: physical_security_devices physical_security_devices_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_security_devices
    ADD CONSTRAINT physical_security_devices_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_links physical_server_dest_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_server_dest_id_fk FOREIGN KEY (physical_server_dest_id) REFERENCES public.physical_servers(id) ON DELETE CASCADE;


--
-- Name: logical_server_physical_server physical_server_id_fk_1657961; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_server_physical_server
    ADD CONSTRAINT physical_server_id_fk_1657961 FOREIGN KEY (physical_server_id) REFERENCES public.physical_servers(id) ON DELETE CASCADE;


--
-- Name: application_physical_server physical_server_id_fk_4543543; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_physical_server
    ADD CONSTRAINT physical_server_id_fk_4543543 FOREIGN KEY (physical_server_id) REFERENCES public.physical_servers(id) ON DELETE CASCADE;


--
-- Name: physical_links physical_server_src_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_server_src_id_fk FOREIGN KEY (physical_server_src_id) REFERENCES public.physical_servers(id) ON DELETE CASCADE;


--
-- Name: physical_servers physical_servers_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_servers
    ADD CONSTRAINT physical_servers_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_servers physical_servers_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_servers
    ADD CONSTRAINT physical_servers_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_servers physical_servers_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_servers
    ADD CONSTRAINT physical_servers_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: physical_servers physical_servers_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_servers
    ADD CONSTRAINT physical_servers_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_links physical_switch_dest_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_switch_dest_id_fk FOREIGN KEY (physical_switch_dest_id) REFERENCES public.physical_switches(id) ON DELETE CASCADE;


--
-- Name: workstations physical_switch_fk_0938434; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT physical_switch_fk_0938434 FOREIGN KEY (physical_switch_id) REFERENCES public.physical_switches(id);


--
-- Name: phones physical_switch_fk_5738332; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.phones
    ADD CONSTRAINT physical_switch_fk_5738332 FOREIGN KEY (physical_switch_id) REFERENCES public.physical_switches(id);


--
-- Name: physical_servers physical_switch_fk_8732342; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_servers
    ADD CONSTRAINT physical_switch_fk_8732342 FOREIGN KEY (physical_switch_id) REFERENCES public.physical_switches(id);


--
-- Name: network_switch_physical_switch physical_switch_id_fk_4543143; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.network_switch_physical_switch
    ADD CONSTRAINT physical_switch_id_fk_4543143 FOREIGN KEY (physical_switch_id) REFERENCES public.physical_switches(id) ON DELETE CASCADE;


--
-- Name: physical_links physical_switch_src_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT physical_switch_src_id_fk FOREIGN KEY (physical_switch_src_id) REFERENCES public.physical_switches(id) ON DELETE CASCADE;


--
-- Name: physical_switches physical_switches_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_switches
    ADD CONSTRAINT physical_switches_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_switches physical_switches_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_switches
    ADD CONSTRAINT physical_switches_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_switches physical_switches_icon_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_switches
    ADD CONSTRAINT physical_switches_icon_id_foreign FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: physical_switches physical_switches_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_switches
    ADD CONSTRAINT physical_switches_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: physical_switches physical_switches_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_switches
    ADD CONSTRAINT physical_switches_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: data_processing_process process_id_fk_0483434; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_processing_process
    ADD CONSTRAINT process_id_fk_0483434 FOREIGN KEY (process_id) REFERENCES public.processes(id) ON DELETE CASCADE;


--
-- Name: information_process process_id_fk_1473025; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.information_process
    ADD CONSTRAINT process_id_fk_1473025 FOREIGN KEY (process_id) REFERENCES public.processes(id) ON DELETE CASCADE;


--
-- Name: application_process process_id_fk_1482573; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_process
    ADD CONSTRAINT process_id_fk_1482573 FOREIGN KEY (process_id) REFERENCES public.processes(id) ON DELETE CASCADE;


--
-- Name: activity_process process_id_fk_1627616; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_process
    ADD CONSTRAINT process_id_fk_1627616 FOREIGN KEY (process_id) REFERENCES public.processes(id) ON DELETE CASCADE;


--
-- Name: entity_process process_id_fk_1627958; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entity_process
    ADD CONSTRAINT process_id_fk_1627958 FOREIGN KEY (process_id) REFERENCES public.processes(id) ON DELETE CASCADE;


--
-- Name: security_control_process process_id_fk_49485754; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_control_process
    ADD CONSTRAINT process_id_fk_49485754 FOREIGN KEY (process_id) REFERENCES public.processes(id) ON DELETE CASCADE;


--
-- Name: operations process_id_fk_7945129; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.operations
    ADD CONSTRAINT process_id_fk_7945129 FOREIGN KEY (process_id) REFERENCES public.processes(id) ON DELETE CASCADE;


--
-- Name: processes processes_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.processes
    ADD CONSTRAINT processes_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: processes processes_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.processes
    ADD CONSTRAINT processes_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: processes processes_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.processes
    ADD CONSTRAINT processes_ibfk_1 FOREIGN KEY (macroprocess_id) REFERENCES public.macro_processuses(id);


--
-- Name: processes processes_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.processes
    ADD CONSTRAINT processes_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: processes processes_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.processes
    ADD CONSTRAINT processes_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: relation_values relation_id_fk_43243244; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.relation_values
    ADD CONSTRAINT relation_id_fk_43243244 FOREIGN KEY (relation_id) REFERENCES public.relations(id) ON DELETE CASCADE;


--
-- Name: document_relation relation_id_fk_6948334; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_relation
    ADD CONSTRAINT relation_id_fk_6948334 FOREIGN KEY (relation_id) REFERENCES public.relations(id) ON DELETE CASCADE;


--
-- Name: relations relations_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.relations
    ADD CONSTRAINT relations_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: relations relations_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.relations
    ADD CONSTRAINT relations_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: relations relations_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.relations
    ADD CONSTRAINT relations_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: relations relations_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.relations
    ADD CONSTRAINT relations_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: permission_role role_id_fk_1470794; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permission_role
    ADD CONSTRAINT role_id_fk_1470794 FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: role_user role_id_fk_1470803; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_user
    ADD CONSTRAINT role_id_fk_1470803 FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: roles roles_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: physical_links router_dest_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT router_dest_id_fk FOREIGN KEY (router_dest_id) REFERENCES public.routers(id) ON DELETE CASCADE;


--
-- Name: logical_flows router_id_fk_4382393; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logical_flows
    ADD CONSTRAINT router_id_fk_4382393 FOREIGN KEY (router_id) REFERENCES public.routers(id) ON DELETE CASCADE;


--
-- Name: physical_router_router router_id_fk_958343; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_router_router
    ADD CONSTRAINT router_id_fk_958343 FOREIGN KEY (router_id) REFERENCES public.routers(id) ON DELETE CASCADE;


--
-- Name: physical_links router_src_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT router_src_id_fk FOREIGN KEY (router_src_id) REFERENCES public.routers(id) ON DELETE CASCADE;


--
-- Name: routers routers_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routers
    ADD CONSTRAINT routers_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: routers routers_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routers
    ADD CONSTRAINT routers_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: routers routers_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routers
    ADD CONSTRAINT routers_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: routers routers_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routers
    ADD CONSTRAINT routers_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: saved_queries saved_queries_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saved_queries
    ADD CONSTRAINT saved_queries_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_security_control security_control_id_fk_49294573; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_security_control
    ADD CONSTRAINT security_control_id_fk_49294573 FOREIGN KEY (security_control_id) REFERENCES public.security_controls(id) ON DELETE CASCADE;


--
-- Name: security_control_process security_control_id_fk_54354354; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_control_process
    ADD CONSTRAINT security_control_id_fk_54354354 FOREIGN KEY (security_control_id) REFERENCES public.security_controls(id) ON DELETE CASCADE;


--
-- Name: security_controls security_controls_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_controls
    ADD CONSTRAINT security_controls_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: security_controls security_controls_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_controls
    ADD CONSTRAINT security_controls_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: security_controls security_controls_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_controls
    ADD CONSTRAINT security_controls_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: security_controls security_controls_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_controls
    ADD CONSTRAINT security_controls_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_security_device security_device_id_fk_304832731; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_security_device
    ADD CONSTRAINT security_device_id_fk_304832731 FOREIGN KEY (security_device_id) REFERENCES public.security_devices(id) ON DELETE CASCADE;


--
-- Name: physical_security_device_security_device security_device_id_fk_43329392; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_security_device_security_device
    ADD CONSTRAINT security_device_id_fk_43329392 FOREIGN KEY (security_device_id) REFERENCES public.security_devices(id) ON DELETE CASCADE;


--
-- Name: security_devices security_devices_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_devices
    ADD CONSTRAINT security_devices_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: security_devices security_devices_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_devices
    ADD CONSTRAINT security_devices_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: security_devices security_devices_icon_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_devices
    ADD CONSTRAINT security_devices_icon_id_foreign FOREIGN KEY (icon_id) REFERENCES public.documents(id);


--
-- Name: security_devices security_devices_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_devices
    ADD CONSTRAINT security_devices_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: security_devices security_devices_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_devices
    ADD CONSTRAINT security_devices_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_flows service_dest_fk_1485550; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT service_dest_fk_1485550 FOREIGN KEY (service_dest_id) REFERENCES public.application_services(id);


--
-- Name: application_flows service_source_fk_1485546; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_flows
    ADD CONSTRAINT service_source_fk_1485546 FOREIGN KEY (service_source_id) REFERENCES public.application_services(id);


--
-- Name: buildings site_fk_1483431; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.buildings
    ADD CONSTRAINT site_fk_1483431 FOREIGN KEY (site_id) REFERENCES public.sites(id);


--
-- Name: physical_servers site_fk_1485322; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_servers
    ADD CONSTRAINT site_fk_1485322 FOREIGN KEY (site_id) REFERENCES public.sites(id);


--
-- Name: workstations site_fk_1485332; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT site_fk_1485332 FOREIGN KEY (site_id) REFERENCES public.sites(id);


--
-- Name: storage_devices site_fk_1485361; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storage_devices
    ADD CONSTRAINT site_fk_1485361 FOREIGN KEY (site_id) REFERENCES public.sites(id);


--
-- Name: peripherals site_fk_1485449; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.peripherals
    ADD CONSTRAINT site_fk_1485449 FOREIGN KEY (site_id) REFERENCES public.sites(id);


--
-- Name: phones site_fk_1485479; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.phones
    ADD CONSTRAINT site_fk_1485479 FOREIGN KEY (site_id) REFERENCES public.sites(id);


--
-- Name: physical_switches site_fk_1485488; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_switches
    ADD CONSTRAINT site_fk_1485488 FOREIGN KEY (site_id) REFERENCES public.sites(id);


--
-- Name: physical_routers site_fk_1485497; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_routers
    ADD CONSTRAINT site_fk_1485497 FOREIGN KEY (site_id) REFERENCES public.sites(id);


--
-- Name: wifi_terminals site_fk_1485507; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wifi_terminals
    ADD CONSTRAINT site_fk_1485507 FOREIGN KEY (site_id) REFERENCES public.sites(id);


--
-- Name: physical_security_devices site_fk_1485517; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_security_devices
    ADD CONSTRAINT site_fk_1485517 FOREIGN KEY (site_id) REFERENCES public.sites(id);


--
-- Name: bays site_id_fk_1483442; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bays
    ADD CONSTRAINT site_id_fk_1483442 FOREIGN KEY (site_id) REFERENCES public.sites(id);


--
-- Name: sites sites_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sites
    ADD CONSTRAINT sites_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: sites sites_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sites
    ADD CONSTRAINT sites_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: sites sites_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sites
    ADD CONSTRAINT sites_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: sites sites_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sites
    ADD CONSTRAINT sites_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: relations source_fk_1494372; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.relations
    ADD CONSTRAINT source_fk_1494372 FOREIGN KEY (source_id) REFERENCES public.entities(id);


--
-- Name: physical_links storage_device_dest_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT storage_device_dest_id_fk FOREIGN KEY (storage_device_dest_id) REFERENCES public.storage_devices(id) ON DELETE CASCADE;


--
-- Name: physical_links storage_device_src_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT storage_device_src_id_fk FOREIGN KEY (storage_device_src_id) REFERENCES public.storage_devices(id) ON DELETE CASCADE;


--
-- Name: storage_devices storage_devices_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storage_devices
    ADD CONSTRAINT storage_devices_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: storage_devices storage_devices_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storage_devices
    ADD CONSTRAINT storage_devices_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: storage_devices storage_devices_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storage_devices
    ADD CONSTRAINT storage_devices_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: storage_devices storage_devices_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storage_devices
    ADD CONSTRAINT storage_devices_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: external_connected_entity_subnetwork subnetwork_id_fk_09848239; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.external_connected_entity_subnetwork
    ADD CONSTRAINT subnetwork_id_fk_09848239 FOREIGN KEY (subnetwork_id) REFERENCES public.subnetworks(id) ON DELETE CASCADE;


--
-- Name: subnetworks subnetworks_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subnetworks
    ADD CONSTRAINT subnetworks_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: subnetworks subnetworks_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subnetworks
    ADD CONSTRAINT subnetworks_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: subnetworks subnetworks_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subnetworks
    ADD CONSTRAINT subnetworks_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: subnetworks subnetworks_subnetwork_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subnetworks
    ADD CONSTRAINT subnetworks_subnetwork_id_foreign FOREIGN KEY (subnetwork_id) REFERENCES public.subnetworks(id) ON DELETE SET NULL;


--
-- Name: subnetworks subnetworks_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subnetworks
    ADD CONSTRAINT subnetworks_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: operation_task task_id_fk_1472749; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.operation_task
    ADD CONSTRAINT task_id_fk_1472749 FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- Name: tasks tasks_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: tasks tasks_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: tasks tasks_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: tasks tasks_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: role_user user_id_fk_1470803; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_user
    ADD CONSTRAINT user_id_fk_1470803 FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: subnetworks vlan_fk_6844934; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subnetworks
    ADD CONSTRAINT vlan_fk_6844934 FOREIGN KEY (vlan_id) REFERENCES public.vlans(id);


--
-- Name: physical_router_vlan vlan_id_fk_1658250; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_router_vlan
    ADD CONSTRAINT vlan_id_fk_1658250 FOREIGN KEY (vlan_id) REFERENCES public.vlans(id) ON DELETE CASCADE;


--
-- Name: vlans vlans_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vlans
    ADD CONSTRAINT vlans_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: vlans vlans_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vlans
    ADD CONSTRAINT vlans_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: vlans vlans_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vlans
    ADD CONSTRAINT vlans_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: vlans vlans_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vlans
    ADD CONSTRAINT vlans_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: man_wan wan_id_fk_1490367; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.man_wan
    ADD CONSTRAINT wan_id_fk_1490367 FOREIGN KEY (wan_id) REFERENCES public.wans(id) ON DELETE CASCADE;


--
-- Name: lan_wan wan_id_fk_1490368; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lan_wan
    ADD CONSTRAINT wan_id_fk_1490368 FOREIGN KEY (wan_id) REFERENCES public.wans(id) ON DELETE CASCADE;


--
-- Name: wans wans_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wans
    ADD CONSTRAINT wans_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: wans wans_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wans
    ADD CONSTRAINT wans_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: wans wans_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wans
    ADD CONSTRAINT wans_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: wans wans_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wans
    ADD CONSTRAINT wans_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_links wifi_terminal_dest_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT wifi_terminal_dest_id_fk FOREIGN KEY (wifi_terminal_dest_id) REFERENCES public.wifi_terminals(id) ON DELETE CASCADE;


--
-- Name: bay_wifi_terminal wifi_terminal_id_fk_1485509; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bay_wifi_terminal
    ADD CONSTRAINT wifi_terminal_id_fk_1485509 FOREIGN KEY (wifi_terminal_id) REFERENCES public.wifi_terminals(id) ON DELETE CASCADE;


--
-- Name: physical_links wifi_terminal_src_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT wifi_terminal_src_id_fk FOREIGN KEY (wifi_terminal_src_id) REFERENCES public.wifi_terminals(id) ON DELETE CASCADE;


--
-- Name: wifi_terminals wifi_terminals_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wifi_terminals
    ADD CONSTRAINT wifi_terminals_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: wifi_terminals wifi_terminals_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wifi_terminals
    ADD CONSTRAINT wifi_terminals_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: wifi_terminals wifi_terminals_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wifi_terminals
    ADD CONSTRAINT wifi_terminals_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: wifi_terminals wifi_terminals_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wifi_terminals
    ADD CONSTRAINT wifi_terminals_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: physical_links workstation_dest_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT workstation_dest_id_fk FOREIGN KEY (workstation_dest_id) REFERENCES public.workstations(id) ON DELETE CASCADE;


--
-- Name: application_workstation workstation_id_fk_1486547; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_workstation
    ADD CONSTRAINT workstation_id_fk_1486547 FOREIGN KEY (workstation_id) REFERENCES public.workstations(id) ON DELETE CASCADE;


--
-- Name: physical_links workstation_src_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.physical_links
    ADD CONSTRAINT workstation_src_id_fk FOREIGN KEY (workstation_src_id) REFERENCES public.workstations(id) ON DELETE CASCADE;


--
-- Name: workstations workstations_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT workstations_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: workstations workstations_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT workstations_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: workstations workstations_domain_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT workstations_domain_id_foreign FOREIGN KEY (domain_id) REFERENCES public.domains(id);


--
-- Name: workstations workstations_entity_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT workstations_entity_id_foreign FOREIGN KEY (entity_id) REFERENCES public.entities(id);


--
-- Name: workstations workstations_network_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT workstations_network_id_foreign FOREIGN KEY (network_id) REFERENCES public.networks(id);


--
-- Name: workstations workstations_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT workstations_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: workstations workstations_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT workstations_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: workstations workstations_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workstations
    ADD CONSTRAINT workstations_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.admin_users(id);


--
-- Name: annuaires zone_admin_fk_1482666; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.annuaires
    ADD CONSTRAINT zone_admin_fk_1482666 FOREIGN KEY (zone_admin_id) REFERENCES public.zone_admins(id);


--
-- Name: forest_ads zone_admin_fk_1482667; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.forest_ads
    ADD CONSTRAINT zone_admin_fk_1482667 FOREIGN KEY (zone_admin_id) REFERENCES public.zone_admins(id);


--
-- Name: zone_admins zone_admins_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zone_admins
    ADD CONSTRAINT zone_admins_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: zone_admins zone_admins_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zone_admins
    ADD CONSTRAINT zone_admins_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: zone_admins zone_admins_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zone_admins
    ADD CONSTRAINT zone_admins_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: zone_admins zone_admins_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zone_admins
    ADD CONSTRAINT zone_admins_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: zone_zone zone_zone_related_zone_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zone_zone
    ADD CONSTRAINT zone_zone_related_zone_id_foreign FOREIGN KEY (related_zone_id) REFERENCES public.zones(id) ON DELETE CASCADE;


--
-- Name: zone_zone zone_zone_zone_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zone_zone
    ADD CONSTRAINT zone_zone_zone_id_foreign FOREIGN KEY (zone_id) REFERENCES public.zones(id) ON DELETE CASCADE;


--
-- Name: zones zones_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zones
    ADD CONSTRAINT zones_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: zones zones_deleted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zones
    ADD CONSTRAINT zones_deleted_by_foreign FOREIGN KEY (deleted_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: zones zones_perimeter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zones
    ADD CONSTRAINT zones_perimeter_id_foreign FOREIGN KEY (perimeter_id) REFERENCES public.perimeters(id) ON DELETE RESTRICT;


--
-- Name: zones zones_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.zones
    ADD CONSTRAINT zones_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict QDnbTDWFYi6iaYCOuDqk67XlmwoWbZkeHVO5UKhQCOMvTdtmvJcBzZp2lIoYqTX

--
-- PostgreSQL database dump
--

\restrict 7UllebzeaTmWa7wIoil1ItcC6Id098moylS8WelyUbXOyhFDBt5Y9zQgWBVf7lv

-- Dumped from database version 16.15
-- Dumped by pg_dump version 16.15

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2021_05_08_191249_create_activities_table	1
2	2021_05_08_191249_create_activity_operation_table	1
3	2021_05_08_191249_create_activity_process_table	1
4	2021_05_08_191249_create_actor_operation_table	1
5	2021_05_08_191249_create_actors_table	1
6	2021_05_08_191249_create_annuaires_table	1
7	2021_05_08_191249_create_application_blocks_table	1
8	2021_05_08_191249_create_application_module_application_service_table	1
9	2021_05_08_191249_create_application_modules_table	1
10	2021_05_08_191249_create_application_service_m_application_table	1
11	2021_05_08_191249_create_application_services_table	1
12	2021_05_08_191249_create_audit_logs_table	1
13	2021_05_08_191249_create_bay_wifi_terminal_table	1
14	2021_05_08_191249_create_bays_table	1
15	2021_05_08_191249_create_buildings_table	1
16	2021_05_08_191249_create_database_entity_table	1
17	2021_05_08_191249_create_database_information_table	1
18	2021_05_08_191249_create_database_m_application_table	1
19	2021_05_08_191249_create_databases_table	1
20	2021_05_08_191249_create_dhcp_servers_table	1
21	2021_05_08_191249_create_dnsservers_table	1
22	2021_05_08_191249_create_domaine_ad_forest_ad_table	1
23	2021_05_08_191249_create_domaine_ads_table	1
24	2021_05_08_191249_create_entities_table	1
25	2021_05_08_191249_create_entity_m_application_table	1
26	2021_05_08_191249_create_entity_process_table	1
27	2021_05_08_191249_create_external_connected_entities_table	1
28	2021_05_08_191249_create_external_connected_entity_network_table	1
29	2021_05_08_191249_create_fluxes_table	1
30	2021_05_08_191249_create_forest_ads_table	1
31	2021_05_08_191249_create_gateways_table	1
32	2021_05_08_191249_create_information_process_table	1
33	2021_05_08_191249_create_information_table	1
34	2021_05_08_191249_create_lan_man_table	1
35	2021_05_08_191249_create_lan_wan_table	1
36	2021_05_08_191249_create_lans_table	1
37	2021_05_08_191249_create_logical_server_m_application_table	1
38	2021_05_08_191249_create_logical_server_physical_server_table	1
39	2021_05_08_191249_create_logical_servers_table	1
40	2021_05_08_191249_create_m_application_process_table	1
41	2021_05_08_191249_create_m_applications_table	1
42	2021_05_08_191249_create_macro_processuses_table	1
43	2021_05_08_191249_create_man_wan_table	1
44	2021_05_08_191249_create_mans_table	1
45	2021_05_08_191249_create_media_table	1
46	2021_05_08_191249_create_network_subnetword_table	1
47	2021_05_08_191249_create_network_switches_table	1
48	2021_05_08_191249_create_networks_table	1
49	2021_05_08_191249_create_operation_task_table	1
50	2021_05_08_191249_create_operations_table	1
51	2021_05_08_191249_create_password_resets_table	1
52	2021_05_08_191249_create_peripherals_table	1
53	2021_05_08_191249_create_permission_role_table	1
54	2021_05_08_191249_create_permissions_table	1
55	2021_05_08_191249_create_phones_table	1
56	2021_05_08_191249_create_physical_router_vlan_table	1
57	2021_05_08_191249_create_physical_routers_table	1
58	2021_05_08_191249_create_physical_security_devices_table	1
59	2021_05_08_191249_create_physical_servers_table	1
60	2021_05_08_191249_create_physical_switches_table	1
61	2021_05_08_191249_create_processes_table	1
62	2021_05_08_191249_create_relations_table	1
63	2021_05_08_191249_create_role_user_table	1
64	2021_05_08_191249_create_roles_table	1
65	2021_05_08_191249_create_routers_table	1
66	2021_05_08_191249_create_security_devices_table	1
67	2021_05_08_191249_create_sites_table	1
68	2021_05_08_191249_create_storage_devices_table	1
69	2021_05_08_191249_create_subnetworks_table	1
70	2021_05_08_191249_create_tasks_table	1
71	2021_05_08_191249_create_users_table	1
72	2021_05_08_191249_create_vlans_table	1
73	2021_05_08_191249_create_wans_table	1
74	2021_05_08_191249_create_wifi_terminals_table	1
75	2021_05_08_191249_create_workstations_table	1
76	2021_05_08_191249_create_zone_admins_table	1
77	2021_05_08_191251_add_foreign_keys_to_activity_operation_table	1
78	2021_05_08_191251_add_foreign_keys_to_activity_process_table	1
79	2021_05_08_191251_add_foreign_keys_to_actor_operation_table	1
80	2021_05_08_191251_add_foreign_keys_to_annuaires_table	1
81	2021_05_08_191251_add_foreign_keys_to_application_module_application_service_table	1
82	2021_05_08_191251_add_foreign_keys_to_application_service_m_application_table	1
83	2021_05_08_191251_add_foreign_keys_to_bay_wifi_terminal_table	1
84	2021_05_08_191251_add_foreign_keys_to_bays_table	1
85	2021_05_08_191251_add_foreign_keys_to_buildings_table	1
86	2021_05_08_191251_add_foreign_keys_to_database_entity_table	1
87	2021_05_08_191251_add_foreign_keys_to_database_information_table	1
88	2021_05_08_191251_add_foreign_keys_to_database_m_application_table	1
89	2021_05_08_191251_add_foreign_keys_to_databases_table	1
90	2021_05_08_191251_add_foreign_keys_to_domaine_ad_forest_ad_table	1
91	2021_05_08_191251_add_foreign_keys_to_entity_m_application_table	1
92	2021_05_08_191251_add_foreign_keys_to_entity_process_table	1
93	2021_05_08_191251_add_foreign_keys_to_external_connected_entity_network_table	1
94	2021_05_08_191251_add_foreign_keys_to_fluxes_table	1
95	2021_05_08_191251_add_foreign_keys_to_forest_ads_table	1
96	2021_05_08_191251_add_foreign_keys_to_information_process_table	1
97	2021_05_08_191251_add_foreign_keys_to_lan_man_table	1
98	2021_05_08_191251_add_foreign_keys_to_lan_wan_table	1
99	2021_05_08_191251_add_foreign_keys_to_logical_server_m_application_table	1
100	2021_05_08_191251_add_foreign_keys_to_logical_server_physical_server_table	1
101	2021_05_08_191251_add_foreign_keys_to_m_application_process_table	1
102	2021_05_08_191251_add_foreign_keys_to_m_applications_table	1
103	2021_05_08_191251_add_foreign_keys_to_man_wan_table	1
104	2021_05_08_191251_add_foreign_keys_to_network_subnetword_table	1
105	2021_05_08_191251_add_foreign_keys_to_operation_task_table	1
106	2021_05_08_191251_add_foreign_keys_to_peripherals_table	1
107	2021_05_08_191251_add_foreign_keys_to_permission_role_table	1
108	2021_05_08_191251_add_foreign_keys_to_phones_table	1
109	2021_05_08_191251_add_foreign_keys_to_physical_router_vlan_table	1
110	2021_05_08_191251_add_foreign_keys_to_physical_routers_table	1
111	2021_05_08_191251_add_foreign_keys_to_physical_security_devices_table	1
112	2021_05_08_191251_add_foreign_keys_to_physical_servers_table	1
113	2021_05_08_191251_add_foreign_keys_to_physical_switches_table	1
114	2021_05_08_191251_add_foreign_keys_to_processes_table	1
115	2021_05_08_191251_add_foreign_keys_to_relations_table	1
116	2021_05_08_191251_add_foreign_keys_to_role_user_table	1
117	2021_05_08_191251_add_foreign_keys_to_storage_devices_table	1
118	2021_05_08_191251_add_foreign_keys_to_subnetworks_table	1
119	2021_05_08_191251_add_foreign_keys_to_wifi_terminals_table	1
120	2021_05_08_191251_add_foreign_keys_to_workstations_table	1
121	2021_05_13_180642_add_cidt_criteria	1
122	2021_05_19_161123_rename_subnetwork	1
123	2021_06_22_170555_add_type	1
124	2021_07_14_071311_create_certificates_table	1
125	2021_08_08_125856_config_right	1
126	2021_08_11_201624_certificate_application_link	1
127	2021_08_18_171048_network_redesign	1
128	2021_08_20_034757_default_gateway	1
129	2021_08_28_152910_cleanup	1
130	2021_09_19_125048_relation-inportance	1
131	2021_09_21_161028_add_router_ip	1
132	2021_09_22_114706_add_security_ciat	1
133	2021_09_23_192127_rename_descrition	1
134	2021_09_28_205405_add_direction_to_flows	1
135	2021_10_12_210233_physical_router_name_type	1
136	2021_10_19_102610_add_address_ip	1
137	2021_11_23_204551_add_app_version	1
138	2022_02_08_210603_create_cartographer_m_application_table	1
139	2022_02_22_32654_add_cert_status	1
140	2022_02_27_162738_add_functional_referent_to_m_application	1
141	2022_02_27_163129_add_editor_to_m_application	1
142	2022_02_27_192155_add_date_fields_to_m_application	1
143	2022_02_28_205630_create_m_application_event_table	1
144	2022_05_02_123756_add_update_to_logical_servers	1
145	2022_05_18_140331_add_is_external_column_to_entities	1
146	2022_05_21_103208_add_type_property_to_entities	1
147	2022_06_27_061444_application_workstation	1
148	2022_07_28_105153_add_link_operation_process	1
149	2022_08_11_165441_add_vpn_fields	1
150	2022_09_13_204845_cert_last_notification	1
151	2022_12_17_115624_rto_rpo	1
152	2023_01_03_205224_database_logical_server	1
153	2023_01_08_123726_add_physical_link	1
154	2023_01_27_165009_add_flux_nature	1
155	2023_01_28_145242_add_logical_devices_link	1
156	2023_02_09_164940_gdpr	1
157	2023_03_16_123031_create_documents_table	1
158	2023_03_22_185812_create_cpe	1
159	2023_04_18_123308_add_gdpr_tables	1
160	2023_05_29_161406_security_controls_links	1
161	2023_06_14_120958_add_physical_address_ip	1
162	2023_08_06_100128_add_physicalserver_size	1
163	2023_08_07_183714_application_physical_server	1
164	2023_09_04_111440_add_application_patching	1
165	2023_09_26_074104_iot	1
166	2023_10_28_124418_add_cluster	1
167	2023_11_30_070804_fix_migration_typo	1
168	2024_02_21_085107_application_patching	1
169	2024_02_29_134239_patching_attributes	1
170	2024_03_14_165211_router_ip_lenght	1
171	2024_03_19_195927_contracts	1
172	2024_04_06_161307_nomalization	1
173	2024_04_08_105719_network_flow	1
174	2024_04_14_072101_add_parent_entity	1
175	2024_04_28_075916_normalize_process_name	1
176	2024_05_09_180526_improve_network_flow	1
177	2024_05_15_212326_routers_log_phys	1
178	2024_06_03_165236_add_user	1
179	2024_06_11_060639_external_connnected_entities_desc	1
180	2024_06_18_125723_link_domain_lservers	1
181	2024_08_27_200851_normalize_storage_devices	1
182	2024_09_22_112404_add_icon	1
183	2024_09_24_044657_move_icons_to_docs	1
184	2024_09_24_084005_move_icons_to_docs	1
185	2024_09_26_160952_building_attributes	1
186	2024_10_31_220940_add_vlan_id	1
187	2024_11_13_183902_add_type_to_logical_servers	1
188	2024_11_26_130914_add_authenticity	1
189	2025_01_03_130604_create_graphs_table	1
190	2025_01_10_123601_logical_server_disabled	1
191	2025_01_16_120601_add_icon_to_process	1
192	2025_01_17_133444_add_table_containers	1
193	2025_02_18_073549_application_assignation	1
194	2025_03_17_094654_datetime_to_date	1
195	2025_03_24_132409_remove_unique_name_deleted_at_indexes	1
196	2025_03_26_133906_external_nullable	1
197	2025_04_27_084635_add_icon_to_servers	1
198	2025_04_27_123200_add_legal_basis	1
199	2025_06_19_072710_add_databases_to_containers	1
200	2025_06_28_155312_add_glpi_fields	2
201	2025_07_01_065432_admin_user_fields	2
202	2025_07_02_123433_rename_permission	2
203	2025_07_07_070846_add_admin_user_application	2
204	2025_07_08_065626_add_references_to_logical_flows	2
205	2025_07_17_145227_activities_bia	2
206	2025_07_17_150249_create_activity_impact_table	2
207	2025_08_23_111003_add_pra	2
208	2025_08_27_135552_physical_logical_security_devices	2
209	2025_09_05_141822_add_external_ref_id_to_entities	2
210	2025_09_21_134035_add_cluster_router	2
211	2025_09_23_113715_add_user_login	2
212	2025_09_29_193233_external_connected_entities_complement	2
213	2025_10_02_205209_add_permissions	2
214	2025_10_04_153231_add_attribut_on_fluxes	2
215	2025_10_08_100558_more_buildings	2
216	2025_10_17_073218_add_cluster_logical_server	2
217	2025_10_17_085635_add_fields_to_cluster	2
218	2025_10_18_174616_add_cluster_physical_server	2
220	2025_10_21_104026_add_lawfullness	3
221	2025_10_27_095802_add_link_applications_security_devices	3
222	2025_10_27_104704_add_fields_security_device	3
223	2025_10_30_081134_change_active_nullable_on_lservers	3
224	2025_11_01_123622_drop_unique_graphs_name_unique_on_graphs_table	3
225	2025_11_09_103559_add_network_switch_vlan	3
226	2025_11_10_090632_add_subnetwork_link	3
227	2025_11_17_110452_add_graph_class	3
228	2016_06_01_000001_create_oauth_auth_codes_table	4
229	2016_06_01_000002_create_oauth_access_tokens_table	4
230	2016_06_01_000003_create_oauth_refresh_tokens_table	4
231	2016_06_01_000004_create_oauth_clients_table	4
232	2016_06_01_000005_create_oauth_personal_access_clients_table	4
236	2025_10_18_183453_add_cluster_router	5
237	2025_11_21_123505_add_chain_to_logical_flow	6
238	2025_11_24_152357_create_mercator_modules_table	6
239	2025_11_30_123016_add_module_to_permissions_table	6
240	2024_06_01_000001_create_oauth_device_codes_table	7
241	2025_12_03_105527_add_ip_to_security_devices	7
242	2025_12_03_111641_add_security_device_to_logical_flows	7
243	2025_12_04_135457_add_manage_module_role	7
244	2026_01_16_114842_add_icon_to_databases	7
245	2026_01_24_091900_add_information_flow_table	7
246	2026_02_13_134714_add_cpe_to_application_modules	7
247	2026_02_18_085500_add_type_color_to_physical_links	7
248	2026_03_02_000001_upgrade_oauth_clients_uuid	7
249	2026_03_03_184523_add_parent_man	7
250	2026_03_05_114042_add_icon_to_physical_switches	7
251	2026_03_11_134833_add_cluster_to_logical_flows	7
252	2026_03_11_175208_add_information_information	7
253	2026_03_22_121031_add_data_processing_fields	7
254	2026_03_30_095429_add_document_permissions	7
255	2026_04_05_180615_add_table_backups	7
256	2026_04_15_100554_add_application_module_entity	7
257	2026_04_17_211419_add_query_table	7
258	2026_05_03_184011_rename_m_applications_to_applications	8
259	2026_05_03_185943_rename_m_application_events_to_application_events	8
260	2026_05_03_192017_rename_m_application_id_in_application_events	8
261	2026_05_03_193002_rename_m_application_permissions	8
262	2026_05_03_230714_consolidate_application_permissions	8
263	2026_05_03_234356_drop_application_cartographer_table	8
264	2026_05_07_215942_rename_fluxes_to_application_flows	8
265	2026_05_08_120804_rename_flux_information_to_application_flow_information	8
266	2026_05_08_212557_rename_domaines_to_domains	8
267	2026_05_11_000001_create_parameters_table	9
268	2026_05_12_113635_drop_unique_name_from_annuaires	9
269	2026_05_12_150000_create_zones_table	9
270	2026_05_12_150001_add_zone_permissions	9
271	2026_05_20_000000_restructure_backups_table	9
272	2026_05_31_000000_create_cartographers_table	9
273	2026_05_31_000001_rename_irregular_permissions	9
274	2026_06_04_090735_create_jobs_table	9
275	2026_06_07_094101_add_soft_deletes_to_cartographers_table	9
276	2026_06_11_000000_create_application_document_table	9
277	2026_06_18_000000_add_ext_refs_to_mapped_objects	9
278	2026_06_21_000000_move_mercator_config_to_parameters	9
279	2026_06_22_000000_rename_room_id_to_building_id_in_bays	9
280	2026_06_22_000001_add_type_and_attributes_columns	9
281	2026_06_22_000002_add_description_and_attributes_to_physical_links	9
282	2026_06_28_000000_drop_unique_name_from_backups_table	9
283	2026_07_05_000000_change_peripheral_domain_to_domain_id	10
284	2026_07_18_000000_create_monarc_sync_items_table	10
285	2026_07_24_000000_add_status_hosting_fields_to_applications	10
286	2026_08_04_000000_add_application_id_to_annuaires	10
287	2026_08_04_000000_fix_physical_servers_patching_typo	10
288	2026_08_11_000000_add_cairn_access_permission	10
289	2026_09_02_000000_add_flow_label_to_users_table	10
290	2026_09_08_000000_add_icon_to_storage_devices	10
291	2026_09_09_000000_add_type_attributes_to_cartography_objects	10
292	2026_09_09_000001_rename_nature_to_type_on_application_flows	10
293	2026_09_10_000000_rename_entity_type_to_type_on_entities	10
294	2026_09_10_000001_move_is_external_to_attributes_on_entities	10
295	2026_09_14_100000_create_perimeters_table	10
296	2026_09_14_100001_add_perimeter_id_to_roles_table	10
297	2026_09_15_000000_add_perimeter_id_to_mapped_objects	10
298	2026_09_15_113000_add_comments_to_applications	10
299	2026_09_16_000000_drop_unique_name_indexes_on_mapped_objects	10
300	2026_09_16_000001_add_perimeter_id_to_admin_users_table	10
301	2026_09_16_100000_add_name_indexes_to_frequently_sorted_tables	10
302	2026_09_21_000000_rename_nom_to_name_in_perimeters_table	10
303	2026_09_24_000000_add_comments_to_application_services	10
304	2026_09_24_000001_add_comments_to_application_modules	10
305	2026_10_10_000000_sanitize_stored_html_fields	10
306	2026_10_28_000000_add_icon_to_information	10
308	2026_11_06_000000_add_blame_columns_to_audited_tables	11
\.


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 308, true);


--
-- PostgreSQL database dump complete
--

\unrestrict 7UllebzeaTmWa7wIoil1ItcC6Id098moylS8WelyUbXOyhFDBt5Y9zQgWBVf7lv

