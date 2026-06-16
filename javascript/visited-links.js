/* Segna i link delle pagine gia' visitate.
   Non si usa :visited (i browser ne permettono solo il cambio di colore e non la
   espongono a JavaScript): salviamo le pagine aperte in localStorage e mettiamo
   la classe .link-visitato sui link che vi puntano, su cui il CSS mostra la pallina. */
(function () {
    'use strict';

    var CHIAVE = 'pataviumPagineVisitate';

    function leggiVisitate() {
        try {
            var raw = localStorage.getItem(CHIAVE);
            var arr = raw ? JSON.parse(raw) : [];
            return Array.isArray(arr) ? arr : [];
        } catch (e) {
            return [];
        }
    }

    function salvaVisitate(arr) {
        try {
            localStorage.setItem(CHIAVE, JSON.stringify(arr));
        } catch (e) {
            /* localStorage non disponibile: si rinuncia silenziosamente */
        }
    }

    var pathCorrente = window.location.pathname;
    var visitate = leggiVisitate();

    // Registra la pagina corrente tra quelle visitate.
    if (visitate.indexOf(pathCorrente) === -1) {
        visitate.push(pathCorrente);
        salvaVisitate(visitate);
    }

    // Bottoni grafici (CTA): NON vanno marcati, l'icona starebbe male.
    var ESCLUDI = '.btn-biglietti, .btn-primary, .btn-auth, .btn-checkout, ' +
        '.btn-ritorno, .btn-select-date, .buy-btn, .buy-button, .action-card, ' +
        '.btn-login, .btn-register, .btn-logout-small';

    function marcaLinkVisitati() {
        // Header (menu), footer e link nei contenuti (incluso es. la Privacy
        // Policy nel form). Il breadcrumb e' escluso (non rientra in questi
        // contesti) per lasciare "Home" del suo colore normale.
        var links = document.querySelectorAll('header #menu a[href], footer a[href], main a[href]');
        Array.prototype.forEach.call(links, function (a) {
            if (a.matches && a.matches(ESCLUDI)) {
                return; // salta i bottoni grafici
            }
            // a.pathname risolve l'href nel percorso assoluto (es. /.../news.php).
            var p = a.pathname;
            // Non si marca la pagina su cui ci si trova.
            if (p && p !== pathCorrente && visitate.indexOf(p) !== -1) {
                // La pallina e' decorativa (CSS ::after): basta la classe.
                a.classList.add('link-visitato');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', marcaLinkVisitati);
    } else {
        marcaLinkVisitati();
    }
})();
