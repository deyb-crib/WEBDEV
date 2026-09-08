const sidebar = document.querySelector('#admin-sidebar');
document.querySelector('.admin-menu-toggle')?.addEventListener('click', () => sidebar?.classList.toggle('is-open'));

const centerForm = document.querySelector('#center-form');
document.querySelector('[data-open-center-form]')?.addEventListener('click', () => {
    if (!centerForm) return;
    centerForm.hidden = false;
    centerForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    centerForm.querySelector('input[name="name"]')?.focus();
});

const scheduleTimeInput = document.querySelector('input[name="schedule_time"]');
if (scheduleTimeInput) {
    const scheduleTimeSelect = document.createElement('select');
    scheduleTimeSelect.name = 'schedule_time';
    scheduleTimeSelect.required = true;
    scheduleTimeSelect.innerHTML = '<option value="">Select a time</option><option value="08:00">8:00 AM</option><option value="09:00">9:00 AM</option><option value="10:00">10:00 AM</option><option value="11:00">11:00 AM</option><option value="12:00">12:00 PM</option><option value="13:00">1:00 PM</option><option value="14:00">2:00 PM</option><option value="15:00">3:00 PM</option><option value="16:00">4:00 PM</option><option value="17:00">5:00 PM</option>';
    scheduleTimeInput.replaceWith(scheduleTimeSelect);
}

document.querySelectorAll('form[action*="add_center.php"], form[action*="edit_center.php"]').forEach((form) => {
    form.enctype = 'multipart/form-data';
    if (!form.querySelector('select[name="facility_type"]')) {
        const field = document.createElement('label');
        field.className = 'admin-field';
        field.textContent = 'Facility type';
        const select = document.createElement('select');
        select.name = 'facility_type';
        select.required = true;
        select.innerHTML = '<option value="Dialysis Center">Dialysis Center</option><option value="Hospital">Hospital</option>';
        field.append(select);
        form.insertBefore(field, form.querySelector('button[type="submit"]'));
    }
    if (!form.querySelector('input[name="image"]')) {
        const field = document.createElement('label');
        field.className = 'admin-field full';
        field.textContent = 'Center picture';
        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'image';
        input.accept = 'image/jpeg,image/png,image/webp';
        field.append(input);
        form.insertBefore(field, form.querySelector('button[type="submit"]'));
    }
});

const centerTable = document.querySelector('.admin-table');
if (centerTable && window.location.pathname.endsWith('/centers.php')) {
    const header = document.createElement('th');
    header.textContent = 'Picture';
    centerTable.querySelector('thead tr')?.prepend(header);
    centerTable.querySelectorAll('tbody tr').forEach((row) => {
        const cell = document.createElement('td');
        cell.innerHTML = '<span class="admin-image-placeholder">No image</span>';
        row.prepend(cell);
    });
}

document.querySelectorAll('[data-confirm]').forEach((button) => button.addEventListener('click', (event) => {
    if (!window.confirm(button.dataset.confirm)) event.preventDefault();
}));
