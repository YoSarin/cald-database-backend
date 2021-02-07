insert into season(id, name, start) values
    (1, 'season 1', '2015-01-01'), (2, 'season 2', '2016-01-01');

insert into team(id, name) values
    (1, 'team 1'), (2, 'team 2');

insert into league(id, name) values
    (1, 'league 1'), (2, 'league 2');

insert into division(id, name) values
    (1, 'division 1'), (2, 'division 2');

insert into tournament(id, name, season_id) values
    (1, 'turnaj 1', 2), (2, 'turnaj 2', 2), (3, 'turnaj 3', 2), (4, 'turnaj 4', 2), (5, 'turnaj 5', 1);

insert into tournament_belongs_to_league_and_division(id, league_id, division_id, tournament_id) values
    (1, 1, 1, 1), (2, 2, 2, 2), (3, 1, 2, 3), (4, 2, 1, 4), (5, 1, 1, 5);

insert into player(id, first_name, last_name, sex) values
    (1, 'player 1', '', 'male'), (2, 'player 2', '', 'female'), (3, 'player 3', '', 'male'), (4, 'player 4', '', 'male');

insert into roster(id, team_id, tournament_belongs_to_league_and_division_id) values
    (1, 1, 1), (2, 2, 1), (3, 1, 2), (4, 2, 2);

insert into fee(id, name, amount) values
    (1, 'fee 1', 1000), (2, 'fee 2', 1001);

insert into fee_needed_for_league(id, fee_id, league_id, since) values
    (1, 1, 1, '2015-01-01'), (2, 2, 2, '2015-01-01');

insert into player_at_team(id, team_id, player_id, since) values
    (1, 1, 1, '2015-01-01'), (2, 1, 2, '2015-01-01'), (3, 2, 3, '2015-01-01'), (4, 2, 4, '2015-01-01');

insert into player_at_roster(id, roster_id, player_id) values
    (1, 1, 1), (2, 1, 2), (3, 1, 3), (4, 2, 4);

insert into user (id, login, password) values
    (1, 'admin', '');

insert into user_has_privilege(id, user_id, privilege, entity, entity_id) values
    (1, 1, 'admin', NULL, NULL);

insert into token(id, user_id, token, valid_until, type) values
    (1, 1, 'token', DATE_ADD(NOW(), INTERVAL 1 WEEK), 'login');

/*
left join player_fee_change pfc on pfc.player_id = pr.player_id and pfc.season_id = t.season_id
*/
