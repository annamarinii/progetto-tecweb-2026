<?php
require_once "../php-dbManager/AccountManager.php";
$messaggio_esito = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // pulizia dei dati
    $nome = htmlspecialchars(trim($_POST['nome']));
    $cognome = htmlspecialchars(trim($_POST['cognome']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $ripeti_password = $_POST['ripeti_password'];

    if ($password !== $ripeti_password) {
        $messaggio_esito = "<div class='error'>Errore: Le password non coincidono.</div>";

    } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s']{1,30}$/", $nome) || !preg_match("/^[a-zA-ZÀ-ÿ\s']{1,30}$/", $cognome)) {
        $messaggio_esito = "<div class='error'>Errore: Nome o cognome non validi (max 30 caratteri, solo lettere).</div>";

    } elseif (!preg_match("/^[a-zA-Z0-9._]{1,16}$/", $username)) {
        $messaggio_esito = "<div class='error'>Errore: Username non valido (max 16 caratteri alfanumerici).</div>";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $messaggio_esito = "<div class='error'>Errore: Indirizzo email non valido.</div>";

    } elseif (strlen($password) < 8 || strlen($password) > 20) {
        $messaggio_esito = "<div class='error'>Errore: La password deve avere tra 8 e 20 caratteri.</div>";

    } else {
        $utente_esiste = AccountManager::check($username, $email);

        if ($utente_esiste) {
            $messaggio_esito = "<div class='error'>Errore: Username o Email già in uso. Scegline altri.</div>";
        } else {
            $salvato = AccountManager::registraUtente($username, $email, $password, $nome, $cognome);

            if ($salvato) {
                $messaggio_esito = "<div class='success'>Registrazione completata! <a href='login.php'>Fai il Login</a></div>";
            } else {
                $messaggio_esito = "<div class='error'>Errore tecnico durante il salvataggio. Riprova.</div>";
            }
        }
    }
}

$pagina_html = file_get_contents('../html/registrazione.html');
$pagina_finita = str_replace('[messaggio_esito]', $messaggio_esito, $pagina_html);

echo $pagina_finita;
?>