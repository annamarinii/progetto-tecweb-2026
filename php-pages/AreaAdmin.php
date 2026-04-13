<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../php-dbManager/init_session.php';
require_once '../php-dbManager/AccountManager.php';
require_once '../php-dbManager/NewsManager.php';
require_once '../php-dbManager/FaqManager.php';
require_once '../php-dbManager/DBConnection.php';

// 1. CONTROLLO SICUREZZA
if (!isset($_SESSION['idUtente'])) {
    header("Location: Login.php");
    exit();
}

$id_utente_corrente = $_SESSION['idUtente'];
$dati_utente = AccountManager::getUtenteById($id_utente_corrente);

if (!$dati_utente || $dati_utente['isAdmin'] == 0) {
    header("Location: Login.php"); 
    exit();
}

// ==========================================
// 2. SEZIONE SCRITTURA (Gestione POST & AJAX)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $esito = false;
    $ancora = "";
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    // --- CASO A: GESTIONE NEWS (Inserimento, Modifica, Eliminazione) ---
    if (isset($_POST['titolo']) || (isset($_POST['elimina']) && $_POST['elimina'] == 'si')) { 
        $idNews = $_POST['id_news'] ?? null;
        $titolo = $_POST['titolo'] ?? '';
        $testo = $_POST['testo'] ?? '';
        $inEvidenza = isset($_POST['inEvidenza']) ? 1 : 0;
        $immagine_path = "";

        if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['immagine']['tmp_name'];
            $file_type = strtolower(pathinfo($_FILES['immagine']['name'], PATHINFO_EXTENSION));
            $new_filename = "news_" . time() . "." . $file_type;
            if (move_uploaded_file($tmp_name, "../assets/images/" . $new_filename)) {
                $immagine_path = "assets/images/" . $new_filename;
            }
        }

        if (isset($_POST['elimina']) && $_POST['elimina'] == 'si') {
            $esito = NewsManager::eliminaNews($idNews);
        } else if ($idNews && $idNews != "") {
            $esito = NewsManager::aggiornaNews($idNews, $titolo, $testo, $immagine_path, $inEvidenza);
        } else {
            $img_to_save = ($immagine_path != "") ? $immagine_path : 'assets/images/default-news.jpg';
            $esito = NewsManager::inserisciNews($titolo, $testo, $img_to_save, $id_utente_corrente, $inEvidenza);
        }
        $ancora = "#gestione-news";

        if ($isAjax) {
            $newsArray = NewsManager::getNews();
            $html_news = "";
            foreach ($newsArray as $news) {
                $percorso = (empty($news['immagine'])) ? '../assets/images/default-news.jpg' : '../' . $news['immagine'];
                $html_news .= '<button type="button" class="news-mini-card" data-news-id="'.htmlspecialchars($news['idNews']).'">
                    <img src="'.$percorso.'" alt="" class="mini-card-img">
                    <div class="mini-card-info"><h4>'.htmlspecialchars($news['titolo']).'</h4></div>
                    <div class="news-full-text" style="display:none;">'.htmlspecialchars($news['testo']).'</div>
                </button>';
            }
            echo json_encode(['status' => ($esito ? 'success' : 'error'), 'html_miniature' => $html_news]);
            exit();
        }
    }

    // --- CASO B: GESTIONE FAQ (Inserimento, Modifica, Eliminazione) ---
    else if (isset($_POST['domanda_faq']) || isset($_POST['elimina_faq'])) {
        if (isset($_POST['elimina_faq'])) {
            $esito = FaqManager::eliminaFaq($_POST['id_faq_elimina']);
        } else {
            $idFaq = $_POST['id_faq'] ?? null;
            if ($idFaq && $idFaq != "") {
                // MODIFICA FAQ
                $esito = FaqManager::aggiornaFaq($idFaq, $_POST['domanda_faq'], $_POST['risposta_faq']);
            } else {
                // NUOVA FAQ
                $esito = FaqManager::inserisciFaqUfficiale($_POST['domanda_faq'], $_POST['risposta_faq']);
            }
        }
        $ancora = "#gestione-faq";

        if ($isAjax) {
            $faqArray = FaqManager::getFaq();
            $html_faq = "";
            foreach ($faqArray as $f) {
                $html_faq .= '
                <div class="faq-admin-card">
                    <div class="faq-card-content">
                        <p class="faq-q">Q: '.htmlspecialchars($f['testo_domanda']).'</p>
                        <p class="faq-a">A: '.htmlspecialchars($f['testo_risposta']).'</p>
                    </div>
                    <div class="faq-actions">
                        <button type="button" class="btn-edit-faq-trigger" 
                                data-id="'.$f['idFaq'].'" 
                                data-q="'.htmlspecialchars($f['testo_domanda']).'" 
                                data-a="'.htmlspecialchars($f['testo_risposta']).'">Modifica</button>
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

    // --- CASO C: RISPOSTA DOMANDA UTENTE ---
    else if (isset($_POST['risposta_utente'])) {
        $conn = DBConnection::getConnessione();
        $sql = "UPDATE DOMANDE SET testo_risposta = ?, lettura_admin = 1, lettura_user = 0 WHERE idDomanda = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $_POST['risposta'], $_POST['id_domanda']);
        $esito = $stmt->execute();
        $stmt->close();
        $conn->close();
        $ancora = "#nuove-domande";

        if ($isAjax) {
            echo json_encode(['status' => ($esito ? 'success' : 'error')]);
            exit();
        }
    }

    // --- CASO D: SEGNA DOMANDA COME LETTA (Da Admin) ---
    else if (isset($_POST['segna_letta_admin'])) {
        $conn = DBConnection::getConnessione();
        $sql = "UPDATE DOMANDE SET lettura_admin = 1 WHERE idDomanda = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_POST['id_domanda']);
        $esito = $stmt->execute();
        $stmt->close();
        $conn->close();

        if ($isAjax) {
            echo json_encode(['status' => ($esito ? 'success' : 'error')]);
            exit();
        }
    }

    header("Location: AreaAdmin.php?status=" . ($esito ? "success" : "error") . "&t=" . time() . $ancora);
    exit();
}

// ==========================================
// 3. SEZIONE LETTURA (Render Pagina)
// ==========================================

$nome_pulito = htmlspecialchars($dati_utente['nome']);
$cognome_pulito = htmlspecialchars($dati_utente['cognome']);
$username_pulito = htmlspecialchars($dati_utente['username']);

// Preparazione News
$newsArray = NewsManager::getNews();
$html_miniature = !empty($newsArray) ? "" : "<p>Nessuna news pubblicata.</p>";
foreach ($newsArray as $news) {
    $img = (empty($news['immagine'])) ? '../assets/images/default-news.jpg' : '../' . $news['immagine'];
    $html_miniature .= '
        <button type="button" class="news-mini-card" data-news-id="'.htmlspecialchars($news['idNews']).'">
            <img src="'.$img.'" alt="" class="mini-card-img">
            <div class="mini-card-info"><h4>'.htmlspecialchars($news['titolo']).'</h4></div>
            <div class="news-full-text" style="display:none;">'.htmlspecialchars($news['testo']).'</div>
        </button>';
}

// Preparazione FAQ (con card professionali)
$faqArray = FaqManager::getFaq();
$html_faq = !empty($faqArray) ? "" : "<p>Nessuna FAQ pubblicata.</p>";
foreach ($faqArray as $f) {
    $html_faq .= '
        <div class="faq-admin-card">
            <div class="faq-card-content">
                <p class="faq-q">Q: '.htmlspecialchars($f['testo_domanda']).'</p>
                <p class="faq-a">A: '.htmlspecialchars($f['testo_risposta']).'</p>
            </div>
            <div class="faq-actions">
                <button type="button" class="btn-edit-faq-trigger" 
                        data-id="'.$f['idFaq'].'" 
                        data-q="'.htmlspecialchars($f['testo_domanda']).'" 
                        data-a="'.htmlspecialchars($f['testo_risposta']).'">Modifica</button>
                <form action="AreaAdmin.php" method="POST" class="form-delete-faq">
                    <input type="hidden" name="elimina_faq" value="si">
                    <input type="hidden" name="id_faq_elimina" value="'.$f['idFaq'].'">
                    <button type="submit" class="btn-delete-faq-ajax" onclick="return confirm(\'Eliminare?\')">Elimina</button>
                </form>
            </div>
        </div>';
}

// Preparazione Domande Utenti
$domandeUtenti = FaqManager::getDomandeUtenti();
$html_domande = !empty($domandeUtenti) ? "" : "<p style='padding:20px;'>Nessuna nuova domanda.</p>";
foreach ($domandeUtenti as $d) {
    $status = $d['lettura_admin'] ? 'read' : 'unread';
    $html_domande .= '
        <li class="mail-row '.$status.'" data-id="'.$d['idDomanda'].'">
            <div class="mail-row-header">
                <div class="read-dot"></div>
                <div class="sender">'.htmlspecialchars($d['username']).'</div>
                <div class="subject">'.htmlspecialchars(substr($d['testo_domanda'], 0, 50)).'...</div>
                <div class="date">'.date("d/m H:i", strtotime($d['data_invio'])).'</div>
            </div>
            <div class="mail-content" style="display: none;">
                <p class="full-message">"'.htmlspecialchars($d['testo_domanda']).'"</p>';
    if ($d['testo_risposta']) {
        $html_domande .= '<p style="color:green; margin-top:10px;"><strong>Risposta data:</strong> '.htmlspecialchars($d['testo_risposta']).'</p>';
    } else {
        $html_domande .= '
                <form action="AreaAdmin.php" method="POST" class="mail-reply-form">
                    <input type="hidden" name="risposta_utente" value="si">
                    <input type="hidden" name="id_domanda" value="'.$d['idDomanda'].'">
                    <textarea name="risposta" rows="3" required placeholder="Rispondi..."></textarea>
                    <button type="submit" class="btn-auth">Invia</button>
                </form>';
    }
    $html_domande .= '</div></li>';
}

$messaggio_esito = "";
if (isset($_GET['status'])) {
    $col = ($_GET['status'] == 'success') ? 'green' : 'red';
    $txt = ($_GET['status'] == 'success') ? 'Operazione completata!' : 'Errore!';
    $messaggio_esito = "<div class='esito-msg' style='color:$col; padding:10px; border:1px solid $col; margin-bottom:20px; font-weight:bold;'>$txt</div>";
}

$pagina_html = file_get_contents('../html/AreaAdmin.html');
$search = ['[nome_utente]','[cognome_utente]','[username_utente]','[lista_news_miniature]','[lista_faq_per_modifica]','[lista_domande_utenti]','[messaggio_esito]'];
$replace = [$nome_pulito, $cognome_pulito, $username_pulito, $html_miniature, $html_faq, $html_domande, $messaggio_esito];
echo str_replace($search, $replace, $pagina_html);