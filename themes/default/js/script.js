window.console = window.console || {
  log: function () {},
};

function selectFile(id, button) {
  $("#" + id).click();
}

function updateAvatar(id) {
  var imageInput = document.getElementById("change-picture-input");
  if (imageInput.files) {
    for (i = 0; i < imageInput.files.length; i++) {
      if (typeof FileReader != "underfined") {
        var reader = new FileReader();
        reader.onload = function (e) {
          $("#" + id).attr("src", e.target.result);
        };
        reader.readAsDataURL(imageInput.files[i]);
      }
    }
  }
}

function open_quick_post() {
  var modal = $("#quick-post-modal");
  modal.modal("toggle");
  return false;
}

function process_user_save(t, type, typeId) {
  var o = $(t);
  o.css("opacity", "0.4");
  var s = o.data("status");
  $.ajax({
    url:
      baseUrl +
      "user/save?type=" +
      type +
      "&id=" +
      typeId +
      "&status=" +
      s +
      "&csrf_token=" +
      requestToken,
    success: function (data) {
      var json = jQuery.parseJSON(data);
      o.find("span").html(json.text);
      o.data("status", json.status);
      o.css("opacity", 1);
      notifySuccess(json.message);
    },
  });
  return false;
}

function read_more(t, id) {
  var o = $(t);
  var container = $("#" + id);
  container.find("span").hide();
  container.find(".text-full").fadeIn();
  if (container.find(".text-full").find("span").length > 0) {
    container.find(".text-full").find("span").fadeIn();
  }
  o.hide();
  return false;
}

/**
 * function to search a string and return first link found
 * @param str
 * @return string
 */
function searchTextForLink(str) {
  pattern = /(^|[\s\n]|<br\/?>)((?:https?|ftp):\/\/[\-A-Z0-9+\u0026\u2019@#\/%?=()~_|!:,.;]*[\-A-Z0-9+\u0026@#\/%=~()_|])/gi;
  pattern = /(^|\s)((https?:\/\/|www\.)[\w-]+(\.[\w-]{2,})+\.?(:\d+)?(\/\S*)?)/gi;
  //pattern = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
  pattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;

  //test for links without http|s://
  matches = pattern.exec(str);
  if (matches && matches[2]) return matches[2];
  return "";
}

function login_required() {
  notifyError($("body").data("general-error"));
}

function show_login_dialog() {
  $("#loginModal").modal("toggle");
  return false;
}

function file_chooser(id) {
  var input = $(id);
  input.on("click", function (e) {
    e.stopPropagation();
  });
  input.click();
  return false;
}

function rotateTopography() {
  if ($("#topo #slides").length) {
    window.cSlide = 1;
    var maxSlide = 3;
    setInterval(function () {
      var cS = window.cSlide + 1;
      if (cS > maxSlide) cS = 1;
      window.cSlide = cS;
      $("#topo .slide").hide();
      $(".slide-" + cS).fadeIn(1000);

      //alert('.slide-'+ cS)
    }, 4000);
  }
}

function slidersInit(UC, slide) {
  if (typeof UC == "undefined") {
    return false;
  }
  if ($("#" + UC + " #slides").length < 1) {
    return false;
  }
  if ($("#" + UC + " #slides").length > 1) {
    return false;
  }
  var maxSlide = $("#" + UC + " #slides .slide").length;
  window.cSlide = window.cSlide || {};
  window.sliderInterval = window.sliderInterval || {};
  if (typeof slide !== "undefined") {
    if (typeof sliderInterval[UC] !== "undefined") {
      clearInterval(sliderInterval[UC]);
    }
    var cS = slide;
    var pS = window.cSlide[UC];
    window.cSlide[UC] = cS;
    $("#" + UC + " #slides .slide-" + pS).css("z-index", 0);
    $("#" + UC + " #slides .slide-" + pS).fadeOut("fast");
    $("#" + UC + " #slides .slide-" + cS).css("z-index", 1);
    $("#" + UC + " #slides .slide-" + cS).fadeIn("fast");
  } else {
    window.cSlide[UC] = 1;
  }
  var slider = document.getElementById(UC);
  if (/auto/.test(slider.className)) {
    sliderInterval[UC] = setInterval(function () {
      var cS = window.cSlide[UC] == maxSlide ? 1 : window.cSlide[UC] + 1;
      var pS = window.cSlide[UC];
      window.cSlide[UC] = cS;
      $("#" + UC + " #slides .slide-" + pS).css("z-index", 0);
      $("#" + UC + " #slides .slide-" + pS).fadeOut("slow");
      $("#" + UC + " #slides .slide-" + cS).css("z-index", 1);
      $("#" + UC + " #slides .slide-" + cS).fadeIn("slow");
    }, 10000);
  }
}

function translateText(t) {
  var a = $(t);
  var c = $("#" + a.data("id") + "-translation");
  c.css("opacity", "0.4");
  var text = c.find("input[type=hidden]").val();
  $.ajax({
    url: baseUrl + "translate/text?csrf_token=" + requestToken,
    type: "POST",
    data: { text: text },
    success: function (data) {
      if (data == "") {
        c.fadeOut();
      } else {
        c.html(data).css("opacity", 1).addClass("translated");
      }
    },
  });
  return false;
}

function open_sidebar_menu() {
  var menu = $("#sidebar-menu");
  var main = $("#main-wrapper");
  if (menu.css("display") == "none") {
    main
      .css("position", "relative")
      .css("overflow", "hidden")
      .css("left", "260px");
    menu.show();
  } else {
    main.css("position", "relative").css("overflow", "hidden").css("left", "0");
    menu.hide();
  }
  return false;
}

function show_home_content(w) {
  if (w) {
    $(".signup-content").hide();
    $(".login-content").fadeIn();
    $(".login-content-toggle").hide();
    $(".signup-content-toggle").fadeIn();
  } else {
    $(".login-content").hide();
    $(".signup-content-toggle").hide();
    $(".signup-content").fadeIn();
    $(".login-content-toggle").fadeIn();
  }
  Hook.fire("home.auth.switched");
  return false;
}

function hide_side_bar_menu() {
  $("#sidebar-menu").hide();
  $("#main-wrapper")
    .css("position", "relative")
    .css("overflow", "hidden")
    .css("left", "0");
}

function froalaInit(selector) {
  selector = selector || ".ckeditor";
  if ($(selector).data("froala.editor")) {
    $(selector).froalaEditor("destroy");
  }
  $(selector)
    .froalaEditor({
      imageUploadParam: "file",
      imageUploadURL: baseUrl + "editor/upload",
      imageUploadParams: { type: "image" },
      imageUploadMethod: "POST",
      imageMaxSize: maxImageSize || 10000000,
      imageAllowedTypes: imageFileTypes || ["jpeg", "jpg", "png", "gif"],
      videoUploadParam: "file",
      videoUploadURL: baseUrl + "editor/upload",
      videoUploadParams: { type: "video" },
      videoUploadMethod: "POST",
      videoMaxSize: maxVideoSize || 10000000,
      videoAllowedTypes: videoFileTypes || [
        "mp4",
        "mov",
        "wmv",
        "3gp",
        "avi",
        "flv",
        "f4v",
        "webm",
      ],
      fileUploadParam: "file",
      fileUploadURL: baseUrl + "editor/upload",
      fileUploadParams: { type: "file" },
      fileUploadMethod: "POST",
      fileMaxSize: maxFilesSize || 10000000,
      fileAllowedTypes: filesFileTypes || [
        "doc",
        "xml",
        "exe",
        "txt",
        "zip",
        "rar",
        "mp3",
        "jpg",
        "png",
        "css",
        "psd",
        "pdf",
        "3gp",
        "ppt",
        "pptx",
        "xls",
        "xlsx",
        "docx",
        "fla",
        "avi",
        "mp4",
        "swf",
        "ico",
        "gif",
        "jpeg",
      ],
    })
    .on("froalaEditor.image.uploaded", function (e, editor, response) {
      var data = JSON.parse(response);
      if (data.status && data.link) {
        notifySuccess(data.message);
      } else {
        notifyError(data.message);
      }
    })
    .on("froalaEditor.image.inserted", function (e, editor, $img, response) {})
    .on("froalaEditor.image.replaced", function (e, editor, $img, response) {})
    .on("froalaEditor.image.error", function (e, editor, error, response) {
      notifyError(error.message);
    })
    .on("froalaEditor.video.uploaded", function (e, editor, response) {
      var data = JSON.parse(response);
      if (data.status && data.link) {
        notifySuccess(data.message);
      } else {
        notifyError(data.message);
      }
    })
    .on("froalaEditor.video.inserted", function (e, editor, $img, response) {})
    .on("froalaEditor.video.replaced", function (e, editor, $img, response) {})
    .on("froalaEditor.video.error", function (e, editor, error, response) {
      notifyError(error.message);
    })
    .on("froalaEditor.image.uploaded", function (e, editor, response) {
      var data = JSON.parse(response);
      if (data.status && data.link) {
        notifySuccess(data.message);
      } else {
        notifyError(data.message);
      }
    })
    .on("froalaEditor.file.inserted", function (e, editor, $img, response) {})
    .on("froalaEditor.file.replaced", function (e, editor, $img, response) {})
    .on("froalaEditor.file.error", function (e, editor, error, response) {
      notifyError(error.message);
    });
  window["textEditorSave"] = function (id) {
    $("#" + id).froalaEditor("events.trigger", "blur");
  };
}

function ckEditorInit(selector) {
  if (typeof window.ckEditors === "undefined") {
    window.ckEditors = [];
  }
  if (typeof window.ckEditors[selector] !== "undefined") {
    window.ckEditors[selector].destroy();
  }
  selector = selector || ".ckeditor";

  ClassicEditor.create(document.querySelector(selector), {
    ckfinder: {
      uploadUrl:
        baseUrl +
        "editor/upload?type=image&link_key=url&status_key=uploaded&file_name=upload",
    },
  })
    .then(function (editor) {
      window.ckEditors[selector] = editor;
      window["textEditorSave"] = function (id) {
        if (typeof window.ckEditors[id] !== "undefined") {
          window.ckEditors[id].updateSourceElement();
        }
      };
    })
    .catch(function (error) {
      console.error(error);
    });
}

function tinyMCEInit(selector, timeout) {
  selector = selector || ".ckeditor";
  selector = selector.trim();
  tinymce.remove(selector);
  if (!selector.match(/\s/)) {
    selector =
      ":not(.mce-tinymce) + " + selector + ", " + selector + ":first-child";
  }

  if (!timeout) {
    setTimeout(function () {
      tinyMCEInit(selector, 5000);
    }, 5000);
  }

  if (typeof tinymce === "undefined") {
    return;
  }

  let language = typeof tinyMCELangId === "undefined" ? "en" : tinyMCELangId;
  tinymce.init({
    selector: selector,
    language: language,
    height: 250,
    menubar: false,
    plugins: [
      "advlist autolink lists link image charmap print preview anchor",
      "searchreplace visualblocks code fullscreen",
      "insertdatetime media table contextmenu paste code textcolor colorpicker spellchecker imgupload",
    ],
    toolbar:
      "styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist table forecolor code | link image imgupload",
    relative_urls: false,
    document_base_url: baseUrl,
    setup: function (editor) {
      editor.on("init", function () {
        $(".mce-tinymce + .mce-tinymce").remove();
      });
    },
  });
  window["textEditorSave"] = function (id) {
    tinymce.triggerSave(true, true);
    let editor = tinymce.get(id);
    if (editor) {
      editor.save();
    }
  };

  document.querySelectorAll(".mce-tinymce iframe").forEach(function (iframe) {
    var iframeDocument =
      iframe.contentDocument || iframe.contentWindow.document;
    var css =
      "body.night-mode {background-color: rgb(48, 48, 48) !important; color: rgb(255, 255, 255) !important;}";
    var head =
      iframeDocument.head || iframeDocument.getElementsByTagName("head")[0];
    var style = iframeDocument.createElement("style");
    head.appendChild(style);
    style.type = "text/css";
    if (style.styleSheet) {
      style.styleSheet.cssText = css;
    } else {
      style.appendChild(iframeDocument.createTextNode(css));
    }
  });
  Hook.fire("night-mode", null, [$("body").hasClass("night-mode")]);
}

Hook.register("home.auth.switched", function (result) {
  var splash02Auth = $(".splash-02 .auth");
  if (splash02Auth.length) {
    document.documentElement.style.setProperty(
      "--splash-02-auth-height",
      splash02Auth.height() + "px"
    );
  }
});

Hook.register("night-mode", function (result, status) {
  document.querySelectorAll(".mce-tinymce iframe").forEach(function (iframe) {
    var iframeDocument =
      iframe.contentDocument || iframe.contentWindow.document;
    if (status) {
      if (
        iframeDocument.body.classList &&
        !iframeDocument.body.classList.contains("night-mode")
      ) {
        iframeDocument.body.classList.add("night-mode");
      }
    } else {
      if (
        iframeDocument.body.classList &&
        iframeDocument.body.classList.contains("night-mode")
      ) {
        iframeDocument.body.classList.remove("night-mode");
      }
    }
  });
});

function textEditorInit(selector) {
  Hook.fire("text-editor.init", null, [textEditorMethod, selector]);
  textEditorMethod =
    typeof textEditorMethod === "undefined" ? tinyMCEInit : textEditorMethod;
  window[textEditorMethod].apply(undefined, [selector]);
  Hook.fire("text-editor.init.after", null, [textEditorMethod, selector]);
}

function reloadInits() {
  Hook.fire("page.reload.init");
  try {
    $(".timeago").timeago();
  } catch (e) {}
  $('[data-toggle="tooltip"]').tooltip({
    trigger: "hover",
  });
  $('[data-toggle="tooltip"]').on("click", function () {
    $(this).tooltip("hide");
  });
  $(".slimscroll").each(function () {
    $(this)
      .slimScroll({
        height: $(this).data("height"),
        start: $(this).data("start") ? $(this).data("start") : "top",
        wheelStep: $(this).data("wheel-step")
          ? parseInt($(this).data("wheel-step"))
          : 20,
        touchScrollStep: $(this).data("touch-scroll-step")
          ? parseInt($(this).data("touch-scroll-step"))
          : 200,
      })
      .bind("slimscrolling", function (e, pos) {
        Hook.fire("slimscroll", null, [this, pos, e]);
      })
      .bind("slimscroll", function (e, pos) {
        Hook.fire("slimscroll", null, [this, pos, e]);
      });
  });

  $(document).on("focus", ".color-picker", function () {
    $(this).ColorPicker({
      onSubmit: function (hsb, hex, rgb, el) {
        jQuery(el).val("#" + hex);
        $("#" + $(el).prop("id") + "-color").css("background-color", "#" + hex);
        jQuery(el).ColorPickerHide();
      },
      onBeforeShow: function () {
        jQuery(this).ColorPickerSetColor(this.value);
      },
    });
  });

  if ($(window).width() > 1000) {
    try {
      $(document.body).trigger("sticky_kit:recalc");
    } catch (e) {}
    try {
      $(".main-sidebar-menu .menu").unstick();
      $(".main-sidebar-menu .menu").stick_in_parent({
        parent: "#main-wrapper > div.dashboard-content > div",
        bottomSpacing: 75,
      });
    } catch (e) {}
    try {
      $(".middle-container .left-col-content").stick_in_parent({
        parent: "#main-wrapper > div.dashboard-content > div.container",
      });
    } catch (e) {}
    try {
      $(".middle-container .main-content.stick").stick_in_parent({
        parent:
          "#main-wrapper > div.dashboard-content > div.container, div#main-wrapper > #user-profile-content-container > div.columns-container > div.container",
      });
    } catch (e) {}
    try {
      $(".middle-container .right-col-content").stick_in_parent({
        parent:
          "#main-wrapper > div.dashboard-content > div.container, div#main-wrapper > #user-profile-content-container > div.columns-container > div.container",
      });
    } catch (e) {}
    try {
      $("#explore-menu")
        .stick_in_parent()
        .stick_in_parent({ parent: "#main-wrapper", offset_top: 50 });
    } catch (e) {}
  }

  textEditorInit();

  $("#bgcolor").spectrum({
    showPalette: true,
    showSelectionPalette: true,
    showInput: true,
    showInitial: true,
    showButtons: false,
    showAlpha: true,
    maxPaletteSize: 10,
    preferredFormat: "hex",
    palette: [
      [
        "rgb(0, 0, 0)",
        "rgb(67, 67, 67)",
        "rgb(102, 102, 102)" /*"rgb(153, 153, 153)","rgb(183, 183, 183)",*/,
        "rgb(204, 204, 204)",
        "rgb(217, 217, 217)",
        /*"rgb(239, 239, 239)", "rgb(243, 243, 243)",*/ "rgb(255, 255, 255)",
      ],
      [
        "rgb(152, 0, 0)",
        "rgb(255, 0, 0)",
        "rgb(255, 153, 0)",
        "rgb(255, 255, 0)",
        "rgb(0, 255, 0)",
        "rgb(0, 255, 255)",
        "rgb(74, 134, 232)",
        "rgb(0, 0, 255)",
        "rgb(153, 0, 255)",
        "rgb(255, 0, 255)",
      ],
      [
        "rgb(230, 184, 175)",
        "rgb(244, 204, 204)",
        "rgb(252, 229, 205)",
        "rgb(255, 242, 204)",
        "rgb(217, 234, 211)",
        "rgb(208, 224, 227)",
        "rgb(201, 218, 248)",
        "rgb(207, 226, 243)",
        "rgb(217, 210, 233)",
        "rgb(234, 209, 220)",
        "rgb(221, 126, 107)",
        "rgb(234, 153, 153)",
        "rgb(249, 203, 156)",
        "rgb(255, 229, 153)",
        "rgb(182, 215, 168)",
        "rgb(162, 196, 201)",
        "rgb(164, 194, 244)",
        "rgb(159, 197, 232)",
        "rgb(180, 167, 214)",
        "rgb(213, 166, 189)",
        "rgb(204, 65, 37)",
        "rgb(224, 102, 102)",
        "rgb(246, 178, 107)",
        "rgb(255, 217, 102)",
        "rgb(147, 196, 125)",
        "rgb(118, 165, 175)",
        "rgb(109, 158, 235)",
        "rgb(111, 168, 220)",
        "rgb(142, 124, 195)",
        "rgb(194, 123, 160)",
        "rgb(166, 28, 0)",
        "rgb(204, 0, 0)",
        "rgb(230, 145, 56)",
        "rgb(241, 194, 50)",
        "rgb(106, 168, 79)",
        "rgb(69, 129, 142)",
        "rgb(60, 120, 216)",
        "rgb(61, 133, 198)",
        "rgb(103, 78, 167)",
        "rgb(166, 77, 121)",
        /*"rgb(133, 32, 12)", "rgb(153, 0, 0)", "rgb(180, 95, 6)", "rgb(191, 144, 0)", "rgb(56, 118, 29)",
                 "rgb(19, 79, 92)", "rgb(17, 85, 204)", "rgb(11, 83, 148)", "rgb(53, 28, 117)", "rgb(116, 27, 71)",*/
        "rgb(91, 15, 0)",
        "rgb(102, 0, 0)",
        "rgb(120, 63, 4)",
        "rgb(127, 96, 0)",
        "rgb(39, 78, 19)",
        "rgb(12, 52, 61)",
        "rgb(28, 69, 135)",
        "rgb(7, 55, 99)",
        "rgb(32, 18, 77)",
        "rgb(76, 17, 48)",
      ],
    ],
    move: function (color) {
      color = color.toHexString();
      $("#main-wrapper").css("background-color", color);
      $("#explore-menu .container > ul .arrow-up").css(
        "border-bottom-color",
        color
      );
      $("#explore-menu").css("border-color", color);
    },
    change: function (color) {
      color = color.toHexString();
      $("#main-wrapper").css("background-color", color);
      $("#explore-menu .container > ul .arrow-up").css(
        "border-bottom-color",
        color
      );
      $("#explore-menu").css("border-color", color);
      $("#bgcolor").val(color);
    },
  });

  $("#linkcolor").spectrum({
    showPalette: true,
    showSelectionPalette: true,
    showInput: true,
    showInitial: true,
    showButtons: false,
    showAlpha: true,
    maxPaletteSize: 10,
    preferredFormat: "hex",
    palette: [
      [
        "rgb(0, 0, 0)",
        "rgb(67, 67, 67)",
        "rgb(102, 102, 102)" /*"rgb(153, 153, 153)","rgb(183, 183, 183)",*/,
        "rgb(204, 204, 204)",
        "rgb(217, 217, 217)",
        /*"rgb(239, 239, 239)", "rgb(243, 243, 243)",*/ "rgb(255, 255, 255)",
      ],
      [
        "rgb(152, 0, 0)",
        "rgb(255, 0, 0)",
        "rgb(255, 153, 0)",
        "rgb(255, 255, 0)",
        "rgb(0, 255, 0)",
        "rgb(0, 255, 255)",
        "rgb(74, 134, 232)",
        "rgb(0, 0, 255)",
        "rgb(153, 0, 255)",
        "rgb(255, 0, 255)",
      ],
      [
        "rgb(230, 184, 175)",
        "rgb(244, 204, 204)",
        "rgb(252, 229, 205)",
        "rgb(255, 242, 204)",
        "rgb(217, 234, 211)",
        "rgb(208, 224, 227)",
        "rgb(201, 218, 248)",
        "rgb(207, 226, 243)",
        "rgb(217, 210, 233)",
        "rgb(234, 209, 220)",
        "rgb(221, 126, 107)",
        "rgb(234, 153, 153)",
        "rgb(249, 203, 156)",
        "rgb(255, 229, 153)",
        "rgb(182, 215, 168)",
        "rgb(162, 196, 201)",
        "rgb(164, 194, 244)",
        "rgb(159, 197, 232)",
        "rgb(180, 167, 214)",
        "rgb(213, 166, 189)",
        "rgb(204, 65, 37)",
        "rgb(224, 102, 102)",
        "rgb(246, 178, 107)",
        "rgb(255, 217, 102)",
        "rgb(147, 196, 125)",
        "rgb(118, 165, 175)",
        "rgb(109, 158, 235)",
        "rgb(111, 168, 220)",
        "rgb(142, 124, 195)",
        "rgb(194, 123, 160)",
        "rgb(166, 28, 0)",
        "rgb(204, 0, 0)",
        "rgb(230, 145, 56)",
        "rgb(241, 194, 50)",
        "rgb(106, 168, 79)",
        "rgb(69, 129, 142)",
        "rgb(60, 120, 216)",
        "rgb(61, 133, 198)",
        "rgb(103, 78, 167)",
        "rgb(166, 77, 121)",
        /*"rgb(133, 32, 12)", "rgb(153, 0, 0)", "rgb(180, 95, 6)", "rgb(191, 144, 0)", "rgb(56, 118, 29)",
                 "rgb(19, 79, 92)", "rgb(17, 85, 204)", "rgb(11, 83, 148)", "rgb(53, 28, 117)", "rgb(116, 27, 71)",*/
        "rgb(91, 15, 0)",
        "rgb(102, 0, 0)",
        "rgb(120, 63, 4)",
        "rgb(127, 96, 0)",
        "rgb(39, 78, 19)",
        "rgb(12, 52, 61)",
        "rgb(28, 69, 135)",
        "rgb(7, 55, 99)",
        "rgb(32, 18, 77)",
        "rgb(76, 17, 48)",
      ],
    ],
    change: function (color) {
      color = color.toHexString();
      $("#main-wrapper > .container a").css("color", color);

      $("#linkcolor").val(color);
    },
    move: function (color) {
      color = color.toHexString();
      $("#main-wrapper > .container a").css("color", color);
    },
  });

  $("#containercolor").spectrum({
    showPalette: true,
    showSelectionPalette: true,
    showInput: true,
    showInitial: true,
    showButtons: false,
    showAlpha: true,
    maxPaletteSize: 10,
    preferredFormat: "rgb",
    allowEmpty: true,
    palette: [
      [
        "rgb(0, 0, 0)",
        "rgb(67, 67, 67)",
        "rgb(102, 102, 102)" /*"rgb(153, 153, 153)","rgb(183, 183, 183)",*/,
        "rgb(204, 204, 204)",
        "rgb(217, 217, 217)",
        /*"rgb(239, 239, 239)", "rgb(243, 243, 243)",*/ "rgb(255, 255, 255)",
      ],
      [
        "rgb(152, 0, 0)",
        "rgb(255, 0, 0)",
        "rgb(255, 153, 0)",
        "rgb(255, 255, 0)",
        "rgb(0, 255, 0)",
        "rgb(0, 255, 255)",
        "rgb(74, 134, 232)",
        "rgb(0, 0, 255)",
        "rgb(153, 0, 255)",
        "rgb(255, 0, 255)",
      ],
      [
        "rgb(230, 184, 175)",
        "rgb(244, 204, 204)",
        "rgb(252, 229, 205)",
        "rgb(255, 242, 204)",
        "rgb(217, 234, 211)",
        "rgb(208, 224, 227)",
        "rgb(201, 218, 248)",
        "rgb(207, 226, 243)",
        "rgb(217, 210, 233)",
        "rgb(234, 209, 220)",
        "rgb(221, 126, 107)",
        "rgb(234, 153, 153)",
        "rgb(249, 203, 156)",
        "rgb(255, 229, 153)",
        "rgb(182, 215, 168)",
        "rgb(162, 196, 201)",
        "rgb(164, 194, 244)",
        "rgb(159, 197, 232)",
        "rgb(180, 167, 214)",
        "rgb(213, 166, 189)",
        "rgb(204, 65, 37)",
        "rgb(224, 102, 102)",
        "rgb(246, 178, 107)",
        "rgb(255, 217, 102)",
        "rgb(147, 196, 125)",
        "rgb(118, 165, 175)",
        "rgb(109, 158, 235)",
        "rgb(111, 168, 220)",
        "rgb(142, 124, 195)",
        "rgb(194, 123, 160)",
        "rgb(166, 28, 0)",
        "rgb(204, 0, 0)",
        "rgb(230, 145, 56)",
        "rgb(241, 194, 50)",
        "rgb(106, 168, 79)",
        "rgb(69, 129, 142)",
        "rgb(60, 120, 216)",
        "rgb(61, 133, 198)",
        "rgb(103, 78, 167)",
        "rgb(166, 77, 121)",
        /*"rgb(133, 32, 12)", "rgb(153, 0, 0)", "rgb(180, 95, 6)", "rgb(191, 144, 0)", "rgb(56, 118, 29)",
                 "rgb(19, 79, 92)", "rgb(17, 85, 204)", "rgb(11, 83, 148)", "rgb(53, 28, 117)", "rgb(116, 27, 71)",*/
        "rgb(91, 15, 0)",
        "rgb(102, 0, 0)",
        "rgb(120, 63, 4)",
        "rgb(127, 96, 0)",
        "rgb(39, 78, 19)",
        "rgb(12, 52, 61)",
        "rgb(28, 69, 135)",
        "rgb(7, 55, 99)",
        "rgb(32, 18, 77)",
        "rgb(76, 17, 48)",
      ],
    ],
    change: function (color) {
      if (color == null) {
        $("#main-wrapper > .container")
          .css("background", "none")
          .css("padding", "0  important");
        $("#linkcolor").val("");
      } else {
        c = color.toRgb();
        color = "rgba(" + c.r + "," + c.g + "," + c.b + ",0.5)";
        $("#main-wrapper > .container")
          .css("background", color)
          .css("padding", "0 10px important");
        //alert(color)
        $("#containercolor").val(color);
      }
    },
    move: function (color) {
      if (color == null) {
        $("#main-wrapper > .container")
          .css("background", "none")
          .css("padding", "0  important");
      } else {
        c = color.toRgb();
        color = "rgba(" + c.r + "," + c.g + "," + c.b + ",0.5)";
        $("#main-wrapper > .container")
          .css("background", color)
          .css("padding", "0 10px important");
      }
    },
  });

  $(document).ready(function () {
    //RTLText.setText($textarea.get(0), $textarea.val());
  });

  $("textarea").each(function () {
    $(this).on("keyup", RTLText.onTextChange);
    $(this).on("keydown", RTLText.onTextChange);
  });

  $(document).on(
    "click",
    ".splash-00 .bottom .left .auth .sessions .session-add",
    function () {
      show_login_dialog();
    }
  );

  $(document).on(
    "click",
    ".splash-00 .bottom .left .auth .sessions .session",
    function (e) {
      let id = $(e.target).closest(".session").data("id");
      let hash = $(e.target).closest(".session").data("hash");
      let cookiePath = $(e.target).closest(".session").data("cookie-path");
      setCookie("sv_loggin_username", id, 30, cookiePath);
      setCookie("sv_loggin_password", hash, 30, cookiePath);
      location.reload();
    }
  );

  $(document).on(
    "click",
    ".splash-00 .bottom .left .auth .sessions .session .remove-button",
    function (e) {
      e.stopPropagation();
      let id = $(e.target).closest(".session").data("id");
      let cookiePath = $(e.target).closest(".session").data("cookie-path");
      let sessions = getCookie("sessions");
      try {
        sessions = JSON.parse(decodeURIComponent(sessions));
      } catch (e) {
        sessions = {};
      }
      if (typeof sessions[id] !== "undefined") {
        delete sessions[id];
      }
      setCookie("sessions", JSON.stringify(sessions), 365, cookiePath);
      $(e.target).closest(".session").remove();
    }
  );

  $(function () {
    document
      .querySelectorAll("input[type=text].tagify")
      .forEach(function (input) {
        var whitelist = $(input).data("whitelist-object") || [];
        $(input).tagify({
          whitelist: whitelist,
          enforceWhitelist: true,
          dropdown: {
            enabled: 1,
            fuzzySearch: true,
          },
        });
      });
  });

  Hook.fire("page.reload.init.after");
}

function toggle_profile_cover_indicator(t) {
  var i = $(".profile-cover-indicator");
  if (t) {
    i.fadeIn();
  } else {
    i.fadeOut();
  }
}

function upload_user_profile_cover(reposition) {
  reposition = reposition || true;
  toggle_profile_cover_indicator(true);
  $("#profile-cover-change-form").ajaxSubmit({
    url: baseUrl + "user/change/cover",
    success: function (data) {
      var result = jQuery.parseJSON(data);
      if (result.status == 0) {
        notifyError(result.message);
      } else {
        var img = result.image;
        $(".profile-cover-wrapper img").attr("src", img);
        $(".profile-resize-cover-wrapper img").attr("src", result.original);
        $("#profile-cover-viewer").data("id", result.id);
        $("#profile-cover-viewer").data("image", result.original);
        $("#profile-cover-viewer").addClass("photo-viewer");
        if (reposition) {
          reposition_user_profile_cover();
        }
      }
      toggle_profile_cover_indicator(false);
    },
  });
}

function reposition_user_profile_cover() {
  var rWrapper = $(".profile-cover-wrapper");
  var oWrapper = $(".profile-resize-cover-wrapper");
  if ($(window).width() <= 750) return false;
  if (oWrapper.find("img").attr("src") == "") return false;
  rWrapper.hide();
  oWrapper.show();
  window.show_profile_cover_button = false;
  $(".profile-cover-reposition-button").show();
  oWrapper.find("img").draggable({
    scroll: false,
    axis: "y",
    cursor: "s-resize",
    drag: function (event, ui) {
      y1 = $("#profile-cover").height();
      y2 = oWrapper.find("img").height();

      if (ui.position.top >= 0) {
        ui.position.top = 0;
      } else if (ui.position.top <= y1 - y2) {
        ui.position.top = y1 - y2;
      }
    },

    stop: function (event, ui) {
      //alert(ui.position.top);
      $("#profile-cover-resized-top").val(ui.position.top);
    },
  });
  return false;
}

function save_user_profile_cover() {
  var i = $("#profile-cover-resized-top").val();
  var width = $(".profile-container").data("width");
  toggle_profile_cover_indicator(true);
  $.ajax({
    url:
      baseUrl +
      "user/profile/cover/reposition?pos=" +
      i +
      "&width=" +
      width +
      "&csrf_token=" +
      requestToken,
    success: function (data) {
      $(".profile-cover-wrapper img").attr("src", data);
      toggle_profile_cover_indicator(false);
      refresh_profile_cover_positioning();
    },
    error: function () {
      toggle_profile_cover_indicator(false);
      refresh_profile_cover_positioning();
    },
  });
  return false;
}

function refresh_profile_cover_positioning() {
  var rWrapper = $(".profile-cover-wrapper");
  var oWrapper = $(".profile-resize-cover-wrapper");
  oWrapper.hide();
  rWrapper.show();
  window.show_profile_cover_button = true;
  $(".profile-cover-reposition-button").hide();
  $("#profile-cover-resized-top").val("0");
}

function cancel_profile_cover_position() {
  refresh_profile_cover_positioning();
  return false;
}

function remove_user_profile_cover(img) {
  $(".profile-cover-wrapper img").attr("src", img);
  $(".profile-resize-cover-wrapper img").attr("src", "");
  $.ajax({
    url: baseUrl + "user/cover/remove?csrf_token=" + requestToken,
  });
  return false;
}

function upload_user_avatar() {
  var form = $("#avatar-crop-control");
  show_profile_image_indicator(true);

  form.ajaxSubmit({
    url: baseUrl + "user/change/avatar",
    success: function (data) {
      data = jQuery.parseJSON(data);
      show_profile_image_indicator(false);
      if (data.status) {
        $(".profile-image").attr("src", data.image);
        $("#profile-image-viewer").data("id", data.id);
        $("#profile-image-viewer").attr("data-id", data.id);
        $("#profile-image-viewer").data("image", data.large);
        $("#profile-image-viewer").attr("data-image", data.large);
      } else {
        // other browser
        var isIE = false;
        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");
        if (msie > 0) {
          // IE 10 or older => return version number
          isIE = parseInt(ua.substring(msie + 5, ua.indexOf(".", msie)), 10);
        }
        var trident = ua.indexOf("Trident/");
        if (trident > 0) {
          // IE 11 => return version number
          var rv = ua.indexOf("rv:");
          isIE = parseInt(ua.substring(rv + 3, ua.indexOf(".", rv)), 10);
        }
        var edge = ua.indexOf("Edge/");
        if (edge > 0) {
          // Edge (IE 12+) => return version number
          isIE = parseInt(ua.substring(edge + 5, ua.indexOf(".", edge)), 10);
        }
        if (!isIE) {
          alertDialog(data.message);
        }
      }
      form.find("input[type=file]").val("");
    },
    uploadProgress: function (event, position, total, percent) {
      var uI = $(".profile-image-indicator .percent-indicator");
      uI.html(percent + "%").fadeIn();
    },
    error: function () {
      show_profile_image_indicator(false);
      alertDialog("An error occurred");
      form.find("input[type=file]").val("");
    },
  });
}

function process_user_tag_suggestion(i) {
  var target = $(i.data("target"));
  $(document).click(function (e) {
    if (!$(e.target).closest(i.data("target")).length) target.hide();
  });

  if (i.val().length > 0) {
    //alert(i.data('friend-only'));
    var friend = i.data("friend-only") == undefined ? 0 : i.data("friend-only");
    $.ajax({
      url: baseUrl + "user/tag/suggestion?csrf_token=" + requestToken,
      data: { term: i.val(), friend: friend },
      success: function (data) {
        target.html(data);
        target.fadeIn();
      },
    });
  } else {
    target.hide();
  }
}

function process_education_suggestion(i) {
  var e = $(i);
  var target = $(i.data("target"));
  var table_f = e.data("table-f");
  var type_f = e.data("type-name");
  $(document).click(function (e) {
    if (!$(e.target).closest(i.data("target")).length) target.hide();
  });

  if (i.val().length > 0) {
    target.fadeIn();
    $.ajax({
      url:
        baseUrl +
        "user/education/suggestion?csrf_token=" +
        requestToken +
        "&term=" +
        i.val() +
        "&field=" +
        table_f +
        "&type=" +
        type_f,
      success: function (data) {
        target.html(data);
      },
    });
  } else {
    target.hide();
  }
}

var confirm = {
  open: function (m) {
    $("#confirmModal").modal({ show: true });
    if (m) {
      $("#confirmModal").find(".modal-body").html(m);
    } else {
      var body = $("#confirmModal").find(".modal-body");
      body.html(body.data("message"));
    }
  },
  url: function (url, m) {
    this.open(m);
    $("#confirm-button")
      .unbind()
      .click(function () {
        window.location = url;
        confirm.close();
      });
    return false;
  },
  action: function (f, m) {
    this.open(m);
    $("#confirm-button")
      .unbind()
      .click(function () {
        f.call();
        confirm.close();
      });
  },
  close: function () {
    $("#confirmModal").modal("hide");
  },
};

function alertDialog(m) {
  $("#alertModal").modal({ show: true });
  if (m) $("#alertModal").find(".modal-body").html(m);
}

function notify(m, t, time) {
  var c = $("#site-wide-notification");
  var cM = c.find(".message");
  var time = time == undefined ? 8000 : time;
  c.fadeOut();
  c.removeClass("error")
    .removeClass("success")
    .removeClass("info")
    .removeClass("warning")
    .addClass(t);
  cM.html(m);
  c.fadeIn("slow");
  setTimeout(function () {
    c.fadeOut("slow");
  }, time);
}

function notifyError(m, time) {
  notify(m, "error", time);
}

function notifySuccess(m, time) {
  notify(m, "success", time);
}

function notifyInfo(m, time) {
  notify(m, "info", time);
}

function notifyWarning(m, time) {
  notify(m, "warning", time);
}

function closeNotify() {
  $("#site-wide-notification").fadeOut();
  return false;
}

function show_profile_image_indicator(m) {
  if (m) {
    $(".profile-image-indicator").fadeIn();
  } else {
    $(".profile-image-indicator").fadeOut();
  }
}

function initLoading() {
  $("#loading-line").show();
  $("#loading-line").width(50 + Math.random() * 30 + "%");
}

function stopLoading() {
  $("#loading-line")
    .width("100%")
    .delay(200)
    .fadeOut(500, function () {
      $(this).width("0%");
    });
}

window.pageLoadHooks = [];

function addPageHook(hook) {
  window.pageLoadHooks.push(hook);
}

function runPageHooks() {
  for (i = 0; i <= window.pageLoadHooks.length - 1; i++) {
    f = window.pageLoadHooks[i];
    r = null;
    eval(window.pageLoadHooks[i])();
  }
}

function display_design(image, repeat, color, position, link, container, id) {
  var m = $("#main-wrapper");
  m.css("background-color", color);
  //css('background-image', 'url('+bgImg+')');
  //alert(color);
  if (image == "") {
    //alert('d')
    m.css("background-image", "none");
  } else {
    m.css("background-image", "url(" + image + ")");
  }
  //m.css("background-attachment", attachment);
  m.css("background-position", "top " + position);
  m.css("background-repeat", repeat);
  $("#explore-menu .container > ul .arrow-up").css(
    "border-bottom-color",
    color
  );
  $("#explore-menu").css("border-color", color);
  if (container != "") {
    $("#main-wrapper > .container")
      .css("background", container)
      .css("padding", "0 10px important");
  } else {
    $("#main-wrapper > .container")
      .css("background", "none")
      .css("padding", "0 important");
  }
  $("#main-wrapper > .container a").css("color", link);
  if (id != undefined) {
    $("#design-active").val(id);
    $("#design-image-input").val(image);
    $(".design-position").prop("checked", "");
    $(".design-position-" + position).prop("checked", "checked");
    $("#bgcolor").val(color);
    $("#bgcolor").data("color", color);
    $(".design-repeat").prop("checked", "");
    $(".design-repeat-" + repeat).prop("checked", "checked");
    $("#linkcolor").val(link);
    $("#linkcolor").data("color", link);
    $("#containercolor").val(container);
    $("#containercolor").data("color", container);
    reloadInits();
  }
  return false;
}

function design_change_image(i) {
  var image = i;
  for (i = 0; i < image.files.length; i++) {
    if (typeof FileReader != "undefined") {
      var reader = new FileReader();
      reader.onload = function (e) {
        $("#main-wrapper").css(
          "background-image",
          "url(" + e.target.result + ")"
        );
      };
      reader.readAsDataURL(image.files[i]);
    }
  }
}

function design_bg_repeat(t) {
  var input = $(t);
  var repeat = input.val();
  $("#main-wrapper").css("background-repeat", repeat);
}

function design_bg_position(t) {
  var input = $(t);
  $("#main-wrapper").css("background-position", "top " + input.val());
}

function open_designer() {
  var pane = $("#design-pane");
  if (pane.css("display") == "none") {
    pane.slideDown(300);
  } else {
    pane.slideUp(300);
  }

  return false;
}

function hide_design_pane() {
  $("#design-pane").slideUp(300);
  return true;
}

function change_listing_layout(target, type, id) {
  var c = $(target);
  var t = type === "list" ? "list-listing-container" : "grid-listing-container";
  c.removeClass("list-listing-container")
    .removeClass("grid-listing-container")
    .addClass(t);
  id ? setCookie(id + "-list-type", type) : undefined;
  return false;
}

function run_global_filter() {
  window.filter_url = $(".global-filter-container").data("url") + "?f=1";
  $(".filter-input").each(function () {
    window.filter_url += "&" + $(this).data("name") + "=" + $(this).val();
  });

  //alert(window.filter_url);
  loadPage(window.filter_url);
  return false;
}

function loadPage(url, f) {
  window.onpopstate = function (e) {
    loadPage(window.location.href, true);
  };

  initLoading();

  $('[data-emoticon="popover"]').popover("hide");
  $.ajax({
    url: url,
    cache: false,
    type: "GET",
    success: function (data) {
      if (data === "login") {
        show_login_dialog();
        stopLoading();
      } else {
        try {
          data = jQuery.parseJSON(data);
          var content = data.content;
          var container = data.container;
          var title = data.title;
          var path = new URL(
            "https://example.com/" +
              url.replace(
                new RegExp(
                  "^" +
                    baseUrl.replace(
                      new RegExp(
                        "[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\" + "/" + "-]",
                        "g"
                      ),
                      "\\$&"
                    ),
                  "gi"
                ),
                ""
              ),
            location
          ).pathname.replace(/^\//gi, "");
          var fullContainerPages = Hook.fire("page.full-container.list", []);
          if (fullContainerPages.indexOf(path) === -1) {
            $("body").removeClass("full-container");
          } else {
            $("body").addClass("full-container");
          }
          $(container).html(content);
          $("body").removeClass("modal-open");
          $(".modal-backdrop").remove();
        } catch (e) {
          console.debug(e);
          window.open(url, "_self");
        }
        document.title = title ? title : document.title;
        Pusher.setPageTitle(document.title);
        $("#explore-container > a span").html(data.menu);
        window.history.pushState({}, "New URL:" + url, url);
        $(window).scrollTop(0);
        if (data.design) {
          display_design(
            data.design.image,
            data.design.repeat,
            data.design.color,
            data.design.position,
            data.design.link,
            data.design.container
          );
        }
        reloadInits();
        stopLoading();
        runPageHooks();
        hide_side_bar_menu();
        if ($(".side-footer").length > 0) {
          $(".footer-content").hide();
        } else {
          $(".footer-content").show();
        }
        $("body").click();
      }
    },
    error: function () {
      stopLoading();
      login_required();
    },
  });
  return false;
}

_firebase = {
  config: {
    apiKey: firebaseAPIKey,
    authDomain: firebaseAuthDomain,
    databaseURL: firebaseDatabaseURL,
    projectId: firebaseProjectId,
    storageBucket: firebaseStorageBucket,
    messagingSenderId: firebaseMessagingSenderId,
  },

  init: function (config) {
    _firebase.instance();
  },

  instance: function (config) {
    if (typeof firebase !== "undefined") {
      config = config || {
        apiKey: firebaseAPIKey,
        authDomain: firebaseAuthDomain,
        databaseURL: firebaseDatabaseURL,
        projectId: firebaseProjectId,
        storageBucket: firebaseStorageBucket,
        messagingSenderId: firebaseMessagingSenderId,
      };
      return firebase.initializeApp(config);
    }
  },
};
_firebase.init();

serviceWorker = {
  registration: null,

  init: function () {
    serviceWorker.register();
  },

  register: function () {
    if (navigator.serviceWorker) {
      navigator.serviceWorker
        .register(baseUrl + "sw.js", { scope: baseUrl })
        .then(
          function (registration) {
            serviceWorker.registration = registration;
            Hook.fire(
              "service-worker.registration.success",
              null,
              registration
            );
            console.log(
              "ServiceWorker registration successful with scope: ",
              registration.scope
            );
          },
          function (error) {
            Hook.fire("service-worker.registration.error", null, [error]);
            console.log("ServiceWorker registration failed: ", error);
          }
        );
      return true;
    } else {
      Hook.fire("service-worker.registration.error", null, [
        new Error("Browser does not support service Worker"),
      ]);
      console.log("Browser does not support service Worker");
      return false;
    }
  },
};
serviceWorker.init();
/**
 * Realtime update push final process
 */
var Pusher = {
  hooks: [],
  alert: false,
  pushIds: [],
  titleCount: 0,
  pageTitle: "",
  userid: "",
  pushCount: 0,

  driver: "ajax",

  drivers: {
    poll: {
      messageQueue: [],
      messageQueueTmp: [],
      requesting: false,
      timeout: null,

      init: function () {
        if (Pusher.driver === "ajax" && loggedIn) {
          Pusher.drivers.poll.check();
        }
      },

      check: function () {
        Pusher.drivers.poll.requesting = true;
        Pusher.drivers.poll.messageQueueNextIndex =
          Pusher.drivers.poll.messageQueue.length;
        var data = { data: [] };
        for (var i in Pusher.drivers.poll.messageQueue) {
          data.data.push(JSON.stringify(Pusher.drivers.poll.messageQueue[i]));
        }
        $.ajax({
          xhr: function () {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener(
              "progress",
              function (e) {
                if (e.lengthComputable) {
                  var progress = e.loaded / e.total;
                  Hook.fire("pusher.send.progress", null, [progress]);
                }
              },
              false
            );

            return xhr;
          },
          url: baseUrl + "ajax/push/check?csrf_token=" + requestToken,
          data: data,
          type: "POST",
          beforeSend: function () {
            for (var i in Pusher.drivers.poll.messageQueue) {
              Hook.fire("pusher.send.before", null, [
                Pusher.drivers.poll.messageQueue[i],
              ]);
            }
          },
          success: function (data) {
            for (var i in Pusher.drivers.poll.messageQueue) {
              Hook.fire("pusher.send.after", null, [
                Pusher.drivers.poll.messageQueue[i],
              ]);
            }
            Pusher.drivers.poll.messageQueue = Pusher.drivers.poll.messageQueue.splice(
              Pusher.drivers.poll.messageQueueNextIndex
            );
            Pusher.onMessage(data);
            Pusher.drivers.poll.requesting = false;
            Pusher.drivers.poll.timeout = setTimeout(function () {
              if (loggedIn) {
                Pusher.drivers.poll.check();
              }
            }, ajaxInterval);
          },
          error: function (error) {
            for (var i in Pusher.drivers.poll.messageQueue) {
              Hook.fire("pusher.send.after", null, [
                Pusher.drivers.poll.messageQueue[i],
              ]);
            }
            Pusher.drivers.poll.requesting = false;
            Pusher.drivers.poll.timeout = setTimeout(function () {
              if (loggedIn) {
                Pusher.drivers.poll.check();
              }
            }, ajaxInterval);
          },
        });
      },

      sendMessage: function (data, uniqueProperty) {
        var replaced = false;
        if (Pusher.driver === "ajax") {
          if (uniqueProperty) {
            for (var i in Pusher.drivers.poll.messageQueue) {
              if (
                typeof Pusher.drivers.poll.messageQueue[i] === "object" &&
                typeof data === "object" &&
                Pusher.drivers.poll.messageQueue[i][uniqueProperty] ===
                  data[uniqueProperty]
              ) {
                Pusher.drivers.poll.messageQueue[i] = data;
                replaced = true;
              }
            }
          }
          if (!replaced) {
            Pusher.drivers.poll.messageQueue.push(data);
          }
          if (!Pusher.drivers.poll.requesting) {
            if (Pusher.drivers.poll.timeout) {
              clearTimeout(Pusher.drivers.poll.timeout);
              Pusher.drivers.poll.check();
            }
          }
        }
      },
    },

    FCM: {
      messaging: null,
      token: null,
      permission: false,

      user: {
        database: null,

        snapshots: [],

        init: function () {
          if (
            typeof firebase === "undefined" ||
            !(_firebase.config.databaseURL + "").length
          ) {
            return false;
          }
          Pusher.drivers.FCM.user.database = firebase.database();
          // Pusher.drivers.FCM.user.database.enableLogging(true, true);
          if (typeof userID === "number") {
            var user = { id: userID, online: true };
            Pusher.drivers.FCM.user.update(user, function (error) {
              if (!error) {
                Pusher.drivers.FCM.user.database
                  .ref("users/" + user.id)
                  .onDisconnect()
                  .update({ online: false });
                Pusher.drivers.FCM.user.database
                  .ref(".info/connected")
                  .on("value", function (snapshot) {
                    var online = snapshot.val();
                    Pusher.drivers.FCM.user.database
                      .ref("users/" + user.id)
                      .update({ online: online });
                  });
                if (typeof friends === "object") {
                  friends.forEach(function (id) {
                    Pusher.drivers.FCM.user.database
                      .ref("users/" + id)
                      .on("value", function (snapshot) {
                        var user = snapshot.val();
                        var previousUser = Pusher.drivers.FCM.user.snapshots[id]
                          ? Pusher.drivers.FCM.user.snapshots[user.id]
                          : user;
                        if (user) {
                          if (user.online !== previousUser.online) {
                            user.online
                              ? Pusher.drivers.FCM.user.onConnect(user)
                              : Pusher.drivers.FCM.user.onDisconnect(user);
                          }
                        }
                        Pusher.drivers.FCM.user.snapshots[id] = user;
                      });
                  });
                }
              }
            });
          }
          return true;
        },

        update: function (user, callback) {
          Pusher.drivers.FCM.user.database
            .ref("users/" + user.id)
            .once("value", function (snapshot) {
              var oldUser = snapshot.val();
              if (oldUser) {
                Pusher.drivers.FCM.user.database
                  .ref("users/" + user.id)
                  .update(user, callback);
              } else {
                Pusher.drivers.FCM.user.database
                  .ref("users/" + user.id)
                  .set(user, callback);
              }
            });
        },

        onConnect: function (user) {
          Hook.fire("friend.connected", null, [user]);
        },

        onDisconnect: function (user) {
          Hook.fire("friend.disconnected", null, [user]);
        },
      },

      init: function () {
        if (
          typeof firebase === "undefined" ||
          !(_firebase.config.messagingSenderId + "").length ||
          !loggedIn ||
          !Pusher.drivers.FCM.user.init()
        ) {
          return false;
        }
        Pusher.drivers.FCM.messaging = firebase.messaging();
        Pusher.drivers.FCM.messaging.usePublicVapidKey(firebasePublicVapidKey);
        serviceWorker.registration
          ? Pusher.drivers.FCM.onServiceWorkerRegister()
          : Hook.register(
              "service-worker.registration.success",
              Pusher.drivers.FCM.onServiceWorkerRegister
            );
        Pusher.drivers.FCM.messaging
          .requestPermission()
          .then(function () {
            Pusher.drivers.FCM.permission = true;
            Pusher.drivers.FCM.setToken();
            Pusher.drivers.FCM.messaging.onTokenRefresh(function () {
              Pusher.drivers.FCM.messaging
                .getToken()
                .then(function (token) {
                  console.log("Token refreshed.");
                  Pusher.drivers.FCM.setToken(token);
                })
                .catch(function (error) {
                  console.log("Unable to retrieve refreshed token ", error);
                });
            });
            Pusher.drivers.FCM.messaging.onMessage(function (payload) {
              var data = JSON.parse(payload.data.pushes);
              // alert('FCM Push Message Arrive in Browser');
              Pusher.onMessage(data);
            });
          })
          .catch(function (error) {
            if (error.code === "messaging/permission-blocked") {
              console.log(
                "Unable to get FCM permission to notify. Falling back to AJAX Polling"
              );
              Pusher.driver = "ajax";
              Pusher.drivers.poll.init();
            } else {
              console.log("Unable to get permission to notify.", error);
            }
          });
      },

      onServiceWorkerRegister: function (result, registration) {
        registration = registration || serviceWorker.registration;
        Pusher.drivers.FCM.messaging.useServiceWorker(registration);
        navigator.serviceWorker.addEventListener("message", function (e) {
          // alert('FCM Push Message Recieved from SW');
          var payload =
            e.data["firebase-messaging-msg-type"] == "push-msg-received"
              ? e.data["firebase-messaging-msg-data"]
              : e.data;
          var data = JSON.parse(payload.data.pushes);
          Pusher.onMessage(data);
        });
      },

      setToken: function (token) {
        if (token) {
          Pusher.drivers.FCM.token = token;
          Pusher.drivers.FCM.updateServerToken();
        } else {
          Pusher.drivers.FCM.messaging
            .getToken()
            .then(function (token) {
              if (token) {
                Pusher.drivers.FCM.token = token;
                Pusher.drivers.FCM.updateServerToken();
              } else {
                console.log(
                  "No Instance ID token available. Request permission to generate one."
                );
              }
            })
            .catch(function (error) {
              if (error.code === "messaging/notifications-blocked") {
                console.log(
                  "Unable to get FCM permission to notify. Falling back to AJAX Polling"
                );
                pusher.driver = "ajax";
                pusher.drivers.poll.init();
              } else {
                console.log(
                  "An error occurred while retrieving token. ",
                  error
                );
              }
            });
        }
      },

      updateServerToken: function (token) {
        token = token || Pusher.drivers.FCM.token;
        var headers = new Headers();
        headers.append("pragma", "no-cache");
        headers.append("cache-control", "no-cache");
        fetch(
          baseUrl +
            "fcm/token/update?token=" +
            token +
            "&csrf_token=" +
            requestToken,
          {
            method: "GET",
            credentials: "same-origin",
            headers: headers,
          }
        )
          .then(function (response) {})
          .catch(function (error) {});
      },

      sendMessage: function (data) {
        if (Pusher.driver === "fcm") {
          var retryTime = 1000;
          var headers = new Headers();
          var formData = new FormData();
          formData.append("data[]", JSON.stringify(data));
          headers.append("pragma", "no-cache");
          headers.append("cache-control", "no-cache");
          Hook.fire("pusher.send.before", null, [data]);
          Hook.fire("pusher.send.progress", null, [0]);
          fetch(baseUrl + "fcm/send?csrf_token=" + requestToken, {
            method: "POST",
            credentials: "same-origin",
            headers: headers,
            body: formData,
          })
            .then(function (response) {
              Hook.fire("pusher.send.progress", null, [1]);
              Hook.fire("pusher.send.after", null, [data]);
              if (response.status !== 200) {
                setTimeout(function () {
                  Pusher.drivers.FCM.sendMessage(data);
                }, retryTime);
              }
            })
            .catch(function (error) {
              Hook.fire("pusher.send.progress", null, [1]);
              Hook.fire("pusher.send.after", null, [data]);
              setTimeout(function () {
                if (loggedIn) {
                  Pusher.drivers.FCM.sendMessage(data);
                }
              }, retryTime);
            });
        }
      },
    },
  },

  init: function () {
    window.pushDriver = pushDriver || "ajax";
    Hook.fire("pusher.init", [undefined]);
    for (var i in Pusher.drivers) {
      Pusher.drivers[i].init();
    }
  },

  sendMessage: function (data, uniqueProperty) {
    for (var i in Pusher.drivers) {
      Pusher.drivers[i].sendMessage(data, uniqueProperty);
    }
  },

  onMessage: function (data) {
    if (data) {
      data = typeof data === "string" ? JSON.parse(data) : data;
      var userID = data.userid;
      var types = data.types;
      var seen = data.seen;
      if (parseInt(seen) === 0) {
        Pusher.onAlert();
      }
      Pusher.setUser(userID);
      if (types) {
        types = typeof types === "string" ? JSON.parse(types) : types;
        for (var i in types) {
          Pusher.run(i, types[i]);
        }
      }
      Pusher.finish();
    }
  },

  onAlert: function () {
    this.alert = true;
  },

  offAlert: function () {
    this.alert = false;
  },

  finish: function () {
    Pusher.pushCount++;
    //final steps to take like sound alert if on
    if (this.alert) {
      if (enableNotificationSound) {
        var audio = document.getElementById("update-sound");
        //audio.load();
        let playPromise = audio.play();
        playPromise !== undefined
          ? playPromise
              .then(function () {
                console.log("Alert Sound!");
              })
              .catch(function (reason) {
                console.log(reason);
              })
          : undefined;
      }
    }
    this.alert = false;
    this.refreshPageTitle();
    Hook.fire("pusher.finish", [undefined]);
  },

  setPageTitle: function (t) {
    this.pageTitle = t;
    this.refreshPageTitle();
  },

  refreshPageTitle: function () {
    if (this.titleCount > 0) {
      pageTitle = this.pageTitle;
      pageTitle = "(" + this.titleCount + ") " + pageTitle;
      document.title = pageTitle ? pageTitle : document.title;
      this.titleCount = 0;
    } else {
      document.title = this.pageTitle ? this.pageTitle : document.title;
    }
  },

  setUser: function (userid) {
    this.userid = userid;
  },

  getUser: function () {
    return this.userid;
  },

  addCount: function (c) {
    this.titleCount = parseInt(this.titleCount) + parseInt(c);
  },

  removeCount: function (c) {
    this.titleCount -= c;
    this.refreshPageTitle();
  },

  addHook: function (hook) {
    this.hooks.push(hook);
  },

  run: function (type, d) {
    for (i = 0; i <= this.hooks.length - 1; i++) {
      f = this.hooks[i];
      r = null;
      eval(this.hooks[i])(type, d);
    }
  },

  addPushId: function (id) {
    this.pushIds.push(id);
  },

  hasPushId: function (id) {
    if (jQuery.inArray(id, this.pushIds) != -1) return true;
    return false;
  },
};

Pusher.init();

//functions to manage cookies
function setCookie(cname, cvalue, exdays, path) {
  if (exdays == undefined) exdays = 365;
  var d = new Date();
  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
  var expires = "expires=" + d.toUTCString();
  document.cookie =
    cname + "=" + cvalue + "; " + expires + (path ? "; path=" + path : "");
}

function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(";");
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == " ") {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function deleteCookie(cname) {
  document.cookie = cname + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC";
}

$(window).on("unload", function () {
  $(window).scrollTop(0);
});

$(function () {
  reloadInits();

  if ($("#home-container").length > 0) {
    window.welcome_item = 1;
    window.welcome_item_interval = setInterval(function () {
      $(".item-" + window.welcome_item).fadeIn();
      if (window.welcome_item >= 3) {
        clearInterval(window.welcome_item_interval);
      }
      window.welcome_item += 1;
    }, 200);
  }

  $(window).resize(function () {
    if ($(window).width() > "750") {
      if ($("#sidebar-menu").css("display") != "none") {
        hide_side_bar_menu();
      }
    }
  });
  Pusher.setPageTitle(document.title);
  //rotateTopography();
  slidersInit("topo", 1);
  $(document).on("keyup", ".auto-grow-input", function () {
    var obj = $(this);
    var height = "20px";
    if (obj.attr("data-height")) {
      height = obj.data("height");
    }
    this.style.height = height;
    this.style.height = this.scrollHeight + "px";
  });

  $(document).on("keyup", ".user-tag-input", function () {
    process_user_tag_suggestion($(this));
  });

  $(document).on("keyup", ".user-education-input", function () {
    process_education_suggestion($(this));
  });

  $(document).on("click", ".confirm", function () {
    confirm.url($(this).attr("href"));
    return false;
  });

  $(document).on("keyup", ".textarea-limit", function () {
    var o = $(this);
    var limit = o.data("text-limit");
    var countTarget = $(o.data("text-limit-count-target"));
    var text = o.val();
    if (text.length > limit) {
      text = text.substr(o, limit);
    }
    o.val(text);
    countTarget.html(limit - text.length);
  });

  $(document).on("click", 'a[ajax="true"]', function () {
    try {
      if (typeof loadAjax !== "undefined" && !loadAjax) return true;
    } catch (e) {}
    return loadPage($(this).attr("href"));
  });

  $(document).on("submit", "#header-search", function () {
    var term = $(this).find("input[type=text]").val();

    $(this).find("input[type=text]").blur();
    if (term != "") {
      url = $(this).attr("action") + "?term=" + term;
      $("#search-dropdown").fadeOut("fast", function () {});
      loadPage(url);
    }
    return false;
  });

  $(document).on("mouseover", ".preview-card", function () {
    var card = $(this).find(".profile-card");
    if (card.length == 0) {
      card = $("<div class='profile-card box'>" + indicator + "</div>");
      card.find("img").css("width", "10px");
      card.find("img").css("display", "block");
      card.find("img").css("margin", "10px auto");
      $(this).append(card);
      $.ajax({
        url:
          baseUrl +
          "preview/card?type=" +
          $(this).data("type") +
          "&id=" +
          $(this).data("id") +
          "&csrf_token=" +
          requestToken,
        success: function (c) {
          card.html(c);
        },
      });
    }
    var obj = $(this);
    $(document).click(function (e) {
      if (!$(e.target).closest(obj).length) card.hide();
    });

    $(document).mouseout(function (e) {
      if (!$(e.target).closest(obj).length) card.hide();
    });
    card.fadeIn();
  });

  if ($("#popup-language-selection").length > 0) {
    $("#popup-language-selection").fadeIn(500);
    document.body.style.overflow = "hidden";
  }
});

$(function () {
  $("#loginModal .forgot").click(function () {
    $("#loginModal").modal("hide");
  });
});

$(function () {
  function checkScroll() {
    if (window.scrollY > 0) {
      $("body").addClass("scrolled");
    } else {
      $("body").removeClass("scrolled");
    }
    if (window.scrollY > 64) {
      $("body").addClass("scrolled-64");
    } else {
      $("body").removeClass("scrolled-64");
    }
  }

  checkScroll();
  $(window).scroll(checkScroll);
});

$(function () {
  $(".right-menu-toggler").on("click", function () {
    if ($("body").hasClass("right-menu-active")) {
      $("body").removeClass("right-menu-active");
    } else {
      $("body").addClass("right-menu-active");
    }
  });
});

function friendsListToggle() {
  if ($("body").hasClass("chat-opened")) {
    $("body").removeClass("chat-opened");
    setCookie("chat-opened", 0);
  } else {
    $("body").addClass("chat-opened");
    setCookie("chat-opened", 1);
  }
}

function friendsListOpen() {
  if (!$("body").hasClass("chat-opened")) {
    $("body").addClass("chat-opened");
    setCookie("chat-opened", 1);
  }
}

function friendsListClose() {
  if ($("body").hasClass("chat-opened")) {
    $("body").removeClass("chat-opened");
    setCookie("chat-opened", 0);
  }
}

function friendsListEvents() {
  $(".friends-list-toggle").on("click", friendsListToggle);
  $(".friends-list-open").on("click", friendsListOpen);
  $(".friends-list-close").on("click", friendsListClose);
}

if (typeof people === "undefined") {
  window.people = {
    filterParams: [],
    filterParamsHooks: [],

    addFilterParamsHook: function (hook) {
      people.filterParamsHooks.push(hook);
    },

    runFilterParamsHooks: function () {
      for (var i = 0; i <= people.filterParamsHooks.length - 1; i++) {
        eval(window.pageLoadHooks[i])();
      }
    },

    submitSearch: function () {
      var url = baseUrl + "people";
      people.filterParams = [];
      if (
        document.getElementById("people-keywords-input") &&
        document.getElementById("people-keywords-input").value != ""
      )
        people.filterParams.push(
          "keywords=" + document.getElementById("people-keywords-input").value
        );
      if (
        document.getElementById("people-gender-select") &&
        document.getElementById("people-gender-select").value != "both"
      )
        people.filterParams.push(
          "gender=" + document.getElementById("people-gender-select").value
        );
      if (
        document.getElementById("people-online-select") &&
        document.getElementById("people-online-select").value != "both"
      )
        people.filterParams.push(
          "online_status=" +
            document.getElementById("people-online-select").value
        );

      if (
        document.getElementById("people-location-select") &&
        document.getElementById("people-location-select").value != "any"
      )
        people.filterParams.push(
          "location=" + document.getElementById("people-location-select").value
        );
      Hook.fire("people.filter.load");
      people.runFilterParamsHooks();
      if (people.filterParams.length > 0)
        url += "?" + people.filterParams.join("&");
      loadPage(url);
      return false;
    },
  };
}

function show_more_content(id, e) {
  var o = $(id);
  if (o.css("display") === "none") {
    $(e).removeClass("ion-arrow-down-b");
    $(e).addClass("ion-arrow-up-b");
    e.innerHTML = e.getAttribute("lesslabel") || "Show More";
    o.show();
  } else {
    $(e).removeClass("ion-arrow-up-b");
    $(e).addClass("ion-arrow-down-b");
    e.innerHTML = e.getAttribute("morelabel") || "Show Less";
    o.hide();
  }
  try {
    $(document.body).trigger("sticky_kit:recalc");
  } catch (e) {
    console.log(e);
  }
  return false;
}

function initMaps() {
  if (typeof google !== "undefined") {
    Hook.fire("googleapis.maps.callbacks");
  }
}

window.live = {
  enablePlayer: true,
  autoplay: true,
  muted: false,

  stream: null,
  recorder: null,
  chunks: [],
  blob: new Blob(),

  player: "#live-player",
  canvas: "#live-canvas",
  shot: "#live-shot",
  record: "#live-record",

  playButton: "#live-play-button",
  pauseButton: "#live-pause-button",

  recorderStartButton: "#live-recorder-start-button",
  recorderStoplaybackButton: "#live-recorder-stop-button",
  recorderSaveButton: "#live-recorder-save-button",

  snapTakeButton: "#live-snap-take-button",
  snapSaveButton: "#live-snap-save-button",

  initCallback: null,
  streamReadyCallback: null,
  playCallback: null,
  pauseCallback: null,
  recorderMimeType: null,
  recorderReadyCallback: null,
  recorderStartCallback: null,
  recorderStopCallback: null,
  recorderSaveCallback: null,
  snapTakeCallback: null,
  snapSaveCallback: null,
  liveEndCallback: null,

  constraint: { audio: true, video: true },

  requestRecordedDataIntervalTimeout: 500,

  init: function (config) {
    let live = this;

    if (config && typeof config === "object") {
      for (var i in config) {
        live[i] = config[i];
      }
    }

    this.player =
      typeof this.player === "string"
        ? document.querySelector(this.player)
        : this.player;
    this.record =
      typeof this.record === "string"
        ? document.querySelector(this.record)
        : this.record;
    this.canvas =
      typeof this.canvas === "string"
        ? document.querySelector(this.canvas)
        : this.canvas;
    this.shot =
      typeof this.shot === "string"
        ? document.querySelector(this.shot)
        : this.shot;

    this.playButton =
      typeof this.playButton === "string"
        ? document.querySelector(this.playButton)
        : this.playButton;
    this.pauseButton =
      typeof this.pauseButton === "string"
        ? document.querySelector(this.pauseButton)
        : this.pauseButton;

    this.recorderStartButton =
      typeof this.recorderStartButton === "string"
        ? document.querySelector(this.recorderStartButton)
        : this.recorderStartButton;
    this.recorderStoplaybackButton =
      typeof this.recorderStoplaybackButton === "string"
        ? document.querySelector(this.recorderStoplaybackButton)
        : this.recorderStartButton;
    this.recorderSaveButton =
      typeof this.recorderSaveButton === "string"
        ? document.querySelector(this.recorderSaveButton)
        : this.recorderStartButton;

    this.snapTakeButton =
      typeof this.snapTakeButton === "string"
        ? document.querySelector(this.snapTakeButton)
        : this.snapTakeButton;
    this.snapSaveButton =
      typeof this.snapSaveButton === "string"
        ? document.querySelector(this.snapSaveButton)
        : this.snapSaveButton;

    if (!(this.player instanceof HTMLElement)) {
      if (
        typeof this.constraint.video !== "undefined" &&
        this.constraint.video
      ) {
        this.player = document.createElement("video");
      } else {
        this.player = document.createElement("audio");
      }
    }

    if (this.record && !(this.record instanceof HTMLElement)) {
      if (
        typeof this.constraint.video !== "undefined" &&
        this.constraint.video
      ) {
        this.record = document.createElement("video");
      } else {
        this.record = document.createElement("audio");
      }
    }

    if (!this.enablePlayer) {
      this.player = null;
    }

    if (this.stream) {
      this.stream.getTracks().forEach(function (track) {
        track.stop();
      });
      live.stream = null;
    }
    navigator.mediaDevices
      .getUserMedia(this.constraint)
      .then(function (stream) {
        live.stream = stream;
        if (live.streamReadyCallback) {
          eval(live.streamReadyCallback)(live);
        }
        live.recorderInit();
        if (typeof live.player === "object") {
          if (live.muted) {
            live.player.muted = true;
            live.player.setAttribute("muted", true);
          }

          live.player.onloadedmetadata = function (e) {
            if (live.autoplay) {
              var playPromise = e.target.play();
              playPromise !== undefined
                ? playPromise.then(function () {}).catch(function () {})
                : undefined;
            } else {
              var pausePromise = e.target.pause();
              pausePromise !== undefined
                ? pausePromise.then(function () {}).catch(function () {})
                : undefined;
            }
          };
          try {
            live.player.srcObject = live.stream;
          } catch (e) {
            console.log(e);
          }
        }
      })
      .catch(function (error) {
        console.error(error);
      });

    this.attachEvents();

    if (this.initCallback) {
      eval(this.initCallback)(this);
    }
  },

  attachEvents: function () {
    let live = this;

    if (this.playButton instanceof HTMLElement) {
      this.playButton.addEventListener("click", function () {
        live.play();
      });
    }
    if (this.pauseButton instanceof HTMLElement) {
      this.pauseButton.addEventListener("click", function () {
        live.pause();
      });
    }

    if (this.recorderStartButton instanceof HTMLElement) {
      this.recorderStartButton.addEventListener("click", function () {
        live.recorderStart();
      });
    }
    if (this.recorderStoplaybackButton instanceof HTMLElement) {
      this.recorderStoplaybackButton.addEventListener("click", function () {
        live.recorderStop();
      });
    }
    if (this.recorderSaveButton instanceof HTMLElement) {
      this.recorderSaveButton.addEventListener("click", function () {
        live.recorderSave();
      });
    }

    if (this.snapTakeButton instanceof HTMLElement) {
      this.snapTakeButton.addEventListener("click", function () {
        live.snapTake();
      });
    }
    if (this.snapSaveButton instanceof HTMLElement) {
      this.snapSaveButton.addEventListener("click", function () {
        live.snapSave();
      });
    }
  },

  play: function () {
    if (typeof this.player === "object") {
      this.player.play();
    }
    if (this.playCallback) {
      eval(this.pauseCallback)(this);
    }
  },

  pause: function () {
    if (typeof this.player === "object") {
      this.player.pause();
    }
    if (this.pauseCallback) {
      eval(this.playCallback)(this);
    }
  },

  recorderInit: function (options) {
    if (typeof MediaRecorder === "function") {
      this.recorderMimeType = this.constraint.video
        ? MediaRecorder.isTypeSupported("video/webm;codecs=vp9")
          ? "video/webm;codecs=vp9,opus"
          : "video/webm;codecs=vp8,opus"
        : "audio/webm";
      var defaultOptions = options || {
        audioBitsPerSecond: 128000,
        videoBitsPerSecond: 2500000,
        mimeType: this.recorderMimeType,
      };
      if (options && typeof options === "object") {
        for (var i in options) {
          defaultOptions[i] = options[i];
        }
      }
      options = defaultOptions;
      this.recorder = new MediaRecorder(this.stream, options);
      if (this.recorderReadyCallback) {
        eval(this.recorderReadyCallback)(this);
      }
    }
  },

  recorderStart: function (restart) {
    if (restart) {
      this.recorderClear();
    }
    if (this.recorder && this.recorder.state === "inactive") {
      let live = this;
      if (this.autoplay) {
        this.play();
      }
      this.recorder.start(1000);
      this.recorder.addEventListener("dataavailable", function (event) {
        live.chunks.push(event.data);
      });
      if (this.requestRecordedDataInterval) {
        clearInterval(this.requestRecordedDataInterval);
      }
      this.requestRecordedDataInterval = setInterval(function () {
        if (live.recorder && live.recorder.state === "recording") {
          live.recorder.requestData();
        }
      }, this.requestRecordedDataIntervalTimeout);
      if (this.recorderStartCallback) {
        eval(this.recorderStartCallback)(this);
      }
    }
  },

  recorderStop: function () {
    if (this.requestRecordedDataInterval) {
      clearInterval(this.requestRecordedDataInterval);
    }
    if (this.recorder && this.recorder.state === "recording") {
      this.recorder.stop();
      if (this.recorderStopCallback) {
        eval(this.recorderStopCallback)(this);
      }
    }
  },

  recorderSave: function () {
    this.blob = new Blob(this.chunks, {
      type: this.recorderMimeType,
      name: "record.webm",
    });
    if (this.record && this.blob) {
      this.record.src = URL.createObjectURL(this.blob);
    }
    if (this.recorderSaveCallback) {
      eval(this.recorderSaveCallback)(this);
    }
    return this.blob;
  },

  recorderClear: function () {
    this.blob = new Blob();
    this.chunks = [];
    this.record.src = "";
  },

  snapTake: function () {
    var canvas = this.canvas ? this.canvas : document.createElement("canvas");
    if (typeof this.player === "object") {
      canvas.width = this.player.videoWidth;
      canvas.height = this.player.videoHeight;
      canvas.getContext("2d").drawImage(this.player, 0, 0);
    }
    if (this.snapTakeCallback) {
      eval(this.snapTakeCallback)(this);
    }
    return canvas.toDataURL("image/png");
  },

  snapSave: function () {
    var data = "";
    if (this.canvas) {
      data = this.canvas.toDataURL("image/png");
      if (this.shot && this.canvas) {
        this.shot.src = data;
      }
    }
    if (this.snapSaveCallback) {
      eval(this.snapSaveCallback)(this);
    }
    return data;
  },

  end: function () {
    this.pause();
    this.recorderStop();
    this.recorderClear();
    if (this.stream) {
      let live = this;
      live.stream.getTracks().forEach(function (track) {
        track.stop();
      });
      this.stream = null;
    }
    if (this.liveEndCallback) {
      eval(this.liveEndCallback)(this);
    }
  },
};

if (typeof language === "undefined") {
  language = {
    phrases: {},

    addPhrase: function (id, phrase) {
      language.phrases[id] = phrase;
    },
    addPhrases: function (phrases) {
      for (var i in phrases) {
        this.addPhrase(i, phrases[i]);
      }
    },
    phrase: function (id) {
      return typeof language.phrases[id] === "undefined"
        ? id
        : language.phrases[id];
    },
  };
}

window.audio = {
  init: function () {
    audio.player.init();
  },

  player: {
    init: function () {
      audio.player.attachEvents();
    },

    attachEvents: function () {
      $(".audio-player > audio").off();

      $(".audio-player > audio").on("play", function (e) {
        var audioPlayer = $(this).closest(".audio-player");
        var playbackButton = audioPlayer.find(".playback-button");
        playbackButton.removeClass("ion-play");
        playbackButton.addClass("ion-pause");
        audioPlayer.addClass("playing");
      });

      $(".audio-player > audio").on("pause", function (e) {
        var audioPlayer = $(this).closest(".audio-player");
        var playbackButton = audioPlayer.find(".playback-button");
        playbackButton.removeClass("ion-pause");
        playbackButton.addClass("ion-play");
        audioPlayer.removeClass("playing");
      });

      $(".audio-player > audio").on("ended", function (e) {
        var audioPlayer = $(this).closest(".audio-player");
        var playbackButton = audioPlayer.find(".playback-button");
        playbackButton.removeClass("ion-pause");
        playbackButton.addClass("ion-play");
        audioPlayer.removeClass("playing");
      });

      $(".audio-player > audio").on("timeupdate", function (e) {
        var audioPlayer = $(this).closest(".audio-player");
        var timeline = audioPlayer.find(".timeline");
        var playHead = audioPlayer.find(".play-head");
        if (timeline && playHead) {
          playHead.css(
            "margin-left",
            (timeline.width() - playHead.width()) *
              (this.currentTime / this.duration) +
              "px"
          );
        }
      });

      $(document).off("click", ".audio-player > .playback-button");

      $(document).on("click", ".audio-player > .playback-button", function (e) {
        var playbackButton = $(this);
        var audioPlayer = playbackButton.closest(".audio-player");
        if (audioPlayer) {
          audioPlayer.find("audio").each(function () {
            var audio = this;
            if (audioPlayer.hasClass("playing")) {
              audio.pause();
            } else {
              audio.play();
            }
          });
        }
      });

      $(document).off("click", ".play-audio");

      $(document).on("click", ".play-audio", function (e) {
        e.preventDefault();
        var button = this;
        var src =
          $(this).data("url") || $(this).attr("src") || $(this).attr("href");
        var audio = new Audio(src);
        $(button).addClass("playing");
        audio.play();
        audio.addEventListener("ended", function (e) {
          $(button).removeClass("playing");
        });
      });
    },
  },
};

function check_coupon_validity() {
  var coupon = $("input.validate-coupon-input").val();
  var url = document.URL;
  var indicator = $("#promotion-coupon-indicator");
  $.ajax({
    beforeSend: function () {
      indicator.show();
    },
    url:
      baseUrl +
      "promotion/coupon/url?link=" +
      encodeURIComponent(url) +
      "&coupon=" +
      encodeURIComponent(coupon) +
      "&csrf_token=" +
      requestToken,
    success: function (data) {
      data = jQuery.parseJSON(data);
      indicator.hide();
      console.log(data);
      if (data.status == 1) {
        url = data.url;
        loadPage(url);
      } else {
        $("#promotion-coupon-error").show();
      }
    },
  });
}

function initVoiceRecorder() {
  if (typeof MediaRecorder === "function") {
    $(".voice-recorder").each(function () {
      var style = $(this).attr("style");
      style = typeof style === "undefined" ? "" : style;
      style = style.trim();
      style = style.length && style.slice(-1) !== ";" ? style + ";" : style;
      style = style + "display: inline-block !important";
      $(this).attr("style", style);
    });
  }
}

initVoiceRecorder();

addPageHook("initVoiceRecorder");

addPageHook("friendsListEvents");

addPageHook("initMaps");

addPageHook("initCropAvatar");

$(function () {
  $(document).on("click", "#gdpr .close", function () {
    setCookie("gdpr-acknowledged", 1);
    $("#gdpr").remove();
  });

  friendsListEvents();

  if (!sessionTimezone) {
    var time = new Date();
    var offset = -time.getTimezoneOffset() / 60;
    $.ajax({
      type: "POST",
      url: baseUrl + "timezone/set",
      data: { offset: offset },
      success: function () {
        // location.reload();
      },
    });
  }

  $(document).on(
    "click",
    ".profileExperience .eListAdd .eListAddButton",
    function () {
      let e = $(this);
      e.hide();
      e.closest(".profileExperience").find(".workplaceForm").show();
    }
  );

  $(document).on("click", ".experienceActions .experienceEdit", function () {
    let e = $(this);
    let type = e.data("type");
    let id = e.data("id");
    let action = "edit";
    let load = "load";
    let container = $("#" + type + "-" + id);
    container.find(".workExperience").css("opacity", "0.5");
    $.ajax({
      type: "POST",
      url: baseUrl + "education/add",
      data: { action: action, id: id, type: type, load: load },
      success: function (res) {
        container.find(".workExperience").css("opacity", "1").hide();
        let data = JSON.parse(res);
        container.append(data.content);
        container.find("#workplaceForm-" + id).show();
      },
    });
    return false;
  });

  $(document).on("click", "#position-suggestion a", function () {
    let e = $(this);
    let name = e.data("name");
    $("#work-position").val(name);
    $("#position-suggestion").hide();
    return false;
  });

  $(document).on("click", "#company-suggestion a", function () {
    let e = $(this);
    let name = e.data("name");
    $("#work-company").val(name);
    $("#company-suggestion").hide();
    return false;
  });

  $(document).on("click", "#college-suggestion a", function () {
    let e = $(this);
    let name = e.data("name");
    $("#college-school-input").val(name);
    $("#college-suggestion").hide();
    return false;
  });

  $(document).on("click", "#high-school-suggestion a", function () {
    let e = $(this);
    let name = e.data("name");
    $("#high-school-input").val(name);
    $("#high-school-suggestion").hide();
    return false;
  });

    $(document).on('click', '.experienceActions .experienceDelete', function () {
        let e = $(this);
        let type = e.data('type');
        let id = e.data('id');
        let action = 'delete';
        let container = $('#' + type + '-' + id);
        container.find('.workExperience').css('opacity', '0.5');
        $.ajax({
            type: 'POST',
            url: baseUrl + 'education/add',
            data: {action: action, id: id, type: type},
            success: function (res) {
                let data = JSON.parse(res);
                if (data.status == '1') {
                    container.remove();
                }
            }
        });
        return false;
    });


    $(document).on('click', '#add-workplace-cancel, #workplaceForm .eCancelBody', function () {
        $('.workplaceForm').hide();
        $('.profileExperience .eListAdd .eListAddButton').show();
        let e = $(this);
        let attr = e.attr('data-id');
        if (typeof attr !== typeof undefined && attr !== false) {
            let id = e.data('id');
            $('#workplaceForm-' + id).hide();
        }
    });


    $(".checkboxEdu").change(function () {
        if ($(this).prop('checked')) {
            let e = $(this);
            $('.not-present-container').find(' .not-present').hide().val('1');
        } else {
            $('.not-present-container').find(' .not-present').show().val('0');
        }
    });

    Hook.register('ajax.form.submit.success', function (result, form, request, response) {
        var data = JSON.parse(response);
        if (typeof data === 'string') {
            data = JSON.parse(data);
        }
        if (data.data) {
            let res = data.data;
            if (res.action == 'add') {
                if (res.type == 'work') {
                    $('.workplaceForm').hide();
                    $('.profileExperience .eListAdd .eListAddButton').show();
                    $('#workProfileExperience').append(res.content).focus();
                } else if (res.type == 'college') {
                    $('.workplaceForm').hide();
                    $('.profileExperience .eListAdd .eListAddButton').show();
                    $('#collegeProfileExperience').append(res.content).focus();
                } else if (res.type == 'high') {
                    $('.workplaceForm').hide();
                    $('.profileExperience .eListAdd .eListAddButton').show();
                    $('#highProfileExperience').append(res.content).focus();
                }
            } else if (res.action == 'edit') {
                $('#workplaceForm-' + res.id).hide();
                var container = $('#' + res.type + '-' + res.id);
                container.html(res.content).focus();
            }
        }
    });


  $(document).on(
    "click",
    "#add-workplace-cancel, #workplaceForm .eCancelBody",
    function () {
      $(".workplaceForm").hide();
      $(".profileExperience .eListAdd .eListAddButton").show();
      let e = $(this);
      let attr = e.attr("data-id");
      if (typeof attr !== typeof undefined && attr !== false) {
        let id = e.data("id");
        $("#workplaceForm-" + id).hide();
      }
    }
  );

  $(".checkboxEdu").change(function () {
    if ($(this).prop("checked")) {
      let e = $(this);
      $(".not-present-container").find(" .not-present").hide().val("1");
    } else {
      $(".not-present-container").find(" .not-present").show().val("0");
    }
  });

  Hook.register("ajax.form.submit.success", function (
    result,
    form,
    request,
    response
  ) {
    var data = JSON.parse(response);
    if (typeof data === "string") {
      data = JSON.parse(data);
    }
    if (data.data) {
      let res = data.data;
      if (res.action == "add") {
        if (res.type == "work") {
          $(".workplaceForm").hide();
          $(".profileExperience .eListAdd .eListAddButton").show();
          $("#workProfileExperience").append(res.content).focus();
        } else if (res.type == "college") {
          $(".workplaceForm").hide();
          $(".profileExperience .eListAdd .eListAddButton").show();
          $("#collegeProfileExperience").append(res.content).focus();
        } else if (res.type == "high") {
          $(".workplaceForm").hide();
          $(".profileExperience .eListAdd .eListAddButton").show();
          $("#highProfileExperience").append(res.content).focus();
        }
      } else if (res.action == "edit") {
        $("#workplaceForm-" + res.id).hide();
        var container = $("#" + res.type + "-" + res.id);
        container.html(res.content).focus();
      }
    }
  });


    // ajax-form
    $(document).on('submit', 'form.ajax-form', function (e) {
        e.preventDefault();
        var form = $(this);
        var request = {};
        var url = form.attr('action');
        url = url ? url : window.location.href;
        request.url = url;
        request.path = url.replace(new RegExp('^' + baseUrl.replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + '/' + '-]', 'g'), '\\$&'), 'gi'), '').split('#')[0].split('?')[0].replace(/\/$/gi, '');
        request.query = url.indexOf('?') >= 0 ? url.split('#')[0].split('?').pop() : null;
        request.fragment = url.indexOf('#') >= 0 ? url.split('#').pop() : null;
        document.activeElement.blur();
        if (!form.find('div.ajax-form-loading').length) {
            form.prepend('<div class="ajax-form-loading"></div>');
        }
        if (!form.find('input[name="ajax"]').length) {
            form.prepend('<input type="hidden" name="ajax" value="1" />');
        }

        form.find('div.ajax-form-loading').fadeIn('fast');

		let formData = new FormData(form.get(0));
		form.find('.data-url-to-binary').each(function(index, input) {
			let name = $(input).attr('name');
			let dataURI = $(input).val();
			if(dataURI) {
				let byteString = atob(dataURI.split(',')[1]);
				let mimeType = dataURI.split(',')[0].split(':')[1].split(';')[0];
				let fileName = $(input).data('file-name') ? $(input).data('file-name') : 'file.' + mimeType.split('/')[1];
				let arrayBuffer = new ArrayBuffer(byteString.length);
				let uint8Array = new Uint8Array(arrayBuffer);
				for (let i = 0; i < byteString.length; i++) {
					uint8Array[i] = byteString.charCodeAt(i);
				}
				let blob = new Blob([arrayBuffer], {type: mimeType});
				formData.set(name, blob, fileName);
			}
		});

		$.ajax({
			url: url,
			method: 'post',
			data: formData,
			processData: false,
			contentType: false,
			beforeSerialize: function (jqForm, options) {
				Hook.fire('ajax.form.submit.serialize.before', null, [form.get(0), request, jqForm, options]);
			},
			beforeSend: function (xhr, options) {
				Hook.fire('ajax.form.submit.before', null, [form.get(0), request, xhr, options]);
			},
			onabort: function () {
				var handled = Hook.fire('ajax.form.submit.abort', false, [form.get(0), request]);
				if (!handled) {
					form.find('div.ajax-form-loading').fadeOut('fast');
				}
			},
			success: function (response) {
				Hook.fire('ajax.form.submit.success', null, [form.get(0), request, response]);
				var data = JSON.parse(response);
				if (typeof data === 'string') {
					data = JSON.parse(data);
				}
				if (typeof data.message !== 'undefined' && data.message) {
					notify(data.message, data.status ? 'success' : 'error');
				}
				if (typeof data.redirect_url !== 'undefined' && data.redirect_url) {
					if (data.reload) {
						window.location.href = data.redirect_url;
					} else {
						loadPage(data.redirect_url);
					}
				} else {
					form.find('div.ajax-form-loading').fadeOut('fast');
				}
			},
			error: function (error) {
				Hook.fire('ajax.form.submit.error', null, [form.get(0), request, error]);
				notify(ajaxFormSubmitError, 'error');
				form.find('div.ajax-form-loading').fadeOut('fast');
			}
		});
		return false;
	});

  var splash02Auth = $(".splash-02 .auth");
  if (splash02Auth.length) {
    document.documentElement.style.setProperty(
      "--splash-02-auth-height",
      splash02Auth.height() + "px"
    );
  }

  $(document).on("submit", "#side-menu-shortcuts-form", function (e) {
    e.preventDefault();
    var form = $(this);
    form.ajaxSubmit({
      url: baseUrl + "menu/shortcuts/save",
      method: "POST",
      success: function (response) {
        $("#side-shortcut-menu-modal").modal("hide");
        $("#side-shortcut-menu-modal").remove();
        $(".modal-backdrop").remove();
        $("body").removeClass("modal-open");
        $("#side-menu-shortcuts").html(response);
        $("#side-shortcut-menu-modal").appendTo("body");
      },
      error: function (error) {},
    });
  });

  $("#side-shortcut-menu-modal").appendTo("body");

  Hook.fire("night-mode", null, [$("body").hasClass("night-mode")]);
  $(document).on("click", ".night-mode-toggle", function (e) {
    e.preventDefault();
    var status = $("body").hasClass("night-mode");
    if (status) {
      $("body").removeClass("night-mode");
      setCookie("night-mode", 0);
      Hook.fire("night-mode", null, [false]);
    } else {
      $("body").addClass("night-mode");
      setCookie("night-mode", 1);
      Hook.fire("night-mode", null, [true]);
    }
  });

  $(document).on("click", ".splash-04 .actions .scroll-animate", function (e) {
    e.preventDefault();
    var type = $(this).data("type");
    var target = $(this).attr("href");
    type === "login" ? show_home_content(true) : show_home_content(false);
    $("html, body").animate({ scrollTop: $(target).offset().top + 1 }, 200);
  });

  if (typeof phrases !== "undefined") {
    language.addPhrases(phrases);
  }

  $(document).on(
    "change",
    '.phone-number-input input[type="hidden"].code',
    function (e) {
      var phoneNumberInput = $(this).closest(".phone-number-input");
      var code = e.target.value;
      var number = $(phoneNumberInput).find('input[type="text"].number').val();
      var phoneNo = (
        String(code) + Number(String(number).replace(/[^\d]/g, ""))
      ).replace(/[^\d]/g, "");
      $(phoneNumberInput).find(".phone-no").val(phoneNo);
      console.log(code);
      console.log(number);
      console.log(phoneNo);
    }
  );

  $(document).on(
    "change",
    '.phone-number-input input[type="text"].number',
    function (e) {
      var phoneNumberInput = $(this).closest(".phone-number-input");
      var number = e.target.value;
      var code = $(phoneNumberInput).find('input[type="hidden"].code').val();
      var phoneNo = (
        String(code) + Number(String(number).replace(/[^\d]/g, ""))
      ).replace(/[^\d]/g, "");
      $(phoneNumberInput).find(".phone-no").val(phoneNo);
      console.log(code);
      console.log(number);
      console.log(phoneNo);
    }
  );

  Hook.register("verify-phone-no.content.loaded", function (result, url, data) {
    if (window.verifyPhoneNoResendButtonInterval) {
      clearInterval(window.verifyPhoneNoResendButtonInterval);
    }
    window.verifyPhoneNoResendButtonInterval = setInterval(function () {
      let button = $(
        ".account-verify-wrapper .phone-no-verify a.resend-button"
      );
      let timeout = parseInt(button.data("timeout"));
      timeout--;
      if (timeout > 0) {
        button.data("timeout", timeout);
        button.find(".countdown").html(timeout);
      } else {
        button.removeClass("disabled");
        button.data("timeout", button.data("timeout-original"));
        button.find(".countdown").html(button.data("timeout-original"));
        clearInterval(window.verifyPhoneNoResendButtonInterval);
      }
    }, 1000);
  });

  $(document).on("shown.bs.modal", ".verify-phone-no-modal", function (e) {
    let invoker = $(e.relatedTarget);
    let modal = $(".verify-phone-no-modal");
    let previousUrl = $(modal).data("url");
    let url = $(invoker).attr("href");
    let modalTitle = $(modal).find(".modal-title");
    let modalContent = $(modal).find(".modal-content");
    let modalBody = $(modal).find(".modal-body");
    if (!modalContent.find("div.verify-phone-no-modal-ajax-loading").length) {
      modalContent.prepend(
        '<div class="verify-phone-no-modal-ajax-loading"></div>'
      );
    }
    if (url && url !== previousUrl) {
      modalTitle.html($(invoker).data("title"));
      modalBody.html("");
      $.ajax({
        url:
          url +
          (url.indexOf("?") >= 0 ? "&" : "?") +
          "ajax=true&csrf_token=" +
          requestToken,
        beforeSend: function () {
          modalContent
            .find("div.verify-phone-no-modal-ajax-loading")
            .fadeIn("fast");
        },
        success: function (response) {
          let data = JSON.parse(response);
          $(modalTitle).html(data.title);
          $(modalBody).html(data.html);
          if (typeof reloadInits === "function") {
            reloadInits();
          }
          $(modal).data("url", url);
          $(modal).attr("data-url", url);
          if (typeof data.message !== "undefined" && data.message) {
            notify(data.message, data.status ? "success" : "error");
          }
          if (typeof data.redirect_url !== "undefined" && data.redirect_url) {
            window.location.href = data.redirect_url;
          }
          Hook.fire("verify-phone-no.content.loaded", null, [url, data]);
          modalContent
            .find("div.verify-phone-no-modal-ajax-loading")
            .fadeOut("fast");
        },
        error: function (error) {
          notify(language.phrase("modal-load-error"), "error");
          modalContent
            .find("div.verify-phone-no-modal-ajax-loading")
            .fadeOut("fast");
        },
      });
    } else {
      Hook.fire("verify-phone-no.content.loaded", null, [url, false]);
    }
  });

  $(document).on("hide.bs.modal", ".verify-phone-no-modal", function (e) {});

  $(document).on("click", ".verify-phone-no-modal a.resend-button", function (
    e
  ) {
    $(e.target).addClass("disabled");
    e.preventDefault();
    let modal = $(".verify-phone-no-modal");
    let url = $(e.target).attr("href");
    let modalTitle = $(modal).find(".modal-title");
    let modalContent = $(modal).find(".modal-content");
    let modalBody = $(modal).find(".modal-body");
    if (!modalContent.find("div.verify-phone-no-modal-ajax-loading").length) {
      modalContent.prepend(
        '<div class="verify-phone-no-modal-ajax-loading"></div>'
      );
    }
    $.ajax({
      url:
        baseUrl +
        "account/verify/phone-no?ajax=true&csrf_token=" +
        requestToken,
      beforeSend: function () {
        modalContent
          .find("div.verify-phone-no-modal-ajax-loading")
          .fadeIn("fast");
      },
      success: function (response) {
        let data = JSON.parse(response);
        $(modalTitle).html(data.title);
        $(modalBody).html(data.html);
        if (typeof reloadInits === "function") {
          reloadInits();
        }
        $(modal).data("url", url);
        $(modal).attr("data-url", url);
        if (typeof data.message !== "undefined" && data.message) {
          notify(data.message, data.status ? "success" : "error");
        }
        if (typeof data.redirect_url !== "undefined" && data.redirect_url) {
          window.location.href = data.redirect_url;
        }
        Hook.fire("verify-phone-no.content.loaded", null, [url, data]);
        modalContent
          .find("div.verify-phone-no-modal-ajax-loading")
          .fadeOut("fast");
      },
      error: function (error) {
        notify(language.phrase("modal-load-error"), "error");
        modalContent
          .find("div.verify-phone-no-modal-ajax-loading")
          .fadeOut("fast");
      },
    });
  });

  $(document).on("click", ".verify-phone-no-modal button.close", function (e) {
    let message = language.phrase("verify-phone-no-close-confirm");
    $("#confirmModal #confirm-button")
      .unbind()
      .click(function () {
        $("#confirmModal").modal("hide");
        $(".verify-phone-no-modal").modal("hide");
      });
    $("#confirmModal .modal-body").html(message);
    $("#confirmModal").modal("show");
  });

  Hook.register("verify-email.content.loaded", function (result, url, data) {
    if (window.verifyEmailResendButtonInterval) {
      clearInterval(window.verifyEmailResendButtonInterval);
    }
    window.verifyEmailResendButtonInterval = setInterval(function () {
      let button = $(".account-verify-wrapper .email-verify a.resend-button");
      let timeout = parseInt(button.data("timeout"));
      timeout--;
      if (timeout > 0) {
        button.data("timeout", timeout);
        button.find(".countdown").html(timeout);
      } else {
        button.removeClass("disabled");
        button.data("timeout", button.data("timeout-original"));
        button.find(".countdown").html(button.data("timeout-original"));
        clearInterval(window.verifyEmailResendButtonInterval);
      }
    }, 1000);
  });

  $(document).on("shown.bs.modal", ".verify-email-modal", function (e) {
    let invoker = $(e.relatedTarget);
    let modal = $(".verify-email-modal");
    let previousUrl = $(modal).data("url");
    let url = $(invoker).attr("href");
    let modalTitle = $(modal).find(".modal-title");
    let modalContent = $(modal).find(".modal-content");
    let modalBody = $(modal).find(".modal-body");
    if (!modalContent.find("div.verify-email-modal-ajax-loading").length) {
      modalContent.prepend(
        '<div class="verify-email-modal-ajax-loading"></div>'
      );
    }
    if (url && url !== previousUrl) {
      modalTitle.html($(invoker).data("title"));
      modalBody.html("");
      $.ajax({
        url:
          url +
          (url.indexOf("?") >= 0 ? "&" : "?") +
          "ajax=true&csrf_token=" +
          requestToken,
        beforeSend: function () {
          modalContent
            .find("div.verify-email-modal-ajax-loading")
            .fadeIn("fast");
        },
        success: function (response) {
          let data = JSON.parse(response);
          $(modalTitle).html(data.title);
          $(modalBody).html(data.html);
          if (typeof reloadInits === "function") {
            reloadInits();
          }
          $(modal).data("url", url);
          $(modal).attr("data-url", url);
          if (typeof data.message !== "undefined" && data.message) {
            notify(data.message, data.status ? "success" : "error");
          }
          if (typeof data.redirect_url !== "undefined" && data.redirect_url) {
            window.location.href = data.redirect_url;
          }
          Hook.fire("verify-email.content.loaded", null, [url, data]);
          modalContent
            .find("div.verify-email-modal-ajax-loading")
            .fadeOut("fast");
        },
        error: function (error) {
          notify(language.phrase("modal-load-error"), "error");
          modalContent
            .find("div.verify-email-modal-ajax-loading")
            .fadeOut("fast");
        },
      });
    } else {
      Hook.fire("verify-email.content.loaded", null, [url, false]);
    }
  });

  $(document).on("hide.bs.modal", ".verify-email-modal", function (e) {});

  $(document).on("click", ".verify-email-modal a.resend-button", function (e) {
    $(e.target).addClass("disabled");
    e.preventDefault();
    let modal = $(".verify-email-modal");
    let url = $(e.target).attr("href");
    let modalTitle = $(modal).find(".modal-title");
    let modalContent = $(modal).find(".modal-content");
    let modalBody = $(modal).find(".modal-body");
    if (!modalContent.find("div.verify-email-modal-ajax-loading").length) {
      modalContent.prepend(
        '<div class="verify-email-modal-ajax-loading"></div>'
      );
    }
    $.ajax({
      url:
        baseUrl + "account/verify/email?ajax=true&csrf_token=" + requestToken,
      beforeSend: function () {
        modalContent.find("div.verify-email-modal-ajax-loading").fadeIn("fast");
      },
      success: function (response) {
        let data = JSON.parse(response);
        $(modalTitle).html(data.title);
        $(modalBody).html(data.html);
        if (typeof reloadInits === "function") {
          reloadInits();
        }
        $(modal).data("url", url);
        $(modal).attr("data-url", url);
        if (typeof data.message !== "undefined" && data.message) {
          notify(data.message, data.status ? "success" : "error");
        }
        if (typeof data.redirect_url !== "undefined" && data.redirect_url) {
          window.location.href = data.redirect_url;
        }
        Hook.fire("verify-email.content.loaded", null, [url, data]);
        modalContent
          .find("div.verify-email-modal-ajax-loading")
          .fadeOut("fast");
      },
      error: function (error) {
        notify(language.phrase("modal-load-error"), "error");
        modalContent
          .find("div.verify-email-modal-ajax-loading")
          .fadeOut("fast");
      },
    });
  });

  $(document).on("click", ".verify-email-modal button.close", function (e) {
    let message = language.phrase("verify-email-close-confirm");
    $("#confirmModal #confirm-button")
      .unbind()
      .click(function () {
        $("#confirmModal").modal("hide");
        $(".verify-email-modal").modal("hide");
      });
    $("#confirmModal .modal-body").html(message);
    $("#confirmModal").modal("show");
  });

  var accountVerifyPageHook = function () {
    if (window.location.href.match(/login\/verify/g)) {
      if ($(".account-verify-wrapper").data("verify-type") === "phone_no") {
        Hook.fire("verify-phone-no.content.loaded", null, [
          window.location.href,
        ]);
      } else {
        Hook.fire("verify-email.content.loaded", null, [window.location.href]);
      }
    }
  };
  accountVerifyPageHook();
  addPageHook(accountVerifyPageHook);

  window.audio.init();

  addPageHook("window.audio.init");

  Hook.register("page.reload.init.after", window.audio.init);
  Hook.register("page.reload.init.after", function () {
    Hook.fire("night-mode", null, [$("body").hasClass("night-mode")]);
  });
  Hook.register("text-editor.init.after", function () {
    Hook.fire("night-mode", null, [$("body").hasClass("night-mode")]);
  });
});

addAvatarChangeHook(function (data) {
  $(".profile-image").attr("src", data.image);
  $("#profile-image-viewer").data("id", data.id);
  $("#profile-image-viewer").attr("data-id", data.id);
  $("#profile-image-viewer").data("image", data.large);
  $("#profile-image-viewer").attr("data-image", data.large);
});

function toggleContent(id) {
  let o = $(id);
  if (o.css("display") == "none") {
    o.show();
  } else {
    o.hide();
  }
  return false;
}

function educationClose(e) {
  let event = $(e);
  event.closest(".box").remove();
  let id = event.data("id");
  let type = event.data("type");
  let container = $("#" + type + "-" + id);
  console.log(type + "-" + id);
  container.find(".workExperience").show().focus();
  return false;
}
