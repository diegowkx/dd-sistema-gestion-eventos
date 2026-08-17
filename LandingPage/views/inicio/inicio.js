document.addEventListener('DOMContentLoaded', () => {

    /*
     * Animación sencilla al entrar a la sección
     * de propiedades.
     */

    const cards = document.querySelectorAll('.property-card');

    if (!cards.length) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {

            entries.forEach((entry) => {

                if (entry.isIntersecting) {

                    entry.target.classList.add('property-card-visible');

                    observer.unobserve(entry.target);
                }

            });

        },
        {
            threshold: 0.1
        }
    );


    cards.forEach((card) => {

        observer.observe(card);

    });

});