
create table if not exists administrators
(
    id         bigint auto_increment
        primary key,
    created_at timestamp null,
    updated_at timestamp null,
    deleted_at timestamp null
);

create table if not exists emotions
(
    name       varchar(255) not null
        primary key,
    category   varchar(255) null,
    created_at timestamp    null,
    updated_at timestamp    null,
    weight     float        null
);

create table if not exists frames
(
    id   bigint auto_increment
        primary key,
    name varchar(255) null,
    path varchar(255) null
);

create table if not exists speeches
(
    id   bigint auto_increment
        primary key,
    text text null
);

create table if not exists users
(
    id                bigint auto_increment
        primary key,
    userable_type     varchar(50)          not null,
    created_at        timestamp            null,
    email             varchar(255)         null,
    name              varchar(255)         null,
    password          varchar(255)         null,
    userable_id       bigint               null,
    updated_at        timestamp            null,
    deleted_at        timestamp            null,
    remember_token    varchar(100)         null,
    email_verified_at timestamp            null,
    confirmation_code varchar(255)         null,
    notifiable        tinyint(1) default 0 null
);

create table if not exists clients
(
    id               bigint auto_increment
        primary key,
    birthdate        timestamp            null,
    contact          varchar(255)         null,
    administrator_id bigint               null,
    created_at       timestamp            null,
    updated_at       timestamp            null,
    deleted_at       timestamp            null,
    is_active        tinyint(1) default 0 null,
    constraint clients_ibfk_2
        foreign key (administrator_id) references users (id)
);

create index administrator_id
    on clients (administrator_id);

create table if not exists emotion_expressions
(
    id              int auto_increment
        primary key,
    client_id       bigint       not null,
    emotion_name    varchar(255) not null,
    expression_name varchar(255) not null,
    created_at      timestamp    null,
    updated_at      timestamp    null,
    constraint emotion_expressions_ibfk_1
        foreign key (client_id) references clients (id),
    constraint emotion_expressions_ibfk_2
        foreign key (emotion_name) references emotions (name)
);

create index client_id
    on emotion_expressions (client_id);

create index emotion_name
    on emotion_expressions (emotion_name);

create table if not exists emotionsnotifications
(
    id            bigint auto_increment
        primary key,
    accuracylimit float        null,
    duration      double       null,
    client_id     bigint       null,
    emotion_name  varchar(255) null,
    created_at    timestamp    null,
    updated_at    timestamp    null,
    constraint emotionsnotifications_ibfk_1
        foreign key (client_id) references users (id),
    constraint emotionsnotifications_ibfk_2
        foreign key (emotion_name) references emotions (name)
);

create index client_id
    on emotionsnotifications (client_id);

create index emotion_name
    on emotionsnotifications (emotion_name);


create table if not exists iterations
(
    id           bigint auto_increment
        primary key,
    created_at   timestamp    null,
    macaddress   varchar(255) null,
    type         varchar(255) not null,
    client_id    bigint       null,
    emotion_name varchar(255) null,
    updated_at   timestamp    null,
    usage_id     varchar(255) not null,
    constraint iterations_usage_id_uindex
        unique (usage_id),
    constraint iterations_ibfk_1
        foreign key (client_id) references users (id),
    constraint iterations_ibfk_2
        foreign key (emotion_name) references emotions (name)
);

create table if not exists contents
(
    id             bigint auto_increment
        primary key,
    accuracy       float        null,
    emotion_name   varchar(255) null,
    createdate     timestamp    null,
    iteration_id   bigint       null,
    created_at     timestamp    null,
    updated_at     timestamp    null,
    childable_id   bigint       null,
    childable_type varchar(50)  not null,
    constraint contents_ibfk_1
        foreign key (emotion_name) references emotions (name),
    constraint contents_ibfk_2
        foreign key (iteration_id) references iterations (id)
);

create table if not exists classifications
(
    id           bigint auto_increment
        primary key,
    accuracy     float        null,
    emotion_name varchar(255) null,
    content_id   bigint       not null,
    constraint classifications_ibfk_2
        foreign key (emotion_name) references emotions (name),
    constraint classifications_ibfk_3
        foreign key (content_id) references contents (id)
);

create index content_id
    on classifications (content_id);

create index emotion_name
    on classifications (emotion_name);

create index client_id
    on iterations (client_id);

create index emotion_name
    on iterations (emotion_name);

create table if not exists logs
(
    id         bigint auto_increment
        primary key,
    content    varchar(255) null,
    created_at timestamp    null,
    macaddress varchar(255) null,
    process    varchar(255) null,
    client_id  bigint       null,
    updated_at timestamp    null,
    constraint logs_ibfk_1
        foreign key (client_id) references users (id)
);

create index client_id
    on logs (client_id);

create table if not exists multimodal_emotions
(
    id           bigint auto_increment
        primary key,
    client_id    bigint       not null,
    emotion_name varchar(255) not null,
    created_at   timestamp    null,
    updated_at   timestamp    null,
    constraint multimodal_emotions_ibfk_1
        foreign key (client_id) references clients (id),
    constraint multimodal_emotions_ibfk_2
        foreign key (emotion_name) references emotions (name)
);

create index multimodalEmotions_ibfk_1
    on multimodal_emotions (client_id);

create index multimodalEmotions_ibfk_2
    on multimodal_emotions (emotion_name);

create table if not exists notifications
(
    id               bigint auto_increment
        primary key,
    accuracy         float        null,
    content          text         null,
    created_at       timestamp    null,
    duration         float        null,
    notificationseen tinyint(1)   null,
    title            varchar(255) null,
    client_id        bigint       null,
    emotion_name     varchar(255) null,
    updated_at       timestamp    null,
    path             varchar(255) null,
    constraint notifications_ibfk_1
        foreign key (client_id) references users (id),
    constraint notifications_ibfk_2
        foreign key (emotion_name) references emotions (name)
);

create index client_id
    on notifications (client_id);

create index emotion_name
    on notifications (emotion_name);

-- New tables to create in local machine ---

create table `questionnaires`
(
    `id`            bigint NOT NULL AUTO_INCREMENT,
    `questionnairable_type`     varchar(50) not null,
    `questionnairable_id`     bigint not null,
    `client_id`     bigint not null,
    `points`        float null,
    `created_at`    timestamp  null default null,
    `updated_at`     timestamp  null default null,
    primary key (`id`),
    constraint `questionnaire_ibfk_1` foreign key (`client_id`) references clients (`id`)
);

create table `geriatric_questionnaires`
(
    `id`            bigint NOT NULL AUTO_INCREMENT,
    `created_at`    timestamp  null default null,
    `updated_at`     timestamp  null default null,
    primary key (`id`)
);

create table `oh_questionnaires`
(
    `id`            bigint NOT NULL AUTO_INCREMENT,
    `created_at`    timestamp  null default null,
    `updated_at`     timestamp  null default null,
    primary key (`id`)
);

create table `responses_questionnaire`
(
    `id`                   bigint NOT NULL AUTO_INCREMENT,
    `questionnaire_id`     bigint not null,
    `response`             varchar(255) not null,
    `is_why`               boolean not null,
    `speech_id`            bigint not null,
    `question`             varchar(255) not null,
    `created_at`           timestamp null default null,
    `updated_at`           timestamp null default null,
    primary key (`id`),
    constraint `responsesQuestionnaire_ibfk_1` foreign key (`questionnaire_id`) references questionnaires (`id`),
    constraint `responsesQuestionnaire_ibfk_2` foreign key (`speech_id`) references speeches (`id`)
);

CREATE TABLE `messages` (
    `id`                  bigint NOT NULL AUTO_INCREMENT,
    `body`                varchar(255) not null,
    `client_id`           bigint not null,
    `isChatbot`           boolean not null,
   `created_at`           timestamp null default null,
    `updated_at`          timestamp null default null,
    primary key (`id`),
    constraint `messages_ibfk_1` foreign key (`client_id`) references clients (`id`)
);