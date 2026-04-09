document.addEventListener('DOMContentLoaded', () => {
    // Viste all'interno della Gestione FAQ
    const dashView = document.getElementById('faq-dashboard');
    const newFaqView = document.getElementById('view-nuova-faq');
    const listFaqView = document.getElementById('view-elenco-faq');
    // Se decidi di fare un editor separato come per le news, aggiungi un ID 'view-modifica-faq'
    const editFaqView = document.getElementById('view-modifica-faq'); 

    // Bottoni della Dashboard FAQ
    const btnNewFaq = document.getElementById('btn-new-faq');
    const btnManageFaq = document.getElementById('btn-manage-faq');

    // Bottoni per tornare indietro
    const btnsBackToDash = document.querySelectorAll('.btn-back-faq');

    // Funzione ausiliaria per nascondere tutti i pannelli FAQ
    function hideAllFaqViews() {
        if(dashView) dashView.style.display = 'none';
        if(newFaqView) newFaqView.style.display = 'none';
        if(listFaqView) listFaqView.style.display = 'none';
        if(editFaqView) editFaqView.style.display = 'none';
    }

    // Navigazione Dashboard FAQ
    if (btnNewFaq && btnManageFaq) {
        btnNewFaq.addEventListener('click', () => {
            hideAllFaqViews();
            newFaqView.style.display = 'block';
        });

        btnManageFaq.addEventListener('click', () => {
            hideAllFaqViews();
            listFaqView.style.display = 'block';
        });
    }

    // Tornare alla Dashboard FAQ
    btnsBackToDash.forEach(btn => {
        btn.addEventListener('click', () => {
            hideAllFaqViews();
            dashView.style.display = 'block';
        });
    });

    // Gestione eliminazione FAQ (se aggiungi pulsanti elimina nell'elenco)
    const deleteFaqBtns = document.querySelectorAll('.btn-delete-faq');
    deleteFaqBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if(confirm("Sei sicuro di voler eliminare questa FAQ?")) {
                const form = this.closest('form');
                if(form) {
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'elimina';
                    actionInput.value = 'si';
                    form.appendChild(actionInput);
                    form.submit();
                }
            }
        });
    });
});