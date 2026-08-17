/* Configuración Global de JavaScript */
const AppConfig = {
    baseUrl: window.location.origin,
    apiTimeout: 10000,
    debug: true,
    
    // Configuración base por defecto para SweetAlert2
    sweetAlertDefaults: {
        confirmButtonColor: '#d9383a',
        cancelButtonColor: '#6c757d',
        customClass: {
            confirmButton: 'btn-confirm-alert',
            cancelButton: 'btn-cancel-alert'
        }
    }
};