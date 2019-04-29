jQuery(document).ready(function( $ ){
    var msp = {
        init: function(){
            msp.init_owl_carousel(),
            msp.bind_create_review_star_buttons()
        },

        init_owl_carousel: function(){
            console.log( 'init carousel' );
            $('#browsing-history-block').owlCarousel({
                // loop:true,
                margin:10,
                responsiveClass:true,
                responsive:{
                    0:{
                        items:4,
                        nav:true
                    },
                    600:{
                        items:8,
                        nav:false
                    },
                    1000:{
                        items:14,
                        loop:false
                    }
                }
            })

            $('.owl-carousel').owlCarousel({
                // loop:true,
                margin:10,
                responsiveClass:true,
                nav: true,
                responsive:{
                    0:{
                        nav: false,
                        items:2,
                    },
                    450:{
                        items:3,
                        nav: true,
                    },
                    1000:{
                        items:5,
                        nav: true,
                    }
                }
            })
        },

        bind_create_review_star_buttons: function(){
            $('i.msp-star-rating').click(function(){
                let rating = $(this).data('rating');
                $('.msp-star-rating').removeClass( 'fas' );

                for( let i = 1; i <= rating; i++ ){
                    $('i.msp-star-rating.rating-' + i).addClass('fas');
                }

                $('#rating').val( rating );
            });
        }
    }

    msp.init();

});