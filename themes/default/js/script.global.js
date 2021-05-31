if (!Element.prototype.matches) {
  Element.prototype.matches = Element.prototype.msMatchesSelector;
}

cSocial = {
  init: function () {
    cSocial.addEvents();
    cSocial.magicSelect.addEvents();
    cSocial.magicInputImagePreview.addEvents();
  },

  addEvents: function () {
    Pusher.addHook("cSocial.push");

    Hook.register("pusher.finish", function () {
      let userOnlineTimes = document.getElementsByClassName("user-online-time");
      let userIds = [];
      let userId;
      for (let i = 0; i < userOnlineTimes.length; i++) {
        userId = userOnlineTimes[i].getAttribute("data-user-id");
        if (userId && userIds.indexOf(userId) === -1) {
          userIds.push(userId);
        }
      }
      Pusher.sendMessage({
        type: "user.online.time",
        user_ids: userIds,
      });
    });

    Hook.register("page.reload.init", function () {
      cSocial.magicSelect.init();
      cSocial.magicInputImagePreview.init();
      cSocial.dateTimePickerInit();
      cSocial.sliderInt();
    });
  },

  magicSelect: {
    init: function () {
      var magicSelects = document.getElementsByClassName("magic-select");
      for (var i = 0; i < magicSelects.length; i++) {
        var name = magicSelects[i].getAttribute("data-name");
        if (name) {
          if (!magicSelects[i].querySelector('input[name="' + name + '"]')) {
            var input = document.createElement("input");
            input.setAttribute("type", "hidden");
            input.setAttribute("name", name);
            input.setAttribute("class", "magic-select-value");
            var firstOption = magicSelects[i].querySelector(
              ".magic-select-option"
            );
            var value = firstOption
              ? firstOption.getAttribute("data-value")
              : "";
            input.setAttribute("value", value);
            magicSelects[i].insertBefore(input, magicSelects[i].firstChild);
          }
          var width = magicSelects[i].getAttribute("data-width");
          if (width) {
            magicSelects[i].style.width = "100%";
            magicSelects[i].style["max-width"] = width;
          }
        }
      }
    },

    addEvents: function () {
      window.addEventListener("click", function (event) {
        let button = event.target;
        if (document.documentElement.contains(event.target)) {
          do {
            if (button.matches(".toggle-button[data-toggle-target-selector]")) {
              event.preventDefault();
              let target = document.querySelector(
                button.getAttribute("data-toggle-target-selector")
              );
              if (target) {
                if (target.classList.contains("toggle-target-active")) {
                  button.classList.remove("toggle-button-active");
                  target.classList.remove("toggle-target-active");
                } else {
                  button.classList.add("toggle-button-active");
                  target.classList.add("toggle-target-active");
                }
              }
            }
            button = button.parentElement;
          } while (button !== null);
        }

        // Check if the clicked target is the search input
        if (event.target.matches(".searchDropdown")) {
          return;
        }

        if (
          event.target.matches(
            ".magic-select-toggle, .magic-select-toggle *"
          ) ||
          event.target.matches(".magic-select-option, .magic-select-option *")
        ) {
          event.preventDefault();
          var magicSelect;
          var magicSelectContent;
          var closest = event.target;
          while (
            closest.parentNode.classList &&
            !closest.parentNode.classList.contains("magic-select") &&
            closest.tagName !== "HTML"
          ) {
            closest = closest.parentNode;
          }
          magicSelect =
            closest !== event.target && closest.parentNode.tagName === "HTML"
              ? null
              : closest.parentNode;
          magicSelectContent = magicSelect.querySelector(
            ".magic-select-content"
          );
          if (magicSelect) {
            if (event.target.matches(".magic-select-toggle")) {
              if (
                magicSelectContent &&
                !magicSelectContent.classList.contains("show")
              ) {
                magicSelectContent.classList.add("show");
                if (
                  magicSelectContent.parentNode.classList.contains(
                    "slimScrollDiv"
                  )
                ) {
                  magicSelectContent.parentNode.style.display = "inline-block";
                }
              }
            } else {
              var magicSelectValue = magicSelect.querySelector(
                ".magic-select-value"
              );
              if (magicSelectValue) {
                var magicSelectToggle = magicSelect.querySelector(
                  ".magic-select-toggle"
                );
                var magicSelectOption = event.target;
                if (!event.target.classList.contains("magic-select-option")) {
                  while (
                    closest.parentNode.classList &&
                    !closest.parentNode.classList.contains(
                      "magic-select-option"
                    ) &&
                    closest.tagName !== "HTML"
                  ) {
                    closest = closest.parentNode;
                  }
                  closest = event.target;
                  magicSelectOption =
                    closest !== event.target &&
                    closest.parentNode.tagName === "HTML"
                      ? null
                      : closest.parentNode;
                }
                magicSelectToggle.innerHTML = magicSelectOption.getAttribute(
                  "data-title"
                )
                  ? magicSelectOption.getAttribute("data-title")
                  : magicSelectOption.innerHTML;
                var value = magicSelectOption.getAttribute("data-value");
                magicSelectValue.setAttribute("value", value);
                if ("createEvent" in document) {
                  var evt = document.createEvent("HTMLEvents");
                  evt.initEvent("change", true, true);
                  magicSelectValue.dispatchEvent(evt);
                }
                if (
                  magicSelectContent &&
                  magicSelectContent.classList.contains("show")
                ) {
                  magicSelectContent.classList.remove("show");
                  if (
                    magicSelectContent.parentNode.classList.contains(
                      "slimScrollDiv"
                    )
                  ) {
                    magicSelectContent.parentNode.style.display = "none";
                  }
                }
              }
            }
          }
        } else if (
          !(
            event.target.matches(".slimScrollBar") ||
            event.target.matches(".slimScrollBar") ||
            event.target.matches(".magic-select-label")
          )
        ) {
          var magicSelectContents = document.getElementsByClassName(
            "magic-select-content"
          );
          for (var i = 0; i < magicSelectContents.length; i++) {
            if (magicSelectContents[i].classList.contains("show")) {
              magicSelectContents[i].classList.remove("show");
              if (
                magicSelectContents[i].parentNode.classList.contains(
                  "slimScrollDiv"
                )
              ) {
                magicSelectContents[i].parentNode.style.display = "none";
              }
            }
          }
        }
      });
    },
  },

  magicInputImagePreview: {
    defaultImageURL: baseUrl + "themes/default/images/mi_prev.png",

    init: function () {
      var magicInputImagePreviews = document.getElementsByClassName(
        "magic-input-image-preview"
      );
      for (var i = 0; i < magicInputImagePreviews.length; i++) {
        var name = magicInputImagePreviews[i].getAttribute("data-name");
        if (name) {
          if (!magicInputImagePreviews[i].querySelector('input[type="file"]')) {
            var input = document.createElement("input");
            input.setAttribute("type", "file");
            input.setAttribute("name", name);
            magicInputImagePreviews[i].appendChild(input);
          }
          if (!magicInputImagePreviews[i].querySelector(".reset")) {
            var reset = document.createElement("span");
            reset.setAttribute("class", "reset ion-close");
            magicInputImagePreviews[i].appendChild(reset);
          }
          var image = magicInputImagePreviews[i].getAttribute("data-image");
          image = image
            ? image
            : cSocial.magicInputImagePreview.defaultImageURL;
          magicInputImagePreviews[i].style.backgroundImage =
            "url(" + image + ")";
          var width = magicInputImagePreviews[i].getAttribute("data-width");
          var height = magicInputImagePreviews[i].getAttribute("data-height");
          if (width) {
            magicInputImagePreviews[i].style.width = "100%";
            magicInputImagePreviews[i].style.maxWidth = width;
            height = height ? height : width;
          }
          if (height) {
            magicInputImagePreviews[i].style.height = height;
          }
        }
      }
    },
    addEvents: function () {
      window.addEventListener("click", function (event) {
        if (event.target.matches(".magic-input-image-preview")) {
          event.preventDefault();
          event.target.querySelector('input[type="file"]').click();
        } else if (
          event.target.matches(".magic-input-image-preview > .reset")
        ) {
          event.target.parentNode.querySelector('input[type="file"]').value =
            "";
          if ("createEvent" in document) {
            var evt = document.createEvent("HTMLEvents");
            evt.initEvent("change", true, true);
            event.target.parentNode
              .querySelector('input[type="file"]')
              .dispatchEvent(evt);
          }
        }
      });
      window.addEventListener("change", function (event) {
        if (
          event.target.matches(
            '.magic-input-image-preview > input[type="file"]'
          )
        ) {
          var fileHandle = event.target;
          if (fileHandle.files && fileHandle.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
              var preview = fileHandle.parentNode;
              preview.style.backgroundImage = "url(" + e.target.result + ")";
              var dimension = fileHandle.parentNode.querySelector(".dimension");
              if (dimension) {
                var image = new Image();
                image.addEventListener("load", function () {
                  dimension.innerHTML =
                    image.width + "px x " + image.height + "px";
                });
                image.src = e.target.result;
              }
              var reset = fileHandle.parentNode.querySelector(".reset");
              if (reset) {
                reset.style.display = "inline-block";
              }
            };
            reader.readAsDataURL(fileHandle.files[0]);
          } else {
            var dimension = event.target.parentNode.querySelector(".dimension");
            if (dimension) {
              dimension.innerHTML = "";
            }
            var image = event.target.parentNode.getAttribute("data-image");
            image = image
              ? image
              : cSocial.magicInputImagePreview.defaultImageURL;
            event.target.parentNode.style.backgroundImage =
              "url(" + image + ")";
            event.target.style.display = "none";
          }
        }
      });
    },
  },

  dateTimePickerInit: function () {
    $.datetimepicker.setLocale(locale);
    $(".datetimepicker").datetimepicker(dateTimePickerOptions);
    $(".datepicker").datetimepicker(datePickerOptions);
    $(".timepicker").datetimepicker(timePickerOptions);

    var start = moment().subtract(29, "days");
    var end = moment();

    function cb(start, end) {
      $("#reportrange span").html(
        start.format("MMMM D, YYYY") + " - " + end.format("MMMM D, YYYY")
      );
    }

    $(".dateRangePicker").daterangepicker(
      {
        opens: "right",
        drops: "up",
        applyClass: "btn-primary",
        locale: {
          cancelLabel: "Cancel",
          applyLabel: "Find Events",
          customRangeLabel: "Choose Range",
        },
        /*ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    } */
      },
      cb
    );
    cb(start, end);
  },

  sliderInt: function () {
    $(".slider-side-widget")
      .not(".slick-initialized")
      .slick({
        infinite: true,
        centerMode: true,
        centerPadding: "1px",
        slidesToShow: 2,
        autoplay: true,
        autoplaySpeed: 2000,
        arrows: true,
        speed: 900,
        nextArrow:
          '<div class="slick-next-tweak"> <div class="ion-ios-arrow-forward"></div></div>',
        prevArrow:
          '<div class="slick-prev-tweak"> <div class="ion-ios-arrow-back"></div></div>',
        responsive: [
          {
            breakpoint: 992,
            settings: {
              arrows: true,
              centerMode: true,
              centerPadding: "40px",
              slidesToShow: 2,
            },
          },
          {
            breakpoint: 768,
            settings: {
              arrows: true,
              centerMode: true,
              centerPadding: "40px",
              slidesToShow: 1,
            },
          },
          {
            breakpoint: 520,
            settings: {
              arrows: true,
              centerMode: true,
              centerPadding: "40px",
              slidesToShow: 1,
            },
          },
          {
            breakpoint: 420,
            settings: {
              arrows: true,
              centerMode: true,
              centerPadding: "40px",
              slidesToShow: 1,
            },
          },
          {
            breakpoint: 340,
            settings: {
              arrows: true,
              centerMode: true,
              centerPadding: "40px",
              slidesToShow: 1,
            },
          },
        ],
      });

    $(".slick-next-tweak").removeClass("slick-arrow");
    $(".slick-prev-tweak").removeClass("slick-arrow");

    $(document).on("mouseover", ".slider-side-widget", function () {
      $(".slick-next-tweak").show();
      $(".slick-prev-tweak").show();
      return false;
    });
    $(document).on("mouseout", ".slider-side-widget", function () {
      $(".slick-next-tweak").hide();
      $(".slick-prev-tweak").hide();
      return false;
    });
  },

  push: function (type, details) {
    if (type === "user.online.time") {
      let userOnlineTimes = document.getElementsByClassName("user-online-time");
      for (let id in details) {
        if (details.hasOwnProperty(id)) {
          for (let userId in details[id]) {
            if (details[id].hasOwnProperty(userId)) {
              for (let i = 0; i < userOnlineTimes.length; i++) {
                if (
                  userOnlineTimes[i].getAttribute("data-user-id") === userId
                ) {
                  userOnlineTimes[i].setAttribute("title", details[id][userId]);
                  if (
                    Date.now() - new Date(details[id][userId]).getTime() <
                    20000
                  ) {
                    $(userOnlineTimes[i]).addClass("active");
                  } else {
                    $(userOnlineTimes[i]).removeClass("active");
                  }
                  $(userOnlineTimes[i]).data("timeago", null).timeago();
                }
              }
            }
          }
          Pusher.addPushId(id);
        }
      }
    }
  },
};

$(function () {
  cSocial.init();
});

$(document).ready(function () {
  $("select").select2();
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition, showError);
  } else {
    alert("Geolocation is not supported by this browser");
  }

  function showPosition(position) {
    var location = {
      lat: position.coords.latitude,
      lon: position.coords.longitude,
    };

    $.post(baseUrl + 'location/save', {location: location}, function (data, status) {

    });
  }

  function showError(error) {
    switch (error.code) {
      case error.PERMISSION_DENIED:
        //alert("User denied the request for Geolocation.");
        break;
      case error.POSITION_UNAVAILABLE:
        //alert("Location information is unavailable.");
        break;
      case error.TIMEOUT:
        //alert("The request to get user location timed out.");
        break;
      case error.UNKNOWN_ERROR:
        //alert("An unknown error occurred.");
        break;
    }
  }
});

let magicSelect = {
  init: function () {
    var magicSelects = document.getElementsByClassName(
      "magic-select search-dropdown"
    );
    for (var i = 0; i < magicSelects.length; i++) {
      // Add search input to each select dropdown
      magicSelect.addSearchInput(magicSelects[i]);
    }
  },
  // Filter items in select dropdown with the value of search input
  search: function (e, ev) {
    if (e.target === document.activeElement) {
      var filter = ev.value.toUpperCase();
      var div = ev.parentElement;
      var children = div.childNodes;
      children.forEach((element) => {
        if (element.classList !== undefined) {
          if (element.classList.contains("magic-select-option")) {
            txtValue = element.textContent || element.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
              element.style.display = "";
            } else {
              element.style.display = "none";
            }
          }
        }
      });
    } else {
      magicSelect.removeSearchListener(target);
    }
    return;
  },

  // Add Search Input to custom select
  addSearchInput: function (select) {
    for (let i = 0; i < select.childNodes.length; i++) {
      if (
        select.childNodes[i].classList !== undefined &&
        select.childNodes[i].classList.contains("magic-select-content")
      ) {
        var selectContent = select.childNodes[i];
      }
    }

    var input = document.createElement("input");
    input.setAttribute("type", "text");
    input.setAttribute("placeholder", "Search...");
    input.setAttribute(
      "class",
      "mx-2 my-2 searchDropdown form-control form-control-sm"
    );
    input.style.width = "180px";

    if (selectContent !== undefined) {
      var exists = false;
      selectContent.childNodes.forEach((element) => {
        if (
          element.classList !== undefined &&
          element.classList.contains("searchDropdown")
        ) {
          exists = true;
        }
      });
      if (!exists) {
        selectContent.insertBefore(input, selectContent.firstChild);
      }
    }
  },

  // Add Event Listener to search input
  addSearchListener: function (target) {
    target.addEventListener("keyup", function () {
      magicSelect.search(event, target);
    });
    window.addEventListener("keyup", function () {
      magicSelect.search(event, target);
    });
  },

  //Remove Event Listener from search input
  removeSearchListener: function (target) {
    window.removeEventListener("keyup", magicSelect.search(event, target));
  },
  addEvents: function () {
    window.addEventListener("click", function (event) {
      if (event.target.matches(".searchDropdown")) {
        magicSelect.addSearchListener(event.target);
        return;
      }
    });
  },
};

function init() {
  magicSelect.init();
  magicSelect.addEvents();
  Hook.register("page.reload.init", function () {
    magicSelect.init();
    $("select.search-dropdown").select2();
    magicSelect.addEvents();
  });
}
$(function () {
  init();
});
