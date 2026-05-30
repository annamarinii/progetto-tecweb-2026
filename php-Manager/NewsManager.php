<?php
require_once "DBConnection.php";

class NewsManager {

    // Limiti coerenti con lo schema DB: titolo VARCHAR(100), testo TEXT.
    const TITOLO_MAX = 100;
    const TESTO_MIN  = 1;

    /**
     * Valida i campi testuali di una news. Riutilizzata da inserimento e aggiornamento.
     * @return bool true se titolo e testo sono presenti e nei limiti.
     */
    public static function validaCampiNews(string $titolo, string $testo): bool
    {
        $titolo = trim($titolo);
        $testo  = trim($testo);
        if ($titolo === '' || $testo === '') {
            return false;
        }
        // Conteggio caratteri robusto anche senza estensione mbstring
        $len_titolo = function_exists('mb_strlen') ? mb_strlen($titolo, 'UTF-8') : strlen($titolo);
        $len_testo  = function_exists('mb_strlen') ? mb_strlen($testo, 'UTF-8')  : strlen($testo);
        if ($len_titolo > self::TITOLO_MAX) {
            return false;
        }
        return $len_testo >= self::TESTO_MIN;
    }

    // Recupera tutte le news
    public static function getNews() {
        $conn = DBConnection::getConnessione();
        $sql = "SELECT N.idNews, N.titolo, N.testo, N.data_pubblicazione, N.immagine, N.inEvidenza, U.nome, U.cognome
                FROM NEWS N
                JOIN UTENTE U ON N.idAutore = U.idUtente
                ORDER BY N.data_pubblicazione DESC";
        $risultato = $conn->query($sql);
        $news_estratte = ($risultato && $risultato->num_rows > 0) ? $risultato->fetch_all(MYSQLI_ASSOC) : [];
        return $news_estratte;
    }

    // Inserisce una nuova news
    public static function inserisciNews($titolo, $testo, $immagine, $idAutore, $inEvidenza) {
        // Validazione difensiva: niente INSERT se titolo/testo non sono validi
        if (!self::validaCampiNews((string) $titolo, (string) $testo) || (int) $idAutore <= 0) {
            return false;
        }
        $conn = DBConnection::getConnessione();
        $sql = "INSERT INTO NEWS (titolo, testo, immagine, idAutore, inEvidenza) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $titolo, $testo, $immagine, $idAutore, $inEvidenza);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }

    // Aggiorna una news esistente
    public static function aggiornaNews($idNews, $titolo, $testo, $immagine, $inEvidenza) {
        // Validazione difensiva: id valido e campi testuali corretti
        if ((int) $idNews <= 0 || !self::validaCampiNews((string) $titolo, (string) $testo)) {
            return false;
        }
        $conn = DBConnection::getConnessione();
        if ($immagine != "") {
            $sql = "UPDATE NEWS SET titolo=?, testo=?, immagine=?, inEvidenza=? WHERE idNews=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssii", $titolo, $testo, $immagine, $inEvidenza, $idNews);
        } else {
            $sql = "UPDATE NEWS SET titolo=?, testo=?, inEvidenza=? WHERE idNews=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $titolo, $testo, $inEvidenza, $idNews);
        }
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }

    // Elimina una news
    public static function eliminaNews($idNews) {
        $conn = DBConnection::getConnessione();
        $sql = "DELETE FROM NEWS WHERE idNews = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idNews);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }

    public static function getUltimeNews($limite = 3) { //per le notizie di home.html
        $conn = DBConnection::getConnessione();

        $sql = "SELECT idNews, titolo, testo, data_pubblicazione, immagine 
                FROM NEWS 
                ORDER BY data_pubblicazione DESC 
                LIMIT ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limite);
        $stmt->execute();

        $risultato = $stmt->get_result();
        $news_estratte = [];

        if ($risultato && $risultato->num_rows > 0) {
            while ($row = $risultato->fetch_assoc()) {
                $news_estratte[] = $row;
            }
        }

        $stmt->close();

        return $news_estratte;
    }
}
?>