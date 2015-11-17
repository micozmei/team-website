$(document).ready(function() {
    function toggleSubmenu() {
        $(this).parent().toggleClass('submenu-active');
    }

    $('.submenu-toggle').on('click', toggleSubmenu);
});
