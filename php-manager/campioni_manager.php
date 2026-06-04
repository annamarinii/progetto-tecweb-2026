<?php
require_once "db_connection.php";

class CampioniManager {

    // Limiti coerenti con lo schema DB: nome VARCHAR(60), categoria VARCHAR(40).
    const NOME_MAX      = 60;
    const CATEGORIA_MAX = 40;
    const ANNO_MIN      = 2000;
    const ANNO_MAX      = 2100;

    /**
     * Valida i campi di un campione. Riutilizzata da inserimento e aggiornamento.
     * @return bool true se nome e categoria sono presenti e nei limiti e l'anno è plausibile.
     */
    public static function validaCampiCampione(string $nome, string $categoria, $anno): bool
    {
        $nome      = trim($nome);
        $categoria = trim($categoria);
        if ($nome === '' || $categoria === '') {
            return false;
        }
        // Conteggio caratteri robusto anche senza estensione mbstring
        $len_nome      = function_exists('mb_strlen') ? mb_strlen($nome, 'UTF-8')      : strlen($nome);
        $len_categoria = function_exists('mb_strlen') ? mb_strlen($categoria, 'UTF-8') : strlen($categoria);
        if ($len_nome > self::NOME_MAX || $len_categoria > self::CATEGORIA_MAX) {
            return false;
        }
        // Anno numerico plausibile (es. 2000–2100)
        if (!is_numeric($anno)) {
            return false;
        }
        $anno = (int) $anno;
        return $anno >= self::ANNO_MIN && $anno <= self::ANNO_MAX;
    }

    // Recupera tutti i campioni
    public static function getCampioni() {
        $conn = DBConnection::getConnessione();
        $sql = "SELECT idCampione, nome, categoria, anno, immagine, alt_immagine, ordine
                FROM CAMPIONI
                ORDER BY ordine ASC, anno DESC";
        $risultato = $conn->query($sql);
        $campioni_estratti = ($risultato && $risultato->num_rows > 0) ? $risultato->fetch_all(MYSQLI_ASSOC) : [];
        return $campioni_estratti;
    }

    // Recupera i campioni per la Home (stesso ordinamento, di default tutti)
    public static function getCampioniHome() {
        $conn = DBConnection::getConnessione();
        $sql = "SELECT idCampione, nome, categoria, anno, immagine, alt_immagine, ordine
                FROM CAMPIONI
                ORDER BY ordine ASC, anno DESC";
        $risultato = $conn->query($sql);
        $campioni_estratti = ($risultato && $risultato->num_rows > 0) ? $risultato->fetch_all(MYSQLI_ASSOC) : [];
        return $campioni_estratti;
    }

    // Inserisce un nuovo campione
    public static function inserisciCampione($nome, $categoria, $anno, $immagine, $alt_immagine, $ordine) {
        // Validazione difensiva: niente INSERT se i campi non sono validi
        if (!self::validaCampiCampione((string) $nome, (string) $categoria, $anno)) {
            return false;
        }
        $anno   = (int) $anno;
        $ordine = (int) $ordine;
        $conn = DBConnection::getConnessione();
        $sql = "INSERT INTO CAMPIONI (nome, categoria, anno, immagine, alt_immagine, ordine) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssissi", $nome, $categoria, $anno, $immagine, $alt_immagine, $ordine);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }

    // Aggiorna un campione esistente
    public static function aggiornaCampione($idCampione, $nome, $categoria, $anno, $immagine, $alt_immagine, $ordine) {
        // Validazione difensiva: id valido e campi corretti
        if ((int) $idCampione <= 0 || !self::validaCampiCampione((string) $nome, (string) $categoria, $anno)) {
            return false;
        }
        $anno       = (int) $anno;
        $ordine     = (int) $ordine;
        $idCampione = (int) $idCampione;
        $conn = DBConnection::getConnessione();
        if ($immagine != "") {
            $sql = "UPDATE CAMPIONI SET nome=?, categoria=?, anno=?, immagine=?, alt_immagine=?, ordine=? WHERE idCampione=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssissii", $nome, $categoria, $anno, $immagine, $alt_immagine, $ordine, $idCampione);
        } else {
            // L'alt si aggiorna comunque, indipendentemente dal cambio immagine
            $sql = "UPDATE CAMPIONI SET nome=?, categoria=?, anno=?, alt_immagine=?, ordine=? WHERE idCampione=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisii", $nome, $categoria, $anno, $alt_immagine, $ordine, $idCampione);
        }
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }

    // Elimina un campione
    public static function eliminaCampione($idCampione) {
        $conn = DBConnection::getConnessione();
        $sql = "DELETE FROM CAMPIONI WHERE idCampione = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idCampione);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }
}
?>