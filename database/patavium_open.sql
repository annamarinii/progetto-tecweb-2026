DROP TABLE IF EXISTS BIGLIETTO;
DROP TABLE IF EXISTS ORDINE;
DROP TABLE IF EXISTS DOMANDA;
DROP TABLE IF EXISTS NEWS;
DROP TABLE IF EXISTS INCONTRO;
DROP TABLE IF EXISTS UTENTE;
DROP TABLE IF EXISTS FAQ;
DROP TABLE IF EXISTS CAMPIONE;


-- tabelle primarie
CREATE TABLE UTENTE (
                        idUtente INT AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(30) NOT NULL UNIQUE,
                        email VARCHAR(30) NOT NULL UNIQUE,
                        password VARCHAR(255) NOT NULL,
                        nome VARCHAR(30) NOT NULL,
                        cognome VARCHAR(30) NOT NULL,
                        isAdmin BOOLEAN DEFAULT FALSE
);

CREATE TABLE INCONTRO (
                          idIncontro INT AUTO_INCREMENT PRIMARY KEY,
                          data DATETIME NOT NULL,
                          sessione VARCHAR(20),
                          stadio VARCHAR(20) NOT NULL
);

-- tabelle secondarie
CREATE TABLE NEWS (
                      idNews INT AUTO_INCREMENT PRIMARY KEY,
                      titolo VARCHAR(100) NOT NULL,
                      testo TEXT NOT NULL,
                      data_pubblicazione DATETIME DEFAULT CURRENT_TIMESTAMP,
                      immagine VARCHAR(255) NOT NULL DEFAULT 'assets/images/default-news.webp',
                      alt_immagine VARCHAR(255) NOT NULL DEFAULT 'Immagine della news',
                      idAutore INT NOT NULL,
                      inEvidenza BOOLEAN DEFAULT FALSE,
                      FOREIGN KEY (idAutore) REFERENCES UTENTE(idUtente)
);

CREATE TABLE DOMANDA (
                         idDomanda INT AUTO_INCREMENT PRIMARY KEY,
                         testo_domanda TEXT NOT NULL,
                         testo_risposta TEXT,
                         lettura_admin BOOLEAN DEFAULT FALSE,
                         lettura_user BOOLEAN DEFAULT FALSE,
                         data_invio DATETIME DEFAULT CURRENT_TIMESTAMP,
                         idUtente INT NOT NULL,
                         FOREIGN KEY (idUtente) REFERENCES UTENTE(idUtente)
);

CREATE TABLE ORDINE (
                        numero_ordine INT AUTO_INCREMENT PRIMARY KEY,
                        totale INT NOT NULL,
                        data_acquisto DATETIME DEFAULT CURRENT_TIMESTAMP,
                        idUtente INT NOT NULL,
                        FOREIGN KEY (idUtente) REFERENCES UTENTE(idUtente)
);

CREATE TABLE BIGLIETTO (
                           idBiglietto INT AUTO_INCREMENT PRIMARY KEY,
                           prezzo DECIMAL (6,2) NOT NULL,
                           tribuna VARCHAR(20),
                           tipo VARCHAR(20) DEFAULT NULL,
                           numero_ordine INT DEFAULT NULL,
                           idIncontro INT NOT NULL,
                           FOREIGN KEY (numero_ordine) REFERENCES ORDINE(numero_ordine),
                           FOREIGN KEY (idIncontro) REFERENCES INCONTRO(idIncontro)
);

CREATE TABLE FAQ (
    idFaq INT AUTO_INCREMENT PRIMARY KEY,
    testo_domanda TEXT NOT NULL,
    testo_risposta TEXT NOT NULL,
    categoria VARCHAR(50) NOT NULL DEFAULT 'Regolamento'
);

CREATE TABLE CAMPIONE (
    idCampione   INT AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(60)  NOT NULL,
    categoria    VARCHAR(40)  NOT NULL,
    anno         SMALLINT     NOT NULL,
    immagine     VARCHAR(255) NOT NULL DEFAULT 'assets/images/logo1.webp',
    alt_immagine VARCHAR(255) NOT NULL DEFAULT 'Ritratto del campione',
    ordine       INT          NOT NULL DEFAULT 0
);


-- INSERT PER LE TABELLE (inserimento user user, admin admin)

INSERT INTO UTENTE (username, email, password, nome, cognome, isAdmin)
VALUES 
('admin', 'admin@pataviumopen.it', '$2y$10$hJs.Dy1/uAcVxtJDMjwE0OqvxWaiwysPVoOaTOs7eqFPNM4ObP8sW', 'admin', 'admin', 1),
('user', 'user@pataviumopen.it', '$2y$10$3c2HqNP45kB2OHZUPl3Zh.giWOM/hT0JFAriYHZPNIk7pw7zMYgeG', 'user', 'user', 0);

INSERT INTO INCONTRO (data, sessione, stadio) VALUES
-- Giorno 1 (18)
('2027-05-18 11:00:00', 'diurna', 'Giotto Court'),
('2027-05-18 19:00:00', 'serale', 'Patavium Arena'),

-- Giorno 2 (19)
('2027-05-19 11:00:00', 'diurna', 'Giotto Court'),
('2027-05-19 19:00:00', 'serale', 'Patavium Arena'),

-- Giorno 3 (20)
('2027-05-20 11:00:00', 'diurna', 'Giotto Court'),
('2027-05-20 19:00:00', 'serale', 'Patavium Arena'),

-- Giorno 4 (21)
('2027-05-21 11:00:00', 'diurna', 'Giotto Court'),
('2027-05-21 19:00:00', 'serale', 'Patavium Arena'),

-- Giorno 5 (22)
('2027-05-22 11:00:00', 'diurna', 'Giotto Court'),
('2027-05-22 19:00:00', 'serale', 'Patavium Arena'),

-- Giorno 6 (23)
('2027-05-23 11:00:00', 'diurna', 'Giotto Court'),
('2027-05-23 19:00:00', 'serale', 'Patavium Arena'),

-- Giorno 7 (24)
('2027-05-24 11:00:00', 'diurna', 'Giotto Court'),
('2027-05-24 19:00:00', 'serale', 'Patavium Arena'),

-- ground
('2027-05-18 09:00:00', 'ground', 'Accesso Ground'),
('2027-05-19 09:00:00', 'ground', 'Accesso Ground'),
('2027-05-20 09:00:00', 'ground', 'Accesso Ground'),
('2027-05-21 09:00:00', 'ground', 'Accesso Ground'),
('2027-05-22 09:00:00', 'ground', 'Accesso Ground'),
('2027-05-23 09:00:00', 'ground', 'Accesso Ground'),
('2027-05-24 09:00:00', 'ground', 'Accesso Ground');

-- verranno 560 biglietti totali divisi in 30 per la diurna e 50 per la serale
INSERT INTO BIGLIETTO (prezzo, tribuna, idIncontro) VALUES

-- ================= GIORNATA 1
-- Incontro 1 (30 Biglietti)
(150, 'Courtside Premium', 1), (150, 'Courtside Premium', 1), (150, 'Courtside Premium', 1),
(80, 'Tribuna Antenore', 1), (80, 'Tribuna Antenore', 1), (80, 'Tribuna Antenore', 1), (80, 'Tribuna Antenore', 1), (80, 'Tribuna Antenore', 1), (80, 'Tribuna Antenore', 1), (80, 'Tribuna Antenore', 1), (80, 'Tribuna Antenore', 1), (80, 'Tribuna Antenore', 1), (80, 'Tribuna Antenore', 1),
(50, 'Tribuna Fondo Campo', 1), (50, 'Tribuna Fondo Campo', 1), (50, 'Tribuna Fondo Campo', 1), (50, 'Tribuna Fondo Campo', 1), (50, 'Tribuna Fondo Campo', 1), (50, 'Tribuna Fondo Campo', 1), (50, 'Tribuna Fondo Campo', 1),
(30, 'Anello Superiore', 1), (30, 'Anello Superiore', 1), (30, 'Anello Superiore', 1), (30, 'Anello Superiore', 1), (30, 'Anello Superiore', 1), (30, 'Anello Superiore', 1), (30, 'Anello Superiore', 1), (30, 'Anello Superiore', 1), (30, 'Anello Superiore', 1), (30, 'Anello Superiore', 1),
-- Incontro 2 (50 Biglietti)
(150, 'Courtside Premium', 2), (150, 'Courtside Premium', 2), (150, 'Courtside Premium', 2), (150, 'Courtside Premium', 2), (150, 'Courtside Premium', 2),
(80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2), (80, 'Tribuna Antenore', 2),
(50, 'Tribuna Fondo Campo', 2), (50, 'Tribuna Fondo Campo', 2), (50, 'Tribuna Fondo Campo', 2), (50, 'Tribuna Fondo Campo', 2), (50, 'Tribuna Fondo Campo', 2), (50, 'Tribuna Fondo Campo', 2), (50, 'Tribuna Fondo Campo', 2), (50, 'Tribuna Fondo Campo', 2), (50, 'Tribuna Fondo Campo', 2), (50, 'Tribuna Fondo Campo', 2),
(30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2), (30, 'Anello Superiore', 2),

-- ================= GIORNATA 2
-- Incontro 3 (30 Biglietti)
(165, 'Courtside Premium', 3), (165, 'Courtside Premium', 3), (165, 'Courtside Premium', 3),
(95, 'Tribuna Antenore', 3), (95, 'Tribuna Antenore', 3), (95, 'Tribuna Antenore', 3), (95, 'Tribuna Antenore', 3), (95, 'Tribuna Antenore', 3), (95, 'Tribuna Antenore', 3), (95, 'Tribuna Antenore', 3), (95, 'Tribuna Antenore', 3), (95, 'Tribuna Antenore', 3), (95, 'Tribuna Antenore', 3),
(65, 'Tribuna Fondo Campo', 3), (65, 'Tribuna Fondo Campo', 3), (65, 'Tribuna Fondo Campo', 3), (65, 'Tribuna Fondo Campo', 3), (65, 'Tribuna Fondo Campo', 3), (65, 'Tribuna Fondo Campo', 3), (65, 'Tribuna Fondo Campo', 3),
(45, 'Anello Superiore', 3), (45, 'Anello Superiore', 3), (45, 'Anello Superiore', 3), (45, 'Anello Superiore', 3), (45, 'Anello Superiore', 3), (45, 'Anello Superiore', 3), (45, 'Anello Superiore', 3), (45, 'Anello Superiore', 3), (45, 'Anello Superiore', 3), (45, 'Anello Superiore', 3),
-- Incontro 4 (50 Biglietti)
(165, 'Courtside Premium', 4), (165, 'Courtside Premium', 4), (165, 'Courtside Premium', 4), (165, 'Courtside Premium', 4), (165, 'Courtside Premium', 4),
(95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4), (95, 'Tribuna Antenore', 4),
(65, 'Tribuna Fondo Campo', 4), (65, 'Tribuna Fondo Campo', 4), (65, 'Tribuna Fondo Campo', 4), (65, 'Tribuna Fondo Campo', 4), (65, 'Tribuna Fondo Campo', 4), (65, 'Tribuna Fondo Campo', 4), (65, 'Tribuna Fondo Campo', 4), (65, 'Tribuna Fondo Campo', 4), (65, 'Tribuna Fondo Campo', 4), (65, 'Tribuna Fondo Campo', 4),
(45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4), (45, 'Anello Superiore', 4),

-- ================= GIORNATA 3
-- Incontro 5 (30 Biglietti)
(180, 'Courtside Premium', 5), (180, 'Courtside Premium', 5), (180, 'Courtside Premium', 5),
(110, 'Tribuna Antenore', 5), (110, 'Tribuna Antenore', 5), (110, 'Tribuna Antenore', 5), (110, 'Tribuna Antenore', 5), (110, 'Tribuna Antenore', 5), (110, 'Tribuna Antenore', 5), (110, 'Tribuna Antenore', 5), (110, 'Tribuna Antenore', 5), (110, 'Tribuna Antenore', 5), (110, 'Tribuna Antenore', 5),
(80, 'Tribuna Fondo Campo', 5), (80, 'Tribuna Fondo Campo', 5), (80, 'Tribuna Fondo Campo', 5), (80, 'Tribuna Fondo Campo', 5), (80, 'Tribuna Fondo Campo', 5), (80, 'Tribuna Fondo Campo', 5), (80, 'Tribuna Fondo Campo', 5),
(60, 'Anello Superiore', 5), (60, 'Anello Superiore', 5), (60, 'Anello Superiore', 5), (60, 'Anello Superiore', 5), (60, 'Anello Superiore', 5), (60, 'Anello Superiore', 5), (60, 'Anello Superiore', 5), (60, 'Anello Superiore', 5), (60, 'Anello Superiore', 5), (60, 'Anello Superiore', 5),
-- Incontro 6 (50 Biglietti)
(180, 'Courtside Premium', 6), (180, 'Courtside Premium', 6), (180, 'Courtside Premium', 6), (180, 'Courtside Premium', 6), (180, 'Courtside Premium', 6),
(110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6), (110, 'Tribuna Antenore', 6),
(80, 'Tribuna Fondo Campo', 6), (80, 'Tribuna Fondo Campo', 6), (80, 'Tribuna Fondo Campo', 6), (80, 'Tribuna Fondo Campo', 6), (80, 'Tribuna Fondo Campo', 6), (80, 'Tribuna Fondo Campo', 6), (80, 'Tribuna Fondo Campo', 6), (80, 'Tribuna Fondo Campo', 6), (80, 'Tribuna Fondo Campo', 6), (80, 'Tribuna Fondo Campo', 6),
(60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6), (60, 'Anello Superiore', 6),

-- ================= GIORNATA 4
-- Incontro 7 (30 Biglietti)
(195, 'Courtside Premium', 7), (195, 'Courtside Premium', 7), (195, 'Courtside Premium', 7),
(125, 'Tribuna Antenore', 7), (125, 'Tribuna Antenore', 7), (125, 'Tribuna Antenore', 7), (125, 'Tribuna Antenore', 7), (125, 'Tribuna Antenore', 7), (125, 'Tribuna Antenore', 7), (125, 'Tribuna Antenore', 7), (125, 'Tribuna Antenore', 7), (125, 'Tribuna Antenore', 7), (125, 'Tribuna Antenore', 7),
(95, 'Tribuna Fondo Campo', 7), (95, 'Tribuna Fondo Campo', 7), (95, 'Tribuna Fondo Campo', 7), (95, 'Tribuna Fondo Campo', 7), (95, 'Tribuna Fondo Campo', 7), (95, 'Tribuna Fondo Campo', 7), (95, 'Tribuna Fondo Campo', 7),
(75, 'Anello Superiore', 7), (75, 'Anello Superiore', 7), (75, 'Anello Superiore', 7), (75, 'Anello Superiore', 7), (75, 'Anello Superiore', 7), (75, 'Anello Superiore', 7), (75, 'Anello Superiore', 7), (75, 'Anello Superiore', 7), (75, 'Anello Superiore', 7), (75, 'Anello Superiore', 7),
-- Incontro 8 (50 Biglietti)
(195, 'Courtside Premium', 8), (195, 'Courtside Premium', 8), (195, 'Courtside Premium', 8), (195, 'Courtside Premium', 8), (195, 'Courtside Premium', 8),
(125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8), (125, 'Tribuna Antenore', 8),
(95, 'Tribuna Fondo Campo', 8), (95, 'Tribuna Fondo Campo', 8), (95, 'Tribuna Fondo Campo', 8), (95, 'Tribuna Fondo Campo', 8), (95, 'Tribuna Fondo Campo', 8), (95, 'Tribuna Fondo Campo', 8), (95, 'Tribuna Fondo Campo', 8), (95, 'Tribuna Fondo Campo', 8), (95, 'Tribuna Fondo Campo', 8), (95, 'Tribuna Fondo Campo', 8),
(75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8), (75, 'Anello Superiore', 8),

-- ================= GIORNATA 5
-- Incontro 9 (30 Biglietti)
(210, 'Courtside Premium', 9), (210, 'Courtside Premium', 9), (210, 'Courtside Premium', 9),
(140, 'Tribuna Antenore', 9), (140, 'Tribuna Antenore', 9), (140, 'Tribuna Antenore', 9), (140, 'Tribuna Antenore', 9), (140, 'Tribuna Antenore', 9), (140, 'Tribuna Antenore', 9), (140, 'Tribuna Antenore', 9), (140, 'Tribuna Antenore', 9), (140, 'Tribuna Antenore', 9), (140, 'Tribuna Antenore', 9),
(110, 'Tribuna Fondo Campo', 9), (110, 'Tribuna Fondo Campo', 9), (110, 'Tribuna Fondo Campo', 9), (110, 'Tribuna Fondo Campo', 9), (110, 'Tribuna Fondo Campo', 9), (110, 'Tribuna Fondo Campo', 9), (110, 'Tribuna Fondo Campo', 9),
(90, 'Anello Superiore', 9), (90, 'Anello Superiore', 9), (90, 'Anello Superiore', 9), (90, 'Anello Superiore', 9), (90, 'Anello Superiore', 9), (90, 'Anello Superiore', 9), (90, 'Anello Superiore', 9), (90, 'Anello Superiore', 9), (90, 'Anello Superiore', 9), (90, 'Anello Superiore', 9),
-- Incontro 10 (50 Biglietti)
(210, 'Courtside Premium', 10), (210, 'Courtside Premium', 10), (210, 'Courtside Premium', 10), (210, 'Courtside Premium', 10), (210, 'Courtside Premium', 10),
(140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10), (140, 'Tribuna Antenore', 10),
(110, 'Tribuna Fondo Campo', 10), (110, 'Tribuna Fondo Campo', 10), (110, 'Tribuna Fondo Campo', 10), (110, 'Tribuna Fondo Campo', 10), (110, 'Tribuna Fondo Campo', 10), (110, 'Tribuna Fondo Campo', 10), (110, 'Tribuna Fondo Campo', 10), (110, 'Tribuna Fondo Campo', 10), (110, 'Tribuna Fondo Campo', 10), (110, 'Tribuna Fondo Campo', 10),
(90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10), (90, 'Anello Superiore', 10),

-- ================= GIORNATA 6
-- Incontro 11 (30 Biglietti)
(225, 'Courtside Premium', 11), (225, 'Courtside Premium', 11), (225, 'Courtside Premium', 11),
(155, 'Tribuna Antenore', 11), (155, 'Tribuna Antenore', 11), (155, 'Tribuna Antenore', 11), (155, 'Tribuna Antenore', 11), (155, 'Tribuna Antenore', 11), (155, 'Tribuna Antenore', 11), (155, 'Tribuna Antenore', 11), (155, 'Tribuna Antenore', 11), (155, 'Tribuna Antenore', 11), (155, 'Tribuna Antenore', 11),
(125, 'Tribuna Fondo Campo', 11), (125, 'Tribuna Fondo Campo', 11), (125, 'Tribuna Fondo Campo', 11), (125, 'Tribuna Fondo Campo', 11), (125, 'Tribuna Fondo Campo', 11), (125, 'Tribuna Fondo Campo', 11), (125, 'Tribuna Fondo Campo', 11),
(105, 'Anello Superiore', 11), (105, 'Anello Superiore', 11), (105, 'Anello Superiore', 11), (105, 'Anello Superiore', 11), (105, 'Anello Superiore', 11), (105, 'Anello Superiore', 11), (105, 'Anello Superiore', 11), (105, 'Anello Superiore', 11), (105, 'Anello Superiore', 11), (105, 'Anello Superiore', 11),
-- Incontro 12 (50 Biglietti)
(225, 'Courtside Premium', 12), (225, 'Courtside Premium', 12), (225, 'Courtside Premium', 12), (225, 'Courtside Premium', 12), (225, 'Courtside Premium', 12),
(155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12), (155, 'Tribuna Antenore', 12),
(125, 'Tribuna Fondo Campo', 12), (125, 'Tribuna Fondo Campo', 12), (125, 'Tribuna Fondo Campo', 12), (125, 'Tribuna Fondo Campo', 12), (125, 'Tribuna Fondo Campo', 12), (125, 'Tribuna Fondo Campo', 12), (125, 'Tribuna Fondo Campo', 12), (125, 'Tribuna Fondo Campo', 12), (125, 'Tribuna Fondo Campo', 12), (125, 'Tribuna Fondo Campo', 12),
(105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12), (105, 'Anello Superiore', 12),

-- ================= GIORNATA 7
-- Incontro 13 (Semifinale - 30 Biglietti)
(240, 'Courtside Premium', 13), (240, 'Courtside Premium', 13), (240, 'Courtside Premium', 13),
(170, 'Tribuna Antenore', 13), (170, 'Tribuna Antenore', 13), (170, 'Tribuna Antenore', 13), (170, 'Tribuna Antenore', 13), (170, 'Tribuna Antenore', 13), (170, 'Tribuna Antenore', 13), (170, 'Tribuna Antenore', 13), (170, 'Tribuna Antenore', 13), (170, 'Tribuna Antenore', 13), (170, 'Tribuna Antenore', 13),
(140, 'Tribuna Fondo Campo', 13), (140, 'Tribuna Fondo Campo', 13), (140, 'Tribuna Fondo Campo', 13), (140, 'Tribuna Fondo Campo', 13), (140, 'Tribuna Fondo Campo', 13), (140, 'Tribuna Fondo Campo', 13), (140, 'Tribuna Fondo Campo', 13),
(120, 'Anello Superiore', 13), (120, 'Anello Superiore', 13), (120, 'Anello Superiore', 13), (120, 'Anello Superiore', 13), (120, 'Anello Superiore', 13), (120, 'Anello Superiore', 13), (120, 'Anello Superiore', 13), (120, 'Anello Superiore', 13), (120, 'Anello Superiore', 13), (120, 'Anello Superiore', 13),
-- Incontro 14 (Finalissima - 50 Biglietti)
(240, 'Courtside Premium', 14), (240, 'Courtside Premium', 14), (240, 'Courtside Premium', 14), (240, 'Courtside Premium', 14), (240, 'Courtside Premium', 14),
(170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14), (170, 'Tribuna Antenore', 14),
(140, 'Tribuna Fondo Campo', 14), (140, 'Tribuna Fondo Campo', 14), (140, 'Tribuna Fondo Campo', 14), (140, 'Tribuna Fondo Campo', 14), (140, 'Tribuna Fondo Campo', 14), (140, 'Tribuna Fondo Campo', 14), (140, 'Tribuna Fondo Campo', 14), (140, 'Tribuna Fondo Campo', 14), (140, 'Tribuna Fondo Campo', 14), (140, 'Tribuna Fondo Campo', 14),
(120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14), (120, 'Anello Superiore', 14);

INSERT INTO BIGLIETTO (prezzo, tribuna, tipo, idIncontro) VALUES
-- ID 15 (18 Maggio) - Prezzo: 20€
(20.00, NULL, 'ground', 15), (20.00, NULL, 'ground', 15), (20.00, NULL, 'ground', 15), (20.00, NULL, 'ground', 15), (20.00, NULL, 'ground', 15),
(20.00, NULL, 'ground', 15), (20.00, NULL, 'ground', 15), (20.00, NULL, 'ground', 15), (20.00, NULL, 'ground', 15), (20.00, NULL, 'ground', 15),
-- ID 16 (19 Maggio) - Prezzo: 25€
(25.00, NULL, 'ground', 16), (25.00, NULL, 'ground', 16), (25.00, NULL, 'ground', 16), (25.00, NULL, 'ground', 16), (25.00, NULL, 'ground', 16),
(25.00, NULL, 'ground', 16), (25.00, NULL, 'ground', 16), (25.00, NULL, 'ground', 16), (25.00, NULL, 'ground', 16), (25.00, NULL, 'ground', 16),
-- ID 17 (20 Maggio) - Prezzo: 30€
(30.00, NULL, 'ground', 17), (30.00, NULL, 'ground', 17), (30.00, NULL, 'ground', 17), (30.00, NULL, 'ground', 17), (30.00, NULL, 'ground', 17),
(30.00, NULL, 'ground', 17), (30.00, NULL, 'ground', 17), (30.00, NULL, 'ground', 17), (30.00, NULL, 'ground', 17), (30.00, NULL, 'ground', 17),
-- ID 18 (21 Maggio) - Prezzo: 35€
(35.00, NULL, 'ground', 18), (35.00, NULL, 'ground', 18), (35.00, NULL, 'ground', 18), (35.00, NULL, 'ground', 18), (35.00, NULL, 'ground', 18),
(35.00, NULL, 'ground', 18), (35.00, NULL, 'ground', 18), (35.00, NULL, 'ground', 18), (35.00, NULL, 'ground', 18), (35.00, NULL, 'ground', 18),
-- ID 19 (22 Maggio) - Prezzo: 40€
(40.00, NULL, 'ground', 19), (40.00, NULL, 'ground', 19), (40.00, NULL, 'ground', 19), (40.00, NULL, 'ground', 19), (40.00, NULL, 'ground', 19),
(40.00, NULL, 'ground', 19), (40.00, NULL, 'ground', 19), (40.00, NULL, 'ground', 19), (40.00, NULL, 'ground', 19), (40.00, NULL, 'ground', 19),
-- ID 20 (23 Maggio) - Prezzo: 45€
(45.00, NULL, 'ground', 20), (45.00, NULL, 'ground', 20), (45.00, NULL, 'ground', 20), (45.00, NULL, 'ground', 20), (45.00, NULL, 'ground', 20),
(45.00, NULL, 'ground', 20), (45.00, NULL, 'ground', 20), (45.00, NULL, 'ground', 20), (45.00, NULL, 'ground', 20), (45.00, NULL, 'ground', 20),
-- ID 21 (24 Maggio) - Prezzo: 50€
(50.00, NULL, 'ground', 21), (50.00, NULL, 'ground', 21), (50.00, NULL, 'ground', 21), (50.00, NULL, 'ground', 21), (50.00, NULL, 'ground', 21),
(50.00, NULL, 'ground', 21), (50.00, NULL, 'ground', 21), (50.00, NULL, 'ground', 21), (50.00, NULL, 'ground', 21), (50.00, NULL, 'ground', 21);

INSERT INTO FAQ (testo_domanda, testo_risposta, categoria) VALUES

-- ===== INFO PRATICHE E ACCESSI =====
('A che ora aprono i cancelli del torneo?','I cancelli aprono tutti i giorni alle ore 09:00. L''accesso alle tribune è consentito a partire da circa 30 minuti prima dell''inizio della sessione della giornata.','Info Pratiche e Accessi'),
('Quali sono gli ingressi principali e come sono organizzati?','L''impianto è suddiviso in settori con accessi dedicati: Area Ovest, Area Est e Area Sud. Ogni biglietto riporta il settore di riferimento e l''ingresso consigliato. La mappa dettagliata degli accessi, con i nomi delle vie e i varchi di sicurezza, viene pubblicata su questa pagina nei giorni che precedono l''inizio del torneo.','Info Pratiche e Accessi'),
('Dove posso parcheggiare?','Sono previste aree di parcheggio in prossimità dei settori Est e Sud. I posti sono limitati e si consiglia l''uso dei mezzi pubblici. Le indicazioni stradali dettagliate e le tariffe verranno comunicate sul sito in avvicinamento all''evento.','Info Pratiche e Accessi'),
('L''impianto è accessibile per le persone con disabilità motorie?','Sì. I campi principali sono dotati di rampe, ascensori e postazioni dedicate. Per assistenza specifica o per prenotare una postazione accessibile è possibile contattare il nostro team tramite il modulo a fondo pagina.','Info Pratiche e Accessi'),
('È possibile entrare e uscire dall''impianto durante la giornata?','Sì, conservando il biglietto è consentito uscire e rientrare nello stesso settore nell''arco della sessione acquistata. Ti consigliamo di verificare i varchi attivi indicati all''ingresso.','Info Pratiche e Accessi'),
('Si può accedere con borse o zaini?','Sono ammesse borse di piccole dimensioni, soggette a controllo ai varchi di sicurezza. Per ragioni di sicurezza alcuni oggetti possono non essere consentiti; l''elenco aggiornato viene pubblicato prima dell''evento.','Info Pratiche e Accessi'),

-- ===== BIGLIETTERIA =====
('Cosa significa acquistare una "sessione" invece di un singolo match?','Il torneo si articola in sessioni di gioco (diurne e serali). Acquistando una sessione hai accesso a tutti gli incontri programmati in quella fascia, nel settore scelto. Questo ti permette di assicurarti il posto in anticipo, anche prima che venga definito il calendario dettagliato degli incontri.','Biglietteria'),
('Posso già sapere quale giocatore scenderà in campo e in quale giorno?','Al momento no: il calendario dettagliato e gli accoppiamenti tra i giocatori vengono definiti solo in avvicinamento al torneo, secondo il tabellone ufficiale. Per questo la vendita è organizzata per sessioni e per settore, e non per singolo incontro. Il programma giornaliero verrà pubblicato sul sito non appena disponibile.','Biglietteria'),
('Che differenza c''è tra Single Session, Ground Pass e Abbonamento?','La Single Session dà accesso a una specifica sessione in una tribuna scelta. Il Ground Pass consente l''accesso alle aree e ai campi secondari per un''intera giornata. L''Abbonamento copre tutte le giornate del torneo per la tribuna selezionata. Trovi il dettaglio e i prezzi nella pagina Biglietti.','Biglietteria'),
('Il biglietto che acquisto è nominativo?','Il biglietto riporta il nome dell''acquirente ma non è strettamente nominativo all''ingresso: può essere ceduto. Ti invitiamo comunque a conservarlo con cura, poiché è il titolo necessario per l''accesso al settore indicato.','Biglietteria'),
('Come ricevo i biglietti dopo l''acquisto?','Una volta completato l''acquisto, i biglietti sono associati al tuo account e consultabili nella tua Area Personale, nella sezione "I miei biglietti", insieme al numero d''ordine.','Biglietteria'),
('Posso ottenere un rimborso o modificare l''acquisto?','Le condizioni di rimborso e di modifica dipendono dalla tipologia di biglietto e dalle tempistiche. Per richieste specifiche contatta il nostro team tramite il modulo a fondo pagina, indicando il numero d''ordine.','Biglietteria'),

-- ===== REGOLAMENTO =====
('Come funziona in generale il torneo?','Il Patavium Open è un torneo di tennis che si sviluppa su più giornate, suddivise in sessioni di gioco. Con il procedere del tabellone gli incontri si concentrano fino alle fasi finali. Il programma di ciascuna giornata viene pubblicato in avvicinamento all''evento.','Regolamento'),
('Cosa succede in caso di pioggia o sospensione degli incontri?','In caso di maltempo gli incontri possono essere sospesi o riprogrammati secondo le decisioni della direzione del torneo. Le tribune coperte non sono soggette a interruzioni per pioggia. Le politiche relative a recuperi ed eventuali rimborsi vengono comunicate caso per caso.','Regolamento'),
('È consentito fotografare o riprendere gli incontri?','Sono consentite riprese e fotografie per uso strettamente personale. Non è permesso l''utilizzo a scopo commerciale o la diffusione di materiale senza autorizzazione, né l''uso di attrezzature professionali senza accredito.','Regolamento'),
('Ci sono punti di ristoro all''interno dell''impianto?','Sì, all''interno dei settori sono presenti aree ristoro e punti vendita. La disponibilità specifica per ciascuna area viene indicata sulla mappa pubblicata prima dell''evento.','Regolamento'),
('Si può portare cibo o acqua da casa?','Per ragioni di sicurezza non è consentito introdurre cibo o bevande dall''esterno. È ammessa una sola bottiglia d''acqua sigillata di massimo 500 ml a persona, che potrà essere sottoposta a controllo agli ingressi. All''interno dei settori sono presenti aree ristoro e punti vendita di cibo e bevande.','Info Pratiche e Accessi'),
('È possibile accedere con animali domestici?','Per motivi di sicurezza e igiene, non è consentito l''accesso con animali domestici, ad eccezione di cani guida per persone con disabilità.','Info Pratiche e Accessi');

-- Inserimento nella tabella NEWS
INSERT INTO NEWS (idNews, titolo, testo, data_pubblicazione, immagine, idAutore, inEvidenza) VALUES
(1, 'Svelato il montepremi record', 'Il comitato direttivo ha ufficializzato un clamoroso incremento del 30% del prize money rispetto alla passata stagione, posizionando il torneo tra i più ricchi e prestigiosi della categoria ATP 250. La finale promette già scintille e un assegno da record storico per il vincitore del singolare maschile e femminile. Gli organizzatori contano così di attirare un parterre di campioni ancora più competitivo, regalando al pubblico padovano un grande spettacolo sportivo.', '2026-05-29 14:50:51', 'assets/images/trofeo_patavium.webp', 1, 1),
(2, 'Lorenzo Milanese si trasferisce a Los Angeles', 'Il giovane talento padovano Lorenzo Milanese ha annunciato questa mattina, in conferenza stampa, il suo imminente trasferimento negli Stati Uniti. Si allenerà a tempo pieno presso una delle più prestigiose accademie tennistiche della California, rinomata per aver forgiato grandi campioni del circuito ATP. Purtroppo per i tifosi locali, questo passo comporterà la sua assenza all''edizione di quest''anno del Patavium Open. ''È stata la decisione più difficile della mia carriera'', ha dichiarato, visibilmente emozionato davanti ai giornalisti.', '2026-05-29 14:50:51', 'assets/images/lorenzo_milanese.webp', 1, 0),
(3, 'Lavori in corso: i campi si preparano per il Patavium Open 2027', 'La macchina organizzativa del Patavium Open non si ferma mai. In questi giorni i nostri addetti stanno lavorando incessantemente alla manutenzione dei campi in terra rossa della Patavium Arena e del Giotto Court. L''utilizzo dei rulli pesanti, unito a nuove tecniche di drenaggio e stesura del manto, garantirà una superficie perfetta, capace di esaltare le giocate dei campioni attesi a Padova. Curiamo ogni minimo dettaglio, dalla granulometria della terra alla perfetta tracciatura delle linee, per offrire spettacolo e massima sicurezza.', '2026-05-29 15:06:41', 'assets/images/campo_rulli.webp', 1, 0),
(4, 'Patavium Junior: i campioni di domani scendono in campo', 'Il grande tennis non è solo per i professionisti! Siamo orgogliosi di annunciare il ritorno del "Patavium Junior", il torneo dedicato alle giovani promesse under 12 e under 14 che farà da apripista all''evento principale. Un''occasione unica per calcare gli stessi campi dove, pochi giorni dopo, si sfideranno i loro idoli mondiali. Sono previsti momenti di gioco, workshop interattivi con i maestri federali e tante sorprese per avvicinare i giovanissimi a questo splendido sport. Venite a tifare per i campioni del futuro!', '2026-05-29 15:06:41', 'assets/images/patavium_junior.webp', 1, 0),
(5, 'Jannik Sinner trionfa agli Internazionali d''Italia 2026!', 'Una giornata storica per il tennis italiano: Jannik Sinner ha conquistato gli Internazionali BNL d''Italia 2026 in una finale mozzafiato al Foro Italico. Con una prestazione magistrale, il campione azzurro ha infiammato il pubblico di Roma, confermandosi tra i giocatori più letali al mondo sulla terra battuta. Questo trionfo accende l''entusiasmo dei tifosi in vista del Patavium Open, dove Sinner è attesissimo. Riuscirà a replicare la magia anche sui nostri campi padovani? La febbre sale e i biglietti vanno già a ruba!', '2026-05-29 15:06:41', 'assets/images/sinner_vince.webp', 1, 1),
(6, 'Un torneo sempre più verde: il nostro impegno per la sostenibilità', 'Il Patavium Open scende in campo per difendere l''ambiente. Quest''anno abbiamo intrapreso una svolta "green" eliminando quasi totalmente la plastica monouso nell''impianto. I visitatori troveranno colonnine per il rifornimento di acqua gratuita e tutti i punti ristoro useranno esclusivamente materiali biodegradabili. Inoltre, grazie alla collaborazione con il Comune di Padova, abbiamo potenziato il servizio di navette elettriche gratuite dalla stazione centrale per disincentivare l''uso delle auto private. Un piccolo gesto concreto per proteggere il nostro futuro.', '2026-05-29 15:06:41', 'assets/images/torneo_sostenibile.webp', 1, 0),
(7, 'Partnership Trenitalia: sconti esclusivi per chi arriva in treno', 'Raggiungere la Patavium Arena non è mai stato così comodo ed ecologico. Grazie al nuovo accordo di partnership siglato oggi, tutti gli appassionati che arriveranno a Padova a bordo dei treni Frecce o Intercity avranno diritto a uno sconto del 15% sui biglietti delle sessioni diurne e del 10% sulle finali serali. Vogliamo premiare i tifosi che scelgono i mezzi pubblici, promuovendo una mobilità sostenibile e riducendo l''impatto ambientale dell''evento. Scopri tutti i dettagli per richiedere lo sconto nella sezione FAQ.', '2026-05-29 15:37:05', 'assets/images/treno_partnership.webp', 1, 0);

-- Inserimento nella tabella DOMANDA (separato dal punto e virgola sopra)
INSERT INTO DOMANDA (testo_domanda, testo_risposta, lettura_admin, lettura_user, idUtente) VALUES
('È possibile acquistare i biglietti direttamente ai botteghini dello stadio il giorno del match?', NULL, 0, 0, 2),
('Quali sono le restrizioni per il parcheggio vicino alla Patavium Arena?', NULL, 1, 0, 2),
('Avete un servizio di deposito bagagli all''ingresso?', 'Sì, è disponibile un servizio gratuito di deposito per oggetti ingombranti.', 1, 0, 2),
('I bambini sotto i 6 anni pagano?', 'No, l''ingresso è gratuito per i bambini sotto i 6 anni senza posto assegnato.', 1, 1, 2),
('Ci sono stazioni di ricarica per auto elettriche nel parcheggio ovest?', NULL, 0, 0, 2),
('In caso di pioggia i biglietti vengono rimborsati?', 'Il rimborso avviene solo se non vengono giocati almeno 60 minuti di match.', 1, 0, 2),
('Non riesco a visualizzare il mio biglietto digitale nell''area utente, cosa posso fare?', NULL, 0, 0, 2),
('Ho smarrito una sciarpa blu in Tribuna Antenore ieri, è stata ritrovata?', NULL, 1, 0, 2),
('La tribuna Fondo Campo è accessibile con sedia a rotelle?', 'Certamente, la tribuna è dotata di rampa dedicata e posti riservati.', 1, 1, 2),
('A che ora è prevista la finale del singolare maschile di domenica?', NULL, 0, 0, 2);

-- Inserimento nella tabella CAMPIONE (gli stessi 4 campioni della home, con i loro alt-text reali)
INSERT INTO CAMPIONE (nome, categoria, anno, immagine, alt_immagine, ordine) VALUES
('Lorenzo Costa', 'Singolo Maschile', 2026, 'assets/images/lorenzo_costa.webp', 'Il tennista Lorenzo Costa sorridente mentre solleva la coppa del torneo dopo la vittoria', 1),
('Sofia Esposito', 'Singolo Femminile', 2026, 'assets/images/sofia_esposito.webp', 'La tennista Sofia Esposito in azione mentre colpisce la palla con un potente rovescio a due mani', 2),
('David Richard', 'Singolo Maschile', 2025, 'assets/images/david_richard.webp', 'Il tennista David Richard in posa sul campo con la racchetta appoggiata sulla spalla', 3),
('Alicia Schneider', 'Singolo Femminile', 2025, 'assets/images/alicia_schneider.webp', 'La tennista Alicia Schneider concentrata mentre prepara il servizio lanciando la palla in alto', 4);