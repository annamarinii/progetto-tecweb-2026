<?php

class DBConnection
{
    private static $connessione = null;
    private static $config = null;

    private static function getConfig()
    {
        if (self::$config === null) {
            $configFile = __DIR__ . '/../config/database.local.php';

            if (!file_exists($configFile)) {
                error_log("DB config mancante: " . $configFile . " (clona config/database.example.php in config/database.local.php)");
                header("Location: /progetto_tecweb_2026/php-pages/500.php");
                exit;
            }

            self::$config = require_once $configFile;
        }

        return self::$config;
    }

    public static function getConnessione()
    {
        // Se la connessione non c'è, la stabiliamo
        if (self::$connessione === null) {

            $config = self::getConfig();

            self::$connessione = new mysqli(
                $config['host'],
                $config['user'],
                $config['pass'],
                $config['name']
            );

            if (self::$connessione->connect_error) {
                error_log("Errore connessione DB: " . self::$connessione->connect_error);
                header("Location: /progetto_tecweb_2026/php-pages/500.php");
                exit;
            }
        }

        return self::$connessione;
    }
}
