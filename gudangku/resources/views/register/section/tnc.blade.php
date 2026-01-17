<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Eu non diam phasellus vestibulum lorem sed risus ultricies. Ipsum a arcu cursus vitae congue mauris rhoncus. Pharetra sit amet aliquam id diam maecenas ultricies. Fringilla urna porttitor rhoncus dolor. Velit euismod in pellentesque massa placerat duis ultricies lacus. Ut enim blandit volutpat maecenas volutpat blandit aliquam. Morbi tincidunt augue interdum velit euismod in pellentesque. Commodo viverra maecenas accumsan lacus vel facilisis volutpat. Quam lacus suspendisse faucibus interdum. Diam ut venenatis tellus in metus vulputate eu. Vitae et leo duis ut diam quam nulla.</p>
<p>Vitae auctor eu augue ut lectus. Vitae purus faucibus ornare suspendisse. Turpis nunc eget lorem dolor. Est sit amet facilisis magna etiam tempor orci eu. Tortor condimentum lacinia quis vel eros donec ac odio. Eget sit amet tellus cras adipiscing. Aliquam nulla facilisi cras fermentum odio eu feugiat pretium nibh. Eget sit amet tellus cras adipiscing enim eu. Sed arcu non odio euismod lacinia. Elementum facilisis leo vel fringilla est ullamcorper eget. Elementum eu facilisis sed odio morbi quis commodo odio. Mauris a diam maecenas sed enim ut sem viverra aliquet. Arcu non odio euismod lacinia at quis.</p>
<p>Ullamcorper sit amet risus nullam eget. Nunc eget lorem dolor sed viverra ipsum. Quam nulla porttitor massa id neque aliquam vestibulum morbi. Amet est placerat in egestas erat imperdiet sed euismod nisi. Eu augue ut lectus arcu bibendum at. Mi tempus imperdiet nulla malesuada pellentesque elit eget gravida. Pretium fusce id velit ut tortor pretium viverra. Lobortis mattis aliquam faucibus purus in massa. Id donec ultrices tincidunt arcu non sodales neque sodales. Leo vel fringilla est ullamcorper eget nulla. Dui vivamus arcu felis bibendum ut tristique et egestas quis. Bibendum est ultricies integer quis auctor elit sed vulputate. Pharetra convallis posuere morbi leo urna molestie. Iaculis urna id volutpat lacus laoreet non curabitur gravida. Lorem donec massa sapien faucibus et molestie ac. Nam aliquam sem et tortor consequat id porta nibh venenatis. Quis blandit turpis cursus in hac. Netus et malesuada fames ac. Risus quis varius quam quisque id diam vel.</p>
<div class="form-check mt-2">
    <input class="" type="checkbox" id="checkTerm">
    <label class="form-check-label ms-2 mt-2" for="flexCheckDefault">I agree with this terms & condition</label>
</div>

<script>
    $(document).ready(function() {
        $('#checkTerm').click(function() {
            if ($(this).is(':checked')) {
                $('#profile_section').css("display","block")
                $('html, body').animate({
                    scrollTop: $('#profile_section').offset().top
                }, [])
                $('#indicator-tnc').removeClass('step-active').addClass('step-finish')
                $('#indicator-profile').addClass('step-active')
                
                $('.step-mobile .title').text("Let's Us to Know You")
                $('.step-mobile .caption').text('Fill this form to make your account')
                $('.progress-bar').css('width', '33%').attr('aria-valuenow', 33) 
            } else {
                $('html, body').animate({
                    scrollTop: $('#tnc_section').offset().top
                }, [])
                $('#indicator-tnc').removeClass('step-finish').addClass('step-active')
                $('#indicator-profile').removeClass('step-active')
                $('#profile_section').css("display","none")

                $('.step-mobile .title').text("Hello There!")
                $('.step-mobile .caption').text('Do you aggree with our terms & condition?')
                $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0) 
            }
        })
    })
</script>