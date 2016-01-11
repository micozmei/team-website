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

    // Global
    window.currentWidth = $(window).width();

    // Setup event listeners 
    $('.submenu-toggle').on('click', toggleSubmenu);
    $(window).on('resize', disableSubmenus);
});
