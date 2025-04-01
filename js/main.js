// Smooth scrolling para los enlaces de navegación
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Animación del header al hacer scroll
let lastScroll = 0;
const header = document.querySelector('.header');

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll <= 0) {
        header.classList.remove('scroll-up');
        return;
    }
    
    if (currentScroll > lastScroll && !header.classList.contains('scroll-down')) {
        // Scroll Down
        header.classList.remove('scroll-up');
        header.classList.add('scroll-down');
    } else if (currentScroll < lastScroll && header.classList.contains('scroll-down')) {
        // Scroll Up
        header.classList.remove('scroll-down');
        header.classList.add('scroll-up');
    }
    lastScroll = currentScroll;
});

// Animación de las tarjetas de categorías al hacer scroll
const observerOptions = {
    threshold: 0.1
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate');
        }
    });
}, observerOptions);

document.querySelectorAll('.categoria-card').forEach(card => {
    observer.observe(card);
});

// Validación del formulario de inscripción
const form = document.querySelector('.inscripcion-form');
if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validación básica
        const nombre = form.querySelector('input[type="text"]').value;
        const email = form.querySelector('input[type="email"]').value;
        const categoria = form.querySelector('select').value;
        
        if (!nombre || !email || !categoria) {
            alert('Por favor, complete todos los campos requeridos');
            return;
        }
        
        // Aquí iría la lógica para enviar el formulario
        alert('¡Gracias por tu inscripción! Nos pondremos en contacto contigo pronto.');
        form.reset();
    });
}

// Contador regresivo para la fecha del evento
function updateCountdown() {
    const eventDate = new Date('2025-06-15T00:00:00').getTime();
    const now = new Date().getTime();
    const distance = eventDate - now;

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    // Actualizar el contador en el DOM si existe el elemento
    const countdownElement = document.querySelector('.countdown');
    if (countdownElement) {
        countdownElement.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
    }

    if (distance < 0) {
        clearInterval(countdownInterval);
        if (countdownElement) {
            countdownElement.innerHTML = "¡El evento ha comenzado!";
        }
    }
}

const countdownInterval = setInterval(updateCountdown, 1000);
updateCountdown(); 