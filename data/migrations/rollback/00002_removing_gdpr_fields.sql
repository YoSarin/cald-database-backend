ALTER TABLE player
    DROP FOREIGN KEY player_ibfk1,
    DROP COLUMN nationality_id,
    DROP COLUMN gdpr_consent;

DROP TABLE nationality;
DROP TABLE address;
