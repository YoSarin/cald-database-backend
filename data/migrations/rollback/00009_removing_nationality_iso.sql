ALTER TABLE nationality
    DROP COLUMN iso_code;

UPDATE nationality SET country_name = "Velká Británie" WHERE country_name = "Spojené království";
UPDATE nationality SET country_name = "Thajwan" WHERE country_name = "Tchaj-wan";
