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

$frammento_faq_admin     = file_get_contents(__DIR__ . '/../item/faq_admin_card.html');
$frammento_domanda       = file_get_contents(__DIR__ . '/../item/domanda_utente_item.html');
$frammento_form_risposta = file_get_contents(__DIR__ . '/../item/faq_risposta_admin.html');
$frammento_news_admin    = file_get_contents(__DIR__ . '/../item/admin_news_row.html');

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
                $percorso = (empty($news['immagine'])) ? '../assets/images/logo1.png' : '../' . htmlspecialchars($news['immagine'], ENT_QUOTES, 'UTF-8');
                $img_name = (empty($news['immagine'])) ? 'logo1.png' : basename($news['immagine']);
                $html_news .= str_replace(
                    ['[NewsID]', '[Percorso]', '[Titolo]', '[Testo]', '[ImgName]', '[InEvidenza]'],
                    [
                        (int)$news['idNews'],
                        $percorso,
                        htmlspecialchars($news['titolo'],    ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($news['testo'],     ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($img_name,          ENT_QUOTES, 'UTF-8'),
                        (int)($news['inEvidenza'] ?? 0)
                    ],
                    $frammento_news_admin
                );
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
            $domanda_faq  = trim(strip_tags($_POST['domanda_faq']  ?? ''));
            $risposta_faq = trim(strip_tags($_POST['risposta_faq'] ?? ''));

            if ($domanda_faq === '' || $risposta_faq === '') {
                if ($isAjax) {
                    echo json_encode(['status' => 'error', 'html_faq' => '']);
                    exit();
                }
                header("Location: AreaAdmin.php?status=error&t=" . time() . "#gestione-faq");
                exit();
            }

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
                $q_pulito = htmlspecialchars($f['testo_domanda'], ENT_QUOTES, 'UTF-8');
                $a_pulito = htmlspecialchars($f['testo_risposta'], ENT_QUOTES, 'UTF-8');
                $html_faq .= str_replace(
                    ['[FaqID]', '[Domanda]', '[Risposta]'],
                    [(int)$f['idFaq'], $q_pulito, $a_pulito],
                    $frammento_faq_admin
                );
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
    $percorso = (empty($news['immagine'])) ? '../assets/images/logo1.png' : '../' . htmlspecialchars($news['immagine'], ENT_QUOTES, 'UTF-8');
    $img_name = (empty($news['immagine'])) ? 'logo1.png' : basename($news['immagine']);
    $html_miniature .= str_replace(
        ['[NewsID]', '[Percorso]', '[Titolo]', '[Testo]', '[ImgName]', '[InEvidenza]'],
        [
            (int)$news['idNews'],
            $percorso,
            htmlspecialchars($news['titolo'],    ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($news['testo'],     ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($img_name,          ENT_QUOTES, 'UTF-8'),
            (int)($news['inEvidenza'] ?? 0)
        ],
        $frammento_news_admin
    );
}

$faqArray = FaqManager::getFaq();
$html_faq = !empty($faqArray) ? "" : "<p>Nessuna FAQ pubblicata.</p>";
foreach ($faqArray as $f) {
    $q = htmlspecialchars($f['testo_domanda'], ENT_QUOTES, 'UTF-8');
    $a = htmlspecialchars($f['testo_risposta'], ENT_QUOTES, 'UTF-8');
    $html_faq .= str_replace(
        ['[FaqID]', '[Domanda]', '[Risposta]'],
        [(int)$f['idFaq'], $q, $a],
        $frammento_faq_admin
    );
}

// Preparazione Domande Utenti
$domandeUtenti = FaqManager::getDomandeUtenti();
$html_domande = !empty($domandeUtenti) ? '' : '<p class="no-data-msg" role="status">Nessuna nuova domanda.</p>';
foreach ($domandeUtenti as $d) {
    $stato_letta = $d['lettura_admin'] ? '' : 'unread';
    $stato_testo = $d['lettura_admin'] ? 'Messaggio letto' : 'Messaggio non letto';
    $domanda = htmlspecialchars($d['testo_domanda'], ENT_QUOTES, 'UTF-8');
    $utente = htmlspecialchars($d['username'], ENT_QUOTES, 'UTF-8');
    $sommario = htmlspecialchars(substr($d['testo_domanda'], 0, 50), ENT_QUOTES, 'UTF-8') . '...';
    $data_invio = date('d/m H:i', strtotime($d['data_invio']));

    if (isset($d['testo_risposta']) && trim($d['testo_risposta']) !== '') {
        $form_risposta = '';
    } else {
        $form_risposta = str_replace('[IDDomanda]', (int)$d['idDomanda'], $frammento_form_risposta);
    }

    $html_domande .= str_replace(
        ['[IDDomanda]', '[StatoLetta]', '[StatoTesto]', '[Username]', '[SommarioMessaggio]', '[DataInvio]', '[TestoCompleto]', '[FormRisposta]'],
        [(int)$d['idDomanda'], $stato_letta, $stato_testo, $utente, $sommario, $data_invio, $domanda, $form_risposta],
        $frammento_domanda
    );
}

// Preparazione Messaggio Esito
$messaggio_esito = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $messaggio_esito = Tool::buildMessage('Ottimo!', 'Operazione completata con successo.', 'success');
    } else {
        $messaggio_esito = Tool::buildMessage('Errore:', 'Si è verificato un errore durante l\'operazione. Riprova.');
    }
}

$pagina_html = file_get_contents(__DIR__ . '/../html/areaadmin.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('areaadmin'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('areaadmin'), $pagina_html);

$search  = ['[nome_utente]', '[cognome_utente]', '[username_utente]', '[lista_news_miniature]', '[lista_faq_per_modifica]', '[lista_domande_utenti]', '[messaggio_esito]'];
$replace = [$nome_pulito, $cognome_pulito, $username_pulito, $html_miniature, $html_faq, $html_domande, $messaggio_esito];
echo str_replace($search, $replace, $pagina_html);