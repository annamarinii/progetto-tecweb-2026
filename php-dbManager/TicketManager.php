<?php

require_once "DBConnection.php";

class TicketManager {
    public static function getInfoGroundPass($data_cercata) //data una data precisa ci restituisce quanti groundpass sono disponibili quel giorno e a quale prezzo
    {
        $conn = DBConnection::getConnessione();

        $sql = "SELECT B.prezzo, COUNT(B.idBiglietto) as quantita_disponibile 
                FROM BIGLIETTI B
                JOIN PROGRAMMA P ON B.idProgramma = P.idProgramma
                WHERE B.tipo = 'ground' 
                AND DATE(P.data) = ? 
                AND B.numero_ordine IS NULL 
                GROUP BY B.prezzo";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $data_cercata); // "s" perché passiamo una stringa tipo '2026-05-18'
        $stmt->execute();

        $risultato = $stmt->get_result();
        $info_biglietto = null;

        if ($risultato && $risultato->num_rows > 0) {
            $info_biglietto = $risultato->fetch_assoc();
        }

        $stmt->close();
        $conn->close();

        return $info_biglietto;
    }

    public static function getInfoSingleSession($data_cercata, $tipo_sessione) //data una data precisa e una sessione ("diurna" o "serale"), ci restituisca il listino prezzi esatto delle 4 tribune per quel singolo evento
    {
        $conn = DBConnection::getConnessione();

        $sql = "SELECT B.tribuna, B.prezzo, COUNT(B.idBiglietto) as quantita_disponibile 
                FROM BIGLIETTI B
                JOIN PROGRAMMA P ON B.idProgramma = P.idProgramma
                WHERE DATE(P.data) = ? 
                AND P.sessione = ? 
                AND B.numero_ordine IS NULL 
                AND B.tipo IS NULL
                GROUP BY B.tribuna, B.prezzo";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $data_cercata, $tipo_sessione);
        $stmt->execute();

        $risultato = $stmt->get_result();
        $info_sessione = [];

        if ($risultato && $risultato->num_rows > 0) {
            while($row = $risultato->fetch_assoc()) {
                $info_sessione[$row['tribuna']] = [
                    'prezzo' => $row['prezzo'],
                    'quantita_disponibile' => $row['quantita_disponibile']
                ];
            }
        }

        $stmt->close();
        $conn->close();

        return $info_sessione;
    }

    public static function getInfoAbbonamenti() //ci restituisce i prezi per gli abbonamenti in base alla tribuna
    {
        $conn = DBConnection::getConnessione();

        // 1. Contiamo i biglietti liberi (numero_ordine IS NULL) per le disponibilità.
        // 2. Estraiamo il MAX(prezzo) per avere il costo di un biglietto per quella sessione.
        // 3. Raggruppiamo tutto per tribuna per fare la Somma (prezzo_totale_base) e il Minimo (abbonamenti_disponibili).
        $sql = "SELECT 
                    tribuna, 
                    MIN(disponibili) as abbonamenti_disponibili,
                    SUM(prezzo_singolo) as prezzo_totale_base
                FROM (
                    SELECT 
                        B.tribuna, 
                        P.idProgramma, 
                        COUNT(CASE WHEN B.numero_ordine IS NULL THEN 1 END) as disponibili,
                        MAX(B.prezzo) as prezzo_singolo
                    FROM BIGLIETTI B
                    JOIN PROGRAMMA P ON B.idProgramma = P.idProgramma
                    WHERE B.tipo IS NULL
                    GROUP BY B.tribuna, P.idProgramma
                ) AS statistiche_sessioni
                GROUP BY tribuna
                HAVING COUNT(idProgramma) = 14";

        $risultato = $conn->query($sql);
        $dati_abbonamenti = [];

        if ($risultato && $risultato->num_rows > 0) {
            while($row = $risultato->fetch_assoc()) {
                $dati_abbonamenti[$row['tribuna']] = [
                    'disponibili' => $row['abbonamenti_disponibili'],
                    'prezzo_base' => $row['prezzo_totale_base']
                ];
            }
        }

        $conn->close();
        return $dati_abbonamenti;
    }


}