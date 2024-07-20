create table if not exists `sessions` (
    `session_id` varchar(128) not null,
    `username` varchar(255) not null,
    `session_expires` datetime not null,
    `session_data` text,
    `session_type` tinyint(1) not null default 0,
    `created_at` timestamp not null default current_timestamp,
    `updated_at` timestamp not null default current_timestamp on update current_timestamp,
    primary key (`session_id`),
    key (`session_expires`)
) engine=InnoDB default charset=utf8mb4;

create table if not exists `admin` (
    `id` int(11) not null auto_increment,
    `name` varchar(255) not null,
    `username` varchar(255) not null,
    `password` varchar(255) not null,
    `created_at` timestamp not null default current_timestamp,
    `updated_at` timestamp not null default current_timestamp on update current_timestamp,
    primary key (`id`),
    UNIQUE (`username`)
) engine=InnoDB default charset=utf8mb4;

create table if not exists `student` (
    `id` int(11) not null auto_increment,
    `name` varchar(255) not null,
    `username` varchar(255) not null,
    `password` varchar(255) not null,
    `failed_attempts` int not null default 0,
    `last_login` timestamp,
    `created_at` timestamp not null default current_timestamp,
    `updated_at` timestamp not null default current_timestamp on update current_timestamp,
    primary key (`id`),
    UNIQUE (`username`)
) engine=InnoDB default charset=utf8mb4;

create table if not exists `subject` (
    `id` int(11) not null auto_increment,
    `name` varchar(255) not null,
    `created_at` timestamp not null default current_timestamp,
    `updated_at` timestamp not null default current_timestamp on update current_timestamp,
    primary key (`id`),
    UNIQUE (`name`)
) engine=InnoDB default charset=utf8mb4;

create table if not exists `exam_paper` (
    `id` int(11) not null auto_increment,
    `uuid` char(36) not null,
    `subject_id` int(11) not null,
    `name` varchar(255) not null,
    `status` enum('preparing', 'freezed') not null default 'preparing',
    `total_questions` int not null default 0,
    `total_time_mins` int not null default 0,
    `created_at` timestamp not null default current_timestamp,
    `updated_at` timestamp not null default current_timestamp on update current_timestamp,
    primary key (`id`),
    UNIQUE (`name`),
    foreign key (`subject_id`) references `subject` (`id`) on delete cascade
) engine=InnoDB default charset=utf8mb4;

ALTER TABLE exam_paper ADD CONSTRAINT exam_paper_ux_1 UNIQUE KEY (uuid);

create table if not exists `exam_paper_question` (
    `id` int(11) not null auto_increment,
    `exam_paper_id` int(11) not null,
    `question` text not null,
    `answer` char(1),
    `explanation` text,
    `course_topic` text,
    `choice_a` text,
    `choice_b` text,
    `choice_c` text,
    `choice_d` text,
    `time_secs` int NOT NULL DEFAULT 0,
    `created_at` timestamp not null default current_timestamp,
    `updated_at` timestamp not null default current_timestamp on update current_timestamp,
    primary key (`id`),
    foreign key (`exam_paper_id`) references `exam_paper` (`id`) on delete cascade
) engine=InnoDB default charset=utf8mb4;

create table if not exists `student_exam` (
    `id` int(11) not null auto_increment,
    `uuid` char(36) not null,
    `student_id` int(11) not null,
    `exam_paper_id` int(11) not null,
    `status` enum('pending', 'started', 'completed') not null default 'pending',
    `score` int not null default 0,
    `total_questions` int not null default 0,
    `total_time_mins` int not null default 0,
    `start_time` timestamp not null default current_timestamp,
    `end_time` timestamp not null default current_timestamp,
    `created_at` timestamp not null default current_timestamp,
    `updated_at` timestamp not null default current_timestamp on update current_timestamp,
    primary key (`id`),
    UNIQUE (`uuid`),
    foreign key (`student_id`) references `student` (`id`) on delete cascade,
    foreign key (`exam_paper_id`) references `exam_paper` (`id`) on delete cascade
) engine=InnoDB default charset=utf8mb4;

ALTER TABLE student_exam ADD CONSTRAINT student_exam_ux_1 UNIQUE KEY (student_id, exam_paper_id);

create table if not exists `student_exam_answer` (
    `id` int(11) not null auto_increment,
    `student_exam_id` int(11) not null,
    `exam_paper_question_id` int(11) not null,
    `answer` char(1),
    `is_correct` tinyint(1) not null default 0,
    `created_at` timestamp not null default current_timestamp,
    `updated_at` timestamp not null default current_timestamp on update current_timestamp,
    primary key (`id`),
    foreign key (`student_exam_id`) references `student_exam` (`id`) on delete cascade,
    foreign key (`exam_paper_question_id`) references `exam_paper_question` (`id`) on delete cascade
) engine=InnoDB default charset=utf8mb4;

ALTER TABLE student_exam_answer ADD CONSTRAINT student_exam_answer_ux_1 UNIQUE KEY (student_exam_id, exam_paper_question_id);

ALTER TABLE student ADD email varchar(255);

create table if not exists `exam_paper_class` (
    `id` int(11) not null auto_increment,
    `exam_paper_id` int(11) not null,
    `class_id` varchar(255) not null,
    primary key (`id`),
    foreign key (`exam_paper_id`) references `exam_paper` (`id`) on delete cascade
) engine=InnoDB default charset=utf8mb4;

create table if not exists `student_class` (
    `id` int(11) not null auto_increment,
    `student_id` int(11) not null,
    `class_id` varchar(255) not null,
    primary key (`id`),
    foreign key (`student_id`) references `student` (`id`) on delete cascade
) engine=InnoDB default charset=utf8mb4;
