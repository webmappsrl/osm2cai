document.addEventListener('DOMContentLoaded', function () {
    // Crea un observer per monitorare i cambiamenti nel DOM
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            let button = document.querySelector('.btn-primary[dusk="create-and-add-another-button"]');
            if (button) {
                button.style.display = 'none';
            }
        });
    });

    // Configura l'observer per monitorare il body
    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
});
