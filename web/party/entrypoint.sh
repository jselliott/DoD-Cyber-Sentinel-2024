#!/bin/bash

# Initialize SQLite database
touch /var/www/html/database/party.db
chmod 666 /var/www/html/database/party.db

sqlite3 /var/www/html/database/party.db <<'EOF'
CREATE TABLE IF NOT EXISTS characters (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL,
    image TEXT NOT NULL,
    hidden INTEGER NOT NULL
);

INSERT INTO characters (title,image,hidden) VALUES ('Cyberlord','boss.png',1);
INSERT INTO characters (title,image,hidden) VALUES ('Analyst','0.png',0);
INSERT INTO characters (title,image,hidden) VALUES ('Pentester','1.png',0);
INSERT INTO characters (title,image,hidden) VALUES ('Malware Author','2.png',0);
INSERT INTO characters (title,image,hidden) VALUES ('RMF Expert','3.png',0);
INSERT INTO characters (title,image,hidden) VALUES ('CISO','4.png',0);
INSERT INTO characters (title,image,hidden) VALUES ('Incident Responder','5.png',0);
INSERT INTO characters (title,image,hidden) VALUES ('Spear Phisher','6.png',0);
INSERT INTO characters (title,image,hidden) VALUES ('Ransomware','7.png',0);
INSERT INTO characters (title,image,hidden) VALUES ('Remote Dev','8.png',0);

CREATE TABLE IF NOT EXISTS saved_characters (
    id INTEGER PRIMARY KEY,
    uuid TEXT NOT NULL,
    name TEXT NOT NULL,
    bio TEXT NOT NULL,
    character_id INTEGER NOT NULL,
    str INTEGER NOT NULL,
    dex INTEGER NOT NULL,
    con INTEGER NOT NULL,
    intl INTEGER NOT NULL,
    wis INTEGER NOT NULL,
    chr INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS promo_codes (
    id INTEGER PRIMARY KEY,
    code TEXT NOT NULL
);

INSERT INTO saved_characters (uuid,name,bio,character_id,str,dex,con,int,wis,chr) VALUES ('af7f3209-a281-49ee-96ab-c40c2217902c','Challenge Dev','A super cool guy!',1,999,999,999,999,999,999);
INSERT INTO promo_codes (code) VALUES ('543ed6552c9f75bae1380a1c52559012');

EOF

# Start Apache server
apache2-foreground