<?php

class Tool
{
    /**
     * Verifica se l'utente è attualmente autenticato.
     *
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['logged']) && $_SESSION['logged'] === true;
    }

    /**
     * Verifica se l'utente ha privilegi amministrativi.
     *
     * @return bool
     */
    public static function isAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1;
    }

    /**
     * Popola in modo atomico le variabili di sessione al momento del login.
     *
     * @param array $utente L'array associativo contenente i dati dell'utente dal database.
     */
    public static function avviaSessioneUtente(array $utente): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['logged'] = true;
        $_SESSION['idUtente'] = $utente['idUtente'];
        $_SESSION['username'] = $utente['username']; 
        $_SESSION['nome'] = $utente['nome'];         
        $_SESSION['cognome'] = $utente['cognome'];   
        $_SESSION['email'] = $utente['email'];       
        $_SESSION['isAdmin'] = $utente['isAdmin']; 
    }

    /**
     * Sanifica una stringa di input in modo sicuro per prevenire XSS.
     *
     * Comportamento di default (nessun tag permesso): trim + strip_tags + htmlspecialchars,
     * cioè neutralizzazione totale dell'HTML (identico alla versione storica).
     *
     * Con $allowedTags valorizzato (es. '<p><b><i><a>'), i tag indicati vengono
     * PRESERVATI come HTML per le aree che richiedono formattazione (es. News).
     * In quel caso NON si applica htmlspecialchars, altrimenti i tag permessi
     * verrebbero escapati e resi inerti.
     *
     * ATTENZIONE (limite noto di strip_tags): con $allowedTags i tag sopravvivono
     * ma i loro ATTRIBUTI non vengono ripuliti (es. <a onclick> o href "javascript:").
     * Usare $allowedTags SOLO su input proveniente da utenti fidati (es. testo News
     * redatto dagli amministratori), mai su input di utenti anonimi.
     *
     * @param mixed  $value
     * @param string $allowedTags Lista di tag da preservare in formato strip_tags (default '': nessuno).
     * @return string
     */
    public static function pulisciInput($value, string $allowedTags = ''): string
    {
        $value = $value ?? '';

        $value_testuale = (string) $value;

        $value_testuale = trim($value_testuale);
        $value_testuale = strip_tags($value_testuale, $allowedTags);

        // Solo quando non si permette alcun tag si applica l'escaping completo.
        if ($allowedTags === '') {
            $value_testuale = htmlspecialchars($value_testuale, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $value_testuale;
    }

    /**
     * Valida un indirizzo email. Da usare SEMPRE prima di qualsiasi query che
     * coinvolga l'email (registrazione, login per email, update profilo).
     *
     * @param string $email
     * @return bool
     */
    public static function validaEmail(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Conta i caratteri in modo sicuro anche senza estensione mbstring.
     */
    private static function lunghezza(string $s): int
    {
        return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
    }

    /**
     * Valida un nome proprio / cognome: solo lettere (anche accentate) e spazi,
     * lunghezza 1..$max. Riutilizzabile in registrazione e update profilo.
     *
     * @param string $valore
     * @param int    $max Lunghezza massima (default 30, come la colonna DB UTENTE.nome/cognome).
     * @return bool
     */
    public static function validaNomeProprio(string $valore, int $max = 30): bool
    {
        $valore = trim($valore);
        if ($valore === '' || self::lunghezza($valore) > $max) {
            return false;
        }
        return (bool) preg_match('/^[A-Za-zÀ-ÿ\s]+$/u', $valore);
    }

    /**
     * Valida un indirizzo email per formato E lunghezza massima.
     * La colonna UTENTE.email è VARCHAR(30): un'email più lunga va respinta con
     * messaggio chiaro invece di causare un errore/troncamento a livello DB.
     *
     * @param string $email
     * @param int    $max Lunghezza massima consentita (default 30).
     * @return bool
     */
    public static function validaEmailCompleta(string $email, int $max = 30): bool
    {
        $email = trim($email);
        return self::validaEmail($email) && self::lunghezza($email) <= $max;
    }

    /**
     * Valida uno username: lettere, numeri, punto e underscore, 1..$max caratteri.
     * Coerente con la regex usata storicamente in registrazione.
     *
     * @param string $username
     * @param int    $max Lunghezza massima (default 16).
     * @return bool
     */
    public static function validaUsername(string $username, int $max = 16): bool
    {
        $username = trim($username);
        if ($username === '') {
            return false;
        }
        // max 16 di default: più restrittivo del DB (VARCHAR 30), coerente con la UX storica
        return (bool) preg_match('/^[a-zA-Z0-9._]{1,' . (int) $max . '}$/', $username);
    }

    /**
     * Calcola il prezzo scontato per gli abbonamenti (sconto del 20%).
     */
    public static function calcolaPrezzoScontato($tribuna, $dati_abbonamenti)
    {
        if (isset($dati_abbonamenti[$tribuna]) && $dati_abbonamenti[$tribuna]['disponibili'] > 0) {
            $prezzo_pieno = $dati_abbonamenti[$tribuna]['prezzo_base'];
            return $prezzo_pieno * 0.80;
        }
        return 0;
    }

    /**
     * Formatta il prezzo per la visualizzazione dell'abbonamento.
     */
    public static function formattaPrezzoAbbonamento($prezzo_scontato): string
    {
        if ($prezzo_scontato > 0) {
            return "€ " . number_format($prezzo_scontato, 2, ',', '.');
        }
        return "Esaurito";
    }

    /**
     * Genera l'HTML di un messaggio di feedback leggendo il template fragment item/alert_message.html.
     *
     * @param string $titolo Il testo in grassetto (es. "Errore:", "Ottimo!").
     * @param string $testo  Il corpo del messaggio (può contenere HTML sicuro come link).
     * @param string $tipo   Il suffisso della classe CSS (es. 'error', 'success', 'error login-error').
     * @return string L'HTML completo del messaggio.
     */
    public static function buildMessage(string $titolo, string $testo, string $tipo = 'error'): string
    {
        $template = file_get_contents(__DIR__ . '/../item/alert_message.html');
        $template = str_replace('[TipoAlert]',   $tipo,   $template);
        $template = str_replace('[TitoloAlert]', $titolo, $template);
        $template = str_replace('[TestoAlert]',  $testo,  $template);
        return $template;
    }

    /**
     * Genera l'HTML dinamico dell'Header del sito gestendo i percorsi in modo intelligente.
     *
     * @param string $currentPage Il nome della pagina corrente per evidenziare il menù.
     * @return string L'HTML dell'header completo.
     */
    public static function buildHeader(string $currentPage): string
    {
        // Rileva dinamicamente se ci troviamo nella root o in php-pages per sistemare i link
        $inRoot = !str_contains($_SERVER['SCRIPT_NAME'], 'php-pages');
        $basePath = $inRoot ? './' : '../';
        $pagesPath = $inRoot ? 'php-pages/' : '';

        // Snippet ANTI-FLASH: gira appena il parser raggiunge il body, prima del
        // rendering del contenuto. Imposta SOLO l'attributo data-theme su <html>
        // (nessuno stile inline) leggendo localStorage o, in mancanza, la preferenza
        // di sistema. Così la pagina non "lampeggia" passando da chiaro a scuro.
        $headerHtml = '<script>
        (function () {
            try {
                var saved = localStorage.getItem("theme");
                var dark = saved ? saved === "dark"
                    : (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches);
                if (dark) { document.documentElement.setAttribute("data-theme", "dark"); }
            } catch (e) { /* localStorage non disponibile: resta tema chiaro */ }
        })();
        </script>
        <header>
        <span class="logo-brand">
            <img src="' . $basePath . 'assets/images/logo1.png" alt="" aria-hidden="true" id="logo-sito" />
            <span>Patavium Open</span>
        </span>

        <nav id="menu" aria-label="Menu principale">
            <ul>';

        $menuItems = [
            'home' => ['url' => $basePath . 'index.php', 'label' => 'Home', 'lang' => 'en'],
            'biglietti' => ['url' => $pagesPath . 'Biglietti.php', 'label' => 'Biglietti', 'lang' => 'it'],
            'news' => ['url' => $pagesPath . 'News.php', 'label' => 'News', 'lang' => 'en'],
            'faq' => ['url' => $pagesPath . 'Faq.php', 'label' => 'Domande', 'lang' => 'it'],
        ];

        foreach ($menuItems as $key => $item) {
            $langAttr = isset($item['lang']) && $item['lang'] === 'en' ? ' lang="en"' : '';
            if (strtolower($currentPage) === $key) {
                $headerHtml .= "\n                <li class=\"current-link\" aria-current=\"page\"{$langAttr}>{$item['label']}</li>";
            } else {
                $headerHtml .= "\n                <li><a href=\"{$item['url']}\"{$langAttr}>{$item['label']}</a></li>";
            }
        }

        $headerHtml .= '
            </ul>

            <ul id="menu-utente">';

        if (self::isLoggedIn()) {
            if (self::isAdmin()) {
                $profileLink = $pagesPath . 'AreaAdmin.php';
                $profileLabel = 'Area Admin';
                $profileKey = 'areaadmin';
            } else {
                $profileLink = $pagesPath . 'AreaUtente.php';
                $profileLabel = 'Area Utente';
                $profileKey = 'areautente';
            }
            
            // Link Profilo (Admin o Utente)
            if (strtolower($currentPage) === $profileKey) {
                $headerHtml .= "\n                <li class=\"current-link\" aria-current=\"page\">\n                        <img src=\"{$basePath}assets/images/user.svg\" alt=\"\" class=\"icone-menu\" />{$profileLabel}</li>";
            } else {
                $headerHtml .= "\n                <li><a href=\"{$profileLink}\">\n                        <img src=\"{$basePath}assets/images/user.svg\" alt=\"\" class=\"icone-menu\" />{$profileLabel}</a></li>";
            }

        } else {
            // Utente non loggato: un solo link "Profilo" che porta al Login
            if (strtolower($currentPage) === 'login') {
                $headerHtml .= "\n                <li class=\"current-link\" aria-current=\"page\">\n                        <img src=\"{$basePath}assets/images/user.svg\" alt=\"\" class=\"icone-menu\" />Profilo</li>";
            } else {
                $headerHtml .= "\n                <li><a href=\"{$pagesPath}Login.php\">\n                        <img src=\"{$basePath}assets/images/user.svg\" alt=\"\" class=\"icone-menu\" />Profilo</a></li>";
            }
        }

        // Link Carrello (Sempre visibile per tutti)
        if (strtolower($currentPage) === 'carrello') {
            $headerHtml .= "\n                <li class=\"current-link\" aria-current=\"page\">\n                        <img src=\"{$basePath}assets/images/shopping-cart.svg\" alt=\"\" class=\"icone-menu\" />Carrello</li>";
        } else {
            $headerHtml .= "\n                <li><a href=\"{$pagesPath}Carrello.php\">\n                        <img src=\"{$basePath}assets/images/shopping-cart.svg\" alt=\"\" class=\"icone-menu\" />Carrello</a></li>";
        }

        // Bottone Dark Mode: semantico, senza onclick inline. Le icone sono decorative
        // (aria-hidden), lo screen reader legge solo aria-label. Stato iniziale neutro:
        // aria-label/aria-pressed/icona vengono sincronizzati da theme.js al caricamento.
        $headerHtml .= "\n                <li>
                    <button type=\"button\" id=\"theme-toggle\" class=\"theme-toggle\" aria-pressed=\"false\" aria-label=\"Attiva il tema scuro\">
                        <span class=\"theme-icon theme-icon-sun\" aria-hidden=\"true\">&#9728;</span>
                        <span class=\"theme-icon theme-icon-moon\" aria-hidden=\"true\">&#9790;</span>
                    </button>
                </li>";

        $headerHtml .= '
            </ul>
        </nav>
    </header>';

        // Logica del tema (toggle, persistenza, ARIA): caricata da un unico punto per
        // tutte le pagine. defer => non blocca il parsing e gira dopo che il bottone esiste.
        $headerHtml .= "\n    <script src=\"{$basePath}javascript/theme.js\" defer></script>";

        return $headerHtml;
    }

    /**
     * Genera l'HTML dinamico del Footer del sito.
     *
     * @param string $currentPage Il nome della pagina corrente.
     * @return string L'HTML del footer completo.
     */
    public static function buildFooter(string $currentPage): string
    {
        $inRoot = !str_contains($_SERVER['SCRIPT_NAME'], 'php-pages');
        $basePath  = $inRoot ? './' : '../';
        $pagesPath = $inRoot ? 'php-pages/' : '';

        return '<footer class="footer-sito">
        <div class="footer-grid">

            <div class="footer-col footer-brand">
                <img src="' . $basePath . 'assets/images/logo1.png" alt="Patavium Open" width="80" height="80" class="footer-logo-img" />
                <p class="footer-tagline">Il grande tennis internazionale nel cuore di Padova. Vivi l\'emozione della terra rossa dal 18 al 24 Maggio 2027.</p>
            </div>

            <nav class="footer-col" aria-labelledby="footer-esplora-titolo">
                <h2 id="footer-esplora-titolo" class="footer-titolo">Esplora</h2>
                <ul>
                    <li><a href="' . $basePath . 'index.php" lang="en">Home</a></li>
                    <li><a href="' . $pagesPath . 'Biglietti.php">Biglietti</a></li>
                    <li><a href="' . $pagesPath . 'News.php" lang="en">News</a></li>
                    <li><a href="' . $pagesPath . 'Faq.php">Domande frequenti</a></li>
                </ul>
            </nav>

            <nav class="footer-col" aria-labelledby="footer-legale-titolo">
                <h2 id="footer-legale-titolo" class="footer-titolo">Informazioni legali</h2>
                <ul>
                    <li><a href="' . $pagesPath . 'Termini.php">Termini e condizioni</a></li>
                    <li><a href="' . $pagesPath . 'Privacy.php">Informativa sulla privacy</a></li>
                </ul>
            </nav>

            <div class="footer-col">
                <h2 class="footer-titolo">Contatti</h2>
                <address class="footer-contatti">
                    <p>Patavium Arena<br />Via dello Sport, 35100 Padova (PD)</p>
                    <p><a href="mailto:info@pataviumopen.it">info@pataviumopen.it</a></p>
                </address>
            </div>

        </div>

        <div class="footer-bottom">
            <p class="footer-copyright">&copy; 2027 Patavium Open. Tutti i diritti riservati.</p>
        </div>
    </footer>';
    }
}