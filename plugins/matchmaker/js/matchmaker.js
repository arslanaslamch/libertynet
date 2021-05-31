if (
  typeof matchmaker === "undefined" ||
  typeof matchmaker.init === "undefined"
) {
  matchmaker = {
    init: function () {
      matchmaker.addEvents();
    },
    encounters: function () {
      return document.querySelectorAll('[data-type="encounter"]');
    },
    addEvents: function () {
      $('[data-type="encounter"] button.dismissUser').on("click", function () {
        matchmaker.dismissUser($(this).data("order"));
      });

      $('[data-type="encounter"] button.likeUser').on("click", function () {
        matchmaker.likeUser($(this));
      });
    },
    likeUser: function (target) {
      var id = $(target).data("id");
      matchmaker.dismissUser($(target).data("order"));
      $.get(`${baseUrl}matchmaker/like/${id}`, function (data, status) {
        var res = JSON.parse(data);
        if (res.status == 1) {
          if (res.matched) {
            var name = res.user.first_name + " " + res.user.last_name;
            $("#matched #matchedUser").html(name);
            document.querySelector("#matched #matchedLink").href =
              baseUrl + res.user.username;
            document.querySelector("#matched #matchedImg").src =
              res.user.avatar;
            $("#matched").modal();
          }
        } else {
          notify(res.message, 'error');
        }
      });
    },
    dismissUser: function (encounter) {
      var pos = matchmaker.getPosition(encounter);
      var encounters = matchmaker.encounters();
      if (pos != undefined && encounters[pos] != undefined) {
        encounters[pos].classList.add("hide");
        if (pos < encounters.length - 1) {
          encounters[pos + 1].classList.remove("hide");
        } else {
          document
            .querySelector("#encounters #noEncounters")
            .classList.add("show");
        }
      }

      return;
    },
    getPosition: function (encounter) {
      var id;
      matchmaker.encounters().forEach((element, index) => {
        if (element.id == encounter) {
          id = index;
        }
      });
      return id;
    },
  };
}

matchmaker.init();

$(function () {
  try {
    addPageHook("matchmaker.init");
  } catch (e) {}
});
