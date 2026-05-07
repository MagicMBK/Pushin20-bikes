CREATE DATABASE IF NOT EXISTS pushin20_bikes;
USE pushin20_bikes;

CREATE TABLE utente (
    id_utente     INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    ruolo         VARCHAR(20) NOT NULL,
    attivo        BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE cliente (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(50) NOT NULL,
    cognome    VARCHAR(50) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    telefono   VARCHAR(20),
    id_utente  INT NOT NULL UNIQUE,
    FOREIGN KEY (id_utente) REFERENCES utente(id_utente)
);

CREATE TABLE meccanico (
    id_meccanico     INT AUTO_INCREMENT PRIMARY KEY,
    nome             VARCHAR(50) NOT NULL,
    cognome          VARCHAR(50) NOT NULL,
    specializzazione VARCHAR(100),
    costo_orario     INT NOT NULL,
    id_utente        INT NOT NULL UNIQUE,
    FOREIGN KEY (id_utente) REFERENCES utente(id_utente)
);

CREATE TABLE moto (
    id_moto    INT AUTO_INCREMENT PRIMARY KEY,
    targa      VARCHAR(10) NOT NULL UNIQUE,
    marca      VARCHAR(50) NOT NULL,
    modello    VARCHAR(50) NOT NULL,
    anno       YEAR NOT NULL,
    id_cliente INT NOT NULL,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
);

CREATE TABLE fornitore (
    id_fornitore    INT AUTO_INCREMENT PRIMARY KEY,
    ragione_sociale VARCHAR(100) NOT NULL,
    partita_iva     VARCHAR(20) NOT NULL UNIQUE,
    email           VARCHAR(100)
);

CREATE TABLE pezzo_ricambio (
    id_pezzo       INT AUTO_INCREMENT PRIMARY KEY,
    codice         VARCHAR(30) NOT NULL UNIQUE,
    nome           VARCHAR(100) NOT NULL,
    quantita_stock INT NOT NULL DEFAULT 0,
    prezzo_vendita INT NOT NULL,
    scorta_minima  INT NOT NULL DEFAULT 5,
    id_fornitore   INT NOT NULL,
    FOREIGN KEY (id_fornitore) REFERENCES fornitore(id_fornitore)
);

CREATE TABLE intervento (
    id_intervento INT AUTO_INCREMENT PRIMARY KEY,
    data_ingresso DATE NOT NULL,
    data_uscita   DATE,
    stato         VARCHAR(20) NOT NULL DEFAULT 'attesa',
    costo_totale  INT DEFAULT 0,
    id_moto       INT NOT NULL,
    id_meccanico  INT NOT NULL,
    FOREIGN KEY (id_moto)      REFERENCES moto(id_moto),
    FOREIGN KEY (id_meccanico) REFERENCES meccanico(id_meccanico)
);

CREATE TABLE utilizzo_pezzo (
    id_intervento   INT NOT NULL,
    id_pezzo        INT NOT NULL,
    quantita        INT NOT NULL DEFAULT 1,
    prezzo_unitario INT NOT NULL,
    PRIMARY KEY (id_intervento, id_pezzo),
    FOREIGN KEY (id_intervento) REFERENCES intervento(id_intervento),
    FOREIGN KEY (id_pezzo)      REFERENCES pezzo_ricambio(id_pezzo)
);

CREATE TABLE ordine (
    id_ordine              INT AUTO_INCREMENT PRIMARY KEY,
    data_ordine            DATE NOT NULL,
    data_consegna_prevista DATE,
    stato                  VARCHAR(20) NOT NULL DEFAULT 'bozza',
    importo_totale         INT DEFAULT 0,
    id_fornitore           INT NOT NULL,
    FOREIGN KEY (id_fornitore) REFERENCES fornitore(id_fornitore)
);

CREATE TABLE dettaglio_ordine (
    id_ordine       INT NOT NULL,
    id_pezzo        INT NOT NULL,
    quantita        INT NOT NULL DEFAULT 1,
    prezzo_unitario INT NOT NULL,
    PRIMARY KEY (id_ordine, id_pezzo),
    FOREIGN KEY (id_ordine) REFERENCES ordine(id_ordine),
    FOREIGN KEY (id_pezzo)  REFERENCES pezzo_ricambio(id_pezzo)
);