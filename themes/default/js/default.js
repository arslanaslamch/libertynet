Hook.register('page.reload.init', function() {
    if ($(window).width() > 1000) {
        try {
            $(".main-sidebar-menu .menu").unstick();
            $(".main-sidebar-menu .menu").stick_in_parent({
                parent: '#main-wrapper',
                bottomSpacing: 0
            });
        } catch (e) {
        }
	}

    $('.tooltip.fade.show').remove();
});

$(document).on('click', '[data-auth-toggle]', function(e) {
    e.preventDefault();
    $('[data-auth-content]').hide();
    $('[data-auth-content="' + $(e.target).data('auth-toggle') + '"]').show();
    document.querySelector('[data-auth-content="' + $(e.target).data('auth-toggle') + '"]').scrollIntoView({behavior: 'smooth'});
});

$("img.avatar-mask").one("load", function(e) {
	$(e.target).parent().addClass('loaded');
}).each(function() {
  if(this.complete) {
      $(this).trigger('load');
  }
});
