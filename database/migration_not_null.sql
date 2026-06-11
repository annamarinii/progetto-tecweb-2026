-- =====================================================================
-- MIGRAZIONE: vincoli NOT NULL sui campi obbligatori dei contenuti admin
-- Patavium Open - eseguire UNA volta su un database GIA' popolato.
-- (Per le installazioni nuove i vincoli sono gia' inclusi in
--  patavium_open.sql, quindi questo file NON serve.)
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1) NEWS: immagine e alt_immagine devono essere obbligatori.
--    Prima si bonificano eventuali righe esistenti con valore NULL,
--    poi si applica il vincolo NOT NULL mantenendo il DEFAULT.
-- ---------------------------------------------------------------------
UPDATE NEWS SET immagine = 'assets/images/default-news.webp' WHERE immagine IS NULL;
UPDATE NEWS SET alt_immagine = 'Immagine della news'        WHERE alt_immagine IS NULL;

ALTER TABLE NEWS
    MODIFY immagine     VARCHAR(255) NOT NULL DEFAULT 'assets/images/default-news.webp',
    MODIFY alt_immagine VARCHAR(255) NOT NULL DEFAULT 'Immagine della news';

-- ---------------------------------------------------------------------
-- 2) CAMPIONI: immagine, alt_immagine e ordine.
--    NOTA: nello schema attuale queste colonne sono GIA' NOT NULL.
--    Le ALTER seguenti sono idempotenti e servono solo a garantire il
--    vincolo su eventuali installazioni piu' vecchie.
-- ---------------------------------------------------------------------
UPDATE CAMPIONI SET immagine = 'assets/images/logo1.webp' WHERE immagine IS NULL;
UPDATE CAMPIONI SET alt_immagine = 'Ritratto del campione' WHERE alt_immagine IS NULL;
UPDATE CAMPIONI SET ordine = 0 WHERE ordine IS NULL;

ALTER TABLE CAMPIONI
    MODIFY immagine     VARCHAR(255) NOT NULL DEFAULT 'assets/images/logo1.webp',
    MODIFY alt_immagine VARCHAR(255) NOT NULL DEFAULT 'Ritratto del campione',
    MODIFY ordine       INT          NOT NULL DEFAULT 0;

-- ---------------------------------------------------------------------
-- 3) FAQ: categoria.
--    NOTA: nello schema attuale la colonna e' GIA' NOT NULL.
--    ALTER idempotente per le installazioni piu' vecchie.
-- ---------------------------------------------------------------------
UPDATE FAQ SET categoria = 'Regolamento' WHERE categoria IS NULL;

ALTER TABLE FAQ
    MODIFY categoria VARCHAR(50) NOT NULL DEFAULT 'Regolamento';
