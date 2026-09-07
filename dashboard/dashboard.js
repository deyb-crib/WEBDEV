const sidebar = document.querySelector('#dashboard-sidebar');
const menuButton = document.querySelector('.dashboard-user');
const closeButton = document.querySelector('.sidebar-close');
const backdrop = document.querySelector('.sidebar-backdrop');
const cancelModal = document.querySelector('#cancel-modal');
const confirmCancelButton = document.querySelector('#confirm-cancel-appointment');
let pendingCancelForm = null;

function setSidebarState(isOpen) {
    document.body.classList.toggle('sidebar-open', isOpen);
    sidebar.setAttribute('aria-hidden', String(!isOpen));
    menuButton.setAttribute('aria-expanded', String(isOpen));

    if (isOpen) {
        closeButton.focus();
    } else {
        menuButton.focus();
    }
}

menuButton.addEventListener('click', () => setSidebarState(true));
closeButton.addEventListener('click', () => setSidebarState(false));
backdrop.addEventListener('click', () => setSidebarState(false));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
        setSidebarState(false);
    }
});

function closeCancelModal() {
    cancelModal?.classList.remove('is-visible');
    cancelModal?.setAttribute('aria-hidden', 'true');
    pendingCancelForm = null;
}

document.querySelectorAll('.cancel-appointment-form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        pendingCancelForm = form;
        cancelModal?.classList.add('is-visible');
        cancelModal?.setAttribute('aria-hidden', 'false');
    });
});

confirmCancelButton?.addEventListener('click', () => {
    pendingCancelForm?.submit();
    closeCancelModal();
});

document.querySelectorAll('[data-close-cancel-modal="true"]').forEach((button) => {
    button.addEventListener('click', closeCancelModal);
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && cancelModal?.classList.contains('is-visible')) {
        closeCancelModal();
    }
});