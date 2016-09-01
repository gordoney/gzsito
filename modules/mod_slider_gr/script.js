jQuery(document).ready(function () {
    function modSliderGrHandler () {
        jQuery('.mod-slider-gr').each(function () {
            jQuery(this).find('.js-slick-slider').slick({  
                dots: false,
                arrows: false,
                speed: 300,
                slidesToShow: 1,
                fade: true,
                draggable: false,
            }); 
        });

        jQuery('.js-dot').click(function () {
            var activeDot = jQuery(this).index();
            jQuery('.mod-slider-gr').find('.js-slick-slider').slick('slickGoTo', activeDot);
            jQuery('.js-dot').removeClass('active');
            jQuery('.mod-slider-gr .dots').each(function () {
                jQuery(this).find('.js-dot').eq(activeDot).addClass('active');
            });
        });
    }

    modSliderGrHandler ();
});
