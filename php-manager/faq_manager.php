<?php
require_once "db_connection.php";

class FaqManager {
    // Recupera FAQ ufficiali
    public static function getFaq() {
        $conn = DBConnection::getConnessione();
        $sql = "SELECT idFaq, testo_domanda, testo_risposta, categoria FROM FAQ ORDER BY categoria, idFaq";
        $risultato = $conn->query($sql);
        $faq = ($risultato && $risultato->num_rows > 0) ? $risultato->fetch_all(MYSQLI_ASSOC) : [];
        return $faq;
    }

    public static function inserisciFaqUfficiale($domanda, $risposta, $categoria) {
        $conn = DBConnection::getConnessione();
        $stmt = $conn->prepare("INSERT INTO FAQ (testo_domanda, testo_risposta, categoria) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $domanda, $risposta, $categoria);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }

    public static function aggiornaFaq($idFaq, $domanda, $risposta, $categoria) {
        $conn = DBConnection::getConnessione();
        $stmt = $conn->prepare("UPDATE FAQ SET testo_domanda = ?, testo_risposta = ?, categoria = ? WHERE idFaq = ?");
        $stmt->bind_param("sssi", $domanda, $risposta, $categoria, $idFaq);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }

    public static function eliminaFaq($idFaq) {
        $conn = DBConnection::getConnessione();
        $stmt = $conn->prepare("DELETE FROM FAQ WHERE idFaq = ?");
        $stmt->bind_param("i", $idFaq);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }

    public static function getDomandaById($idDomanda) {
        $conn = DBConnection::getConnessione();
        $stmt = $conn->prepare("SELECT * FROM DOMANDE WHERE idDomanda = ?");
        $stmt->bind_param("i", $idDomanda);
        $stmt->execute();
        $risultato = $stmt->get_result();
        $domanda = ($risultato && $risultato->num_rows > 0) ? $risultato->fetch_assoc() : null;
        $stmt->close();
        return $domanda;
    }

    public static function eliminaDomanda($idDomanda) {
        $conn = DBConnection::getConnessione();
        $stmt = $conn->prepare("DELETE FROM DOMANDE WHERE idDomanda = ?");
        $stmt->bind_param("i", $idDomanda);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }

    // Inserisce una domanda inviata da un utente loggato (Tabella DOMANDE)
    public static function inserisciDomanda($testo_domanda, $idUtente) {
        $conn = DBConnection::getConnessione();
        $stmt = $conn->prepare("INSERT INTO DOMANDE (testo_domanda, idUtente) VALUES (?, ?)");
        $stmt->bind_param("si", $testo_domanda, $idUtente);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }

    // Recupera domande utenti (non ancora risposte)
    public static function getDomandeUtenti() {
        $conn = DBConnection::getConnessione();
        $sql = "SELECT D.*, U.username FROM DOMANDE D 
                JOIN UTENTE U ON D.idUtente = U.idUtente 
                WHERE D.testo_risposta IS NULL OR D.testo_risposta = ''
                ORDER BY D.data_invio DESC";
        $risultato = $conn->query($sql);
        $domande = ($risultato && $risultato->num_rows > 0) ? $risultato->fetch_all(MYSQLI_ASSOC) : [];
        return $domande;
    }

    public static function rispondiADomanda($idDomanda, $risposta) {
        $conn = DBConnection::getConnessione();
        // Impostiamo lettura_admin a 1 e lettura_user a 0 (notifica per l'utente)
        $stmt = $conn->prepare("UPDATE DOMANDE SET testo_risposta = ?, lettura_admin = 1, lettura_user = 0 WHERE idDomanda = ?");
        $stmt->bind_param("si", $risposta, $idDomanda);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }

    public static function segnaLettaAdmin($idDomanda) {
        $conn = DBConnection::getConnessione();
        $stmt = $conn->prepare("UPDATE DOMANDE SET lettura_admin = 1 WHERE idDomanda = ?");
        $stmt->bind_param("i", $idDomanda);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }

    // Recupera le notifiche (domande con risposta) per l'utente
    public static function getNotificheUtente($idUtente) {
        $conn = DBConnection::getConnessione();
        $sql = "SELECT idDomanda, testo_domanda, testo_risposta, data_invio, lettura_user
                FROM DOMANDE 
                WHERE idUtente = ? AND testo_risposta IS NOT NULL AND testo_risposta != ''
                ORDER BY data_invio DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUtente);
        $stmt->execute();
        $risultato = $stmt->get_result();
        
        $notifiche = [];
        if ($risultato && $risultato->num_rows > 0) {
            $notifiche = $risultato->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
        return $notifiche;
    }
}