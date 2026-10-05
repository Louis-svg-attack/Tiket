import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import {
    Chart,
    ArcElement,
    LineElement,
    BarElement,
    PointElement,
    BarController,
    LineController,
    DoughnutController,
    CategoryScale,
    LinearScale,
    Filler,
    Legend,
    Title,
    Tooltip,
} from 'chart.js';

Chart.register(
    ArcElement, LineElement, BarElement, PointElement,
    BarController, LineController, DoughnutController,
    CategoryScale, LinearScale, Filler, Legend, Title, Tooltip,
);

window.Alpine = Alpine;
window.Chart = Chart;
window.Swal = Swal;

// SweetAlert2 confirm-before-submit for any <form data-confirm="...">
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form.matches('[data-confirm]') && !form.dataset.confirmed) {
        e.preventDefault();
        Swal.fire({
            title: form.dataset.confirmTitle || 'Anda yakin?',
            text: form.dataset.confirm,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });
    }
}, true);

// Flash toast helper
window.flashToast = (icon, title) => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });
};

Alpine.start();
