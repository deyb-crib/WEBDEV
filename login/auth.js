document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.querySelector('#password');
    const passwordToggle = document.querySelector('#password-visibility');
    const passwordStatus = document.querySelector('#password-status');

    passwordToggle?.addEventListener('change', () => {
        if (!passwordInput) return;
        passwordInput.type = passwordToggle.checked ? 'text' : 'password';
    });

    passwordInput?.addEventListener('input', () => {
        if (!passwordStatus) return;
        passwordStatus.textContent = passwordInput.value.length > 0
            ? 'Password entered.'
            : '';
    });
});
