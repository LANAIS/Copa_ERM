import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Efectos de animación para elementos cuando aparecen en viewport
document.addEventListener('DOMContentLoaded', () => {
    // Función para aplicar animaciones a elementos cuando entran en el viewport
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.fade-in');
        
        elements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementTop < windowHeight * 0.9) {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }
        });
    };
    
    // Eventos para detectar scroll
    window.addEventListener('scroll', animateOnScroll);
    // Ejecutar una vez al cargar para elementos visibles inicialmente
    animateOnScroll();
});
