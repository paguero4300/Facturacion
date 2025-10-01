/**
 * =============================================
 * ANIMACIONES DINÁMICAS PARA HERO SECTION 2024
 * =============================================
 * 
 * Funcionalidades:
 * - Texto rotativo para subtítulos
 * - Efectos de parallax suave
 * - Animaciones de entrada escalonadas
 * - Interacciones de hover avanzadas
 * - Optimización de rendimiento
 * - Respeto por preferencias de accesibilidad
 */

class HeroAnimations {
    constructor() {
        this.isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.isTouch = 'ontouchstart' in window;
        this.rafId = null;
        this.observerOptions = {
            threshold: 0.1,
            rootMargin: '50px'
        };
        
        this.init();
    }

    /**
     * Inicializa todas las animaciones del hero
     */
    init() {
        // Esperar a que el DOM esté completamente cargado
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupAnimations());
        } else {
            this.setupAnimations();
        }
    }

    /**
     * Configura todas las animaciones
     */
    setupAnimations() {
        if (this.isReducedMotion) {
            this.setupReducedMotionFallbacks();
            return;
        }

        this.setupIntersectionObserver();
        this.setupTextRotation();
        this.setupParallaxEffects();
        this.setupHoverEffects();
        this.setupCTAInteractions();
        this.setupSocialMediaEffects();
        this.bindEvents();
        
        console.log('🎭 Hero animations initialized successfully');
    }

    /**
     * Configura alternativas para usuarios con movimiento reducido
     */
    setupReducedMotionFallbacks() {
        const elements = document.querySelectorAll('.animate-fade-in-up, .animation-delay-300');
        elements.forEach(el => {
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
    }

    /**
     * Configura el observer para animaciones de entrada
     */
    setupIntersectionObserver() {
        if (!('IntersectionObserver' in window)) return;

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateElement(entry.target);
                    this.observer.unobserve(entry.target);
                }
            });
        }, this.observerOptions);

        // Observar elementos animables
        const animatableElements = document.querySelectorAll(
            '.hero-content, .hero-visual, .contact-card, .hero-ctas, .social-media'
        );
        
        animatableElements.forEach(el => {
            this.observer.observe(el);
        });
    }

    /**
     * Anima elementos cuando entran en viewport
     */
    animateElement(element) {
        element.style.opacity = '1';
        element.style.transform = 'translateY(0)';
        element.classList.add('animate-fade-in-up');
    }

    /**
     * Configura la rotación de texto dinámico
     */
    setupTextRotation() {
        const subtitleElement = document.querySelector('.subtitle-rotating');
        if (!subtitleElement) return;

        const textsData = subtitleElement.getAttribute('data-texts');
        if (!textsData) return;

        try {
            const texts = JSON.parse(textsData);
            if (texts.length < 2) return;

            let currentIndex = 0;
            const rotationInterval = 4000; // 4 segundos

            const rotateText = () => {
                if (this.isReducedMotion) return;

                currentIndex = (currentIndex + 1) % texts.length;
                
                // Efecto de salida
                subtitleElement.style.opacity = '0';
                subtitleElement.style.transform = 'translateY(-20px)';
                
                setTimeout(() => {
                    subtitleElement.textContent = texts[currentIndex];
                    // Efecto de entrada
                    subtitleElement.style.opacity = '1';
                    subtitleElement.style.transform = 'translateY(0)';
                }, 300);
            };

            // Iniciar rotación
            setInterval(rotateText, rotationInterval);
            
        } catch (error) {
            console.warn('Error parsing subtitle texts:', error);
        }
    }

    /**
     * Configura efectos de parallax suave
     */
    setupParallaxEffects() {
        if (this.isTouch) return; // Deshabilitar en dispositivos táctiles

        const parallaxElements = document.querySelectorAll('.particle, .floating-elements > div');
        
        const handleScroll = () => {
            if (this.rafId) return;
            
            this.rafId = requestAnimationFrame(() => {
                const scrolled = window.pageYOffset;
                const rate = scrolled * -0.5;
                
                parallaxElements.forEach((element, index) => {
                    const speed = 0.2 + (index * 0.1);
                    const yPos = -(scrolled * speed);
                    element.style.transform = `translateY(${yPos}px)`;
                });
                
                this.rafId = null;
            });
        };

        // Throttled scroll listener
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            if (scrollTimeout) return;
            scrollTimeout = setTimeout(() => {
                handleScroll();
                scrollTimeout = null;
            }, 16); // ~60fps
        }, { passive: true });
    }

    /**
     * Configura efectos de hover avanzados
     */
    setupHoverEffects() {
        if (this.isTouch) return;

        // Efecto de seguimiento de mouse en tarjeta de contacto
        const contactCard = document.querySelector('.contact-card');
        if (contactCard) {
            this.setupCardTiltEffect(contactCard);
        }

        // Efecto de ondas en CTAs
        const ctaButtons = document.querySelectorAll('.cta-primary, .cta-secondary');
        ctaButtons.forEach(button => {
            this.setupRippleEffect(button);
        });
    }

    /**
     * Efecto de inclinación 3D para tarjetas
     */
    setupCardTiltEffect(card) {
        const handleMouseMove = (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 10;
            const rotateY = (centerX - x) / 10;
            
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
        };

        const handleMouseLeave = () => {
            card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
        };

        card.addEventListener('mousemove', handleMouseMove);
        card.addEventListener('mouseleave', handleMouseLeave);
    }

    /**
     * Efecto de ondas (ripple) para botones
     */
    setupRippleEffect(button) {
        button.addEventListener('click', (e) => {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const ripple = document.createElement('span');
            ripple.className = 'ripple-effect';
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                left: ${x - 10}px;
                top: ${y - 10}px;
                width: 20px;
                height: 20px;
                pointer-events: none;
            `;
            
            button.style.position = 'relative';
            button.style.overflow = 'hidden';
            button.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    }

    /**
     * Configura interacciones específicas de CTAs
     */
    setupCTAInteractions() {
        const ctaPrimary = document.querySelector('.cta-primary');
        const ctaSecondary = document.querySelector('.cta-secondary');

        // Efecto de loading para CTA primario
        if (ctaPrimary) {
            ctaPrimary.addEventListener('click', (e) => {
                if (e.target.classList.contains('loading')) return;
                
                const originalText = e.target.querySelector('.cta-text').textContent;
                const textElement = e.target.querySelector('.cta-text');
                
                e.target.classList.add('loading');
                textElement.textContent = 'Cargando...';
                
                // Simular navegación
                setTimeout(() => {
                    e.target.classList.remove('loading');
                    textElement.textContent = originalText;
                }, 1500);
            });
        }

        // Efecto de pulso para CTA secundario en hover
        if (ctaSecondary && !this.isTouch) {
            const pulseEffect = () => {
                ctaSecondary.style.animation = 'pulse-glow 1s ease-in-out';
                setTimeout(() => {
                    ctaSecondary.style.animation = '';
                }, 1000);
            };

            ctaSecondary.addEventListener('mouseenter', pulseEffect);
        }
    }

    /**
     * Efectos especiales para redes sociales
     */
    setupSocialMediaEffects() {
        const socialLinks = document.querySelectorAll('.social-link');
        
        socialLinks.forEach((link, index) => {
            // Animación de entrada escalonada
            setTimeout(() => {
                link.style.opacity = '1';
                link.style.transform = 'translateY(0) scale(1)';
            }, 500 + (index * 100));

            // Efecto de rotación en hover
            if (!this.isTouch) {
                link.addEventListener('mouseenter', () => {
                    const icon = link.querySelector('svg');
                    if (icon) {
                        icon.style.transform = 'scale(1.2) rotate(5deg)';
                    }
                });

                link.addEventListener('mouseleave', () => {
                    const icon = link.querySelector('svg');
                    if (icon) {
                        icon.style.transform = 'scale(1) rotate(0deg)';
                    }
                });
            }
        });
    }

    /**
     * Vincula eventos adicionales
     */
    bindEvents() {
        // Optimización de rendimiento en resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            if (resizeTimeout) clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleResize();
            }, 250);
        });

        // Manejo de cambios en preferencias de movimiento
        const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        motionQuery.addEventListener('change', (e) => {
            this.isReducedMotion = e.matches;
            if (this.isReducedMotion) {
                this.setupReducedMotionFallbacks();
            }
        });

        // Pausa de animaciones cuando la pestaña no está visible
        document.addEventListener('visibilitychange', () => {
            const particles = document.querySelectorAll('.particle');
            particles.forEach(particle => {
                particle.style.animationPlayState = document.hidden ? 'paused' : 'running';
            });
        });
    }

    /**
     * Maneja el redimensionamiento de ventana
     */
    handleResize() {
        // Recalcular posiciones si es necesario
        const isMobile = window.innerWidth < 768;
        const particles = document.querySelectorAll('.particle');
        
        particles.forEach(particle => {
            particle.style.display = isMobile ? 'none' : 'block';
        });
    }

    /**
     * Limpia recursos y event listeners
     */
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
        }

        // Remover event listeners específicos si es necesario
        console.log('🎭 Hero animations destroyed');
    }
}

// Añadir estilos CSS para animaciones dinámicas
const dynamicStyles = `
<style>
@keyframes ripple-animation {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

.cta-primary.loading {
    pointer-events: none;
    opacity: 0.8;
}

.cta-primary.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    margin: auto;
    border: 2px solid transparent;
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.social-link {
    opacity: 0;
    transform: translateY(20px) scale(0.8);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.social-link svg {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Mejoras de performance */
.particle,
.floating-elements > div,
.geometric-shapes > div {
    will-change: transform;
    backface-visibility: hidden;
    perspective: 1000px;
}

/* Optimizaciones para dispositivos de bajo rendimiento */
@media (max-resolution: 150dpi) {
    .particle,
    .geometric-shapes > div {
        animation-duration: 8s;
    }
}
</style>
`;

// Inyectar estilos dinámicos
document.head.insertAdjacentHTML('beforeend', dynamicStyles);

// Inicializar cuando el documento esté listo
const heroAnimations = new HeroAnimations();

// Exponer para uso global si es necesario
window.HeroAnimations = HeroAnimations;
window.heroAnimations = heroAnimations;

// Limpieza al descargar la página
window.addEventListener('beforeunload', () => {
    heroAnimations.destroy();
});