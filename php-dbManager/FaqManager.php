<?php

require_once "DBConnection.php";

class FaqManager
{
    public static function getFaq()
    {
        $conn = DBConnection::getConnessione();

        // estraggo le robe
        $sql = "SELECT testo_domanda, testo_risposta FROM faq";
        $risultato = $conn->query($sql);

        $faq_estratte = [];
        if ($risultato && $risultato->num_rows > 0) {
            $faq_estratte = $risultato->fetch_all(MYSQLI_ASSOC);
        }

        $conn->close();
        return $faq_estratte;
    }
    public static function inserisciDomanda($testo_domanda, $idUtente)
    {
        $conn = DBConnection::getConnessione();

        $sql = "INSERT INTO DOMANDE (testo_domanda, idUtente) VALUES (?, ?)";

        $stmt = $conn->prepare($sql);
        // si una stringa e un intero
        $stmt->bind_param("si", $testo_domanda, $idUtente);

        $esito = $stmt->execute();

        $stmt->close();
        $conn->close();

        return $esito;
    }
}
