<?php
require_once "DBConnection.php";

class FaqManager
{
    // 1. GESTIONE TABELLA "FAQ" (Contenuti Pubblici del Sito)

     // Recupera tutte le FAQ ufficiali per la pagina pubblica o l'admin
    public static function getFaq()
    {
        $conn = DBConnection::getConnessione();

        // Seleziono l'ID per permettere l'eliminazione, oltre ai testi
        $sql = "SELECT idFaq, testo_domanda, testo_risposta FROM FAQ";
        $risultato = $conn->query($sql);

        $faq_estratte = [];
        if ($risultato && $risultato->num_rows > 0) {
            $faq_estratte = $risultato->fetch_all(MYSQLI_ASSOC);
        }

        $conn->close();
        return $faq_estratte;
    }


     // Inserisce una nuova coppia Domanda/Risposta ufficiale (Tabella FAQ)
    public static function inserisciFaqUfficiale($testo_domanda, $testo_risposta)
    {
        $conn = DBConnection::getConnessione();

        $sql = "INSERT INTO FAQ (testo_domanda, testo_risposta) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $testo_domanda, $testo_risposta);

        $esito = $stmt->execute();

        $stmt->close();
        $conn->close();
        return $esito;
    }

    // Elimina una FAQ ufficiale tramite il suo ID

    public static function eliminaFaq($idFaq)
    {
        $conn = DBConnection::getConnessione();

        $sql = "DELETE FROM FAQ WHERE idFaq = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idFaq);

        $esito = $stmt->execute();

        $stmt->close();
        $conn->close();
        return $esito;
    }

     // Inserisce una domanda inviata da un utente loggato (Tabella DOMANDE)
    public static function inserisciDomanda($testo_domanda, $idUtente)
    {
        $conn = DBConnection::getConnessione();

        $sql = "INSERT INTO DOMANDE (testo_domanda, idUtente) VALUES (?, ?)";

        $stmt = $conn->prepare($sql);
        // "si" -> stringa (testo_domanda), intero (idUtente)
        $stmt->bind_param("si", $testo_domanda, $idUtente);

        $esito = $stmt->execute();

        $stmt->close();
        $conn->close();

        return $esito;
    }

     //Recupera le domande inviate dagli utenti per l'Admin (Tabella DOMANDE)
    public static function getDomandeUtenti()
    {
        $conn = DBConnection::getConnessione();

        // Recuperiamo anche lo username dell'utente che ha fatto la domanda
        $sql = "SELECT D.*, U.username 
                FROM DOMANDE D 
                JOIN UTENTE U ON D.idUtente = U.idUtente 
                WHERE D.testo_risposta IS NULL OR D.testo_risposta = ''
                ORDER BY D.data_invio DESC";
        
        $risultato = $conn->query($sql);
        $domande = ($risultato && $risultato->num_rows > 0) ? $risultato->fetch_all(MYSQLI_ASSOC) : [];
        
        $conn->close();
        return $domande;
    }
    public static function aggiornaFaq($idFaq, $domanda, $risposta) {
        $conn = DBConnection::getConnessione();
        $sql = "UPDATE FAQ SET testo_domanda = ?, testo_risposta = ? WHERE idFaq = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $domanda, $risposta, $idFaq);
        $esito = $stmt->execute();
        $stmt->close();
        $conn->close();
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
        $conn->close();
        return $notifiche;
    }
}
?>