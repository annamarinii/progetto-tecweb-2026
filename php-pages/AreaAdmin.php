<?php

require_once '../php-dbManager/init_session.php';
require_once '../php-dbManager/AccountManager.php';
require_once '../php-dbManager/NewsManager.php';

// 2. CONTROLLO SICUREZZA (Solo admin loggati)
if (!isset($_SESSION['idUtente'])) {
    header("Location: Login.php");
    exit();
}

$id_utente_corrente = $_SESSION['idUtente'];
$dati_utente = AccountManager::getUtenteById($id_utente_corrente);

if (!$dati_utente || $dati_utente['isAdmin'] == 0) {
    header("Location: Login.php"); // Reindirizza se non è admin
    exit();
}

// ==========================================
// 3. SEZIONE SCRITTURA (Gestione POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $target_dir = "../assets/images/";

    // Recupero dati dal form
    $idNews = isset($_POST['id_news']) ? $_POST['id_news'] : null;
    $titolo = isset($_POST['titolo']) ? $_POST['titolo'] : '';
    $testo = isset($_POST['testo']) ? $_POST['testo'] : '';
    $inEvidenza = isset($_POST['inEvidenza']) ? 1 : 0;
    $immagine_path = "";

    // Gestione Caricamento File Immagine
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['immagine']['tmp_name'];
        $file_type = strtolower(pathinfo($_FILES['immagine']['name'], PATHINFO_EXTENSION));

        if (getimagesize($tmp_name) !== false) {
            $new_filename = "news_" . time() . "." . $file_type;
            if (move_uploaded_file($tmp_name, $target_dir . $new_filename)) {
                $immagine_path = "assets/images/" . $new_filename;
            }
        }
    }

    // Esecuzione tramite NewsManager (LOGICA UNIFICATA)
    if (isset($_POST['elimina']) && $_POST['elimina'] == 'si') {
        $esito = NewsManager::eliminaNews($idNews);
    } else if ($idNews && $idNews != "") {
        $esito = NewsManager::aggiornaNews($idNews, $titolo, $testo, $immagine_path, $inEvidenza);
    } else {
        // Nuovo inserimento
        $img_to_save = ($immagine_path != "") ? $immagine_path : 'assets/images/default-news.jpg';
        $esito = NewsManager::inserisciNews($titolo, $testo, $img_to_save, $id_utente_corrente, $inEvidenza);
    }

    // REDIRECT alla pagina stessa per pulire il buffer ed evitare doppi invii
    $status = $esito ? "success" : "error";
    header("Location: AreaAdmin.php?status=" . $status);
    exit();
}

// ==========================================
// 4. SEZIONE LETTURA (Preparazione Pagina)
// ==========================================

// Dati Utente per la testata
$nome_pulito = htmlspecialchars($dati_utente['nome']);
$cognome_pulito = htmlspecialchars($dati_utente['cognome']);
$username_pulito = htmlspecialchars($dati_utente['username']);

// Recupero News per le miniature (Dashboard Admin)
$newsArray = NewsManager::getNews();
$html_miniature = '';

if (empty($newsArray)) {
    $html_miniature = '<p>Nessuna news pubblicata al momento.</p>';
} else {
    foreach ($newsArray as $news) {
        $id = htmlspecialchars($news['idNews']);
        $titolo_news = htmlspecialchars($news['titolo']);
        $testo_news = htmlspecialchars($news['testo']);

        // Percorso immagine per anteprima admin
        $immagine = htmlspecialchars($news['immagine']);
        $percorso_anteprima = (empty($immagine)) ? '../assets/images/default-news.jpg' : '../' . $immagine;

        $html_miniature .= '
            <button type="button" class="news-mini-card" data-news-id="' . $id . '">
                <img src="' . $percorso_anteprima . '" alt="" class="mini-card-img">
                <div class="mini-card-info">
                    <h4>' . $titolo_news . '</h4>
                </div>
                <div class="news-full-text" style="display:none;">' . $testo_news . '</div>
            </button>';
    }
}

// Gestione Messaggi di Esito
$messaggio_esito = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $messaggio_esito = "<div class='success' style='color:green; padding:10px; border:1px solid green; margin-bottom:20px;'>Operazione completata con successo!</div>";
    } else {
        $messaggio_esito = "<div class='error' style='color:red; padding:10px; border:1px solid red; margin-bottom:20px;'>Errore durante l'aggiornamento dei dati.</div>";
    }
}

// Caricamento del Template HTML
$pagina_html = file_get_contents('../html/areaadmin.html');

// Sostituzioni finali
$pagina_html = str_replace('[nome_utente]', $nome_pulito, $pagina_html);
$pagina_html = str_replace('[cognome_utente]', $cognome_pulito, $pagina_html);
$pagina_html = str_replace('[username_utente]', $username_pulito, $pagina_html);
$pagina_html = str_replace('[lista_news_miniature]', $html_miniature, $pagina_html);
$pagina_html = str_replace('[messaggio_esito]', $messaggio_esito, $pagina_html);

echo $pagina_html;
?>