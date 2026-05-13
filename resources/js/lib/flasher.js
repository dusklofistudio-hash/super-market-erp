// Bridge between PHPFlasher and SweetAlert2 + our toast helpers.
import Swal from 'sweetalert2';

export function flashSuccess(message, title) {
    return Swal.fire({
        toast: true,
        icon: 'success',
        position: 'top-end',
        title: title || message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}

export function flashError(message, title) {
    return Swal.fire({
        toast: true,
        icon: 'error',
        position: 'top-end',
        title: title || message,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
    });
}

export function confirmDelete({ title, text, confirmText, cancelText } = {}) {
    return Swal.fire({
        title: title || 'Are you sure?',
        text: text || 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmText || 'Yes, delete it',
        cancelButtonText: cancelText || 'Cancel',
    });
}

export function flushInertiaFlash(props) {
    const flash = props?.flash || {};
    if (flash.success) flashSuccess(flash.success);
    if (flash.error) flashError(flash.error);
}
