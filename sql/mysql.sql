CREATE TABLE todomod_task (

    task_id       INT(8) UNSIGNED     NOT NULL AUTO_INCREMENT,
    task_poster   VARCHAR(255)        NOT NULL DEFAULT '',
    task_title    VARCHAR(255)        NOT NULL DEFAULT '',
    task_created  INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    task_deadline INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    task_priority TINYINT(1) UNSIGNED NOT NULL DEFAULT '2',
    task_status   TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    task_detail   TEXT,

    PRIMARY KEY (task_id),
    KEY task_title (task_title(40))

)
    ENGINE = ISAM;


CREATE TABLE todomod_trash_task (

    task_id       INT(8) UNSIGNED     NOT NULL AUTO_INCREMENT,
    task_poster   VARCHAR(255)        NOT NULL DEFAULT '',
    task_title    VARCHAR(255)        NOT NULL DEFAULT '',
    task_created  INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    task_deadline INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    task_priority TINYINT(1) UNSIGNED NOT NULL DEFAULT '2',
    task_status   TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    task_detail   TEXT,

    PRIMARY KEY (task_id),
    KEY task_title (task_title(40))

)
    ENGINE = ISAM;
