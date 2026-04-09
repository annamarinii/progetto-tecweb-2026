document.addEventListener('DOMContentLoaded', () => {
    const mailRows = document.querySelectorAll('.mail-row');

    mailRows.forEach(row => {
        const header = row.querySelector('.mail-row-header');
        const content = row.querySelector('.mail-content');
        const sendBtn = row.querySelector('.btn-send-reply');

        header.addEventListener('click', () => {
            const isExpanded = content.style.display === 'block';
            if (isExpanded) {
                content.style.display = 'none';
            } else {
                content.style.display = 'block';
            }

            if (row.classList.contains('unread')) {
                row.classList.remove('unread');
                row.classList.add('read');
            }
        });

        content.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        if (sendBtn) {
            sendBtn.addEventListener('click', () => {
                if (sendBtn.classList.contains('loading')) return;

                sendBtn.classList.add('loading');
                setTimeout(() => {
                    row.classList.add('fade-out');

                    setTimeout(() => {
                        row.style.height = '0';
                        row.style.padding = '0';
                        row.style.margin = '0';
                        row.style.border = 'none';
                        row.style.overflow = 'hidden';

                        setTimeout(() => {
                            row.remove();
                        }, 300);
                    }, 500);

                }, 1200);
            });
        }
    });
});
