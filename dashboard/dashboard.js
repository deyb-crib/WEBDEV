const sidebar = document.querySelector('#dashboard-sidebar');
const menuButton = document.querySelector('.dashboard-user');
const closeButton = document.querySelector('.sidebar-close');
const backdrop = document.querySelector('.sidebar-backdrop');

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