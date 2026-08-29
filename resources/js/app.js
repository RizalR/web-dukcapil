document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');

    toggle?.addEventListener('click', () => {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!isOpen));
        menu?.classList.toggle('hidden');
    });

    document.querySelectorAll('[data-image-slider]').forEach((slider) => {
        const slides = [...slider.querySelectorAll('[data-slide]')];
        const dots = [...slider.querySelectorAll('[data-slide-dot]')];

        if (slides.length < 2) {
            return;
        }

        let currentIndex = 0;
        let autoplayTimer;
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const showSlide = (nextIndex) => {
            currentIndex = (nextIndex + slides.length) % slides.length;

            slides.forEach((slide, index) => {
                const isCurrent = index === currentIndex;
                slide.classList.toggle('opacity-100', isCurrent);
                slide.classList.toggle('opacity-0', !isCurrent);
                slide.classList.toggle('pointer-events-none', !isCurrent);
                slide.setAttribute('aria-hidden', String(!isCurrent));
            });

            dots.forEach((dot, index) => {
                const isCurrent = index === currentIndex;
                dot.classList.toggle('bg-ocean', isCurrent);
                dot.classList.toggle('bg-slate-300', !isCurrent);
                dot.classList.toggle('hover:bg-slate-400', !isCurrent);
                dot.setAttribute('aria-current', String(isCurrent));
            });
        };

        const stopAutoplay = () => window.clearInterval(autoplayTimer);
        const startAutoplay = () => {
            stopAutoplay();

            if (!prefersReducedMotion && !document.hidden) {
                autoplayTimer = window.setInterval(() => showSlide(currentIndex + 1), 6000);
            }
        };

        slider.querySelector('[data-slide-previous]')?.addEventListener('click', () => {
            showSlide(currentIndex - 1);
        });
        slider.querySelector('[data-slide-next]')?.addEventListener('click', () => {
            showSlide(currentIndex + 1);
        });
        dots.forEach((dot, index) => dot.addEventListener('click', () => {
            showSlide(index);
        }));

        slider.addEventListener('mouseenter', stopAutoplay);
        slider.addEventListener('mouseleave', startAutoplay);
        slider.addEventListener('focusin', stopAutoplay);
        slider.addEventListener('focusout', (event) => {
            if (!slider.matches(':hover') && !slider.contains(event.relatedTarget)) {
                startAutoplay();
            }
        });
        slider.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                showSlide(currentIndex - 1);
            }

            if (event.key === 'ArrowRight') {
                showSlide(currentIndex + 1);
            }
        });
        document.addEventListener('visibilitychange', startAutoplay);
        startAutoplay();
    });
});
