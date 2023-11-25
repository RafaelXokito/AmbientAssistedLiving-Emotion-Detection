
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
    name       varchar(255) not null, 
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

ALTER TABLE `emotions` ADD COLUMN display_name varchar(255) null;

UPDATE `emotions` SET display_name = 'raiva' WHERE name = 'angry';

UPDATE `emotions` SET display_name = 'desgosto' WHERE name = 'disgust';

UPDATE `emotions` SET display_name = 'medo' WHERE name = 'fear';

UPDATE `emotions` SET display_name = 'culpa' WHERE name = 'guilt';

UPDATE `emotions` SET display_name = 'felicidade' WHERE name = 'happy';

UPDATE `emotions` SET display_name = 'tristeza' WHERE name = 'sad';

UPDATE `emotions` SET display_name = 'dor' WHERE name = 'pain';

UPDATE `emotions` SET display_name = 'vergonha' WHERE name = 'shame';

UPDATE `emotions` SET display_name = 'negativa' WHERE name = 'negative';

UPDATE `emotions` SET display_name = 'positiva' WHERE name = 'positive';

UPDATE `emotions` SET display_name = 'neutra' WHERE name = 'neutral';

CREATE TABLE `questionnaire_types` (
    `name` NVARCHAR(255) not null,
    `display_name` NVARCHAR(255) not null,
    `points_min` float NOT NULL,
    `points_max` float NOT NULL,
    PRIMARY KEY (`name`),
    CONSTRAINT `questionnaire_types_points_min_check` CHECK (points_min >= 0 and points_min < points_max),
    CONSTRAINT `questionnaire_types_points_max_check` CHECK (points_max > 0 and points_max > points_min)
);

INSERT INTO `questionnaire_types` (`name`, `display_name`, `points_min`, `points_max`) VALUES ('GeriatricQuestionnaire', 'Questionário de depressão geriátrica', 0, 15);
INSERT INTO `questionnaire_types` (`name`, `display_name`, `points_min`, `points_max`) VALUES ('OxfordHappinessQuestionnaire', 'Questionário de Felicidade de Oxford', 1, 6);

CREATE TABLE `questions` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `number` bigint NOT NULL,
    `question` NVARCHAR(255) NOT NULL,
    `questionnaire` NVARCHAR(255) not null,
    UNIQUE KEY `unique_number_questionnaire` (`number`, `questionnaire`),
    constraint `questions_ibfk_1` foreign key (`questionnaire`) references questionnaire_types (`name`),
    PRIMARY KEY (`id`)
);

INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('1', "Sente-se satisfeito(a) com a sua vida?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('2', "Abandonou muitos dos seus interesses e actividades?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('3', "Acha que falta significado na sua vida?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('4', "Costuma se sentir aborrecido(a) com frequência?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('5', "Sente-se de bom humor na maior parte do tempo?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('6', "Tem medo que algo de mau lhe aconteça?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('7', "Na maior parte do tempo, sente-se feliz?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('8', "Sente-se frequentemente abandonado(a)?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('9', "Prefere ficar em casa em vez de sair e experimentar coisas novas?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('10', "Sente que tem mais problemas de memória do que outras pessoas da sua idade?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('11', "Acredita que é maravilhoso estar vivo(a)?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('12', "Recentemente, sentiu-se como inútil ou incapaz?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('13', "No momento, está a sentir-se cheio de energia?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('14', "Sente que perdeu a sua esperança?", 'GeriatricQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('15', "Acha que as outras pessoas estão melhores que si?", 'GeriatricQuestionnaire');

INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('1', "Sente-se insatisfeito(a) com a sua maneira de ser?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('2', "Dedica-se inteiramente aos outros?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('3', "Sente que a vida é muito gratificante?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('4', "Tem sentimentos muito calorosos em relação a quase toda a gente?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('5', "Sente que raramente acorda com a sensação de ter 'carregado as baterias'?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('6', "Acha que é pouco otimista relativamente ao futuro?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('7', "Acredita que facilmente retira prazer das coisas que faz?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('8', "Sente que está sempre comprometido(a) ou envolvido(a)?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('9', "Acredita que a vida é boa?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('10', "Acha que o mundo é um mau sítio?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('11', "Você ri muito?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('12', "Está muito satisfeito(a) com tudo na sua vida?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('13', "Acha que não é atraente?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('14', "Há diferenças entre aquilo que gostava de fazer e o que tem feito?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('15', "É muito feliz?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('16', "Acredita que as coisas à sua volta são encantadoras?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('17', "Tem muita facilidade em animar os outros?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('18', "Acha que se adapta sempre a tudo o que quer?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('19', "Sente que não tem controlo sobre a sua vida?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('20', "Sente-se capaz de enfrentar qualquer desafio?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('21', "Está sempre completamente atento ao que o rodeia?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('22', "Sente regulamente alegria e exaltação?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('23', "Acha díficil tomar decisões?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('24', "Sente que não encontra sentido e significado na sua vida?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('25', "Sente que tem sempre muita energia?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('26', "Tem sempre uma influência positiva nos acontecimentos?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('27', "Acredita que se diverte pouco junto de outras pessoas?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('28', "Acha que está pouco saudável?", 'OxfordHappinessQuestionnaire');
INSERT INTO `questions` (`number`, `question`, `questionnaire`) VALUES ('29', "Tem muitas memórias infelizes do seu passado?", 'OxfordHappinessQuestionnaire');

CREATE TABLE `questionnaire_result_mappings` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `points_min` float NOT NULL,
    `points_max` float NOT NULL,
    `points_max_inclusive` boolean NOT NULL,
    `message` NVARCHAR(255) not null,
    `short_message` NVARCHAR(255) not null,
    `questionnaire` NVARCHAR(255) not null,
    UNIQUE KEY `unique_points_questionnaire_result_mappings` (`points_min`, `points_max`, `questionnaire`),
    constraint `questionnaire_result_mappings_ibfk_1` foreign key (`questionnaire`) references questionnaire_types (`name`),
    CONSTRAINT `result_mappings_points_min_check` CHECK (points_min >= 0 and points_min < points_max),
    CONSTRAINT `result_mappings_points_max_check` CHECK (points_max > 0 and points_max > points_min),
    PRIMARY KEY (`id`)
);

INSERT INTO `questionnaire_result_mappings` (`points_min`, `points_max`, `message`, `short_message`, `points_max_inclusive`, `questionnaire`) VALUES (0, 5, "Baseado nas suas respostas que forneceu não apresenta sinais de depressão.", "Sem depressão", TRUE, 'GeriatricQuestionnaire');
INSERT INTO `questionnaire_result_mappings` (`points_min`, `points_max`, `message`, `short_message`, `points_max_inclusive`, `questionnaire`) VALUES (6, 10, "Com base nas respostas que forneceu apresenta apenas leves sinais de depressão.", "Depressão leve", TRUE, 'GeriatricQuestionnaire');
INSERT INTO `questionnaire_result_mappings` (`points_min`, `points_max`, `message`, `short_message`, `points_max_inclusive`, `questionnaire`) VALUES (11, 15, "Tendo em conta as suas respostas apresenta sintomas de depressão graves. Comunique com a sua família e considere marcar uma consulta com o seu médico de família.", "Depressão severa", TRUE, 'GeriatricQuestionnaire');

INSERT INTO `questionnaire_result_mappings` (`points_min`, `points_max`, `message`, `short_message`, `points_max_inclusive`, `questionnaire`) VALUES (0, 2, "Baseado nas suas respostas sente-se triste e provavelmente está a ver-se a si e à sua situação atual pior do que realmente é.",  "Muito Infeliz", FALSE, 'OxfordHappinessQuestionnaire');
INSERT INTO `questionnaire_result_mappings` (`points_min`, `points_max`, `message`, `short_message`, `points_max_inclusive`, `questionnaire`) VALUES (2, 3, "Tendo em conta as suas respostas está um pouco triste. Tente conversar com a sua família e fazer alguma atividade que o deixe feliz.", "Um pouco Infeliz", FALSE, 'OxfordHappinessQuestionnaire');
INSERT INTO `questionnaire_result_mappings` (`points_min`, `points_max`, `message`, `short_message`, `points_max_inclusive`, `questionnaire`) VALUES (3, 4, "Considerando as respostas que forneceu demonstra sentimentos de neutralidade.", "Neutro", FALSE, 'OxfordHappinessQuestionnaire');
INSERT INTO `questionnaire_result_mappings` (`points_min`, `points_max`, `message`, `short_message`, `points_max_inclusive`, `questionnaire`) VALUES (4, 5, "Baseado nas respostas fornecidas está feliz. Sorria e pensamentos positivos!", "Bastante Feliz", FALSE, 'OxfordHappinessQuestionnaire');
INSERT INTO `questionnaire_result_mappings` (`points_min`, `points_max`, `message`, `short_message`, `points_max_inclusive`, `questionnaire`) VALUES (5, 6, "Parabéns! Com base nas suas respostas está muito feliz!", "Muito Feliz", FALSE, 'OxfordHappinessQuestionnaire');


 ALTER TABLE `questionnaire_types`
 ADD COLUMN `questionnairable_model_name` varchar(50) not null;

 UPDATE `questionnaire_types` SET questionnairable_model_name = 'App\\Models\\OxfordHappinessQuestionnaire' WHERE name = 'OxfordHappinessQuestionnaire';
 UPDATE `questionnaire_types` SET questionnairable_model_name = 'App\\Models\\GeriatricQuestionnaire' WHERE name = 'GeriatricQuestionnaire';

-- mysql -u sail -p -h 127.0.0.1 -P 3306 AALBackend

-------------------------------------------------------------------------------
-- add mapping for emotions 
-------------------------------------------------------------------------------

-- alter table `questionnaires` to have an FK for questionnaire_types
-- alter the questionnaire model to have the FK