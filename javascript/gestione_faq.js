document.addEventListener('DOMContentLoaded', () => {
    const faqDash = document.getElementById('faq-dashboard');
    const newFaqView = document.getElementById('view-nuova-faq');
    const listFaqView = document.getElementById('view-elenco-faq');
    const faqForm = document.querySelector('#view-nuova-faq form');

    function hideAllFaqViews() {
        [faqDash, newFaqView, listFaqView].forEach(view => {
            if (view) view.classList.add('hidden');
        });
    }

    // Forza lo stato iniziale FAQ
    hideAllFaqViews();
    faqDash?.classList.remove('hidden');

    document.getElementById('btn-new-faq')?.addEventListener('click', () => {
        hideAllFaqViews();
        faqForm?.reset();
        newFaqView.classList.remove('hidden');
    });

    document.getElementById('btn-manage-faq')?.addEventListener('click', () => {
        hideAllFaqViews();
        listFaqView.classList.remove('hidden');
    });

    document.querySelectorAll('.btn-back-faq').forEach(btn => {
        btn.addEventListener('click', () => {
            hideAllFaqViews();
            faqDash.classList.remove('hidden');
        });
    });

    // Modifica FAQ tramite card
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-edit-faq-trigger')) {
            const btn = e.target;
            document.getElementById('faq-domanda').value = btn.dataset.q;
            document.getElementById('faq-risposta').value = btn.dataset.a;
            
            // Se hai un campo hidden per l'ID
            const idInput = faqForm.querySelector('input[name="id_faq"]');
            if(idInput) idInput.value = btn.dataset.id;

            hideAllFaqViews();
            newFaqView.classList.remove('hidden');
        }
    });

    function mostraMessaggioFaq(testo, tipo) {
        document.querySelectorAll('.ajax-dynamic-msg-faq').forEach(m => m.remove());
        const contenitore = document.getElementById('gestione-faq');
        const msg = document.createElement('div');
        msg.className = 'ajax-dynamic-msg-faq';
        const colore = tipo === 'success' ? 'green' : 'red';
        msg.innerHTML = `<div style="color:${colore}; padding:10px; border:1px solid ${colore}; margin-bottom:20px; font-weight:bold; background:white;">${testo}</div>`;
        contenitore?.prepend(msg);
        setTimeout(() => {
            msg.style.opacity = "0";
            setTimeout(() => msg.remove(), 500);
        }, 2000);
    }

    faqForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch(this.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                mostraMessaggioFaq('Operazione completata!', 'success');
                if (data.html_faq) document.querySelector('.faq-list-admin').innerHTML = data.html_faq;
                if (faqIdInput.value !== "") {
                    setTimeout(() => {
                        hideAllFaqViews();
                        listFaqView?.classList.remove('hidden');
                    }, 1000);
                } else {
                    this.reset();
                }
            }
        });
    });

    document.querySelector('.faq-list-admin')?.addEventListener('submit', function(e) {
        const targetForm = e.target;
        if (targetForm.classList.contains('form-delete-faq')) {
            e.preventDefault();
            if(!confirm('Eliminare definitivamente?')) return;
            fetch(targetForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(targetForm)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    mostraMessaggioFaq('FAQ Eliminata', 'success');
                    targetForm.closest('.faq-admin-card').remove();
                }
            });
        }
    });
});