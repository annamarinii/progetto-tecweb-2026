<?php
require_once "DBConnection.php";

class NewsManager {
    
    // Recupera tutte le news
    public static function getNews() {
        $conn = DBConnection::getConnessione();
        $sql = "SELECT N.idNews, N.titolo, N.testo, N.data_pubblicazione, N.immagine, U.nome, U.cognome 
                FROM NEWS N 
                JOIN UTENTE U ON N.idAutore = U.idUtente 
                ORDER BY N.data_pubblicazione DESC";
        $risultato = $conn->query($sql);
        $news_estratte = ($risultato && $risultato->num_rows > 0) ? $risultato->fetch_all(MYSQLI_ASSOC) : [];
        $conn->close();
        return $news_estratte;
    }

    // Inserisce una nuova news
    public static function inserisciNews($titolo, $testo, $immagine, $idAutore, $inEvidenza) {
        $conn = DBConnection::getConnessione();
        $sql = "INSERT INTO NEWS (titolo, testo, immagine, idAutore, inEvidenza) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $titolo, $testo, $immagine, $idAutore, $inEvidenza);
        $esito = $stmt->execute();
        $conn->close();
        return $esito;
    }

    // Aggiorna una news esistente
    public static function aggiornaNews($idNews, $titolo, $testo, $immagine, $inEvidenza) {
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
        $conn->close();
        return $esito;
    }

    // Elimina una news
    public static function eliminaNews($idNews) {
        $conn = DBConnection::getConnessione();
        $sql = "DELETE FROM NEWS WHERE idNews = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idNews);
        $esito = $stmt->execute();
        $conn->close();
        return $esito;
    }
}
?>