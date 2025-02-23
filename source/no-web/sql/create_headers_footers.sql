-- name: human-readable name
-- contents: content data
-- content_type: header, footer
create table extra_content (
    id           int unsigned not null primary key auto_increment,
    name         varchar(255) not null,
    content_type varchar(255) not null,
    is_default   tinyint(1) not null default 0
);

-- content_id: foreign key to extra_content
-- content_format: html, text
create table extra_content_data (
    id             int unsigned not null primary key auto_increment,
    content_id     int not null,
    content_format varchar(255) not null,
    data           text
);

