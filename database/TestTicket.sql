INSERT INTO BIGLIETTI (prezzo, tribuna, idProgramma)
SELECT
    CASE
        WHEN tribuna = 'Courtside Premium' THEN 150 + (idProgramma * 5)
        WHEN tribuna = 'Tribuna Antenore' THEN 80 + (idProgramma * 5)
        WHEN tribuna = 'Tribuna Fondo Campo' THEN 50 + (idProgramma * 5)
        ELSE 30 + (idProgramma * 5)
        END as prezzo,
    tribuna,
    idProgramma
FROM (
         -- Genera gli ID Programma da 1 a 14
         SELECT 1 as idProgramma UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
         UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
         UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
         UNION SELECT 13 UNION SELECT 14
     ) as sessioni
         CROSS JOIN (
    -- Genera i nomi delle Tribune
    SELECT 'Courtside Premium' as tribuna UNION SELECT 'Tribuna Antenore'
    UNION SELECT 'Tribuna Fondo Campo' UNION SELECT 'Anello Superiore'
) as tribune
         CROSS JOIN (
    -- Moltiplicatore: genera 3 righe per ogni combinazione sopra
    SELECT 1 as copia UNION SELECT 2 UNION SELECT 3
) as moltiplicatore;


INSERT INTO BIGLIETTI (prezzo, tribuna, tipo, idProgramma)
VALUES
    -- Giornata 1 (id 15)
    (20, NULL, 'ground', 15), (20, NULL, 'ground', 15), (20, NULL, 'ground', 15),
    -- Giornata 2 (id 16)
    (25, NULL, 'ground', 16), (25, NULL, 'ground', 16), (25, NULL, 'ground', 16),
    -- Giornata 3 (id 17)
    (30, NULL, 'ground', 17), (30, NULL, 'ground', 17), (30, NULL, 'ground', 17),
    -- Giornata 4 (id 18)
    (35, NULL, 'ground', 18), (35, NULL, 'ground', 18), (35, NULL, 'ground', 18),
    -- Giornata 5 (id 19)
    (40, NULL, 'ground', 19), (40, NULL, 'ground', 19), (40, NULL, 'ground', 19),
    -- Giornata 6 (id 20)
    (45, NULL, 'ground', 20), (45, NULL, 'ground', 20), (45, NULL, 'ground', 20),
    -- Giornata 7 (id 21)
    (50, NULL, 'ground', 21), (50, NULL, 'ground', 21), (50, NULL, 'ground', 21);


-- Ordini per l'Utente 1 (Anna)
INSERT INTO ORDINE (totale, data_acquisto, idUtente) VALUES (50,  '2026-04-10 10:30:00', 1); -- Ordine #1
INSERT INTO ORDINE (totale, data_acquisto, idUtente) VALUES (120, '2026-04-12 15:45:00', 1); -- Ordine #2
INSERT INTO ORDINE (totale, data_acquisto, idUtente) VALUES (80,  '2026-04-15 09:00:00', 1); -- Ordine #3

-- Ordini per l'Utente 2 (Marco)
INSERT INTO ORDINE (totale, data_acquisto, idUtente) VALUES (25,  '2026-04-11 11:20:00', 2); -- Ordine #4
INSERT INTO ORDINE (totale, data_acquisto, idUtente) VALUES (240, '2026-04-14 18:10:00', 2); -- Ordine #5