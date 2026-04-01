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
}
