SET NAMES 'utf8';
SET CHARACTER SET utf8;

DROP DATABASE IF EXISTS :new_schema_name:;
CREATE DATABASE IF NOT EXISTS :new_schema_name:;

CREATE TABLE IF NOT EXISTS :new_schema_name:.team (
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(255),
    founded_at DATETIME default NULL,
    city varchar(255),
    www varchar(255),
    email varchar(255),
    active boolean default true
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT t.id, t.name, t.dateOfRegistration as founded_at, t.city, t.www, t.email, (tt.element is NULL) as active
FROM Team t
LEFT JOIN Team_tags tt ON tt.Team_id = t.id and tt.element = 'inactive';


CREATE TABLE IF NOT EXISTS :new_schema_name:.season(
    id int AUTO_INCREMENT PRIMARY KEY,
    start DATETIME NOT NULL
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT null as id, name, startDate as start FROM Season;


CREATE TABLE IF NOT EXISTS :new_schema_name:.player (
    id int AUTO_INCREMENT PRIMARY KEY,
    first_name varchar(255),
    last_name varchar(255),
    birth_date DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    email varchar(255),
    phone varchar(32),
    sex ENUM('male', 'female') DEFAULT NULL,
    state ENUM('active', 'inactive', 'deleted')
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT
	m.id, m.firstName as first_name, m.surname as last_name,
	m.birthDate as birth_date, if (m.dateOfRegistration is NULL, NOW(), m.dateOfRegistration) as created_at,
	m.email, null as phone, IF(m.gender = 1, 'male', 'female') as sex, 'active' as state
FROM Member m;

CREATE TABLE IF NOT EXISTS :new_schema_name:.player_at_team (
    id int AUTO_INCREMENT PRIMARY KEY,
    team_id int NOT NULL,
    player_id int NOT NULL,
    first_season INT,
    last_season INT DEFAULT NULL,
    valid boolean default true,
    FOREIGN KEY(team_id) REFERENCES team(id),
    FOREIGN KEY(player_id) REFERENCES player(id),
    FOREIGN KEY(first_season) REFERENCES season(id),
    FOREIGN KEY(last_season) REFERENCES season(id)
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT
	m.id, m.team_id, m.user_id as player_id, COALESCE(s.id, 1) as first_season, null as until, (m.id = m2.id) as valid
FROM TeamMembership m
LEFT JOIN (SELECT max(id) as id, user_id FROM TeamMembership GROUP BY user_id) m2 ON m2.user_id = m.user_id
LEFT JOIN :new_schema_name:.season s ON s.start <= m.dateOfRegistration
WHERE s.id IN (SELECT MAX(id) FROM :new_schema_name:.season WHERE start <= m.dateOfRegistration) OR s.id IS NULL;


CREATE TABLE IF NOT EXISTS :new_schema_name:.team_representative (
    id int AUTO_INCREMENT PRIMARY KEY,
    player_at_team_id int NOT NULL,
    function ENUM('captain', 'contact'),
    FOREIGN KEY(player_at_team_id) REFERENCES player_at_team(id)
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT
	null as id, m.id as player_at_team_id, 'captain' as function
	FROM Team
	INNER JOIN TeamMembership m ON Team.captain_id = m.user_id and Team.id = m.team_id;

CREATE TABLE IF NOT EXISTS :new_schema_name:.tournament (
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(255),
    date DATETIME,
    season_id int,
    location varchar(255),
    duration int,
    deleted boolean DEFAULT false,
    FOREIGN KEY (season_id) REFERENCES season(id)
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT t.id, t.name, t.date, t.venue as location, t.duration, s.id as season_id, false as deleted
FROM Tournament t
LEFT JOIN :new_schema_name:.season s ON s.start < t.date and ((SELECT min(s2.start) FROM :new_schema_name:.season s2 WHERE s2.start > s.start) > t.date OR (SELECT min(s2.start) FROM :new_schema_name:.season s2 WHERE s2.start > s.start) is null);

CREATE TABLE IF NOT EXISTS :new_schema_name:.league (
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(255),
    cond text default null
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT
	null as id, 'Mistrovství ČR' as name, null as cond UNION ALL SELECT
	null as id, 'Halové Mistrovství ČR' as name, null as cond UNION ALL SELECT
	null as id, 'Středoškolka' as name, '{"player":{"highschool":true}, "team":{"same_highschool"}}}' as cond UNION ALL SELECT
	null as id, 'U23' as name, '{"player":{"age":23}}}' as cond;

CREATE TABLE IF NOT EXISTS :new_schema_name:.division (
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(255),
    cond text default null
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT
	null as id, 'mixed' as name, null as cond UNION ALL SELECT
	null as id, 'open' as name, null as cond UNION ALL SELECT
	null as id, 'women' as name, '{"player":{"fields":{"sex":"female"}}}' as cond;

CREATE TABLE IF NOT EXISTS :new_schema_name:.tournament_belongs_to_league_and_division (
    id int AUTO_INCREMENT PRIMARY KEY,
    league_id int,
    division_id int,
    tournament_id int,
    FOREIGN KEY (league_id) REFERENCES league(id),
    FOREIGN KEY (tournament_id) REFERENCES tournament(id),
    FOREIGN KEY (division_id) REFERENCES division(id)
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT null as id, IF(l_out.id is not null, l_out.id, l_in.id) as league_id, d.id as division_id, t.Tournament_id as tournament_id
FROM Tournament_divisions t
LEFT JOIN Tournament tr ON tr.id = t.Tournament_id
LEFT JOIN :new_schema_name:.division d ON d.name = t.element
LEFT JOIN :new_schema_name:.league l_out ON l_out.name = 'Mistrovství ČR' and month(tr.date) > 3 and month(tr.date) < 11
LEFT JOIN :new_schema_name:.league l_in ON l_in.name = 'Halové Mistrovství ČR' and (month(tr.date) <= 3 or month(tr.date) >= 11);

CREATE TABLE IF NOT EXISTS :new_schema_name:.fee (
    id int AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    since_season int,
    amount int,
    type ENUM('player_per_season', 'player_per_tournament', 'team_per_season', 'team_per_tournament')
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT
	-- null as id, "ČALD poplatek 2006+" as name, "2005-11-01 00:00:00" as since, 150 as amount, 'player_per_season' as type UNION ALL SELECT
	null as id, "ČALD poplatek 2007+" as name, 1 as since_season, 200 as amount, 'player_per_season' as type UNION ALL SELECT
	null as id, "ČALD poplatek 2010+" as name, 2 as since_season, 250 as amount, 'player_per_season' as type UNION ALL SELECT
	null as id, "ČALD poplatek 2013+" as name, 7 as since_season, 350 as amount, 'player_per_season' as type;

CREATE TABLE IF NOT EXISTS :new_schema_name:.player_fee_change (
  id int AUTO_INCREMENT PRIMARY KEY,
  player_id int,
  season_id int,
  amount int,
  FOREIGN KEY (player_id) REFERENCES player(id),
  FOREIGN KEY (season_id) REFERENCES season(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS :new_schema_name:.fee_needed_for_league (
    id int AUTO_INCREMENT PRIMARY KEY,
	league_id int,
	fee_id int,
    since_season int,
    valid boolean DEFAULT true,
    FOREIGN KEY (league_id) REFERENCES league(id),
    FOREIGN KEY (fee_id) REFERENCES fee(id),
    FOREIGN KEY (since_season) REFERENCES season(id)
)
COMMENT "Ve které lize musí člověk/tým hrát, aby pro něj poplatek platil. Pokud poplatek patří do více lig, stačí aby hrál jednu z nich. Stejně tak, pokud hraje hráč více lig se stejným poplatkem, platí pouze jednou"
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT null as id, l.id as league_id, f.id as fee_id, f.since_season as since_season, false as valid
FROM :new_schema_name:.league l, :new_schema_name:.fee f where l.name in ('Mistrovství ČR', 'Halové Mistrovství ČR');

UPDATE :new_schema_name:.fee_needed_for_league SET valid = true WHERE since_season = (SELECT m FROM (SELECT max(since_season) as m FROM :new_schema_name:.fee_needed_for_league) t );

ALTER TABLE :new_schema_name:.fee DROP COLUMN since_season;

CREATE TABLE IF NOT EXISTS :new_schema_name:.roster (
    id int AUTO_INCREMENT PRIMARY KEY,
    team_id int,
    tournament_belongs_to_league_and_division_id int,
    name VARCHAR(255) DEFAULT NULL,
    seeding int DEFAULT NULL,
    final_result int DEFAULT NULL,
    FOREIGN KEY(team_id) REFERENCES team(id),
    FOREIGN KEY(tournament_belongs_to_league_and_division_id) REFERENCES tournament_belongs_to_league_and_division(id)
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT
	null as id, r.team_id, tld.id as tournament_belongs_to_league_and_division_id, trm.teamName as name, null as seeding, null as final_result
FROM TeamRoster r
LEFT JOIN TeamRosterMember trm ON trm.roster_id = r.id
LEFT JOIN :new_schema_name:.tournament_belongs_to_league_and_division tld ON r.tournament_id = tld.tournament_id
LEFT JOIN :new_schema_name:.division d ON d.id = tld.division_id
WHERE LOWER(trm.teamName) LIKE CONCAT(d.name, '%')
GROUP BY r.id, trm.teamName;

CREATE TABLE IF NOT EXISTS :new_schema_name:.player_at_roster (
    id int AUTO_INCREMENT PRIMARY KEY,
    roster_id int NOT NULL,
    player_id int NOT NULL,
    FOREIGN KEY(roster_id) REFERENCES roster(id) ON DELETE CASCADE,
    FOREIGN KEY(player_id) REFERENCES player(id)
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT
	null as id, cr.id as roster_id, m.id as player_id
FROM TeamRoster r
LEFT JOIN TeamRosterMember trm ON trm.roster_id = r.id
LEFT JOIN Member m ON m.id = trm.member_id
LEFT JOIN :new_schema_name:.tournament_belongs_to_league_and_division tld ON r.tournament_id = tld.tournament_id
LEFT JOIN :new_schema_name:.division d ON d.id = tld.division_id
LEFT JOIN :new_schema_name:.roster cr ON cr.tournament_belongs_to_league_and_division_id = tld.id AND cr.team_id = r.team_id AND cr.name = trm.teamName
WHERE LOWER(trm.teamName) LIKE CONCAT(d.name, '%');

CREATE TABLE IF NOT EXISTS :new_schema_name:.fee_payments (
    id int AUTO_INCREMENT PRIMARY KEY,
    team_id int,
    season_id int,
    paid_at DATETIME DEFAULT NULL,
    paid_amount int,
    variable_symbol varchar(255),
    FOREIGN KEY (team_id) REFERENCES team(id),
    FOREIGN KEY (season_id) REFERENCES season(id)
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
(SELECT
	null as id, p.team_id, s.id as season_id, p.date as paid_at, p.amount * count(pm.members_id) as paid_amount, p.variableSymbol as variable_symbol
FROM PendingPayment p
LEFT JOIN PendingPayment_Member pm ON pm.PendingPayment_id = p.id
LEFT JOIN :new_schema_name:.season s ON s.start < p.date and ((SELECT min(s2.start) FROM :new_schema_name:.season s2 WHERE s2.start > s.start) > p.date OR (SELECT min(s2.start) FROM :new_schema_name:.season s2 WHERE s2.start > s.start) is null)
WHERE p.validated
GROUP BY p.id
);

CREATE TABLE IF NOT EXISTS :new_schema_name:.highschool (
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(255),
    city varchar(255)
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;

CREATE TABLE IF NOT EXISTS :new_schema_name:.player_at_highschool (
    id int AUTO_INCREMENT PRIMARY KEY,
    player_id int,
    highschool_id int,
    since_season int,
    valid boolean DEFAULT true,
    FOREIGN KEY (player_id) REFERENCES player(id),
    FOREIGN KEY (highschool_id) REFERENCES highschool(id),
    FOREIGN KEY (since_season) REFERENCES season(id)
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;

CREATE TABLE IF NOT EXISTS :new_schema_name:.user (
    id int AUTO_INCREMENT PRIMARY KEY,
    login varchar(255),
    email varchar(255),
    password char(64) NOT NULL,
    salt char(32) NOT NULL,
    created_at DATETIME NOT NUll,
    state ENUM('waiting_for_confirmation', 'confirmed', 'blocked', 'password_reset'),
    UNIQUE KEY unique_login (login)
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT
	null as id, u.email, u.login,
	@salt := MD5(CONCAT(MD5(CONCAT(MD5(RAND()), u.login)), 'import')) as salt,
	SHA2(CONCAT(SHA2(CONCAT(SHA2(u.password, 256), @salt), 256), u.login), 256) as password,
	IFNULL(u.dateOfRegistration, NOW()) as created_at,
	'confirmed' as state
FROM User u;

CREATE TABLE IF NOT EXISTS :new_schema_name:.token (
    id int AUTO_INCREMENT PRIMARY KEY,
    user_id int NOT NULL,
    token varchar(64),
    valid_until DATETIME DEFAULT NULL,
    type ENUM('email_verification', 'login'),
    FOREIGN KEY (user_id) REFERENCES user(id)
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;

CREATE TABLE IF NOT EXISTS :new_schema_name:.user_has_privilege (
    id int AUTO_INCREMENT PRIMARY KEY,
    user_id int,
    privilege ENUM('admin', 'edit', 'view'),
    entity ENUM('team', 'highschool') DEFAULT NULL,
    entity_id int DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id)
)
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
SELECT
	null as id, u.id as user_id, 'edit' as privilege, 'team' as entity, t.id as entity_id
FROM Team t
LEFT JOIN :new_schema_name:.user u ON u.login = t.agent_login
WHERE t.agent_login IS NOT NULL
;

INSERT INTO :new_schema_name:.user (email,login,salt,password,created_at,state) VALUES ('noone@noone.noone','admin','e49f68e084c1cf6507602928dd58b467','7043560ddbe621ddf2dbb0cd83ec8d1822419cd52a86abd5a74fac9006385a21',NOW(),'confirmed');
INSERT INTO :new_schema_name:.user_has_privilege (user_id, privilege) SELECT id as user_id, 'admin' as privilege FROM :new_schema_name:.user WHERE email = 'noone@noone.noone';

/** DEV-ONLY:
GRANT ALL ON :new_schema_name:.* TO 'cald'@'%';

INSERT INTO :new_schema_name:.token (user_id, token, valid_until, type)
    SELECT id, 'token', '2999-01-01 00:00:00', 'login' FROM :new_schema_name:.user WHERE email = 'noone@noone.noone';

CREATE OR REPLACE VIEW :new_schema_name:.rosters AS
    select distinct s.name as season_name, tm.name as team_name, p.id, p.last_name
    from :new_schema_name:.tournament t
    left join :new_schema_name:.season s on t.season_id = s.id
    left join :new_schema_name:.tournament_belongs_to_league_and_division tld on tld.tournament_id = t.id
    left join :new_schema_name:.roster r on r.tournament_belongs_to_league_and_division_id = tld.id
    left join :new_schema_name:.player_at_roster pr on pr.roster_id = r.id
    left join :new_schema_name:.player p on pr.player_id = p.id
    left join :new_schema_name:.team tm on r.team_id = tm.id
:DEV-ONLY **/
