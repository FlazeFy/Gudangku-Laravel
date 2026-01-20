<div id="nav_scroll-holder"></div>

<script>
    const handleScrollTopButton = () => {
        if (window.scrollY > window.innerHeight) {
            if ($('#scroll-to-top-btn').length === 0) {
                $('#nav_scroll-holder').prepend(`<button class="btn btn-primary mb-2 w-100 text-nowrap" id="scroll-to-top-btn"><i class="fa-solid fa-arrow-up"></i><span class="d-none d-md-inline"> Scroll to Top</span></button>`)

                $('#scroll-to-top-btn').on('click', function () {
                    $('html, body').animate({ scrollTop: 0 }, 200)
                })
            }
        } else {
            $('#scroll-to-top-btn').remove()
        }
    }
    handleScrollTopButton()
    
    $(window).on('scroll resize', () => {
        handleScrollTopButton()
    })
</script>