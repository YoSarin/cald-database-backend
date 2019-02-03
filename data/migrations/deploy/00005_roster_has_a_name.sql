ALTER TABLE roster
    ADD CONSTRAINT unique_roster_name_per_tournament_and_team UNIQUE (tournament_belongs_to_league_and_division_id, team_id, name);
