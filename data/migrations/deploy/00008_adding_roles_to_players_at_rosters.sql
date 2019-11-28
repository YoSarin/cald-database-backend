ALTER TABLE player_at_roster
    ADD COLUMN role VARCHAR(255);

UPDATE player_at_roster SET role = "player";

ALTER TABLE player_at_roster MODIFY COLUMN role VARCHAR(255) NOT NULL;
