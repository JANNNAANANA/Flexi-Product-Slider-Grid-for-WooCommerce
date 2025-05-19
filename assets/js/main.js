document.addEventListener("DOMContentLoaded", function () {
    (function ($) {
            setTimeout(function () {  // Delay to let layout stabilize
                $('.flexipsg-swiper-preloader').fadeOut(100, function () {
                    $(this).remove();
                });
            }, 1000); // Add a small delay
            const flexipsgCarousel = document.querySelectorAll(".flexipsgSwiper");

            flexipsgCarousel.forEach(function (swiper, index) {
                const prevButton = swiper.querySelector(".flexipsg-button-prev");
                const nextButton = swiper.querySelector(".flexipsg-button-next");
                const pagination = swiper.querySelector(".flexipsg-pagination");

                if (swiper.swiper) {
                    swiper.swiper.destroy(true, true); // Destroy existing Swiper instance
                }

                let  flexipsgInstance = new Swiper(swiper, {
                    loop: true, 
                    // autoplay: {
                    //     delay: 3000,
                    //     disableOnInteraction: false,
                    // },
                    slidesPerView: 1,
                    spaceBetween: 20,
                    pagination: {
                        el: pagination,
                        clickable: true
                    },
                    navigation: {
                        nextEl: nextButton,
                        prevEl: prevButton
                    },
                    breakpoints: {
                        640: { slidesPerView: 1, spaceBetween: 30 },
                        768: { slidesPerView: 2, spaceBetween: 20 },
                        1024: { slidesPerView: 3, spaceBetween: 30 },
                        1350: { slidesPerView: 4, spaceBetween: 30 }
                    },
                    observer: true,
                    observeParents: true,
                    on: {
                        init: function () {
                            console.log(`Swiper ${index} initialized`);
                        },
                        resize: function () {
                            flexipsgInstance.update(); // Fixes issues when resizing
                        }
                    }
                });
            });
       


			


    })(jQuery);
});



