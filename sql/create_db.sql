create table if not exists app_scoring_criteria
(
    id int auto_increment,
    type varchar(45) null,
    text tinytext null,
    constraint app_scoring_criteria_id_uindex
        unique (id)
);

alter table app_scoring_criteria
    add primary key (id);

create table if not exists application_functions
(
    id int auto_increment
        primary key,
    function_id int not null,
    application_portfolio_id int not null
);

create table if not exists application_portfolio
(
    id int auto_increment,
    name varchar(45) not null,
    description tinytext null,
    type varchar(45) null comment 'Software,System,API,Microservice',
    api int(1) default 0 null,
    interface varchar(12) null,
    licensor varchar(45) null,
    licensor_contact_info varchar(45) null,
    version varchar(45) null,
    renewal_date datetime null,
    url_info tinytext null,
    url_support tinytext null,
    persona_id int null comment 'internal persona as contact',
    parent_id int null comment 'Dependency if any upon other applications',
    pricing_model varchar(45) null,
    unit_cost int null,
    license_type varchar(24) not null,
    license_term int null comment 'In terms of number of months',
    user_count int(2) null,
    commissioned_date date null,
    decommissioned_date date null,
    domain_id int null comment 'domain in which the application operates',
    status int default 0 not null,
    business_value int null,
    IT_value int null,
    constraint application_portfolio_id_uindex
        unique (id),
    constraint application_portfolio_name_uindex
        unique (name)
);

alter table application_portfolio
    add primary key (id);

create table if not exists application_scoring
(
    id int auto_increment
        primary key,
    application_portfolio_id int not null,
    type varchar(45) null,
    score int null,
    app_scoring_criteria_id int null
);

create table if not exists business_glossary
(
    id int auto_increment,
    acronym varchar(12) null,
    term varchar(45) not null,
    description tinytext null,
    domain_id int null,
    status int default 0 null,
    constraint business_glossary_id_uindex
        unique (id)
);

alter table business_glossary
    add primary key (id);

create table if not exists capabilities
(
    id int auto_increment
        primary key,
    capability varchar(45) not null,
    description tinytext null
);

create table if not exists data_dictionary
(
    id int auto_increment,
    domain_id int null,
    data_name varchar(45) not null,
    size int(3) null,
    not_null int(1) default 0 null,
    `unique` int(1) default 0 null,
    default_value varchar(45) null,
    description tinytext not null,
    derived_from tinytext null comment 'actual formula or name of stored production or function',
    modified_timestamp datetime default CURRENT_TIMESTAMP null,
    physical int(1) default 1 null,
    application_id int default 1001 null comment 'application source of this data if external to business application',
    state varchar(45) default 'review' null comment 'discussion, approved, development, test, release',
    data_type varchar(12) default 'varchar' not null,
    pk int(1) null,
    entity_id int not null,
    status int default 0 not null,
    API_in int null,
    API_out int null,
    data_label varchar(45) null,
    relationship varchar(45) null,
    constraint data_dictionary_id_uindex
        unique (id)
);

alter table data_dictionary
    add primary key (id);

create table if not exists data_entity
(
    id int auto_increment
        primary key,
    entity_name varchar(45) null,
    domain_id int not null,
    status int default 0 not null,
    description tinytext null,
    physical int default 1 null,
    entity_name_singular varchar(45) null,
    x decimal(8,6) null,
    y decimal(8,6) null
);

create table if not exists data_mappings
(
    id int auto_increment
        primary key,
    data_dictionary_id int not null comment 'data as parent',
    child_id int not null comment 'data as child',
    data_entity_id int not null,
    child_entity_id int not null,
    description text null
)
    comment 'defines relationships between data for mapping or dependences';

create table if not exists domain
(
    id int auto_increment,
    domain_name varchar(45) null,
    parent_id int null,
    description tinytext null,
    status int default 0 null,
    persona_id int null,
    constraint domains_domain_name_uindex
        unique (domain_name),
    constraint domains_domain_pk_uindex
        unique (id)
);

alter table domain
    add primary key (id);

create table if not exists entity_relationships
(
    id int auto_increment
        primary key,
    entity_id_parent int null,
    entity_parent_data_id int null,
    entity_id_child int null,
    entity_child_data_id int null,
    type varchar(12) default 'OM' null
);

create table if not exists function
(
    id int auto_increment
        primary key,
    function varchar(45) not null,
    description tinytext null,
    status int default 0 not null,
    constraint function_function_uindex
        unique (function)
);

create table if not exists persona
(
    id int auto_increment,
    acronym varchar(45) null,
    term tinytext null,
    description tinytext null,
    document varchar(45) null,
    status int default 0 not null,
    constraint personas_persona_title_uindex
        unique (acronym),
    constraint personas_personas_pk_uindex
        unique (id)
);

alter table persona
    add primary key (id);

create table if not exists persona_domain
(
    id int auto_increment,
    persona_id int not null,
    privileges varchar(45) null,
    is_owner int default 0 not null,
    domain_id int not null,
    status int default 0 not null,
    constraint persona_domain_id_uindex
        unique (id)
);

alter table persona_domain
    add primary key (id);

create table if not exists persona_journeys
(
    id int auto_increment
        primary key,
    persona_id int not null,
    start_view_id int not null,
    sequence int null,
    end_view_id int null
);

create table if not exists user
(
    id int auto_increment,
    persona_id int not null,
    status int default 0 not null,
    constraint user_id_uindex
        unique (id)
);

create table if not exists valuelists
(
    id int auto_increment
        primary key,
    type varchar(45) not null,
    text varchar(45) not null,
    value int not null
);

create index valuelists_id_index
    on valuelists (id);

create index valuelists_id_type_index
    on valuelists (id, type);

create table if not exists view_data
(
    id int auto_increment,
    data_dictionary_id int not null,
    description tinytext null,
    name varchar(45) null,
    view_id int not null,
    data_entity_id int not null,
    status int default 0 not null,
    constraint view_data_id_uindex
        unique (id)
);

alter table view_data
    add primary key (id);

create table if not exists views
(
    id int auto_increment,
    name varchar(45) not null,
    description text null,
    domain_id int not null,
    view_id int null comment 'parent view if any',
    type varchar(45) null comment 'input form,list view,dashboard,content',
    url tinytext null,
    phase_1 int default 0 null,
    url_snapshot tinytext null,
    status int default 0 null,
    phase_2 int default 0 null,
    phase_3 int default 0 null,
    phase_4 int default 0 null,
    phase_5 int default 0 null,
    phase_6 int default 0 null,
    source varchar(45) not null,
    priority decimal(3,1) default 0.0 null,
    progress varchar(45) null,
    constraint views_id_uindex
        unique (id)
);

alter table views
    add primary key (id);

create table if not exists views_in_views
(
    id int auto_increment
        primary key,
    view_id int null,
    parent_view_id int null,
    status int default 0 not null
);

