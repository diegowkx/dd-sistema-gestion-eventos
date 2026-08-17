document.addEventListener('DOMContentLoaded', () => {

    /*
     * ============================================================
     * GALERÍA
     * ============================================================
     */

    const imagenPrincipal =
        document.getElementById('imagenPrincipal');

    const thumbnails =
        document.querySelectorAll('.gallery-thumbnail');


    thumbnails.forEach((thumbnail) => {

        thumbnail.addEventListener('click', () => {

            const nuevaImagen =
                thumbnail.dataset.image;

            if (!nuevaImagen || !imagenPrincipal) {
                return;
            }

            imagenPrincipal.src = nuevaImagen;

        });

    });


    /*
     * ============================================================
     * INTERÉS
     * ============================================================
     *
     * Por ahora solamente dejamos preparado el botón.
     *
     * En el siguiente paso este botón abrirá el formulario
     * que guardará el lead en clientes_interesados.
     */

    const btnEstoyInteresado =
        document.getElementById('btnEstoyInteresado');


    if (btnEstoyInteresado) {

        btnEstoyInteresado.addEventListener('click', () => {

            alert(
                'Aquí abriremos el formulario para registrar tu interés en esta propiedad.'
            );

        });

    }

});