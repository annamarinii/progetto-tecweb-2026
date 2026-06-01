/* ==========================================================================
   GESTIONE DARK MODE  (Separation of Concerns)
   - Stile: interamente in style.css tramite i token semantici --col-*
            e i selettori :root / [data-theme="dark"].
   - Logica (questo file): SOLO cambio di stato e persistenza. Modifica
            l'attributo data-theme su <html> (document.documentElement) e
            sincronizza gli attributi ARIA del bottone. Nessuno stile inline.
   Priorità all'avvio: 1) scelta salvata in localStorage, 2) preferenza del
   sistema operativo via window.matchMedia('(prefers-color-scheme: dark)').
   ========================================================================== */
(function () {
    'use strict';

    var STORAGE_KEY = 'theme';
    var root = document.documentElement;

    /* Legge il tema attualmente applicato su <html> (impostato dallo snippet
       anti-flash nell'header, o di default chiaro). */
    function temaCorrente() {
        return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    /* Determina il tema iniziale: prima il salvataggio utente, poi il sistema. */
    function temaIniziale() {
        var salvato;
        try { salvato = localStorage.getItem(STORAGE_KEY); } catch (e) { salvato = null; }
        if (salvato === 'dark' || salvato === 'light') {
            return salvato;
        }
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }

    /* Applica il tema: aggiorna l'attributo su <html> e gli ARIA del bottone.
       Lo stile reagisce da solo grazie ai selettori CSS. */
    function applicaTema(tema, bottone) {
        if (tema === 'dark') {
            root.setAttribute('data-theme', 'dark');
        } else {
            root.removeAttribute('data-theme');
        }
        if (bottone) {
            var scuroAttivo = tema === 'dark';
            // aria-pressed: il bottone è "premuto" quando il tema scuro è attivo
            bottone.setAttribute('aria-pressed', scuroAttivo ? 'true' : 'false');
            // aria-label: descrive l'AZIONE che il click compirà
            bottone.setAttribute(
                'aria-label',
                scuroAttivo ? 'Attiva il tema chiaro' : 'Attiva il tema scuro'
            );
        }
    }

    function init() {
        var bottone = document.getElementById('theme-toggle');

        // Sincronizza lo stato iniziale (anche se il bottone non c'è, il tema resta coerente)
        applicaTema(temaIniziale(), bottone);

        if (!bottone) { return; }

        // Click: 1) inverte il tema, 2) lo salva, 3) aggiorna ARIA e icona (via CSS)
        bottone.addEventListener('click', function () {
            var nuovo = temaCorrente() === 'dark' ? 'light' : 'dark';
            applicaTema(nuovo, bottone);
            try { localStorage.setItem(STORAGE_KEY, nuovo); } catch (e) { /* storage non disponibile */ }
        });

        // Se l'utente non ha mai scelto manualmente, segui i cambi di tema del sistema
        if (window.matchMedia) {
            var mq = window.matchMedia('(prefers-color-scheme: dark)');
            var onChange = function (e) {
                var haScelto;
                try { haScelto = localStorage.getItem(STORAGE_KEY); } catch (err) { haScelto = null; }
                if (haScelto !== 'dark' && haScelto !== 'light') {
                    applicaTema(e.matches ? 'dark' : 'light', bottone);
                }
            };
            if (mq.addEventListener) { mq.addEventListener('change', onChange); }
            else if (mq.addListener) { mq.addListener(onChange); } // browser datati
        }
    }

    // Lo script è caricato con defer, ma proteggiamo comunque l'accesso al DOM.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
