/*
 * INDICATORE PAGINE VISITATE (senza :visited)
 * -------------------------------------------------------------------------
 * I browser, per privacy, sulla pseudo-classe CSS :visited permettono di
 * cambiare SOLO i colori di elementi gia' visibili: niente icone, niente
 * sottolineature "nuove", e da JavaScript lo stato :visited non e' leggibile.
 * Per dare un segnale affidabile usiamo quindi un tracciamento nostro: salviamo
 * in localStorage le pagine aperte su questo sito e marchiamo con la classe
 * .link-visitato i link di header e footer che puntano a quelle pagine. Essendo
 * una classe normale (non :visited), il CSS puo' aggiungere l'icona pallina.
 */
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
                a.classList.add('link-visitato');
                // Testo per screen reader: l'icona pallina e' solo visiva.
                if (!a.querySelector('.indicatore-visitato-sr')) {
                    var sr = document.createElement('span');
                    sr.className = 'sr-only indicatore-visitato-sr';
                    sr.textContent = ' (pagina già visitata)';
                    a.appendChild(sr);
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', marcaLinkVisitati);
    } else {
        marcaLinkVisitati();
    }
})();
