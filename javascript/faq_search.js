document.addEventListener('DOMContentLoaded', () => {
    const campoRicerca = document.getElementById('faq-search');
    const feedback = document.getElementById('faq-search-feedback');
    const faqItems = document.querySelectorAll('.faq-item');
    const categorie = document.querySelectorAll('.faq-category');

    // Se la barra di ricerca non è presente nella pagina, non facciamo nulla
    if (!campoRicerca) return;

    let timeoutId = null;

    function mostraTutto() {
        faqItems.forEach(item => item.classList.remove('hidden'));
        categorie.forEach(cat => cat.classList.remove('hidden'));
        if (feedback) feedback.textContent = '';
    }

    function filtra() {
        const termine = campoRicerca.value.trim().toLowerCase();

        // Campo vuoto: mostra tutto e svuota il feedback
        if (termine === '') {
            mostraTutto();
            return;
        }

        let visibili = 0;

        // Filtra ogni singola FAQ in base al testo
        faqItems.forEach(item => {
            if (item.textContent.toLowerCase().includes(termine)) {
                item.classList.remove('hidden');
                visibili++;
            } else {
                item.classList.add('hidden');
                item.open = false;   
            }
        });

        // Nascondi le categorie che non hanno più nessuna FAQ visibile
        categorie.forEach(cat => {
            const itemsCat = cat.querySelectorAll('.faq-item');
            const tuttiNascosti = Array.from(itemsCat).every(item => item.classList.contains('hidden'));
            cat.classList.toggle('hidden', tuttiNascosti);
        });

        // Aggiorna il messaggio per gli screen reader (aria-live)
        if (feedback) {
            if (visibili === 0) {
                feedback.textContent = 'Nessun risultato per «' + campoRicerca.value.trim() + '»';
            } else {
                feedback.textContent = visibili + (visibili === 1 ? ' risultato trovato' : ' risultati trovati');
            }
        }
    }

    // Debounce ~150ms per non spammare l'area aria-live
    campoRicerca.addEventListener('input', () => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(filtra, 150);
    });
});
