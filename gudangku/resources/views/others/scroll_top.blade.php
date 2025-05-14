<div id="nav_scroll-holder" style="position:fixed; right:20px; bottom:20px; z-index:1000; width: 11vw;"></div>
<script>
    const handle_scroll_top_btn = () => {
        if (window.scrollY > window.innerHeight) {
            if ($('#scroll-to-top-btn').length === 0) {
                $('#nav_scroll-holder').prepend(`
                    <button class="btn btn-primary mb-2 w-100" id="scroll-to-top-btn"><i class="fa-solid fa-arrow-up"></i> Scroll to Top</button>
                `);

                $('#scroll-to-top-btn').on('click', function () {
                    $('html, body').animate({ scrollTop: 0 }, 200);
                });
            }
        } else {
            $('#scroll-to-top-btn').remove();
        }
    };
    handle_scroll_top_btn()
    $(window).on('scroll resize', () => {
        handle_scroll_top_btn()
    });
</script>