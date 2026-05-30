<?php

require_once "DBConnection.php";

class TicketManager
{
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
            while ($row = $risultato->fetch_assoc()) {
                $info_sessione[$row['tribuna']] = [
                    'prezzo' => $row['prezzo'],
                    'quantita_disponibile' => $row['quantita_disponibile']
                ];
            }
        }

        $stmt->close();

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
            while ($row = $risultato->fetch_assoc()) {
                $dati_abbonamenti[$row['tribuna']] = [
                    'disponibili' => $row['abbonamenti_disponibili'],
                    'prezzo_base' => $row['prezzo_totale_base']
                ];
            }
        }

        return $dati_abbonamenti;
    }

    public static function getDisponibilitaGroundPass(int $idProgramma): int
    {
        $conn = DBConnection::getConnessione();
        $sql  = "SELECT COUNT(idBiglietto) AS disponibili
                 FROM BIGLIETTI
                 WHERE tipo = 'ground' AND idProgramma = ? AND numero_ordine IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idProgramma);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['disponibili'] ?? 0);
    }

    public static function getDisponibilitaAbbonamento(string $tribuna): int
    {
        $conn = DBConnection::getConnessione();
        // Un abbonamento = 1 biglietto per ciascuna delle 14 sessioni nella stessa tribuna.
        // Il numero di abbonamenti disponibili = MIN(biglietti liberi per sessione).
        $sql  = "SELECT MIN(disponibili) AS disponibili
                 FROM (
                     SELECT COUNT(CASE WHEN B.numero_ordine IS NULL THEN 1 END) AS disponibili
                     FROM BIGLIETTI B
                     JOIN PROGRAMMA P ON B.idProgramma = P.idProgramma
                     WHERE B.tribuna = ? AND B.tipo IS NULL
                     GROUP BY P.idProgramma
                 ) AS sessioni
                 HAVING COUNT(*) = 14";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $tribuna);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['disponibili'] ?? 0);
    }

    public static function getCategorieBiglietti()
    {
        $conn = DBConnection::getConnessione();

        $res = $conn->query("SELECT MIN(prezzo) AS prezzo_min FROM BIGLIETTI WHERE tipo IS NULL");
        $prezzo_single = ($res && $res->num_rows > 0) ? (float)$res->fetch_assoc()['prezzo_min'] : null;

        $res = $conn->query("SELECT MIN(prezzo) AS prezzo_min FROM BIGLIETTI WHERE tipo = 'ground'");
        $prezzo_ground = ($res && $res->num_rows > 0) ? (float)$res->fetch_assoc()['prezzo_min'] : null;

        // L'abbonamento = somma dei prezzi minimi di ognuna delle 14 sessioni (tipo IS NULL)
        $res = $conn->query(
            "SELECT SUM(prezzo_min) AS prezzo_min
             FROM (
                 SELECT MIN(B.prezzo) AS prezzo_min
                 FROM BIGLIETTI B
                 JOIN PROGRAMMA P ON B.idProgramma = P.idProgramma
                 WHERE B.tipo IS NULL
                 GROUP BY P.idProgramma
             ) AS sessioni"
        );
        $prezzo_abb = ($res && $res->num_rows > 0) ? (float)$res->fetch_assoc()['prezzo_min'] : null;

        return [
            ['tipo' => 'abbonamento', 'prezzo_min' => $prezzo_abb],
            ['tipo' => 'single',      'prezzo_min' => $prezzo_single],
            ['tipo' => 'ground',      'prezzo_min' => $prezzo_ground],
        ];
    }

    public static function getBigliettiUtente($idUtente)
    {
        $conn = DBConnection::getConnessione();

        $sql = "SELECT B.idBiglietto, B.tipo, B.tribuna, B.prezzo, 
                   P.data, P.sessione, O.numero_ordine
            FROM BIGLIETTI B
            JOIN ORDINE O ON B.numero_ordine = O.numero_ordine
            JOIN PROGRAMMA P ON B.idProgramma = P.idProgramma
            WHERE O.idUtente = ?
            ORDER BY O.data_acquisto DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUtente);
        $stmt->execute();
        $risultato = $stmt->get_result();

        $biglietti_raggruppati = [];
        while ($row = $risultato->fetch_assoc()) {
            // Chiave unica per raggruppare i biglietti comprati insieme
            // Usiamo data e sessione come discriminanti aggiuntivi (per single session e ground pass)
            $tipo = $row['tipo'];
            $numero_ordine = $row['numero_ordine'];
            $tribuna = $row['tribuna'];
            
            if ($tipo == 'abbonamento') {
                $key = "{$numero_ordine}_abb_{$tribuna}";
                if (!isset($biglietti_raggruppati[$key])) {
                    $row['quantita'] = 0;
                    $row['conteggio_parziale'] = 0;
                    $biglietti_raggruppati[$key] = $row;
                }
                $biglietti_raggruppati[$key]['conteggio_parziale']++;
                // Un abbonamento è composto da 14 biglietti. 
                // Quindi ogni 14 biglietti trovati, la quantità reale di abbonamenti sale di 1.
                if ($biglietti_raggruppati[$key]['conteggio_parziale'] % 14 == 1) {
                    $biglietti_raggruppati[$key]['quantita']++;
                }
            } else {
                $data = $row['data'];
                $sessione = $row['sessione'];
                $key = "{$numero_ordine}_{$tipo}_{$tribuna}_{$data}_{$sessione}";
                
                if (!isset($biglietti_raggruppati[$key])) {
                    $row['quantita'] = 0;
                    $biglietti_raggruppati[$key] = $row;
                }
                $biglietti_raggruppati[$key]['quantita']++;
            }
        }

        $stmt->close();

        // Convertiamo l'array associativo in un array indicizzato
        return array_values($biglietti_raggruppati);
    }

}