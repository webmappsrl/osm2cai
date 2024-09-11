document.addEventListener('DOMContentLoaded', function () {
    // Controlla se siamo sulla pagina di creazione della risorsa UgcPoi
    if (window.location.href.includes('/resources/ugc-pois/new')) {
        console.log('sto eseguendo');
        
        // Utilizza un setInterval per attendere che il pulsante sia disponibile nel DOM
        let interval = setInterval(function () {
            let button = document.querySelector('.btn-primary[dusk="create-and-add-another-button"]');
            if (button) {
                button.style.display = 'none';
                clearInterval(interval); // Interrompi il ciclo una volta che trovi il pulsante
            }
        }, 100); // Verifica ogni 100ms
    }
});
