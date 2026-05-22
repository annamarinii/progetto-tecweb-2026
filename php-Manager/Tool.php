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
        $_SESSION['username'] = $utente['username']; // Corretto in minuscolo come da DB SQL
        $_SESSION['nome'] = $utente['nome'];         // Corretto in minuscolo come da DB SQL
        $_SESSION['cognome'] = $utente['cognome'];   // Corretto in minuscolo come da DB SQL
        $_SESSION['email'] = $utente['email'];       // Corretto in minuscolo come da DB SQL
        $_SESSION['isAdmin'] = $utente['isAdmin'];   // Corretto in base alla colonna del DB SQL
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
        <a href="' . $basePath . 'index.php" class="logo-link">
            <img src="' . $basePath . 'assets/images/logo1.png" alt="Patavium Open" id="logo-sito" />
            <span>Patavium Open</span>
        </a>

        <nav id="menu" aria-label="Navigazione principale">
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
        $basePath = $inRoot ? './' : '../';

        return '<footer class="footer-sito">
        <hr aria-hidden="true" />
        <div class="footer-contenuto">
            <div class="footer-logo">
                 <img src="' . $basePath . 'assets/images/logo1.png" alt="Patavium Open" width="120" />
            </div>
            <nav class="footer-nav" aria-label="Informazioni legali">
                <ul>
                    <li><a href="' . $basePath . 'html/termini.html">Termini e condizioni</a></li>
                    <li><a href="' . $basePath . 'html/privacy.html">Informativa sulla privacy</a></li>
                </ul>
            </nav>
        </div>
        <p class="footer-copyright">&copy; 2027 Patavium Open. Tutti i diritti riservati.</p>
    </footer>';
    }
}