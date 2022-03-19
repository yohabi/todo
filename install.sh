#!/bin/bash

if [ $# -gt 1 ];
  then
	DB_NAME=TASK_DB_$1
	DB_USER=$1
	DB_PASSWORD=$2
	PGLINK="postgresql://$DB_USER:$DB_PASSWORD@localhost/$DB_NAME"

	# USER, PASSWORD
	sudo -u postgres dropuser $DB_USER; sudo -u postgres dropdb $DB_NAME
    printf "Creating user %s\n" $DB_USER
	sudo -u postgres createuser $DB_USER
	sudo -u postgres psql -c "ALTER USER $DB_USER WITH PASSWORD '$DB_PASSWORD'"
	sudo -u postgres createdb -O $DB_USER $DB_NAME
	printf "\nhost\t$DB_NAME\t$DB_USER\t192.168.1.0/24\tpassword\n" >> /etc/postgresql/13/main/pg_hba.conf
	sudo -i -u postgres psql -c "SELECT pg_reload_conf()"

	# DATABASE
	printf "Making DATABASE structure...\n"
	psql $PGLINK -c "DROP TABLE IF EXISTS ta_status, ta_task"
	psql $PGLINK -c "CREATE TABLE ta_status (status_id INT NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY, status_description VARCHAR(100) NOT NULL)"
	psql $PGLINK -c "CREATE TABLE ta_task (
	task_id INT NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
	task_priority INT NOT NULL DEFAULT 0,
	task_status INT NOT NULL DEFAULT 2,
	task_description VARCHAR(300),
	task_comment VARCHAR(300),
	task_date_open timestamp without time zone NOT NULL DEFAULT current_timestamp,
	task_date_deadline timestamp without time zone,
	task_date_close timestamp without time zone
	--CONSTRAINT fk_status FOREIGN KEY (task_status) REFERENCES ta_status (status_id)
	)"
	psql $PGLINK -c "INSERT INTO ta_status (status_description) VALUES ('удалена'), ('открыта'), ('закрыта')"

	# WEB-INTERFACE
	rm -rf /var/www/html/todo/$DB_USER
	mkdir /var/www/html/todo/$DB_USER
	chmod 777 /var/www/html/todo/$DB_USER
	cp ./src/* /var/www/html/todo/$DB_USER/
	printf "<?php\n\$page_password = \"$DB_PASSWORD\";\n\$db_user = \"$DB_USER\";\n\$db_password = \"$DB_PASSWORD\";\n\$db_name = \"$DB_NAME\";\n?>" > /var/www/html/todo/$DB_USER/secret.php
	chmod 666 /var/www/html/todo/$DB_USER/*
  else
    printf "ERROR: no arguments\nUse 'sudo install.sh <username> <password>'\n"
fi
