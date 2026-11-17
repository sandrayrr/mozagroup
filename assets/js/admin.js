document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const overlay = document.querySelector('[data-sidebar-overlay]');
    const sidebar = document.querySelector('#admin-sidebar');

    function setSidebar(open) {
        document.body.classList.toggle('sidebar-open', open);

        if (toggle) {
            toggle.setAttribute('aria-expanded', String(open));
        }
    }

    toggle?.addEventListener('click', () => {
        setSidebar(
            !document.body.classList.contains('sidebar-open')
        );
    });

    overlay?.addEventListener('click', () => {
        setSidebar(false);
        toggle?.focus();
    });

    document.addEventListener('keydown', (event) => {
        if (
            event.key === 'Escape'
            && document.body.classList.contains('sidebar-open')
        ) {
            setSidebar(false);
            toggle?.focus();
        }
    });

    // Tutup sidebar setelah memilih menu pada layar kecil.
    sidebar?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setSidebar(false));
    });

    window.matchMedia('(max-width: 900px)')
        .addEventListener('change', () => setSidebar(false));

    // Dipakai nanti pada form penghapusan data.
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirm
                || 'Yakin ingin menghapus data ini?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});