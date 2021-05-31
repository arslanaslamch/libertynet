function initStoreSlider(){
    var jcarousel = $('.jcarousel');

    jcarousel
        .on('jcarousel:reload jcarousel:create', function () {
            var carousel = $(this),
                width = carousel.innerWidth();

            if (width >= 600) {
                width = width / 3;
            } else if (width >= 350) {
                width = width / 2;
            }
            console.log(width);

            carousel.jcarousel('items').css('width', Math.ceil(width) + 'px');
        })
        .jcarousel({
            wrap: 'circular'
        });

    $('.jcarousel-control-prev')
        .jcarouselControl({
            target: '-=1'
        });

    $('.jcarousel-control-next')
        .jcarouselControl({
            target: '+=1'
        });

    $('.jcarousel-pagination')
        .on('jcarouselpagination:active', 'a', function() {
            $(this).addClass('active');
        })
        .on('jcarouselpagination:inactive', 'a', function() {
            $(this).removeClass('active');
        })
        .on('click', function(e) {
            e.preventDefault();
        })
        .jcarouselPagination({
            perPage: 1,
            item: function(page) {
                return '<a href="#' + page + '">' + page + '</a>';
            }
        });
    $('.jcarousel')
        .jcarouselAutoscroll({
            interval: 7000,
            autostart: true
        })
    ;
}

(function($) {
    $(function() {
        initStoreSlider();
    });
})(jQuery);
function store_slider_page_loaded() {
    initStoreSlider()
}

addPageHook('store_slider_page_loaded');

$(document).on('click','.product-quick-view',function(){
    var obj = $(this);
    var pi = obj.data('id');
    var n = obj.data('name');

    var modal = $("#store-modal");
    modal.find('.me-head').html(n);
    modal.find('.indicator').fadeIn();
    modal.modal('toggle');

    $.ajax({
        url : baseUrl + "quickview/product",
        data : {pid : pi},
       success : function(data){
           modal.find('.indicator').hide();
           modal.find('.modal-body').html(data);
       }
    });

return false;

});

$(document).on('click','.tobeclicked',function(){
    $(this).next('ul').slideToggle();
});