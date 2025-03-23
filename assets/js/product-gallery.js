document.addEventListener('DOMContentLoaded', function() {
    // Initialize thumbnail slider
    const thumbsSlider = new Swiper('.product-thumbs-slider', {
        spaceBetween: 10,
        slidesPerView: 4,
        freeMode: true,
        watchSlidesProgress: true,
        breakpoints: {
            320: {
                slidesPerView: 3,
            },
            480: {
                slidesPerView: 4,
            },
            768: {
                slidesPerView: 4,
            }
        }
    });

    // Initialize main slider
    const mainSlider = new Swiper('.product-main-slider', {
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        thumbs: {
            swiper: thumbsSlider,
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        }
    });
});