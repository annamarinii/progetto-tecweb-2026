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
     * @param mixed $value
     * @return string
     */
    public static function pulisciInput($value): string
    {
        if (is_string($value)) {
            $value = trim($value);
            $value = strip_tags($value);
            return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return '';
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

        $headerHtml = '<header>
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
                $headerHtml .= "\n                <li id=\"currentLink\" aria-current=\"page\"{$langAttr}>{$item['label']}</li>";
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
                $headerHtml .= "\n                <li id=\"currentLink\" aria-current=\"page\">\n                        <img src=\"{$basePath}assets/images/user.svg\" alt=\"\" class=\"icone-menu\" />{$profileLabel}</li>";
            } else {
                $headerHtml .= "\n                <li><a href=\"{$profileLink}\">\n                        <img src=\"{$basePath}assets/images/user.svg\" alt=\"\" class=\"icone-menu\" />{$profileLabel}</a></li>";
            }

        } else {
            // Utente non loggato: un solo link "Profilo" che porta al Login
            if (strtolower($currentPage) === 'login') {
                $headerHtml .= "\n                <li id=\"currentLink\" aria-current=\"page\">\n                        <img src=\"{$basePath}assets/images/user.svg\" alt=\"\" class=\"icone-menu\" />Profilo</li>";
            } else {
                $headerHtml .= "\n                <li><a href=\"{$pagesPath}Login.php\">\n                        <img src=\"{$basePath}assets/images/user.svg\" alt=\"\" class=\"icone-menu\" />Profilo</a></li>";
            }
        }

        // Link Carrello (Sempre visibile per tutti)
        if (strtolower($currentPage) === 'carrello') {
            $headerHtml .= "\n                <li id=\"currentLink\" aria-current=\"page\">\n                        <img src=\"{$basePath}assets/images/shopping-cart.svg\" alt=\"\" class=\"icone-menu\" />Carrello</li>";
        } else {
            $headerHtml .= "\n                <li><a href=\"{$pagesPath}Carrello.php\">\n                        <img src=\"{$basePath}assets/images/shopping-cart.svg\" alt=\"\" class=\"icone-menu\" />Carrello</a></li>";
        }

        $headerHtml .= '
            </ul>
        </nav>
    </header>';

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