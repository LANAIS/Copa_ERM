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
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate');
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

document.querySelectorAll('.categoria-card, .fecha-item, .contacto-info, .redes-sociales').forEach(element => {
    observer.observe(element);
});

// Efecto de partículas en el hero
function createParticles() {
    const particlesContainer = document.createElement('div');
    particlesContainer.className = 'particles';
    document.querySelector('.hero').appendChild(particlesContainer);

    for (let i = 0; i < 50; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.width = Math.random() * 5 + 'px';
        particle.style.height = particle.style.width;
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 20 + 's';
        particlesContainer.appendChild(particle);
    }
}

createParticles();

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
            showAlert('Por favor, complete todos los campos requeridos', 'error');
            return;
        }
        
        // Aquí iría la lógica para enviar el formulario
        showAlert('¡Gracias por tu inscripción! Nos pondremos en contacto contigo pronto.', 'success');
        form.reset();
    });
}

// Función para mostrar alertas
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const form = document.querySelector('.inscripcion-form');
    form.insertBefore(alertDiv, form.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Contador regresivo para la fecha del evento
function updateCountdown() {
    const countdownElement = document.querySelector('.countdown');
    if (!countdownElement) return;
    
    // Obtener la fecha desde el atributo data-target-date o usar la fecha predeterminada
    let eventDateStr = countdownElement.getAttribute('data-target-date');
    if (!eventDateStr) {
        eventDateStr = '2025-06-15T00:00:00';
    }
    
    const eventDate = new Date(eventDateStr).getTime();
    const now = new Date().getTime();
    const distance = eventDate - now;

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    countdownElement.innerHTML = `
        <div class="countdown-item">
            <span class="countdown-number">${String(days).padStart(2, '0')}</span>
            <span class="countdown-label">Días</span>
        </div>
        <div class="countdown-item">
            <span class="countdown-number">${String(hours).padStart(2, '0')}</span>
            <span class="countdown-label">Horas</span>
        </div>
        <div class="countdown-item">
            <span class="countdown-number">${String(minutes).padStart(2, '0')}</span>
            <span class="countdown-label">Minutos</span>
        </div>
        <div class="countdown-item">
            <span class="countdown-number">${String(seconds).padStart(2, '0')}</span>
            <span class="countdown-label">Segundos</span>
        </div>
    `;

    if (distance < 0) {
        clearInterval(countdownInterval);
        countdownElement.innerHTML = "<div class='countdown-expired'>¡El evento ha comenzado!</div>";
    }
}

const countdownInterval = setInterval(updateCountdown, 1000);
updateCountdown();

// Menú móvil
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const navLinks = document.querySelector('.nav-links');

if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', () => {
        navLinks.classList.toggle('active');
        mobileMenuBtn.classList.toggle('active');
    });
}

// Efecto de parallax en el hero
window.addEventListener('scroll', () => {
    const hero = document.querySelector('.hero');
    const scrolled = window.pageYOffset;
    hero.style.backgroundPositionY = scrolled * 0.5 + 'px';
}); 