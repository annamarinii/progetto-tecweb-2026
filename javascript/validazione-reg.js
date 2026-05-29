document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-registrazione');

    if (!form) return;

    // Regex forte: min 8 char, almeno una minuscola, una maiuscola, un numero, un carattere speciale
    const REGEX_PASSWORD = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/;

    // Mappa id-requisito → funzione di test
    const requisitiPassword = {
        'req-lunghezza': v => v.length >= 8,
        'req-maiuscola': v => /[A-Z]/.test(v),
        'req-minuscola': v => /[a-z]/.test(v),
        'req-numero':    v => /\d/.test(v),
        'req-speciale':  v => /[^a-zA-Z\d\s]/.test(v)
    };

    // Aggiorna la checklist visiva in tempo reale mentre l'utente digita
    function aggiornaRequisitiPassword(valore) {
        Object.entries(requisitiPassword).forEach(([id, test]) => {
            const li = document.getElementById(id);
            if (!li) return;
            if (test(valore)) {
                li.classList.add('soddisfatto');
                li.classList.remove('non-soddisfatto');
            } else {
                li.classList.remove('soddisfatto');
                li.classList.add('non-soddisfatto');
            }
        });
    }

    const passInput = document.getElementById('pass');
    if (passInput) {
        passInput.addEventListener('input', () => {
            aggiornaRequisitiPassword(passInput.value);
        });
    }

    const inputs = form.querySelectorAll('input');

    const validaCampo = (input, isSubmit = false) => {
        const erroreSpan = document.getElementById(`err-${input.id}`);
        let messaggio = "";

        input.classList.remove('invalid');
        if (erroreSpan) {
            erroreSpan.classList.remove('visible');
            erroreSpan.textContent = "";
        }

        // Non mostrare errori "campo vuoto" su blur (solo su submit)
        if (input.value.trim() === '' && !isSubmit) {
            return true;
        }

        // 1. Validità HTML5 (pattern, type=email, required)
        if (!input.checkValidity()) {
            input.classList.add('invalid');
            messaggio = input.title || "Campo non valido.";
        }

        // 2. Forza password
        if (input.id === 'pass' && input.value !== '') {
            if (!REGEX_PASSWORD.test(input.value)) {
                input.classList.add('invalid');
                messaggio = "La password non soddisfa tutti i requisiti indicati.";
            }
        }

        // 3. Coincidenza password
        if (input.id === 'ripeti_pass') {
            const passOriginale = document.getElementById('pass').value;
            if (input.value !== passOriginale) {
                input.classList.add('invalid');
                messaggio = "Le password non coincidono.";
            }
        }

        if (messaggio && erroreSpan) {
            erroreSpan.textContent = messaggio;
            erroreSpan.classList.add('visible');
            return false;
        }
        return true;
    };

    // Validazione in tempo reale all'uscita dal campo (blur)
    inputs.forEach(input => {
        input.addEventListener('blur', () => validaCampo(input));
    });

    // Validazione completa all'invio
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        let formValido = true;
        let primoInputInvalido = null;

        inputs.forEach(input => {
            if (!validaCampo(input, true)) {
                formValido = false;
                if (!primoInputInvalido) primoInputInvalido = input;
            }
        });

        // Sposta il focus sul primo campo con errore (accessibilità)
        if (!formValido) {
            if (primoInputInvalido) primoInputInvalido.focus();
            return;
        }

        const btnSubmit = form.querySelector('button[type="submit"]');
        btnSubmit.disabled = true;
        btnSubmit.textContent = "Registrazione in corso...";

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(data => {
            let msgDiv = document.getElementById('messaggio-ajax');
            if (!msgDiv) {
                msgDiv = document.createElement('div');
                msgDiv.id = 'messaggio-ajax';
                msgDiv.setAttribute('role', 'alert');
                msgDiv.setAttribute('aria-live', 'assertive');
                form.parentNode.insertBefore(msgDiv, form);
            }

            if (data.status === 'success') {
                msgDiv.className = 'form-message message-success';
                msgDiv.innerHTML = 'Registrazione completata! <br><br><a href="../php-pages/Login.php" class="btn-auth">Vai al Login</a>';
                form.reset();
                aggiornaRequisitiPassword('');
            } else {
                msgDiv.className = 'form-message message-error';
                msgDiv.textContent = data.message;
            }

            btnSubmit.disabled = false;
            btnSubmit.textContent = "Registrati Subito";
        })
        .catch(() => {
            let msgDiv = document.getElementById('messaggio-ajax');
            if (!msgDiv) {
                msgDiv = document.createElement('div');
                msgDiv.id = 'messaggio-ajax';
                msgDiv.setAttribute('role', 'alert');
                msgDiv.setAttribute('aria-live', 'assertive');
                form.parentNode.insertBefore(msgDiv, form);
            }
            msgDiv.className = 'form-message message-error';
            msgDiv.textContent = "Errore di connessione al server. Riprova.";
            btnSubmit.disabled = false;
            btnSubmit.textContent = "Registrati Subito";
        });
    });
});
