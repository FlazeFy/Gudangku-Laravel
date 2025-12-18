let closed_control = false
$(window).on('scroll', function() {
    if ($(this).scrollTop() > 250) {
        if(closed_control == false){
            $('#collapseControl').collapse('hide')
            $('#total-holder').addClass('position-absolute').css({'right':0,'top':'-5px'}).toggleClass('mt-2 mt-0')
        }
        closed_control = true
    } else {
        if(closed_control == false){
            $('#collapseControl').collapse('show')
            $('#total-holder').removeClass('position-absolute').toggleClass('mt-0 mt-2')
        }
        closed_control = false
    }
});