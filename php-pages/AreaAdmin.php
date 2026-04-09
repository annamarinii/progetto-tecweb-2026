<?php

require_once '../php-dbManager/init_session.php';
require_once '../php-dbManager/AccountManager.php';
require_once '../php-dbManager/NewsManager.php';

// Controllo sicurezza base (utente loggato)
if (!isset($_SESSION['idUtente'])) {
    header("Location: login.php");
    exit();
}

$id_utente_corrente = $_SESSION['idUtente'];
$dati_utente = AccountManager::getUtenteById($id_utente_corrente);

// Se per qualche motivo manca l'utente, lo facciamo scloggare
if (!$dati_utente) {
    header("Location: Login.php");
    exit();
}

$nome_pulito = htmlspecialchars($dati_utente['nome'] ?? 'Admin');
$cognome_pulito = htmlspecialchars($dati_utente['cognome'] ?? '');
$email_pulita = htmlspecialchars($dati_utente['email'] ?? '');
$username_pulito = htmlspecialchars($dati_utente['username'] ?? '');

// ===================================
// GESTIONE DINAMICA DELLE NEWS
// ===================================

$newsArray = NewsManager::getNews();
$html_miniature = '';

if (empty($newsArray)) {
    $html_miniature = '<p>Nessuna news pubblicata al momento.</p>';
} else {
    foreach ($newsArray as $news) {
        $id = htmlspecialchars($news['idNews']);
        $titolo = htmlspecialchars($news['titolo']);
        $testo_completo = htmlspecialchars($news['testo']); // Lo passiamo di nascosto per Javascript!
        
        // Pulizia percorso immagine
        $immagine = htmlspecialchars($news['immagine']);
        if (empty($immagine)) {
            $immagine = '../assets/images/default-news.jpg';
        } else if (strpos($immagine, '../') !== 0 && strpos($immagine, 'http') !== 0) {
            // Se nel DB è 'assets/images/foto.jpg', noi siamo in php-pages/, quindi da areadmin serve ../
            $immagine = '../' . ltrim($immagine, '/');
        }

        // Formattazione data italiana
        $data_obj = new DateTime($news['data_pubblicazione']);
        $data_formattata = $data_obj->format('d/m/Y');
        
        $html_miniature .= '
            <button type="button" class="news-mini-card" data-news-id="' . $id . '">
                <img src="' . $immagine . '" alt="Copertina news" class="mini-card-img">
                <div class="mini-card-info">
                    <h4>' . $titolo . '</h4>
                    <span class="mini-date">' . $data_formattata . '</span>
                </div>
                <!-- Div nascosto dal quale JS copierà il vero testo completo della news -->
                <div class="news-full-text" style="display:none;">' . $testo_completo . '</div>
            </button>
        ';
    }
}

// ===================================
// RENDER DELLA PAGINA HTML
// ===================================

$pagina_html = file_get_contents('../html/areadmin.html');

// Template testate classiche
$pagina_html = str_replace('[nome_utente]', $nome_pulito, $pagina_html);
$pagina_html = str_replace('[cognome_utente]', $cognome_pulito, $pagina_html);
$pagina_html = str_replace('[email_utente]', $email_pulita, $pagina_html);
$pagina_html = str_replace('[username_utente]', $username_pulito, $pagina_html);

// Rimpiazzo delle miniature News Dinamiche
$pagina_html = str_replace('[lista_news_miniature]', $html_miniature, $pagina_html);

// Se hai altri placeholder in futuro si aggiungono qui

echo $pagina_html;
