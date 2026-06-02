/* Menu hamburger dell'header (mobile/tablet). Nessun onclick inline: tutta la
   logica è qui. Gestisce aria-expanded e aria-label, la chiusura con Escape (con
   ritorno del focus al bottone), il click esterno e il ritorno al layout desktop. */
(function () {
    'use strict';

    function init() {
        var header = document.querySelector('header');
        var toggle = document.getElementById('nav-toggle');
        var menu = document.getElementById('menu');
        if (!header || !toggle || !menu) return;

        function setOpen(open) {
            header.classList.toggle('nav-open', open);
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open
                ? 'Chiudi il menu di navigazione'
                : 'Apri il menu di navigazione');
        }

        function isOpen() {
            return toggle.getAttribute('aria-expanded') === 'true';
        }

        // Apertura/chiusura col bottone (funziona anche da tastiera: è un <button>)
        toggle.addEventListener('click', function () {
            setOpen(!isOpen());
        });

        // Quando si sceglie una pagina dal menu, la tendina si chiude
        menu.addEventListener('click', function (e) {
            if (e.target.closest('a')) setOpen(false);
        });

        // Escape: chiude e riporta il focus sul bottone (gestione focus da tastiera)
        document.addEventListener('keydown', function (e) {
            if ((e.key === 'Escape' || e.key === 'Esc') && isOpen()) {
                setOpen(false);
                toggle.focus();
            }
        });

        // Click fuori dall'header: chiude la tendina
        document.addEventListener('click', function (e) {
            if (isOpen() && !header.contains(e.target)) setOpen(false);
        });

        // Tornando al layout desktop, azzera lo stato (evita aria-expanded "true" residuo)
        var desktop = window.matchMedia('(min-width: 993px)');
        function onChange(e) { if (e.matches) setOpen(false); }
        if (desktop.addEventListener) desktop.addEventListener('change', onChange);
        else if (desktop.addListener) desktop.addListener(onChange); /* browser datati */
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
