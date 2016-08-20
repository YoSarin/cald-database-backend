CREATE TABLE IF NOT EXISTS user (
    id int AUTO_INCREMENT PRIMARY KEY,
    email varchar(255),
    password char(64) NOT NULL,
    salt char(32) NOT NULL,
    UNIQUE KEY unique_email (email)
);

CREATE TABLE IF NOT EXISTS privilege (
    id int AUTO_INCREMENT PRIMARY KEY,
    description TEXT,
    name varchar(255)
);

CREATE TABLE IF NOT EXISTS user_has_privilege (
    id int AUTO_INCREMENT PRIMARY KEY,
    user_id int,
    privilege_id int,
    entity_id int DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (privilege_id) REFERENCES privilege(id)
);

CREATE TABLE IF NOT EXISTS player (
    id int AUTO_INCREMENT PRIMARY KEY,
    first_name varchar(255),
    last_name varchar(255),
    birth_date DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    email varchar(255),
    phone varchar(32),
    sex ENUM('male', 'female') DEFAULT NULL,
    state ENUM('active', 'inactive', 'deleted')
);

CREATE TABLE IF NOT EXISTS league (
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(255),
    cond text default null,
    cald_fee boolean
);

CREATE TABLE IF NOT EXISTS season (
    id int AUTO_INCREMENT PRIMARY KEY,
    year DATETIME NOT NULL,
    cald_fee int
);

CREATE TABLE IF NOT EXISTS tournament (
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(255),
    date DATETIME,
    league_id int,
    season_id int,
    FOREIGN KEY (league_id) REFERENCES league(id),
    FOREIGN KEY (season_id) REFERENCES season(id)
);

CREATE TABLE IF NOT EXISTS team (
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(255),
    founded_at DATETIME default NULL,
    city varchar(255),
    www varchar(255),
    email varchar(255)
);

CREATE TABLE IF NOT EXISTS roster (
    id int AUTO_INCREMENT PRIMARY KEY,
    team_id int,
    tournament_id int,
    seeding int DEFAULT NULL,
    final_result int DEFAULT NULL,
    FOREIGN KEY(team_id) REFERENCES team(id),
    FOREIGN KEY(tournament_id) REFERENCES tournament(id)
);

CREATE TABLE IF NOT EXISTS player_at_team (
    id int AUTO_INCREMENT PRIMARY KEY,
    team_id int NOT NULL,
    player_id int NOT NULL,
    since DATETIME default NULL,
    valid boolean default true,
    FOREIGN KEY(team_id) REFERENCES team(id),
    FOREIGN KEY(player_id) REFERENCES player(id)
);

CREATE TABLE IF NOT EXISTS team_representative (
    id int AUTO_INCREMENT PRIMARY KEY,
    player_at_team_id int NOT NULL,
    function ENUM('captain', 'contact'),
    FOREIGN KEY(player_at_team_id) REFERENCES player_at_team(id)
);

CREATE TABLE IF NOT EXISTS player_at_roster (
    id int AUTO_INCREMENT PRIMARY KEY,
    roster_id int NOT NULL,
    player_id int NOT NULL,
    FOREIGN KEY(roster_id) REFERENCES roster(id),
    FOREIGN KEY(player_id) REFERENCES player(id)
);

CREATE TABLE IF NOT EXISTS highschool (
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(255),
    city varchar(255)
);

CREATE TABLE IF NOT EXISTS player_at_highschool (
    id int AUTO_INCREMENT PRIMARY KEY,
    player_id int,
    highschool_id int,
    since DATETIME DEFAULT NULL,
    valid boolean DEFAULT true,
    FOREIGN KEY (player_id) REFERENCES player(id),
    FOREIGN KEY (highschool_id) REFERENCES highschool(id)
);


CREATE TABLE IF NOT EXISTS fee (
    id int AUTO_INCREMENT PRIMARY KEY,
    team_id int,
    season_id int,
    paid_at DATETIME DEFAULT NULL,
    fee int,
    vs int,
    FOREIGN KEY (team_id) REFERENCES team(id),
    FOREIGN KEY (season_id) REFERENCES season(id)
);
