create table dr_hosts (
    id int unsigned not null primary key auto_increment,
    address varchar(255) not null,
    password varchar(255) not null,
    status varchar(255) not null default 'pending',
    message varchar(255)
);
