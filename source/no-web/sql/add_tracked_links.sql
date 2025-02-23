create table tracked_links (
    id     int unsigned not null primary key auto_increment,
    msg_id int unsigned not null,
    url    varchar(255) not null,
    action varchar(255) not null,
    target varchar(255) not null
);

