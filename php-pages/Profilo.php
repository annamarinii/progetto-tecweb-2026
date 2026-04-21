<?php
require_once '../php-dbManager/init_session.php';

if (!isset($_SESSION['idUtente'])) {
    header("Location: Login.php");
    exit();
}

$file_html = (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1) 
             ? '../html/areaadmin.html' 
             : '../html/areautente.html';

$pagina_html = file_get_contents($file_html);

$pagina_html = str_replace('[nome_utente]', $_SESSION['nome'] ?? '', $pagina_html);
$pagina_html = str_replace('[cognome_utente]', $_SESSION['cognome'] ?? '', $pagina_html);
$pagina_html = str_replace('[email_utente]', $_SESSION['email'] ?? '', $pagina_html);
$pagina_html = str_replace('[username_utente]', $_SESSION['username'] ?? '', $pagina_html);

$pagina_html = str_replace('[tipo_biglietto]', 'Single Session', $pagina_html);
$pagina_html = str_replace('[idBiglietto]', '0000', $pagina_html);

echo $pagina_html;
?>