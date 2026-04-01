<?php

class DBConnection
{
    public static function getConnessione()
    {
        $host = "localhost";
        $user = "root";
        $pass = "root";
        $db = "pataviumopen";

        $connessione = new mysqli($host, $user, $pass, $db);
        if ($connessione->connect_error) {
            die("Errore DB: " . $connessione->connect_error);
        }
        return $connessione;
    }
}
