document.addEventListener('DOMContentLoaded', function() {
    // Fecha objetivo: 1 de Agosto de 2025 a las 00:00:00
    const targetDate = new Date('2025-08-01T00:00:00').getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = targetDate - now;

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Actualizar los números
        document.getElementById('days').textContent = String(days).padStart(2, '0');
        document.getElementById('hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');

        // Si la cuenta regresiva termina
        if (distance < 0) {
            clearInterval(countdownInterval);
            document.getElementById('countdown').innerHTML = '<div class="countdown-finished">¡El evento ha comenzado!</div>';
        }
    }

    // Actualizar cada segundo
    updateCountdown();
    const countdownInterval = setInterval(updateCountdown, 1000);

    // Efecto de partículas para el contador
    function createParticles() {
        const container = document.querySelector('.countdown-container');
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.width = Math.random() * 2 + 'px';
            particle.style.height = particle.style.width;
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDuration = Math.random() * 3 + 2 + 's';
            particle.style.animationDelay = Math.random() * 2 + 's';
            container.appendChild(particle);
        }
    }

    createParticles();
}); 