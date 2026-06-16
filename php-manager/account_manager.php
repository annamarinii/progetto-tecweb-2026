<?php

require_once "db_connection.php";
require_once "tool.php";

class AccountManager
{
    // Valida la forza della password: min 8 char, almeno una minuscola, maiuscola, numero e carattere speciale
    public static function validaPassword(string $password): bool
    {
        return (bool) preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/', $password);
    }

    /**
     * Verifica se un'email è già registrata. $escludiId permette di ignorare un
     * utente specifico (utile in update profilo: l'utente può mantenere la propria).
     */
    public static function emailEsiste(string $email, ?int $escludiId = null): bool
    {
        $conn = DBConnection::getConnessione();
        if ($escludiId === null) {
            $sql = "SELECT idUtente FROM UTENTE WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
        } else {
            $sql = "SELECT idUtente FROM UTENTE WHERE email = ? AND idUtente <> ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $email, $escludiId);
        }
        if (!$stmt) {
            return true; // in dubbio, blocca (fail-safe)
        }
        $stmt->execute();
        $esiste = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $esiste;
    }

    /**
     * Verifica se uno username è già in uso. $escludiId come sopra.
     */
    public static function usernameEsiste(string $username, ?int $escludiId = null): bool
    {
        $conn = DBConnection::getConnessione();
        if ($escludiId === null) {
            $sql = "SELECT idUtente FROM UTENTE WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
        } else {
            $sql = "SELECT idUtente FROM UTENTE WHERE username = ? AND idUtente <> ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $username, $escludiId);
        }
        if (!$stmt) {
            return true; // fail-safe
        }
        $stmt->execute();
        $esiste = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $esiste;
    }

    /**
     * Registra un nuovo utente.
     *
     * Validazione difensiva lato Manager: i dati sono ri-controllati QUI, a
     * prescindere dal controller chiamante o dalla validazione JS lato client.
     * Nessuna query di INSERT viene eseguita se un controllo fallisce.
     *
     * @return true|string  true se registrato; altrimenti un codice di errore:
     *   'dati_non_validi', 'email_esistente', 'username_esistente', oppure false su errore tecnico.
     */
    public static function registraUtente($nome, $cognome, $username, $email, $password)
    {
        $nome     = trim((string) $nome);
        $cognome  = trim((string) $cognome);
        $username = trim((string) $username);
        $email    = trim((string) $email);
        $password = (string) $password;

        // 1. Validazione logica (blocca prima di toccare il DB).
        //    validaEmailCompleta controlla formato E lunghezza (colonna VARCHAR(30)).
        if (!Tool::validaNomeProprio($nome) || !Tool::validaNomeProprio($cognome)
            || !Tool::validaUsername($username)
            || !Tool::validaEmailCompleta($email)
            || !self::validaPassword($password)) {
            return 'dati_non_validi';
        }

        // 2. Unicità: email e username non devono già esistere
        if (self::emailEsiste($email)) {
            return 'email_esistente';
        }
        if (self::usernameEsiste($username)) {
            return 'username_esistente';
        }

        // 3. Inserimento
        $conn = DBConnection::getConnessione();
        $password_criptata = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO UTENTE (username, email, password, nome, cognome) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("sssss", $username, $email, $password_criptata, $nome, $cognome);
            $esito = $stmt->execute();
            $stmt->close();
            return $esito === true ? true : false;
        }
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
            return $esiste;
        }
        return true;
    }

    // login
    public static function verificaLogin($identificativo, $password_inserita)
    {
        $identificativo = trim((string) $identificativo);
        $password_inserita = (string) $password_inserita;

        // Validazione minima: credenziali non vuote (nessuna query con input vuoto)
        if ($identificativo === '' || $password_inserita === '') {
            return false;
        }

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
                    return $utente;
                }
            }
            $stmt->close();
        }
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

        return $utente; //ci ritorna l'array con le informazioni dell'utente
    }

    /**
     * Aggiorna il profilo utente con validazione difensiva lato Manager.
     *
     * @return true|string  true se aggiornato; altrimenti codice di errore:
     *   'dati_non_validi', 'email_esistente', 'username_esistente', oppure false su errore tecnico.
     */
    public static function updateUtente($id, $nome, $cognome, $email, $username)
    {
        $id       = (int) $id;
        $nome     = trim((string) $nome);
        $cognome  = trim((string) $cognome);
        $email    = trim((string) $email);
        $username = trim((string) $username);

        // 1. Validazione logica (stesse regole della registrazione, coerenza garantita)
        if ($id <= 0
            || !Tool::validaNomeProprio($nome) || !Tool::validaNomeProprio($cognome)
            || !Tool::validaUsername($username)
            || !Tool::validaEmailCompleta($email)) {
            return 'dati_non_validi';
        }

        // 2. Unicità escludendo l'utente stesso (può tenere la propria email/username)
        if (self::emailEsiste($email, $id)) {
            return 'email_esistente';
        }
        if (self::usernameEsiste($username, $id)) {
            return 'username_esistente';
        }

        // 3. Aggiornamento
        $conn = DBConnection::getConnessione();
        $query = "UPDATE UTENTE SET nome = ?, cognome = ?, email = ?, username = ? WHERE idUtente = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ssssi", $nome, $cognome, $email, $username, $id);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito === true ? true : false;
    }
    public static function segnaRispostaComeLetta($idDomanda, $idUtente) {
        $conn = DBConnection::getConnessione();
        $sql = "UPDATE DOMANDA SET lettura_user = 1 WHERE idDomanda = ? AND idUtente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idDomanda, $idUtente);
        $esito = $stmt->execute();
        $stmt->close();
        return $esito;
    }
}

