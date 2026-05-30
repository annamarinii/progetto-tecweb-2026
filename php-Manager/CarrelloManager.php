<?php

require_once "DBConnection.php";

class CarrelloManager {

    /**
     * Metodo pubblico unificato: crea ordine + associa biglietti in un'unica
     * transazione atomica. Commit se tutto va a buon fine, rollback su eccezione.
     * Restituisce l'idOrdine appena creato.
     */
    public static function processaAcquisto(int $idUtente, float $totale, array $carrello): int {
        $conn = DBConnection::getConnessione();
        $conn->begin_transaction();

        try {
            $idOrdine = self::creaOrdine($idUtente, $totale);

            foreach ($carrello as $item) {
                self::associaBiglietto($idOrdine, $item);
            }

            $conn->commit();
            return $idOrdine;

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    // -------------------------------------------------------------------
    // Metodi privati: recuperano la connessione dal Singleton internamente
    // -------------------------------------------------------------------

    private static function creaOrdine(int $idUtente, float $totale): int {
        $conn = DBConnection::getConnessione();

        $sql  = "INSERT INTO ORDINE (totale, idUtente) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $totale, $idUtente); // "d"=double per i centesimi
        $stmt->execute();
        $idOrdine = $stmt->insert_id;
        $stmt->close();

        return $idOrdine;
    }

    private static function associaBiglietto(int $idOrdine, array $item): void {
        $conn          = DBConnection::getConnessione();
        $tipo_carrello = strtolower(trim($item['tipologia']));
        $titolo_pulito = trim($item['titolo']);
        $idProg        = (int) $item['idProgramma'];
        $quantita      = (int) $item['quantita'];

        if ($tipo_carrello === 'abbonamento') {
            // Un abbonamento copre tutti e 14 i programmi settimanali.
            // bind_param è chiamato UNA SOLA VOLTA fuori dal ciclo (lavora per riferimento).
            // Dentro il ciclo aggiorniamo solo $idProgrammaLoop e richiamiamo execute().
            $sql  = "UPDATE BIGLIETTI
                        SET numero_ordine = ?, tipo = 'abbonamento'
                      WHERE tribuna = ?
                        AND idProgramma = ?
                        AND numero_ordine IS NULL
                      LIMIT ?";
            $stmt          = $conn->prepare($sql);
            $idProgrammaLoop = 0;
            $stmt->bind_param("isii", $idOrdine, $titolo_pulito, $idProgrammaLoop, $quantita);

            for ($i = 1; $i <= 14; $i++) {
                $idProgrammaLoop = $i;
                $stmt->execute();
            }
            $stmt->close();

        } elseif ($tipo_carrello === 'ground pass' || $tipo_carrello === 'ground') {
            $sql  = "UPDATE BIGLIETTI
                        SET numero_ordine = ?
                      WHERE tipo = 'ground'
                        AND idProgramma = ?
                        AND numero_ordine IS NULL
                      LIMIT ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $idOrdine, $idProg, $quantita);
            $stmt->execute();
            $stmt->close();

        } else {
            // Single Session o qualsiasi altro tipo
            $sql  = "UPDATE BIGLIETTI
                        SET numero_ordine = ?
                      WHERE tribuna = ?
                        AND idProgramma = ?
                        AND numero_ordine IS NULL
                      LIMIT ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isii", $idOrdine, $titolo_pulito, $idProg, $quantita);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Nessun biglietto disponibile per la Tribuna: {$titolo_pulito}");
            }
            $stmt->close();
        }
    }
}
