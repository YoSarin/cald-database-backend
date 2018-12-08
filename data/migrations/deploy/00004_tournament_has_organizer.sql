ALTER TABLE tournament
    ADD COLUMN organizing_team_id integer NULL DEFAULT NULL;

-- i am missing constrint here, because when i tried to create it, it all fust fet apart with 
-- error "ERROR 1292 (22007): Incorrect datetime value: '0000-00-00 00:00:00' for column 'date' at row 97"
    
ALTER TABLE roster
    ADD COLUMN finalized boolean DEFAULT false;
