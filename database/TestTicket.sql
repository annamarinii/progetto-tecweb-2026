-- Inserimento di 1 biglietto per ogni tribuna in ogni sessione (14 sessioni * 4 tribune = 56 biglietti)
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
         SELECT 1 as idProgramma UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
         UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
         UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
         UNION SELECT 13 UNION SELECT 14
     ) as sessioni
         CROSS JOIN (
    SELECT 'Courtside Premium' as tribuna UNION SELECT 'Tribuna Antenore'
    UNION SELECT 'Tribuna Fondo Campo' UNION SELECT 'Anello Superiore'
) as tribune;


-- Inserimento di 1 biglietto ground per ogni giornata (7 biglietti)
INSERT INTO BIGLIETTI (prezzo, tribuna, tipo, idProgramma)
VALUES
    (20, NULL, 'ground', 15),
    (25, NULL, 'ground', 16),
    (30, NULL, 'ground', 17),
    (35, NULL, 'ground', 18),
    (40, NULL, 'ground', 19),
    (45, NULL, 'ground', 20),
    (50, NULL, 'ground', 21);