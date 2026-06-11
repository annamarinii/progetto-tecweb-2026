<?php
require_once '../php-manager/init_session.php';
require_once '../php-manager/account_manager.php';
require_once '../php-manager/news_manager.php';
require_once '../php-manager/campioni_manager.php';
require_once '../php-manager/faq_manager.php';
require_once '../php-manager/db_connection.php';
require_once '../php-manager/tool.php';

// CONTROLLO ACCESSO RIGIDO
if (!Tool::isLoggedIn() || !Tool::isAdmin()) {
    // Reindirizza al CONTROLLER 403.php (non al template .html grezzo): è lui a
    // sostituire [BasePath]/[Header]/[Footer] e a impostare lo status 403. Arrivando
    // alla root, [BasePath]='./' risolve correttamente CSS, immagini e link.
    header("Location: ../403.php");
    exit();
}

$id_utente_corrente = $_SESSION['idUtente'];
$dati_utente = AccountManager::getUtenteById($id_utente_corrente);

$frammento_faq_admin     = file_get_contents(__DIR__ . '/../item/faq_admin_card.html');
$frammento_domanda       = file_get_contents(__DIR__ . '/../item/domanda_utente_item.html');
$frammento_form_risposta = file_get_contents(__DIR__ . '/../item/faq_risposta_admin.html');
$frammento_news_admin    = file_get_contents(__DIR__ . '/../item/admin_news_row.html');
$frammento_campione_admin = file_get_contents(__DIR__ . '/../item/admin_campione_row.html');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $esito = false;
    $ancora = "";
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    // --- GESTIONE CAMPIONI (LATO INGRESSO: SALVATAGGIO PURO NEL DB) ---
    // Intercettata PRIMA delle news: l'eliminazione campione invia anch'essa elimina=si,
    // perciò la si distingue tramite la presenza di 'nome' (insert/update) o 'id_campione'.
    if (isset($_POST['nome']) || (isset($_POST['elimina']) && $_POST['elimina'] == 'si' && isset($_POST['id_campione']))) {
        $idCampione = isset($_POST['id_campione']) ? $_POST['id_campione'] : null;
        // Non si convertono le entità in ingresso per salvare dati puri. Si ripulisce solo da tag dannosi se necessario.
        $nome      = isset($_POST['nome']) ? trim(strip_tags($_POST['nome'])) : '';
        $categoria = isset($_POST['categoria']) ? trim(strip_tags($_POST['categoria'])) : '';
        $anno      = isset($_POST['anno']) ? trim($_POST['anno']) : '';
        // Ordine di visualizzazione: ora OBBLIGATORIO. Conserviamo il valore grezzo
        // per validarne la presenza prima della conversione a intero.
        $ordine_raw = isset($_POST['ordine']) ? trim($_POST['ordine']) : '';
        $ordine    = ($ordine_raw !== '') ? (int) $ordine_raw : 0;
        // Testo alternativo dell'immagine (accessibilità): ora OBBLIGATORIO. Il fallback
        // resta solo come ultima difesa coerente con il DEFAULT della colonna nel DB.
        $alt_raw = isset($_POST['alt_immagine']) ? trim(strip_tags($_POST['alt_immagine'])) : '';
        $alt_immagine = ($alt_raw !== '') ? $alt_raw : 'Ritratto del campione';
        $immagine_path = "";

        $upload_msg = "";
        $estensioni_ok = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (isset($_FILES['immagine'])) {
            if ($_FILES['immagine']['error'] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['immagine']['tmp_name'];
                $file_type = strtolower(pathinfo($_FILES['immagine']['name'], PATHINFO_EXTENSION));

                // Sicurezza: accetta solo immagini con estensione whitelisted E
                // contenuto realmente di tipo immagine (evita upload di .php travestiti).
                $mime_ok = function_exists('getimagesize') ? (bool) @getimagesize($tmp_name) : true;

                if (!in_array($file_type, $estensioni_ok, true) || !$mime_ok) {
                    $upload_msg = "Formato immagine non valido. Sono ammessi solo JPG, PNG, GIF o WEBP.";
                } else {
                    $new_filename = "campione_" . time() . "." . $file_type;
                    if (move_uploaded_file($tmp_name, "../assets/images/" . $new_filename)) {
                        $immagine_path = "assets/images/" . $new_filename;
                    } else {
                        $upload_msg = "Impossibile spostare il file nel server.";
                    }
                }
            } else if ($_FILES['immagine']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload_msg = "Errore caricamento file (Codice PHP: " . $_FILES['immagine']['error'] . "). Forse il file è troppo pesante.";
            }
        }

        $isEliminazione = (isset($_POST['elimina']) && $_POST['elimina'] == 'si');
        // Inserimento = nessun id_campione presente. In creazione l'immagine è
        // obbligatoria; in modifica può restare quella esistente (file vuoto = invariato).
        $isInserimentoCampione = !$isEliminazione && empty($idCampione);

        // Validazione difensiva lato server: nome, categoria, anno, alt e ordine sono
        // obbligatori; l'immagine è obbligatoria in creazione; estensione non valida blocca.
        $errore_campione = "";
        if (!$isEliminazione) {
            if (!CampioniManager::validaCampiCampione($nome, $categoria, $anno)) {
                $errore_campione = "Nome, categoria e anno del campione sono obbligatori.";
            } elseif ($upload_msg !== "") {
                $errore_campione = $upload_msg;
            } elseif ($alt_raw === "") {
                $errore_campione = "Il testo alternativo dell'immagine è obbligatorio.";
            } elseif ($ordine_raw === "" || !is_numeric($ordine_raw)) {
                $errore_campione = "L'ordine di visualizzazione è obbligatorio.";
            } elseif ($isInserimentoCampione && $immagine_path === "") {
                $errore_campione = "L'immagine del campione è obbligatoria.";
            }
        }

        if (!$isEliminazione && $errore_campione !== "") {
            $msg_errore = $errore_campione;
            header("Location: area_admin.php?status=error&msg=" . urlencode($msg_errore) . "&t=" . time() . "#gestione-campioni");
            exit();
        }

        if ($isEliminazione) {
            $esito = CampioniManager::eliminaCampione($idCampione);
        } else if ($idCampione && $idCampione != "") {
            $esito = CampioniManager::aggiornaCampione($idCampione, $nome, $categoria, $anno, $immagine_path, $alt_immagine, $ordine);
        } else {
            $img_to_save = ($immagine_path != "") ? $immagine_path : 'assets/images/logo1.webp';
            $esito = CampioniManager::inserisciCampione($nome, $categoria, $anno, $img_to_save, $alt_immagine, $ordine);
        }
        $ancora = "#gestione-campioni";
    }

    // --- GESTIONE NEWS (LATO INGRESSO: SALVATAGGIO PURO NEL DB) ---
    else if (isset($_POST['titolo']) || (isset($_POST['elimina']) && $_POST['elimina'] == 'si')) {
        $idNews = isset($_POST['id_news']) ? $_POST['id_news'] : null;
        // Non si convertono le entità in ingresso per salvare dati puri. Si ripulisce solo da tag dannosi se necessario.
        $titolo = isset($_POST['titolo']) ? trim(strip_tags($_POST['titolo'])) : '';
        $testo = isset($_POST['testo']) ? trim(strip_tags($_POST['testo'])) : '';
        // Testo alternativo dell'immagine (accessibilità): ora è OBBLIGATORIO.
        // Conserviamo il valore grezzo per poterne validare la presenza; il fallback
        // resta solo come ultima difesa coerente con il DEFAULT della colonna.
        $alt_raw = isset($_POST['alt_immagine']) ? trim(strip_tags($_POST['alt_immagine'])) : '';
        $alt_immagine = ($alt_raw !== '') ? $alt_raw : 'Immagine della news';
        $inEvidenza = isset($_POST['inEvidenza']) ? 1 : 0;
        $immagine_path = "";

        $upload_msg = "";
        $estensioni_ok = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (isset($_FILES['immagine'])) {
            if ($_FILES['immagine']['error'] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['immagine']['tmp_name'];
                $file_type = strtolower(pathinfo($_FILES['immagine']['name'], PATHINFO_EXTENSION));

                // Sicurezza: accetta solo immagini con estensione whitelisted E
                // contenuto realmente di tipo immagine (evita upload di .php travestiti).
                $mime_ok = function_exists('getimagesize') ? (bool) @getimagesize($tmp_name) : true;

                if (!in_array($file_type, $estensioni_ok, true) || !$mime_ok) {
                    $upload_msg = "Formato immagine non valido. Sono ammessi solo JPG, PNG, GIF o WEBP.";
                } else {
                    $new_filename = "news_" . time() . "." . $file_type;
                    if (move_uploaded_file($tmp_name, "../assets/images/" . $new_filename)) {
                        $immagine_path = "assets/images/" . $new_filename;
                    } else {
                        $upload_msg = "Impossibile spostare il file nel server.";
                    }
                }
            } else if ($_FILES['immagine']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload_msg = "Errore caricamento file (Codice PHP: " . $_FILES['immagine']['error'] . "). Forse il file è troppo pesante.";
            }
        }

        $isEliminazione = (isset($_POST['elimina']) && $_POST['elimina'] == 'si');
        // Inserimento = nessun id_news presente. In creazione l'immagine di copertina
        // è obbligatoria; in modifica può restare quella esistente (file vuoto = invariato).
        $isInserimentoNews = !$isEliminazione && empty($idNews);

        // Validazione difensiva lato server: titolo, testo e alt sono sempre obbligatori;
        // l'immagine di copertina è obbligatoria in fase di creazione.
        $errore_news = "";
        if (!$isEliminazione) {
            if (!NewsManager::validaCampiNews($titolo, $testo)) {
                $errore_news = "Titolo e testo della news sono obbligatori.";
            } elseif ($upload_msg !== "") {
                $errore_news = $upload_msg;
            } elseif ($isInserimentoNews && $immagine_path === "") {
                $errore_news = "L'immagine di copertina della news è obbligatoria.";
            } elseif ($alt_raw === "") {
                $errore_news = "Il testo alternativo dell'immagine è obbligatorio.";
            }
        }

        if (!$isEliminazione && $errore_news !== "") {
            $msg_errore = $errore_news;
            if ($isAjax) {
                echo json_encode(['status' => 'error', 'upload_msg' => $msg_errore, 'html_miniature' => '']);
                exit();
            }
            header("Location: area_admin.php?status=error&msg=" . urlencode($msg_errore) . "&t=" . time() . "#gestione-news");
            exit();
        }

        if ($isEliminazione) {
            $esito = NewsManager::eliminaNews($idNews);
        } else if ($idNews && $idNews != "") {
            $esito = NewsManager::aggiornaNews($idNews, $titolo, $testo, $immagine_path, $alt_immagine, $inEvidenza);
        } else {
            $img_to_save = ($immagine_path != "") ? $immagine_path : 'assets/images/logo1.webp';
            $esito = NewsManager::inserisciNews($titolo, $testo, $img_to_save, $alt_immagine, $id_utente_corrente, $inEvidenza);
        }
        $ancora = "#gestione-news";

        if ($isAjax) {
            $newsArray = NewsManager::getNews();
            $html_news = "";
            foreach ($newsArray as $news) {
                $percorso = (empty($news['immagine'])) ? '../assets/images/logo1.webp' : '../' . htmlspecialchars($news['immagine'], ENT_QUOTES, 'UTF-8');
                $img_name = (empty($news['immagine'])) ? 'logo1.webp' : basename($news['immagine']);
                $html_news .= str_replace(
                    ['[NewsID]', '[Percorso]', '[Titolo]', '[Testo]', '[ImgName]', '[AltImmagine]', '[InEvidenza]'],
                    [
                        (int)$news['idNews'],
                        $percorso,
                        htmlspecialchars($news['titolo'],        ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($news['testo'],         ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($img_name,              ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($news['alt_immagine'] ?? '', ENT_QUOTES, 'UTF-8'),
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
            $categoria_faq = trim(strip_tags($_POST['categoria'] ?? ''));

            // La categoria è OBBLIGATORIA e deve appartenere alla whitelist prevista:
            // un valore vuoto o non riconosciuto fa fallire la validazione (niente più
            // fallback silenzioso a 'Regolamento').
            $categorie_valide = ['Info Pratiche e Accessi', 'Biglietteria', 'Regolamento'];

            if ($domanda_faq === '' || $risposta_faq === '' || !in_array($categoria_faq, $categorie_valide, true)) {
                if ($isAjax) {
                    echo json_encode(['status' => 'error', 'html_faq' => '']);
                    exit();
                }
                header("Location: area_admin.php?status=error&t=" . time() . "#gestione-faq");
                exit();
            }

            if ($idFaq && $idFaq != "") {
                $esito = FaqManager::aggiornaFaq($idFaq, $domanda_faq, $risposta_faq, $categoria_faq);
            } else {
                $esito = FaqManager::inserisciFaqUfficiale($domanda_faq, $risposta_faq, $categoria_faq);
            }
        }
        $ancora = "#gestione-faq";

        if ($isAjax) {
            $faqArray = FaqManager::getFaq();
            $html_faq = "";
            foreach ($faqArray as $f) {
                $q_pulito = htmlspecialchars($f['testo_domanda'], ENT_QUOTES, 'UTF-8');
                $a_pulito = htmlspecialchars($f['testo_risposta'], ENT_QUOTES, 'UTF-8');
                $cat_pulita = htmlspecialchars($f['categoria'], ENT_QUOTES, 'UTF-8');
                $html_faq .= str_replace(
                    ['[FaqID]', '[Domanda]', '[Risposta]', '[Categoria]'],
                    [(int)$f['idFaq'], $q_pulito, $a_pulito, $cat_pulita],
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

    header("Location: area_admin.php?status=" . ($esito ? "success" : "error") . "&t=" . time() . $ancora);
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
    $percorso = (empty($news['immagine'])) ? '../assets/images/logo1.webp' : '../' . htmlspecialchars($news['immagine'], ENT_QUOTES, 'UTF-8');
    $img_name = (empty($news['immagine'])) ? 'logo1.webp' : basename($news['immagine']);
    $html_miniature .= str_replace(
        ['[NewsID]', '[Percorso]', '[Titolo]', '[Testo]', '[ImgName]', '[AltImmagine]', '[InEvidenza]'],
        [
            (int)$news['idNews'],
            $percorso,
            htmlspecialchars($news['titolo'],        ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($news['testo'],         ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($img_name,              ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($news['alt_immagine'] ?? '', ENT_QUOTES, 'UTF-8'),
            (int)($news['inEvidenza'] ?? 0)
        ],
        $frammento_news_admin
    );
}

$campioniArray = CampioniManager::getCampioni();
$html_campioni = !empty($campioniArray) ? "" : "<p>Nessun campione pubblicato.</p>";
foreach ($campioniArray as $campione) {
    $percorso = (empty($campione['immagine'])) ? '../assets/images/logo1.webp' : '../' . htmlspecialchars($campione['immagine'], ENT_QUOTES, 'UTF-8');
    $img_name = (empty($campione['immagine'])) ? 'logo1.webp' : basename($campione['immagine']);
    $html_campioni .= str_replace(
        ['[CampioneID]', '[Percorso]', '[ImgName]', '[Nome]', '[AltImmagine]', '[Categoria]', '[Anno]', '[Ordine]'],
        [
            (int)$campione['idCampione'],
            $percorso,
            htmlspecialchars($img_name,                   ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($campione['nome'],           ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($campione['alt_immagine'] ?? '', ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($campione['categoria'],      ENT_QUOTES, 'UTF-8'),
            (int)$campione['anno'],
            (int)$campione['ordine']
        ],
        $frammento_campione_admin
    );
}

$faqArray = FaqManager::getFaq();
$html_faq = !empty($faqArray) ? "" : "<p>Nessuna FAQ pubblicata.</p>";
foreach ($faqArray as $f) {
    $q = htmlspecialchars($f['testo_domanda'], ENT_QUOTES, 'UTF-8');
    $a = htmlspecialchars($f['testo_risposta'], ENT_QUOTES, 'UTF-8');
    $cat = htmlspecialchars($f['categoria'], ENT_QUOTES, 'UTF-8');
    $html_faq .= str_replace(
        ['[FaqID]', '[Domanda]', '[Risposta]', '[Categoria]'],
        [(int)$f['idFaq'], $q, $a, $cat],
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
        // Se il ramo che ha generato l'errore ha passato un dettaglio specifico
        // (es. "Formato immagine non valido"), lo si mostra; altrimenti messaggio generico.
        // htmlspecialchars obbligatorio: il valore arriva da $_GET e buildMessage lo inserisce raw nell'HTML.
        $dettaglio_errore = (isset($_GET['msg']) && trim($_GET['msg']) !== '')
            ? htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8')
            : 'Si è verificato un errore durante l\'operazione. Riprova.';
        $messaggio_esito = Tool::buildMessage('Errore:', $dettaglio_errore);
    }
}

$pagina_html = file_get_contents(__DIR__ . '/../pages/area_admin.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('areaadmin'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('areaadmin'), $pagina_html);

$search  = ['[nome_utente]', '[cognome_utente]', '[username_utente]', '[lista_news_miniature]', '[ListaCampioniAdmin]', '[lista_faq_per_modifica]', '[lista_domande_utenti]', '[messaggio_esito]'];
$replace = [$nome_pulito, $cognome_pulito, $username_pulito, $html_miniature, $html_campioni, $html_faq, $html_domande, $messaggio_esito];
echo str_replace($search, $replace, $pagina_html);