

-- Author:  eoneil
-- Created: Aug 7, 2019
-- This treats a tag as an entity, allowing the possibility of
-- having a tag with no todos related to it.
-- Alternatively, we could treat tags as weak entities of todos,
-- dependent for their identity and existence on their todo. The
-- server on pg. 207 and the later mongodb setup handle tags this way.

-- create database tododb;
drop table if exists todo_tags;
drop table if exists todos;
drop table if exists tags;

create table todos (
id int auto_increment,
description varchar(100),
primary key (id)
);

create table tags (
id int auto_increment,
name varchar(20),
primary key (id),
unique(name)
);

create table todo_tags (
todoid int not null,
tagid int not null,
primary key(todoid, tagid),
foreign key(todoid) references todos(id),
foreign key(tagid) references tags(id)
);
