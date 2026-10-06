import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

// Custom SweetAlert instance tailored to PCM Simo branding
const customSwal = Swal.mixin({
    customClass: {
        popup: 'swal2-pcm-popup',
        title: 'swal2-pcm-title',
        htmlContainer: 'swal2-pcm-html',
        confirmButton: 'swal2-pcm-confirm',
        cancelButton: 'swal2-pcm-cancel',
    },
    buttonsStyling: false,
    showClass: {
        popup: 'swal2-pcm-show',
    },
    hideClass: {
        popup: 'swal2-pcm-hide',
    },
});

export const showAlert = {
    success: (title, text = '') => {
        return customSwal.fire({
            icon: 'success',
            iconColor: '#006837',
            title,
            text,
            confirmButtonText: 'Selesai',
        });
    },

    error: (title, text = '') => {
        return customSwal.fire({
            icon: 'error',
            iconColor: '#e11d48',
            title,
            text,
            confirmButtonText: 'Mengerti',
        });
    },

    warning: (title, text = '') => {
        return customSwal.fire({
            icon: 'warning',
            iconColor: '#f59e0b',
            title,
            text,
            confirmButtonText: 'Baik',
        });
    },

    info: (title, text = '') => {
        return customSwal.fire({
            icon: 'info',
            iconColor: '#0284c7',
            title,
            text,
            confirmButtonText: 'Baik',
        });
    },

    confirm: async (title, text = '', confirmButtonText = 'Ya, Lanjutkan') => {
        const result = await customSwal.fire({
            icon: 'warning',
            iconColor: '#f59e0b',
            title,
            text,
            showCancelButton: true,
            confirmButtonText,
            cancelButtonText: 'Batal',
            reverseButtons: true,
        });
        return result.isConfirmed;
    },

    toast: (title, icon = 'success') => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            customClass: {
                popup: 'swal2-pcm-toast',
            },
            showClass: {
                popup: 'swal2-pcm-show',
            },
            hideClass: {
                popup: 'swal2-pcm-hide',
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });
        return Toast.fire({
            icon,
            iconColor: icon === 'success' ? '#006837' : (icon === 'info' ? '#0284c7' : undefined),
            title,
        });
    },
};

export default showAlert;
