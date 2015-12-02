CREATE TABLE IF NOT EXISTS domains (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(255),
  alt_names VARCHAR(255),
  serial INT,
  header TEXT,
  options TEXT,
  enabled BOOLEAN DEFAULT 1,
  last_updated TIMESTAMP,
  PRIMARY KEY(id)

);

CREATE TABLE IF NOT EXISTS networks (
  id INT NOT NULL AUTO_INCREMENT,
  domain_id INT REFERENCES domains(id),
  name VARCHAR(255),
  network VARCHAR(255),
  netmask VARCHAR(255),
  vlan INT,
  options TEXT,
  enabled BOOLEAN DEFAULT 1,
  last_updated TIMESTAMP,
  PRIMARY KEY (id)
);


ALTER TABLE namespace ADD network_id INT REFERENCES networks(id);

