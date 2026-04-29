<?php

require_once "DBConnection.php";

class CarrelloManager {
    public static function creaOrdine($idUtente, $totale) {
        $conn = DBConnection::getConnessione();
        $sql = "INSERT INTO ORDINE (totale, idUtente) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $totale, $idUtente);
        $stmt->execute();
        $idOrdine = $stmt->insert_id;
        $stmt->close();
        $conn->close();
        return $idOrdine;
    }


    public static function associaBiglietto($idOrdine, $item) {
        $conn = DBConnection::getConnessione();

        $tipo_carrello = strtolower(trim($item['tipologia']));
        $titolo_pulito = trim($item['titolo']);
        $idProg = (int)$item['idProgramma'];
        $quantita = (int)$item['quantita'];

        if ($tipo_carrello === 'abbonamento') {
            $sql = "UPDATE BIGLIETTI SET numero_ordine = ?, tipo = 'abbonamento' WHERE tribuna = ? AND idProgramma = ? AND numero_ordine IS NULL LIMIT ?";
            $stmt = $conn->prepare($sql);
            for ($i = 1; $i <= 14; $i++) {
                $stmt->bind_param("isii", $idOrdine, $titolo_pulito, $i, $quantita);
                $stmt->execute();
            }
            $stmt->close();

        } else if ($tipo_carrello === 'ground pass' || $tipo_carrello === 'ground') {
            $sql = "UPDATE BIGLIETTI SET numero_ordine = ? WHERE tipo = 'ground' AND idProgramma = ? AND numero_ordine IS NULL LIMIT ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $idOrdine, $idProg, $quantita);
            $stmt->execute();
            $stmt->close();

        } else {
            // BIGLIETTO SINGOLO
            $sql = "UPDATE BIGLIETTI SET numero_ordine = ? WHERE tribuna = ? AND idProgramma = ? AND numero_ordine IS NULL LIMIT ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isii", $idOrdine, $titolo_pulito, $idProg, $quantita);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                die("<h1>ERRORE DATABASE</h1><p>Non ho trovato nessun biglietto libero per:<br>Tribuna: <b>{$titolo_pulito}</b><br>ID Programma: <b>{$idProg}</b></p>");
            }
            $stmt->close();
        }
        $conn->close();
    }
}