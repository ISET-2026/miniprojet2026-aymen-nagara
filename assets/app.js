import Swal from 'sweetalert2';

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', function handler(e) {
        e.preventDefault();
        const msg = form.getAttribute('data-confirm') || 'Confirmer ?';
        Swal.fire({
            title: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui',
            cancelButtonText: 'Annuler',
        }).then((result) => {
            if (result.isConfirmed) {
                form.removeEventListener('submit', handler, false);
                form.submit();
            }
        });
    });
});
