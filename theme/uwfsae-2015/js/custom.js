$(document).ready(function() {
    function toggleSubmenu() {
        $(this).parent().toggleClass('submenu-active');
    }

    function disableSubmenus() {
        var width = $(this).width();
        
        // 768px is from bootstrap's breakpoints.
        var previousIsSmall = window.currentWidth < 768;
        var currentIsSmall = width < 768;

        if (previousIsSmall != currentIsSmall) {
            // Reset menus if we've crossed a breakpoint
            $('.submenu-toggle').parent().removeClass('submenu-active');
        }
        window.currentWidth = width;

    }

    function updateCountdown(span, originalTime) {
        var currentTime = new Date();
        var difference = originalTime.getTime() - currentTime.getTime();

        var numSeconds = Math.floor(difference / 1000);
        var numMinutes = Math.floor(numSeconds / 60);
        var numHours = Math.floor(numMinutes / 60);
        var numDays = Math.floor(numHours / 24);
        var numWeeks = Math.floor(numDays / 7);

        var text = "";
        if (difference >= 0) {
            text = numWeeks + "w " +
                    (numDays % 7) + "d " + 
                    (numHours % 24) + "h " + 
                    (numMinutes % 60) + "m left";
        } else {
            text = "Finished!";
        }

        span.html(text);
    }

    function attachCountdownListeners() {
        $('.clock').each(function (index) {
            var initialDate = new Date($(this).html());
            updateCountdown($(this), initialDate);
            window.setInterval(function() {
                updateCountdown($(this), initialDate);
            }, 15000);
        });
    }

    // Globals
    window.currentWidth = $(window).width();

    // Setup event listeners 
    $('.submenu-toggle').on('click', toggleSubmenu);
    $(window).on('resize', disableSubmenus);

    attachCountdownListeners();

    // for application page
    $('button.btn-submit-loading').on('click', function() {
        var form = $(this).parent().closest('form');
        console.log(form.callProp('checkValidity'));
        if (form[0].checkValidity()) {
            $(this).button('loading');
        }
    });
});
