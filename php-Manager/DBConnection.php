<?php

class DBConnection
{
    // 1. Costanti senza modificatore di visibilità
    const DB_HOST = "localhost";
    const DB_USER = "root";
    const DB_PASS = "root";
    const DB_NAME = "pataviumopen";

    // 2. La proprietà statica (le variabili la supportavano già) rimane privata
    private static $connessione = null;

    public static function getConnessione()
    {
        // Se la connessione non c'è, la stabiliamo
        if (self::$connessione === null) {

            // Usiamo self:: per richiamare le costanti della classe stessa
            self::$connessione = new mysqli(
                self::DB_HOST,
                self::DB_USER,
                self::DB_PASS,
                self::DB_NAME
            );

            if (self::$connessione->connect_error) {
                die("Errore DB: " . self::$connessione->connect_error);
            }
        }

        return self::$connessione;
    }
}
