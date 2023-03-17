

--- New tables to create in local machine ---

create table `geriatric_questionnaires`
(
    `id`            bigint NOT NULL AUTO_INCREMENT,
    `client_id`     bigint not null,
    `points`        float null,
    `created_at`    timestamp  null default null,
    `updated_at`     timestamp  null default null,
    primary key (`id`),
    constraint `geriatricQuestionnaire_ibfk_1` foreign key (`client_id`) references clients (`id`)
);

create table `responses_geriatric_questionnaire`
(
    `id`                   bigint NOT NULL AUTO_INCREMENT,
    `questionnaire_id`     bigint not null,
    `response`             varchar(255) not null,
    `why`                  varchar(255) null default null,
    `question`             varchar(255) not null,
    `created_at`           timestamp null default null,
    `updated_at`           timestamp null default null,
    primary key (`id`),
    constraint `responsesGeriatricQuestionnaire_ibfk_1` foreign key (`questionnaire_id`) references geriatric_questionnaires (`id`),
    constraint `responsesGeriatricQuestionnaire_check_response` CHECK (`response` IN ('Sim','Não')),
    constraint `responsesGeriatricQuestionnaire_check_question` CHECK (`question` >= 1 AND `question` <= 15)
);


ALTER TABLE responses_geriatric_questionnaire
ADD COLUMN speech_id bigint not null;


ALTER TABLE responses_geriatric_questionnaire
ADD CONSTRAINT `responsesGeriatricQuestionnaire_ibfk_2` foreign key (`speech_id`) references speeches (`id`);

ALTER TABLE responses_geriatric_questionnaire
DROP COLUMN why;

ALTER TABLE responses_geriatric_questionnaire
ADD COLUMN is_why boolean not null;

ALTER TABLE responses_geriatric_questionnaire
DROP CONSTRAINT responsesGeriatricQuestionnaire_check_response;