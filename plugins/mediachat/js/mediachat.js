if (typeof window.mediaChat === "undefined") {
  window.mediaChat = {
    id: 0,
    type: "voice",
    isCaller: null,
    state: null,
    userName: null,
    userAvatar: null,
    liveConfig: {
      constraint: { audio: true, video: true },
      muted: true,
      record: false,
      audioDevice: null,
      videoDevice: null,
    },
    live: null,
    iceServers: [
      { urls: "stun:stun.l.google.com:19302" },
      { urls: "stun:stun.services.mozilla.com:3478" },
    ],
    iceTransportPolicy: "all",
    peerConnection: null,
    peerConnections: {},
    sessionDescription: null,
    connectionTimeout: mediaChatConnectionTimeout || 30,
    pollInterval: 5000,
    endTimeout: null,
    time: 0,
    timeUpdate: null,
    ringtone: new Audio(baseUrl + "plugins/mediachat/audio/ring.mp3"),
    humtone: new Audio(baseUrl + "plugins/mediachat/audio/hum.mp3"),
    localAudio: null,
    localVideo: null,
    remoteAudio: null,
    remoteVideo: null,
    localVideoEnabled: null,
    localAudioEnabled: null,
    remoteVideoEnabled: null,
    remoteAudioEnabled: null,
    fullscreenEnabled: null,
    availableFriends: {},

    init: function () {
      mediaChat.pollInterval = ajaxInterval || mediaChat.pollInterval;
      setInterval(mediaChat.streamSupportUpdate, mediaChat.pollInterval);
      mediaChat.addPhrases();
      mediaChat.addEvents();
      Pusher.addHook("mediaChat.push");
    },

    addEvents: function () {
      $(document).on("click", ".mediachat-call-button", function (e) {
        e.preventDefault();
        if (mediaChat.supported()) {
          let streamSupport = !parseInt(
            e.currentTarget.getAttribute("data-disabled")
          );
          if (streamSupport) {
            let userId = e.currentTarget.getAttribute("data-user-id");
            let type = e.currentTarget.getAttribute("data-type");
            mediaChat.make(userId, type);
          } else {
            notify(e.currentTarget.getAttribute("title"), "warning");
          }
        } else {
          notify(language.phrase("device-not-supported"), "warning");
        }
      });

      $(document).on("click", "#mediachat-receive-button", function (e) {
        e.preventDefault();
        let id = e.currentTarget.getAttribute("data-call-id");
        mediaChat.receive(id);
      });

      $(document).on("click", "#mediachat-drop-button", function (e) {
        e.preventDefault();
        mediaChat.drop();
      });

      $(document).on("click", "#mediachat-end", function (e) {
        e.preventDefault();
        mediaChat.end();
      });

      $(document).on("click", "#mediachat-toggle-video", function (e) {
        e.preventDefault();
        mediaChat.toggleVideo();
      });

      $(document).on("click", "#mediachat-toggle-voice", function (e) {
        e.preventDefault();
        mediaChat.toggleAudio();
      });

      $(document).on("click", "#mediachat-toggle-fullscreen", function (e) {
        e.preventDefault();
        mediaChat.toggleFullscreen();
      });

      $(document).on("click", "[data-conference-id]", function (e) {
        e.preventDefault();
        let id = e.target.getAttribute("data-conference-id");
        mediaChat.apiCall(id);
      });

      $(document).on("shown.bs.modal", ".mediachat-modal", function (e) {
        let invoker = $(e.relatedTarget);
        let modal = this;
        let previousUrl = $(modal).data("url");
        let url = $(invoker).attr("href");
        let modalTitle = $(modal).find(".modal-title");
        let modalContent = $(modal).find(".modal-content");
        let modalBody = $(modal).find(".modal-body");
        if (!modalContent.find("div.mediachat-modal-ajax-loading").length) {
          modalContent.prepend(
            '<div class="mediachat-modal-ajax-loading"></div>'
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
            beforeSend: function (response) {
              modalContent
                .find("div.mediachat-modal-ajax-loading")
                .fadeIn("fast");
            },
            success: function (response) {
              let data = JSON.parse(response);
              $(modalTitle).html(data.title);
              $(modalBody).html(data.html);
              $(modal).data("url", url);
              $(modal).attr("data-url", url);
              let form = modalBody.find("form");
              form.addClass("ajax-form");
              if (typeof data.message !== "undefined" && data.message) {
                notify(data.message, data.status ? "success" : "error");
              }
              if (
                typeof data.redirect_url !== "undefined" &&
                data.redirect_url
              ) {
                window.location.href = data.redirect_url;
              } else {
                form.find("div.ajax-form-loading").fadeOut("fast");
              }
              modalContent
                .find("div.mediachat-modal-ajax-loading")
                .fadeOut("fast");
            },
            error: function (error) {
              notify("Error occurred while loading modal", "error");
              modalContent
                .find("div.mediachat-modal-ajax-loading")
                .fadeOut("fast");
            },
          });
        }
      });

      $(document).on("hide.bs.modal", ".mediachat-modal", function (e) {
        let invoker = $(e.relatedTarget);
      });

      $(document).on("submit", ".mediachat-form.ajax-form", function (e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr("action");
        url = url ? url : window.location.href;
        document.activeElement.blur();
        if (!form.find("div.ajax-form-loading").length) {
          form.prepend('<div class="ajax-form-loading"></div>');
        }
        if (!form.find('input[name="ajax"]').length) {
          form.prepend('<input type="hidden" name="ajax" value="1" />');
        }

        form.find("div.ajax-form-loading").fadeIn("fast");

        form.ajaxSubmit({
          url: url,
          success: function (data) {
            data = JSON.parse(data);
            if (typeof data === "string") {
              data = JSON.parse(data);
            }
            if (typeof data.message !== "undefined" && data.message) {
              notify(data.message, data.status ? "success" : "error");
            }
            if (typeof data.redirect_url !== "undefined" && data.redirect_url) {
              document.location.href = data.redirect_url;
            } else {
              let action =
                typeof data.action !== "undefined" && data.action
                  ? data.action
                  : "";
              switch (action) {
                case "add":
                  break;

                case "edit":
                  break;

                case "delete":
                  break;

                default:
                  break;
              }
              form.find("div.ajax-form-loading").fadeOut("fast");
            }
          },
          error: function (error) {
            notify("Error occurred while submitting form", "error");
            form.find("div.ajax-form-loading").fadeOut("fast");
          },
        });
        return false;
      });

      $(document).on("change", ".server-input-type", function (e) {
        let type = $(this).val();
        let form = $(this).closest("form");
        $(form).data("server-type", type);
        $(form).attr("data-server-type", type);
      });

      Hook.register("mediachat.friends.available", function (
        result,
        container,
        userId
      ) {
        container = container || document;
        container
          .querySelectorAll(".mediachat-call-button")
          .forEach(function (button) {
            let uId = parseInt(userId)
              ? userId
              : button.getAttribute("data-user-id");
            button.setAttribute("data-user-id", uId);
            button.setAttribute(
              "data-disabled",
              !!parseInt(mediaChat.availableFriends[uId]) ? 0 : 1
            );
            button.getAttribute("data-type") === "video"
              ? button.setAttribute(
                  "title",
                  mediaChat.availableFriends[uId]
                    ? language.phrase("video-call")
                    : language.phrase("user-unavailable-video-call")
                )
              : button.setAttribute(
                  "title",
                  mediaChat.availableFriends[uId]
                    ? language.phrase("voice-call")
                    : language.phrase("user-unavailable-voice-call")
                );
          });
      });
    },

    callNewUser: function (chat_id) {
      var pc = new RTCPeerConnection({
        iceTransportPolicy: mediaChat.iceTransportPolicy,
        iceServers: mediaChat.iceServers,
      });

      pc.ontrack = function (e) {
        var video = mediaChat.remoteVideo.cloneNode(true);
        if (video.srcObject !== e.streams[0]) {
          video.id = e.streams[0].id;
          video.srcObject = e.streams[0];
          console.log(video);
          document.getElementById("mediachat-remote-media").appendChild(video);
        }
      };

      pc.onicecandidate = function (e) {
        pc.addIceCandidate(e.candidate).then(
          function () {
            mediaChat.sendMessage({
              type: "mediachat.call.ice.candidate",
              id: chat_id,
              data: e.candidate,
            });
            console.log("ICECandidate Added successfully!");
          },
          function (e) {
            console.log(`Failed to add ICE candidate: ${e.toString()}`);
          }
        );
      };

      pc.createOffer()
        .then(
          function (desc) {
            pc.setLocalDescription(desc);
            pc.setRemoteDescription(desc);
            pc.createAnswer().then(
              function (rdesc) {
                pc.setLocalDescription(rdesc);
                pc.setRemoteDescription(rdesc);
              },
              function (error) {
                console.log(
                  `Failed to create session description: ${error.toString()}`
                );
              }
            );
          },
          function (error) {
            console.log(
              `Failed to create session description: ${error.toString()}`
            );
          }
        )
        .then(function () {
          mediaChat.sendMessage({
            type: "mediachat.call.session.description",
            id: chat_id,
            data: pc.localDescription,
          });
        });
    },
    apiCall: function (id) {
      var form = {
        is_caller: 1,
        user_id: id,
        chat_id: mediachat.id,
        type: "video",
      };
      $.ajax({
        type: "POST",
        url: baseUrl + "mediachat/conference",
        data: form,
        success: function (res) {
          res = JSON.parse(res);
          if (!res.success) {
            alert("User could not be called!");
          } else {
            return mediaChat.callNewUser(res.id);
          }
        },
        error: function (error) {
          console.log(error, error.data);
          alert(error.message);
        },
      });
    },

    addPhrases: function () {
      if (typeof mediaChatPhrases !== "undefined") {
        language.addPhrases(mediaChatPhrases);
      }
    },

    sendMessage: function (data, uniqueProperty) {
      if (Pusher && typeof Pusher.sendMessage === "function") {
        Pusher.sendMessage(data, uniqueProperty);
      }
    },

    make: function (userId, type) {
      mediaChat.state = "calling";
      let form = document.getElementById("mediachat-call-form");
      type = type || "voice";
      form.querySelector('[name="id"]').value = undefined;
      form.querySelector('[name="is_caller"]').value = 1;
      form.querySelector('[name="type"]').value = type;
      form.querySelector('[name="user_id"]').value = userId;
      form.querySelector('[name="session_description"]').value = undefined;
      window.open(
        "",
        "mediachat-call",
        "width = 870, height = 651, scrollbars = no"
      );
      form.submit();
      mediaChat.state = null;
    },

    receive: function (id) {
      if (mediaChat.state === "prompting") {
        mediaChat.state = null;
        document.getElementById("mediachat-receive-container").style.display =
          "none";
        let form = document.getElementById("mediachat-call-form");
        mediaChat.ringtone.removeEventListener("ended", mediaChat.playRingtone);
        form.querySelector('[name="id"]').value = id;
        form.querySelector('[name="is_caller"]').value = 0;
        form.querySelector('[name="type"]').value = undefined;
        form.querySelector('[name="user_id"]').value = undefined;
        form.querySelector('[name="session_description"]').value = undefined;
        window.open(
          "",
          "mediachat-call",
          "width = 870, height = 651, scrollbars = no"
        );
        form.submit();
      }
    },

    prompt: function (data) {
      mediaChat.state = "prompting";
      mediaChat.id = data.id;
      mediaChat.ring();
      document
        .getElementById("mediachat-receive-button")
        .setAttribute("data-call-id", mediaChat.id);
      document.getElementById("mediachat-receiver-instruction").innerHTML =
        "Incoming " + data.type + " Call";
      document.getElementById("mediachat-caller-name").innerHTML =
        data.user_name;
      document.getElementById("mediachat-caller-avatar").src = data.user_avatar;
      document.getElementById("mediachat-receive-container").style.display =
        "flex";
    },

    drop: function () {
      if (mediaChat.state === "prompting") {
        mediaChat.state = null;
        mediaChat.ringtone.removeEventListener("ended", mediaChat.playRingtone);
        mediaChat.ringtone.removeEventListener("ended", mediaChat.playHumtone);
        document.getElementById("mediachat-receive-container").style.display =
          "none";
      }
    },

    end: function () {
      if (
        mediaChat.state === "calling" ||
        mediaChat.state === "connecting" ||
        mediaChat.state === "connected"
      ) {
        mediaChat.state = "ending";
        if (mediaChat.fullscreenEnabled) {
          mediaChat.disableFullscreen();
        }
        mediaChat.stopTimer();
        document.getElementById("mediachat-time").innerHTML =
          "Disconnecting...";
        if (mediaChat.peerConnection) {
          mediaChat.peerConnection.getLocalStreams().forEach(function (stream) {
            stream.getTracks().forEach(function (track) {
              track.stop();
            });
          });
          mediaChat.peerConnection.close();
          mediaChat.peerConnection = null;
        }

        if (mediaChat.live) {
          mediaChat.live.end();
        }

        $.getJSON(
          baseUrl +
            "mediachat/call/end?id=" +
            mediaChat.id +
            "&csrf_token=" +
            requestToken,
          function (data) {
            window.close();
          }
        );
        mediaChat.state = null;
      }
    },

    initCall: function (data) {
      mediaChat.state = "calling";
      window.onbeforeunload = function () {
        mediaChat.end();
      };

      mediaChat.hum();

      mediaChat.localAudio = document.querySelector("#mediachat-local-audio");
      mediaChat.localVideo = document.querySelector("#mediachat-local-video");
      mediaChat.remoteAudio = document.querySelector("#mediachat-remote-audio");
      mediaChat.remoteVideo = document.querySelector("#mediachat-remote-video");

      mediaChat.localAudio.muted = true;
      mediaChat.localVideo.muted = true;
      mediaChat.remoteAudio.muted = false;
      mediaChat.remoteVideo.muted = false;

      let onLoadedMetaData = function (e) {
        mediaChat.play(e.target);
      };

      mediaChat.localAudio.onloadedmetadata = onLoadedMetaData;
      mediaChat.localVideo.onloadedmetadata = onLoadedMetaData;
      mediaChat.remoteAudio.onloadedmetadata = onLoadedMetaData;
      mediaChat.remoteVideo.onloadedmetadata = onLoadedMetaData;

      mediaChat.id = data.id;
      mediaChat.sessionDescription = data.session_description;
      mediaChat.iceServers = data.ice_servers;
      mediaChat.iceTransportPolicy = data.ice_transport_policy;
      mediaChat.type = data.type;
      mediaChat.isCaller = data.is_caller;
      mediaChat.userName = data.user_name;
      mediaChat.userAvatar = data.user_avatar;
      mediaChat.localAudioEnabled = true;
      mediaChat.remoteAudioEnabled = true;
      mediaChat.liveConfig.constraint.audio = true;
      mediaChat.liveConfig.constraint.video =
        data.type === "video" ? { width: 640 } : false;
      mediaChat.localVideoEnabled = data.type === "video";
      mediaChat.remoteVideoEnabled = data.type === "video";
      mediaChat.localVideoEnabled = data.type === "video";
      mediaChat.remoteVideoEnabled = data.type === "video";

      if (!data.status) {
        notify(language.phrase("error-in-connection"), "error");
        mediaChat.end();
      }

      document.getElementById("mediachat-time").innerHTML = data.message;
      document.getElementById("mediachat-remote-avatar").style.backgroundImage =
        "url(" + mediaChat.userAvatar + ")";
      document.getElementById("mediachat-remote-info").innerHTML =
        mediaChat.userName;

      if (mediaChat.type === "voice") {
        $("#mediachat-remote-avatar").css("display", "inline-block");
      }

      mediaChat.peerConnection = new RTCPeerConnection({
        iceTransportPolicy: mediaChat.iceTransportPolicy,
        iceServers: mediaChat.iceServers,
      });

      mediaChat.peerConnection.onicecandidate = function (e) {
        if (e.candidate) {
          mediaChat.sendMessage({
            type: "mediachat.call.ice.candidate",
            id: mediaChat.id,
            data: e.candidate,
          });
          console.debug(
            "ICE (" +
              (e.candidate.candidate.indexOf("relay") < 0 ? "STUN" : "TURN") +
              ") Sent: ",
            e.candidate
          );
        }
      };

      mediaChat.peerConnection.oniceconnectionstatechange = function (e) {
        if (mediaChat.peerConnection) {
          switch (mediaChat.peerConnection.iceConnectionState) {
            case "connected":
              mediaChat.onParticipantConnected();
              break;

            case "completed":
              mediaChat.onConnected();
              break;

            case "failed":
              notify(language.phrase("error-in-connection"), "error");
              mediaChat.end();
              break;

            case "disconnected":
              mediaChat.onDisconnected();
              break;

            case "closed":
              mediaChat.onParticipantDisconnected();
              break;
          }
        }
      };

      mediaChat.peerConnection.ontrack = function (e) {
        mediaChat.onTrackAdded(e);
      };

      mediaChat.peerConnection.onremovetrack = function (e) {
        mediaChat.onTrackRemoved(e);
      };

      mediaChat.liveConfig.streamReadyCallback = function (live) {
        let tracks = live.stream.getTracks();
        tracks.forEach(function (track) {
          if (track.kind === "audio") {
            mediaChat.localAudio.srcObject = new MediaStream([track]);
          } else if (track.kind === "video") {
            mediaChat.localVideo.srcObject = new MediaStream([track]);
          }
        });

        mediaChat.peerConnection.addStream(mediaChat.live.stream);

        if (mediaChat.localAudioEnabled) {
          mediaChat.enableAudio();
        }

        if (mediaChat.localVideoEnabled) {
          mediaChat.enableVideo();
        }

        if (mediaChat.isCaller) {
          mediaChat.peerConnection
            .createOffer()
            .then(function (offer) {
              mediaChat.sessionDescription = offer;
              mediaChat.peerConnection.setLocalDescription(offer);
            })
            .then(function () {
              mediaChat.sendMessage({
                type: "mediachat.call.session.description",
                id: mediaChat.id,
                data: mediaChat.peerConnection.localDescription,
              });
              mediaChat.state = "connecting";
              if (mediaChat.endTimeout) {
                clearTimeout(mediaChat.endTimeout);
              }
              mediaChat.endTimeout = setTimeout(function () {
                mediaChat.end();
              }, (mediaChat.pollInterval / 1000 +
                mediaChat.connectionTimeout * 2) *
                1000);
            })
            .catch(function (reason) {
              console.log(reason);
            });
        } else {
          mediaChat.sessionDescription = data.session_description;
          mediaChat.peerConnection
            .setRemoteDescription(
              new RTCSessionDescription(mediaChat.sessionDescription)
            )
            .then(function () {
              return mediaChat.peerConnection.createAnswer();
            })
            .then(function (answer) {
              return mediaChat.peerConnection.setLocalDescription(answer);
            })
            .then(function () {
              mediaChat.state = "connecting";
              mediaChat.sendMessage({
                type: "mediachat.call.session.description",
                id: mediaChat.id,
                data: mediaChat.peerConnection.localDescription,
              });
            });
        }
      };

      mediaChat.live = window.live;
      mediaChat.live.init(mediaChat.liveConfig);
    },

    play: function (media) {
      let playPromise = media.play();
      playPromise !== undefined
        ? playPromise.then(function () {}).catch(function () {})
        : undefined;
    },

    hum: function () {
      mediaChat.playHumtone();
      mediaChat.humtone.addEventListener("ended", mediaChat.playHumtone);
    },

    playHumtone: function () {
      mediaChat.play(mediaChat.humtone);
    },

    ring: function () {
      mediaChat.playRingtone();
      mediaChat.ringtone.addEventListener("ended", mediaChat.playRingtone);
    },

    playRingtone: function () {
      mediaChat.play(mediaChat.ringtone);
    },

    startTimer: function () {
      mediaChat.time = 0;
      mediaChat.timeUpdate = setInterval(function () {
        document.getElementById("mediachat-time").innerHTML =
          ("00" + Math.floor(parseInt(mediaChat.time, 10) / 60)).slice(-2) +
          ":" +
          ("00" + (parseInt(mediaChat.time, 10) % 60)).slice(-2);
        mediaChat.time++;
      }, 1000);
    },

    stopTimer: function () {
      clearInterval(mediaChat.timeUpdate);
    },

    resetTimer: function () {
      document.getElementById("mediachat-time").innerHTML = "00:00";
    },

    enableAudio: function () {
      mediaChat.peerConnection.getLocalStreams().forEach(function (stream) {
        stream.getTracks().forEach(function (track) {
          if (track.kind === "audio") {
            track.enabled = true;
            mediaChat.localAudioEnabled = true;
          }
        });
      });
      $("#mediachat-toggle-voice").addClass("active");
    },

    disableAudio: function () {
      mediaChat.peerConnection.getLocalStreams().forEach(function (stream) {
        stream.getTracks().forEach(function (track) {
          if (track.kind === "audio") {
            track.enabled = false;
            mediaChat.localAudioEnabled = false;
          }
        });
      });
      $("#mediachat-toggle-voice").removeClass("active");
    },

    toggleAudio: function () {
      if (mediaChat.localAudioEnabled) {
        mediaChat.disableAudio();
      } else {
        mediaChat.enableAudio();
      }
    },

    enableVideo: function () {
      mediaChat.peerConnection.getLocalStreams().forEach(function (stream) {
        stream.getTracks().forEach(function (track) {
          if (track.kind === "video") {
            track.enabled = true;
            mediaChat.localVideoEnabled = true;
          }
        });
      });
      $("#mediachat-toggle-video").addClass("active");
      $("#mediachat-local").css("display", "inline-block");
    },

    disableVideo: function () {
      mediaChat.peerConnection.getLocalStreams().forEach(function (stream) {
        stream.getTracks().forEach(function (track) {
          if (track.kind === "video") {
            track.enabled = false;
            mediaChat.localVideoEnabled = false;
          }
        });
      });
      $("#mediachat-toggle-video").removeClass("active");
      $("#mediachat-local").css("display", "none");
    },

    toggleVideo: function () {
      if (mediaChat.localVideoEnabled) {
        mediaChat.disableVideo();
      } else {
        mediaChat.enableVideo();
      }
    },

    enableFullscreen: function () {
      if (document.documentElement.requestFullscreen) {
        document.documentElement.requestFullscreen();
      } else if (document.documentElement.msRequestFullscreen) {
        document.documentElement.msRequestFullscreen();
      } else if (document.documentElement.mozRequestFullScreen) {
        document.documentElement.mozRequestFullScreen();
      } else if (document.documentElement.webkitRequestFullScreen) {
        document.documentElement.webkitRequestFullScreen();
      }
      mediaChat.fullscreenEnabled = true;
      $("#mediachat-toggle-fullscreen").addClass("active");
    },

    disableFullscreen: function () {
      if (document.exitFullscreen) {
        document.exitFullscreen();
      } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
      } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
      } else if (document.webkitCancelFullScreen) {
        document.webkitCancelFullScreen();
      }
      mediaChat.fullscreenEnabled = false;
      $("#mediachat-toggle-fullscreen").removeClass("active");
    },

    toggleFullscreen: function () {
      if (!mediaChat.fullscreenEnabled) {
        mediaChat.enableFullscreen();
      } else {
        mediaChat.disableFullscreen();
      }
    },

    streamSupportUpdate: function () {
      mediaChat.supported()
        ? mediaChat.sendMessage(
            {
              type: "mediachat.can.stream",
              stream_support: 1,
            },
            "type"
          )
        : undefined;
    },

    supported: function () {
      let supported = false;
      supported =
        typeof navigator !== "undefined" &&
        typeof navigator.mediaDevices !== "undefined" &&
        typeof navigator.mediaDevices.getUserMedia !== "undefined" &&
        typeof window.RTCPeerConnection !== "undefined"
          ? true
          : supported;
      return supported;
    },

    onTrackAdded: function (e) {
      if (e.track.kind === "audio") {
        mediaChat.remoteAudio.srcObject = new MediaStream([e.track]);
        mediaChat.remoteAudioEnabled = true;
      } else {
        mediaChat.remoteVideo.srcObject = new MediaStream([e.track]);
        mediaChat.remoteVideoEnabled = true;
        $(mediaChat.remoteVideo).css("display", "block");
        $("#mediachat-remote-avatar").css("display", "none");
        $("#mediachat-centered-avatar").css("display", "none");
      }
    },

    onTrackRemoved: function (e) {
      if (e.track.kind === "audio") {
        mediaChat.remoteAudio.srcObject = null;
        mediaChat.remoteAudioEnabled = false;
      } else {
        mediaChat.remoteVideo.srcObject = null;
        mediaChat.remoteVideoEnabled = false;
        $(mediaChat.remoteVideo).css("display", "none");
        $("#mediachat-remote-avatar").css("display", "inline-block");
      }
    },

    onParticipantConnected: function () {
      if (mediaChat.state === "connecting") {
        mediaChat.resetTimer();
        mediaChat.startTimer();
        mediaChat.state = "connected";
        if (mediaChat.endTimeout) {
          clearTimeout(mediaChat.endTimeout);
        }
        mediaChat.humtone.removeEventListener("ended", mediaChat.playHumtone);
      }
    },

    onParticipantDisconnected: function () {
      mediaChat.end();
    },

    onConnected: function (e) {},

    onDisconnected: function () {
      mediaChat.peerConnection.getLocalStreams().forEach(function (stream) {
        stream.getTracks().forEach(function (track) {
          track.stop();
        });
      });
      mediaChat.peerConnection.getRemoteStreams().forEach(function (stream) {
        stream.getTracks().forEach(function (track) {
          track.stop();
        });
      });
      mediaChat.end();
    },

    push: function (type, data) {
      if (type === "mediachat.call") {
        if (!mediaChat.state && mediaChat.supported()) {
          mediaChat.prompt(data);
        }
      } else if (type === "mediachat.call.ended") {
        for (let id in data) {
          if (
            data.hasOwnProperty(id) &&
            !Pusher.hasPushId(id) &&
            parseInt(data[id].id) === parseInt(mediaChat.id)
          ) {
            if (mediaChat.state === "prompting") {
              mediaChat.drop();
            } else if (
              mediaChat.state === "calling" ||
              mediaChat.state === "connecting" ||
              mediaChat.state === "connected"
            ) {
              mediaChat.end();
            }
          }
        }
      } else if (type === "mediachat.friends.available") {
        mediaChat.availableFriends = data;
        Hook.fire("mediachat.friends.available");
      } else if (type === "mediachat.call.ice.candidate") {
        for (let id in data) {
          if (data.hasOwnProperty(id) && !Pusher.hasPushId(id)) {
            for (let index = 0; index < data[id].length; index++) {
              if (parseInt(data[id][index].id) === parseInt(mediaChat.id)) {
                if (
                  mediaChat.peerConnection &&
                  mediaChat.peerConnection.remoteDescription
                ) {
                  console.debug(
                    "ICE (" +
                      (data[id][index].data.candidate.indexOf("relay") < 0
                        ? "STUN"
                        : "TURN") +
                      ") Received: ",
                    new RTCIceCandidate(data[id][index].data)
                  );
                  mediaChat.peerConnection.addIceCandidate(
                    new RTCIceCandidate(data[id][index].data)
                  );
                  Pusher.addPushId(id);
                }
              }
            }
          }
        }
      } else if (type === "mediachat.call.session.description") {
        for (let id in data) {
          if (
            data.hasOwnProperty(id) &&
            !Pusher.hasPushId(id) &&
            parseInt(data[id].id) === parseInt(mediaChat.id)
          ) {
            if (data[id].data.type === "answer") {
              if (mediaChat.endTimeout) {
                //clearTimeout(mediaChat.endTimeout);
              }
              mediaChat.peerConnection
                .setRemoteDescription(new RTCSessionDescription(data[id].data))
                .then(function () {});
            }
            Pusher.addPushId(id);
          }
        }
      }
    },
  };

  $(function () {
    mediaChat.init();
  });
}