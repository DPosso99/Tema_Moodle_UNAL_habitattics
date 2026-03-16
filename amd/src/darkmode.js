define(['jquery'], function($) {
    return {
        init: function() {
            const darkModeToggle = $('#darkmode-toggle');
            const body = $('body');

            // Verificar si el modo oscuro está activado en el almacenamiento local
            if (localStorage.getItem('darkmode') === 'enabled') {
                body.addClass('dark-mode');
            }

            // Alternar el modo oscuro al hacer clic en el botón
            darkModeToggle.on('click', function(e) {
                e.preventDefault();
                if (body.hasClass('dark-mode')) {
                    body.removeClass('dark-mode');
                    localStorage.setItem('darkmode', 'disabled');
                } else {
                    body.addClass('dark-mode');
                    localStorage.setItem('darkmode', 'enabled');
                }
            });
        }
    };
});