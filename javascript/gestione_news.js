document.addEventListener('DOMContentLoaded', () => {
    // Viste all all'interno della Gestione News
    const dashView = document.getElementById('news-dashboard');
    const newNewsView = document.getElementById('view-nuova-news');
    const listNewsView = document.getElementById('view-elenco-news');
    const editNewsView = document.getElementById('view-modifica-news');

    // Bottoni della Dashboard
    const btnNewNews = document.getElementById('btn-new-news');
    const btnManageNews = document.getElementById('btn-manage-news');

    // Bottoni per tornare indietro
    const btnsBackToDash = document.querySelectorAll('.btn-back-news');
    const btnBackToList = document.querySelector('.btn-back-to-list');

    // Miniature
    const miniCards = document.querySelectorAll('.news-mini-card');

    // Funzione ausiliaria per nascondere tutti i pannelli
    function hideAllViews() {
        if(dashView) dashView.style.display = 'none';
        if(newNewsView) newNewsView.style.display = 'none';
        if(listNewsView) listNewsView.style.display = 'none';
        if(editNewsView) editNewsView.style.display = 'none';
    }

    // Navigazione Dashboard
    if (btnNewNews && btnManageNews) {
        btnNewNews.addEventListener('click', () => {
            hideAllViews();
            newNewsView.style.display = 'block';
        });

        btnManageNews.addEventListener('click', () => {
            hideAllViews();
            listNewsView.style.display = 'block';
        });
    }

    // Tornare alla Dashboard
    btnsBackToDash.forEach(btn => {
        btn.addEventListener('click', () => {
            hideAllViews();
            dashView.style.display = 'block';
        });
    });

    // Tornare dalla singola news all'elenco delle miniature
    if (btnBackToList) {
        btnBackToList.addEventListener('click', () => {
            hideAllViews();
            listNewsView.style.display = 'block';
        });
    }

    // Click su una miniatura per aprire l'editor personalizzato
    miniCards.forEach(card => {
        card.addEventListener('click', () => {
            const title = card.querySelector('h4').textContent;
            const imgSrc = card.querySelector('.mini-card-img').getAttribute('src');
            const newsId = card.getAttribute('data-news-id');
            const imgName = imgSrc.substring(imgSrc.lastIndexOf('/')+1); // Estrae il nome dal path
            
            // Estrazione del VERO testo completo (salvato nascosto nel DIV php)
            const fullTextDiv = card.querySelector('.news-full-text');
            const testoReale = fullTextDiv ? fullTextDiv.innerHTML : '';
            
            // Popoliamo il form di modifica con i dati reali del record estratto dal database!
            const editFormTitle = document.getElementById('edit-titolo');
            const spanImgCorrente = document.getElementById('span-img-corrente');
            const editFormId = document.getElementById('edit-id-news');
            const editFormText = document.getElementById('edit-testo');
            
            if(editFormTitle) editFormTitle.value = title;
            if(spanImgCorrente) spanImgCorrente.textContent = imgName;
            if(editFormId) editFormId.value = newsId;
            if(editFormText) editFormText.value = testoReale;

            hideAllViews();
            editNewsView.style.display = 'block';
        });
    });

    // I pulsanti nel HTML verranno trasformati in type="submit" in modo che
    // chiamino il file API_SalvaNews.php senza essere bloccati.
    
    const deleteMockBtns = document.querySelectorAll('.btn-delete-mock');

    deleteMockBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            // Richiede una conferma prima dell'eliminazione reale
            if(confirm("Sei sicuro di voler eliminare definitivamente questa news? L'operazione non è reversibile.")) {
                // Troviamo il form padre e aggiungiamo un input action esplicito
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
