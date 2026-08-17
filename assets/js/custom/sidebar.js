document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    // Selecciona el botón del hamburguesa/toggle (funciona con ID o Clase)
    const toggleBtn = document.querySelector('#nav_toggle') || document.querySelector('.nav-control');

    // 1. Cargar estado previo
    const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
    if (isCollapsed) {
        body.classList.add('sidebar-collapsed');
    }

    // 2. Alternar sidebar y guardar selección
    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            body.classList.toggle('sidebar-collapsed');
            
            const currentState = body.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebar_collapsed', currentState);
        });
    }
});