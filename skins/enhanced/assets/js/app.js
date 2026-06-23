$(function() {
    // Desktop menu
    $('nav#layout-mainmenu .dropdown').on( "mouseenter", function(){ 
        $('a.dropdown-toggle', this).dropdown('toggle');
    }).on( "mouseleave", function(){ 
        $('a.dropdown-toggle', this).parent().removeClass('open');
    });
    $('nav#layout-mainmenu a.dropdown-toggle').click(function(e){
        e.preventDefault();
        window.location.replace($(this).attr('href')); 
    });

    // Mobile menu
    $('.mainmenu-collapsed .dropdown.active').addClass('open');
    $('.mainmenu-collapsed .dropdown-toggle').click(function(e){ 
        e.preventDefault();
        $(this).parent().toggleClass('open');
    });
});