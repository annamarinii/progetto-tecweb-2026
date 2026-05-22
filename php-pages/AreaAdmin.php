<?php
require_once '../php-Manager/init_session.php';
require_once '../php-Manager/AccountManager.php';
require_once '../php-Manager/NewsManager.php';
require_once '../php-Manager/FaqManager.php';
require_once '../php-Manager/DBConnection.php';
require_once '../php-Manager/Tool.php';

// CONTROLLO ACCESSO RIGIDO
if (!Tool::isLoggedIn() || !Tool::isAdmin()) {
    header("Location: ../html/403.html"); 
    exit();
}

$id_utente_corrente = $_SESSION['idUtente'];
$dati_utente = AccountManager::getUtenteById($id_utente_corrente);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $esito = false;
    $ancora = "";
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    // --- GESTIONE NEWS (LATO INGRESSO: SALVATAGGIO PURO NEL DB) ---
    if (isset($_POST['titolo']) || (isset($_POST['elimina']) && $_POST['elimina'] == 'si')) { 
        $idNews = isset($_POST['id_news']) ? $_POST['id_news'] : null;
        // Non si convertono le entità in ingresso per salvare dati puri. Si ripulisce solo da tag dannosi se necessario.
        $titolo = isset($_POST['titolo']) ? trim(strip_tags($_POST['titolo'])) : '';
        $testo = isset($_POST['testo']) ? trim(strip_tags($_POST['testo'])) : '';
        $inEvidenza = isset($_POST['inEvidenza']) ? 1 : 0;
        $immagine_path = "";

        $upload_msg = "";
        if (isset($_FILES['immagine'])) {
            if ($_FILES['immagine']['error'] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['immagine']['tmp_name'];
                $file_type = strtolower(pathinfo($_FILES['immagine']['name'], PATHINFO_EXTENSION));
                $new_filename = "news_" . time() . "." . $file_type;
                if (move_uploaded_file($tmp_name, "../assets/images/" . $new_filename)) {
                    $immagine_path = "assets/images/" . $new_filename;
                } else {
                    $upload_msg = "Impossibile spostare il file nel server.";
                }
            } else if ($_FILES['immagine']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload_msg = "Errore caricamento file (Codice PHP: " . $_FILES['immagine']['error'] . "). Forse il file è troppo pesante.";
            }
        }

        if (isset($_POST['elimina']) && $_POST['elimina'] == 'si') {
            $esito = NewsManager::eliminaNews($idNews);
        } else if ($idNews && $idNews != "") {
            $esito = NewsManager::aggiornaNews($idNews, $titolo, $testo, $immagine_path, $inEvidenza);
        } else {
            $img_to_save = ($immagine_path != "") ? $immagine_path : 'assets/images/logo1.png';
            $esito = NewsManager::inserisciNews($titolo, $testo, $img_to_save, $id_utente_corrente, $inEvidenza);
        }
        $ancora = "#gestione-news";

        if ($isAjax) {
            $newsArray = NewsManager::getNews();
            $html_news = "";
            foreach ($newsArray as $news) {
                // LATO USCITA: Sanificazione rigorosa contro vulnerabilità XSS prima di iniettare nell'HTML
                $titolo_pulito = Tool::pulisciInput($news['titolo']);
                $testo_pulito = Tool::pulisciInput($news['testo']);
                $percorso = (empty($news['immagine'])) ? '../assets/images/logo1.png' : '../' . Tool::pulisciInput($news['immagine']);
                $html_news .= '<button type="button" class="news-mini-card" data-news-id="'.htmlspecialchars($news['idNews']).'">
                    <img src="'.$percorso.'" alt="" class="mini-card-img">
                    <div class="mini-card-info"><h4>'.$titolo_pulito.'</h4></div>
                    <div class="news-full-text">'.$testo_pulito.'</div>
                </button>';
            }
            echo json_encode(['status' => ($esito ? 'success' : 'error'), 'upload_msg' => $upload_msg, 'html_miniature' => $html_news]);
            exit();
        }
    }

    // --- GESTIONE FAQ (LATO INGRESSO: SALVATAGGIO PURO NEL DB) ---
    else if (isset($_POST['domanda_faq']) || isset($_POST['elimina_faq'])) {
        if (isset($_POST['elimina_faq'])) {
            $esito = FaqManager::eliminaFaq($_POST['id_faq_elimina']);
        } else {
            $idFaq = isset($_POST['idFaq']) ? $_POST['idFaq'] : null;
            // Solo trim e strip tags per il database
            $domanda_faq = trim(strip_tags($_POST['domanda_faq']));
            $risposta_faq = trim(strip_tags($_POST['risposta_faq']));
            
            if ($idFaq && $idFaq != "") {
                $esito = FaqManager::aggiornaFaq($idFaq, $domanda_faq, $risposta_faq);
            } else {
                $esito = FaqManager::inserisciFaqUfficiale($domanda_faq, $risposta_faq);
            }
        }
        $ancora = "#gestione-faq";

        if ($isAjax) {
            $faqArray = FaqManager::getFaq();
            $html_faq = "";
            foreach ($faqArray as $f) {
                // LATO USCITA: XSS protection
                $q_pulito = Tool::pulisciInput($f['testo_domanda']);
                $a_pulito = Tool::pulisciInput($f['testo_risposta']);
                $html_faq .= '
                <div class="faq-admin-card">
                    <div class="faq-card-content">
                        <p class="faq-q">Q: '.$q_pulito.'</p>
                        <p class="faq-a">A: '.$a_pulito.'</p>
                    </div>
                    <div class="faq-actions">
                        <button type="button" class="btn-edit-faq-trigger" 
                                data-id="'.$f['idFaq'].'" 
                                data-q="'.$q_pulito.'" 
                                data-a="'.$a_pulito.'">Modifica</button>
                        <form action="AreaAdmin.php" method="POST" class="form-delete-faq">
                            <input type="hidden" name="elimina_faq" value="si">
                            <input type="hidden" name="id_faq_elimina" value="'.$f['idFaq'].'">
                            <button type="submit" class="btn-delete-faq-ajax">Elimina</button>
                        </form>
                    </div>
                </div>';
            }
            echo json_encode(['status' => ($esito ? 'success' : 'error'), 'html_faq' => $html_faq]);
            exit();
        }
    }

    // --- RISPOSTA DOMANDA UTENTE ---
    else if (isset($_POST['risposta_utente'])) {
        $risposta = trim(strip_tags($_POST['risposta']));
        $esito = FaqManager::rispondiADomanda($_POST['id_domanda'], $risposta);
        $ancora = "#nuove-domande";
        if ($isAjax) {
            echo json_encode(['status' => ($esito ? 'success' : 'error')]);
            exit();
        }
    }

    // --- SEGNA DOMANDA COME LETTA ---
    else if (isset($_POST['segna_letta_admin'])) {
        $esito = FaqManager::segnaLettaAdmin($_POST['id_domanda']);
        if ($isAjax) {
            echo json_encode(['status' => ($esito ? 'success' : 'error')]);
            exit();
        }
    }

    header("Location: AreaAdmin.php?status=" . ($esito ? "success" : "error") . "&t=" . time() . $ancora);
    exit();
}

// ==========================================
// SEZIONE LETTURA (Render Pagina)
// ==========================================

$nome_pulito = Tool::pulisciInput($dati_utente['nome']);
$cognome_pulito = Tool::pulisciInput($dati_utente['cognome']);
$username_pulito = Tool::pulisciInput($dati_utente['username']);

$newsArray = NewsManager::getNews();
$html_miniature = !empty($newsArray) ? "" : "<p>Nessuna news pubblicata.</p>";
foreach ($newsArray as $news) {
    $img = (empty($news['immagine'])) ? '../assets/images/logo1.png' : '../' . Tool::pulisciInput($news['immagine']);
    $titolo = Tool::pulisciInput($news['titolo']);
    $testo = Tool::pulisciInput($news['testo']);
    $html_miniature .= '
        <button type="button" class="news-mini-card" data-news-id="'.htmlspecialchars($news['idNews']).'">
            <img src="'.$img.'" alt="" class="mini-card-img">
            <div class="mini-card-info"><h4>'.$titolo.'</h4></div>
            <div class="news-full-text">'.$testo.'</div>
        </button>';
}

$faqArray = FaqManager::getFaq();
$html_faq = !empty($faqArray) ? "" : "<p>Nessuna FAQ pubblicata.</p>";
foreach ($faqArray as $f) {
    $q = Tool::pulisciInput($f['testo_domanda']);
    $a = Tool::pulisciInput($f['testo_risposta']);
    $html_faq .= '
        <div class="faq-admin-card">
            <div class="faq-card-content">
                <p class="faq-q">Q: '.$q.'</p>
                <p class="faq-a">A: '.$a.'</p>
            </div>
            <div class="faq-actions">
                <button type="button" class="btn-edit-faq-trigger" 
                        data-id="'.$f['idFaq'].'" 
                        data-q="'.$q.'" 
                        data-a="'.$a.'">Modifica</button>
                <form action="AreaAdmin.php" method="POST" class="form-delete-faq">
                    <input type="hidden" name="elimina_faq" value="si">
                    <input type="hidden" name="id_faq_elimina" value="'.$f['idFaq'].'">
                    <button type="submit" class="btn-delete-faq-ajax delete-confirm">Elimina</button>
                </form>
            </div>
        </div>';
}

// Preparazione Domande Utenti
$domandeUtenti = FaqManager::getDomandeUtenti();
$html_domande = !empty($domandeUtenti) ? "" : "<p class='no-data-msg'>Nessuna nuova domanda.</p>";
foreach ($domandeUtenti as $d) {
    $status = $d['lettura_admin'] ? 'read' : 'unread';
    // LATO USCITA: XSS protection su domande/username dal database
    $domanda = Tool::pulisciInput($d['testo_domanda']);
    $utente = Tool::pulisciInput($d['username']);
    
    $html_domande .= '
        <li class="mail-row '.$status.'" data-id="'.$d['idDomanda'].'">
            <div class="mail-row-header">
                <div class="read-dot" aria-hidden="true"></div>
                <div class="sender">'.$utente.'</div>
                <div class="subject">'.htmlspecialchars(substr($d['testo_domanda'], 0, 50)).'...</div>
                <div class="date">'.date("d/m H:i", strtotime($d['data_invio'])).'</div>
            </div>
            <div class="mail-content hidden-content">
                <p class="full-message">"'.$domanda.'"</p>';
    
    if (isset($d['testo_risposta']) && $d['testo_risposta'] != "") {
        $risposta = Tool::pulisciInput($d['testo_risposta']);
        $html_domande .= '<p class="reply-given"><strong>Risposta data:</strong> '.$risposta.'</p>';
    } else {
        $html_domande .= '
                <form action="AreaAdmin.php" method="POST" class="mail-reply-form">
                    <input type="hidden" name="risposta_utente" value="si">
                    <input type="hidden" name="id_domanda" value="'.$d['idDomanda'].'">
                    <label for="risposta_'.$d['idDomanda'].'" class="sr-only">Scrivi la tua risposta</label>
                    <textarea id="risposta_'.$d['idDomanda'].'" name="risposta" rows="3" required placeholder="Rispondi..."></textarea>
                    <button type="submit" class="btn-auth">Invia</button>
                </form>';
    }
    $html_domande .= '</div></li>';
}

// Preparazione Messaggio Esito
$messaggio_esito = "";
if (isset($_GET['status'])) {
    $tipo_status = ($_GET['status'] == 'success') ? 'esito-success' : 'esito-error';
    $testo_status = ($_GET['status'] == 'success') ? 'Operazione completata!' : 'Errore!';
    $messaggio_esito = "<div class='esito-msg $tipo_status' role='alert' aria-live='assertive'>$testo_status</div>";
}

$pagina_html = file_get_contents('../html/areaadmin.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('areaadmin'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('areaadmin'), $pagina_html);

$search = ['[nome_utente]','[cognome_utente]','[username_utente]','[lista_news_miniature]','[lista_faq_per_modifica]','[lista_domande_utenti]','[messaggio_esito]'];
$replace = [$nome_pulito, $cognome_pulito, $username_pulito, $html_miniature, $html_faq, $html_domande, $messaggio_esito];
echo str_replace($search, $replace, $pagina_html);