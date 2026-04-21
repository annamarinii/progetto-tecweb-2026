<?php

require_once "DBConnection.php";

class AccountManager
{
    // registrazione
    public static function registraUtente($username, $email, $password_in_chiaro, $nome, $cognome)
    {
        $conn = DBConnection::getConnessione();

        $password_criptata = password_hash($password_in_chiaro, PASSWORD_DEFAULT);

        $sql = "INSERT INTO UTENTE (username, email, password, nome, cognome) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // "sssss" perchè sto passando 5 stringhe
            $stmt->bind_param("sssss", $username, $email, $password_criptata, $nome, $cognome);

            // esegue la query
            $esito = $stmt->execute();

            $stmt->close();
            $conn->close();

            // restituisce true se salvato, false se errore
            return $esito;
        }

        $conn->close();
        return false;
    }
    // controllo se ci sono duplicati in fase di registrazione
    public static function check($username, $email)
    {
        $conn = DBConnection::getConnessione();

        $sql = "SELECT idUtente FROM UTENTE WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $risultato = $stmt->get_result();

            // se esiste vuol dire che il numero di rows sarà maggiore di zero
            $esiste = $risultato->num_rows > 0;

            $stmt->close();
            $conn->close();
            return $esiste;
        }
        $conn->close();
        return true;
    }

    // login
    public static function verificaLogin($identificativo, $password_inserita)
    {
        $conn = DBConnection::getConnessione();

        // cerco corrispondenze con l'email o con lo username
        $sql = "SELECT idUtente, username, email, password, nome, cognome, isAdmin FROM UTENTE WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ss", $identificativo, $identificativo);
            $stmt->execute();
            $risultato = $stmt->get_result();

            if ($risultato->num_rows === 1) {
                $utente = $risultato->fetch_assoc();

                if (password_verify($password_inserita, $utente['password'])) {
                    unset($utente['password']);

                    $stmt->close();
                    $conn->close();
                    return $utente;
                }
            }
            $stmt->close();
        }
        $conn->close();
        return false;
    }
    public static function getUtenteById($idUtente)
    {
        $conn = DBConnection::getConnessione();

        $sql = "SELECT * FROM UTENTE WHERE idUtente = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUtente); // i sta per numero intero
        $stmt->execute();

        $risultato = $stmt->get_result();
        $utente = null;

        if ($risultato && $risultato->num_rows > 0) {
            $utente = $risultato->fetch_assoc();
        }

        $stmt->close();
        $conn->close();

        return $utente; //ci ritorna l'array con le informazioni dell'utente
    }

    public static function updateUtente($id, $nome, $cognome, $email, $username)
    {
        $conn = DBConnection::getConnessione();

        $query = "UPDATE UTENTE SET nome = ?, cognome = ?, email = ?, username = ? WHERE idUtente = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $nome, $cognome, $email, $username, $id);

        return $stmt->execute();
    }
    public static function segnaRispostaComeLetta($idDomanda, $idUtente) {
        $conn = DBConnection::getConnessione();
        $sql = "UPDATE DOMANDE SET lettura_user = 1 WHERE idDomanda = ? AND idUtente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idDomanda, $idUtente);
        $esito = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $esito;
    }
}

