let closed_control = false
$(window).on('scroll', function() {
    if ($(this).scrollTop() > 250) {
        if(closed_control == false){
            $('#collapseControl').collapse('hide')
            $('#total-holder').addClass('position-absolute').css({'right':0,'top':'-5px'})
        }
        closed_control = true
    } else {
        if(closed_control == false){
            $('#collapseControl').collapse('show')
            $('#total-holder').removeClass('position-absolute')
        }
        closed_control = false
    }
});