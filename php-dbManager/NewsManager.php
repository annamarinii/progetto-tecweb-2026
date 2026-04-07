<?php

require_once "DBConnection.php";

class NewsManager
{
    public static function getNews()
    {
        $conn = DBConnection::getConnessione();
        $sql = "SELECT N.titolo, N.testo, N.data_pubblicazione, N.immagine, U.nome, U.cognome 
                FROM NEWS N 
                JOIN UTENTE U ON N.idAutore = U.idUtente 
                ORDER BY N.data_pubblicazione DESC";
        
        $risultato = $conn->query($sql);

        $news_estratte = [];
        if ($risultato && $risultato->num_rows > 0) {
            $news_estratte = $risultato->fetch_all(MYSQLI_ASSOC);
        }

        $conn->close();
        return $news_estratte;
    }
}