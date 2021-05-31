/* Adblock */
$(document).ready(function(){
    (function(){
        var adBlockFlag = document.createElement('div');
        adBlockFlag.innerHTML = '&nbsp;';
        adBlockFlag.className = 'adsbox';
        $('body').append(adBlockFlag);
        window.setTimeout(function() {
          if (adBlockFlag.offsetHeight === 0) {
            showAdBlockPopUp();
            $('body').addClass('adblock');
          }
          adBlockFlag.remove();
        }, 100);

        function showAdBlockPopUp(){
            var adBlockerPopup = $('#adBlockerPopup');
            adBlockerPopup.modal({
                backdrop: 'static',
                keyboard: false
            });
            adBlockerPopup.modal('show');
        }

        $(document).on('click', '#adBlockerPopupRefresh', function(){
            location.reload();
        });

    })();
});
/* Adblock */