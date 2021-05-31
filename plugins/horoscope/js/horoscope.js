if (typeof horoscope === "undefined" || typeof horoscope.init === "undefined") {
  horoscope = {
    init: function () {
      horoscope.addEvents();
    },
    addEvents: function () {
      $("#signs").on("change", function () {
        $(".z-sign").each(function (id, elem) {
          if (!$(elem).hasClass("d-none")) {
            $(elem).addClass("d-none");
          }
          $(elem).fadeOut();
        });
        var val = $("#signs").val();
        $(`#${val}`).removeClass("d-none");
        $(`#${val}`).fadeIn();
      });
    },
  };
}

horoscope.init();

$(function () {
  try {
    addPageHook("horoscope.init");
  } catch (e) {}
});
