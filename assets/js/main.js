document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-nav-toggle]');
    const menu = document.querySelector('#public-menu');
    const navbar = document.querySelector('.navbar');

    if (!toggle || !menu || !navbar) {
        return;
    }

    const mobile = window.matchMedia('(max-width: 1100px)');

    navbar.classList.add('nav-enhanced');

    function closeMenu() {
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Buka navigasi');
    }

    function syncNavigation() {
        toggle.hidden = !mobile.matches;
        closeMenu();
    }

    toggle.addEventListener('click', () => {
        const open = !menu.classList.contains('is-open');

        menu.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute(
            'aria-label',
            open ? 'Tutup navigasi' : 'Buka navigasi'
        );
    });

    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMenu);
    });

    document.addEventListener('keydown', (event) => {
        if (
            event.key === 'Escape'
            && menu.classList.contains('is-open')
        ) {
            closeMenu();
            toggle.focus();
        }
    });

    mobile.addEventListener('change', syncNavigation);

    syncNavigation();
});
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.querySelector('#gallery-lightbox');
    const links = Array.from(
        document.querySelectorAll('a[data-lightbox]')
    );

    if (!dialog || !links.length || typeof dialog.showModal !== 'function') {
        return;
    }

    const image = dialog.querySelector('.lightbox-image');
    const caption = dialog.querySelector('.lightbox-caption');
    const closeButton = dialog.querySelector('[data-lightbox-close]');
    const previousButton = dialog.querySelector('[data-lightbox-prev]');
    const nextButton = dialog.querySelector('[data-lightbox-next]');

    let currentIndex = 0;
    let trigger = null;

    function showImage(index) {
        currentIndex = (index + links.length) % links.length;

        const link = links[currentIndex];
        const title = link.dataset.caption || '';

        image.src = link.href;
        image.alt = title;

        caption.textContent =
            `${title} (${currentIndex + 1}/${links.length})`;

        previousButton.hidden = links.length < 2;
        nextButton.hidden = links.length < 2;
    }

    links.forEach((link, index) => {
        link.addEventListener('click', (event) => {
            // Pertahankan perilaku buka tab baru dengan Ctrl/Cmd.
            if (
                event.ctrlKey
                || event.metaKey
                || event.shiftKey
                || event.altKey
                || event.button !== 0
            ) {
                return;
            }

            event.preventDefault();

            trigger = link;
            showImage(index);

            dialog.showModal();
            document.body.classList.add('lightbox-open');
        });
    });

    closeButton.addEventListener('click', () => dialog.close());

    previousButton.addEventListener('click', () => {
        showImage(currentIndex - 1);
    });

    nextButton.addEventListener('click', () => {
        showImage(currentIndex + 1);
    });

    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            showImage(currentIndex - 1);
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            showImage(currentIndex + 1);
        }
    });

    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) {
            return;
        }

        const bounds = dialog.getBoundingClientRect();

        if (
            event.clientX < bounds.left
            || event.clientX > bounds.right
            || event.clientY < bounds.top
            || event.clientY > bounds.bottom
        ) {
            dialog.close();
        }
    });

    dialog.addEventListener('close', () => {
        document.body.classList.remove('lightbox-open');
        image.removeAttribute('src');
        trigger?.focus();
    });
});