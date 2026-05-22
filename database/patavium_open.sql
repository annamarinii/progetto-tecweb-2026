DROP TABLE IF EXISTS BIGLIETTI;
DROP TABLE IF EXISTS ORDINE;
DROP TABLE IF EXISTS DOMANDE;
DROP TABLE IF EXISTS NEWS;
DROP TABLE IF EXISTS PROGRAMMA;
DROP TABLE IF EXISTS UTENTE;
DROP TABLE IF EXISTS FAQ;


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

CREATE TABLE PROGRAMMA (
                          idProgramma INT AUTO_INCREMENT PRIMARY KEY,
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
                      immagine VARCHAR(255) DEFAULT 'assets/images/default-news.jpg',
                      idAutore INT NOT NULL,
                      inEvidenza BOOLEAN DEFAULT FALSE,
                      FOREIGN KEY (idAutore) REFERENCES UTENTE(idUtente)
);

CREATE TABLE DOMANDE (
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

CREATE TABLE BIGLIETTI (
                           idBiglietto INT AUTO_INCREMENT PRIMARY KEY,
                           prezzo DECIMAL (6,2) NOT NULL,
                           tribuna VARCHAR(20),
                           tipo VARCHAR(20) DEFAULT NULL,
                           numero_ordine INT DEFAULT NULL,
                           idProgramma INT NOT NULL,
                           FOREIGN KEY (numero_ordine) REFERENCES ORDINE(numero_ordine),
                           FOREIGN KEY (idProgramma) REFERENCES PROGRAMMA(idProgramma)
);

CREATE TABLE FAQ (
    idFaq INT AUTO_INCREMENT PRIMARY KEY,
    testo_domanda TEXT NOT NULL,
    testo_risposta TEXT NOT NULL
);


-- INSERT PER LE TABELLE (inserimento user user, admin admin)

INSERT INTO UTENTE (username, email, password, nome, cognome, isAdmin)
VALUES 
('admin', 'admin@pataviumopen.it', '$2y$10$hJs.Dy1/uAcVxtJDMjwE0OqvxWaiwysPVoOaTOs7eqFPNM4ObP8sW', 'admin', 'admin', 1),
('user', 'user@pataviumopen.it', '$2y$10$3c2HqNP45kB2OHZUPl3Zh.giWOM/hT0JFAriYHZPNIk7pw7zMYgeG', 'user', 'user', 0);

INSERT INTO PROGRAMMA (data, sessione, stadio) VALUES
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
INSERT INTO BIGLIETTI (prezzo, tribuna, idProgramma) VALUES

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

INSERT INTO BIGLIETTI (prezzo, tribuna, tipo, idProgramma) VALUES
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

INSERT INTO FAQ (testo_domanda, testo_risposta) VALUES
('A che ora aprono i cancelli del torneo?','I cancelli aprono tutti i giorni alle ore 09:00 del mattino. L''accesso alle tribune è consentito circa 30 minuti prima dell match della giornata.'),
('Il biglietto che acquisto è nominativo?', 'No, i biglietti NON sono nominativi. E'' indicato il nome dell''acquirente, ma possono essere ceduti ad altri.'),
('L''impianto è accessibile per le persone con disabilità motorie?','Assolutamente sì. Tutti i campi principali sono dotati di rampe e ascensori.'),
('Posso portare cibo, bevande o macchine fotografiche nell''impianto?', 'È consentito portare piccoli snack e bottiglie non in vetro di massimo 500ml. Non è permesso l''ingresso con borse frigo. Le macchine fotografiche sono ammesse solo se senza flash e con obiettivi non professionali. Se gli oggetti portati non sono in linea con le regole dell''impianto, il nostro staff li costudirà all''ingresso.'),
('Dove posso parcheggiare la macchina?', 'È disponibile un ampio parcheggio nell''area Ovest distante pochi minuti a piedi dall''ingresso principale. Il parcheggio è incluso per chi è in possesso dell''Abbonamento stagionale, mentre per gli altri biglietti è a tariffa giornaliera.'),
('I minori devono essere accompagnati da un adulto?', 'Il minore entro i 14 anni deve necessariamente essere accompagnato da un adulto per accedere ai campi; sia il minore sia l''adulto dovranno essere muniti di regolare titolo di ingresso.'),
('Posso entrare anche a sessione iniziata?', 'Sì, è possibile accedere in qualsiasi momento, compatibilmente ai tempi di gioco.');

INSERT INTO NEWS (titolo, testo, immagine, idAutore) 
VALUES
('Svelato il montepremi record', 'Il comitato direttivo ha ufficializzato un clamoroso incremento del 30% del prize money rispetto alla passata stagione, posizionando il torneo tra i più ricchi e prestigiosi della categoria ATP 250. La finale promette già scintille e un assegno da record storico per il vincitore del singolare maschile e femminile.', 'assets/images/trofeo_patavium.jpg', 1);
('Lorenzo Milanese si trasferisce a Los Angeles', 'Il comitato direttivo ha ufficializzato un clamoroso incremento del 30% del prize money rispetto alla passata stagione, posizionando il torneo tra i più ricchi e prestigiosi della categoria ATP 250. La finale promette già scintille e un assegno da record storico per il vincitore del singolare maschile e femminile.', 'assets/images/lorenzo_milanese.jpg', 1);

INSERT INTO DOMANDE (testo_domanda, testo_risposta, lettura_admin, lettura_user, idUtente) 
VALUES 
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