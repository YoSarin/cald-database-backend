CREATE TABLE nationality (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE,
    country_name VARCHAR(255),
    PRIMARY KEY(id)
);

CREATE TABLE address (
    id INT NOT NULL AUTO_INCREMENT,
    type ENUM('permanent residence', 'residence in czechia'),
    player INT NOT NULL,
    city VARCHAR(255),
    street VARCHAR(255),
    zip_code VARCHAR(10),
    country VARCHAR(255),
    PRIMARY KEY(id),
    CONSTRAINT address_ibfk1 FOREIGN KEY (player) REFERENCES player(id)
);

ALTER TABLE player
    ADD COLUMN nationality INT,
    ADD COLUMN gdpr_consent BOOL NOT NULL DEFAULT FALSE,
    ADD CONSTRAINT player_ibfk1 FOREIGN KEY (nationality) REFERENCES nationality(id);
