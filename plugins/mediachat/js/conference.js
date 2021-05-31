if (typeof window.conference === "undefined") {
  window.conference = {
    state: null,
    localUuid: null,
    localDisplayName: null,
    localStream: null,
    serverConnection: null,
    peerConnections: {},
    friends: [],
    localId: null,
    chatId: null,
    peerConnectionConfig: {
      iceServers: [
        { urls: "stun:stun.l.google.com:19302" },
        { urls: "stun:stun.services.mozilla.com:3478" },
      ],
      iceTransportPolicy: "all",
    },
    fullscreenEnabled: false,
    init: function () {
      conference.addEvents();
      Pusher.addHook("conference.gotMessageFromServer");
    },
    addEvents: function () {
      $(document).on("click", ".conference-call-button", function (e) {
        e.preventDefault();
        if (conference.supported()) {
          let streamSupport = !parseInt(
            e.currentTarget.getAttribute("data-disabled")
          );
          if (streamSupport) {
            let userId = e.currentTarget.getAttribute("data-user-id");
            //let type = e.currentTarget.getAttribute("data-type");
            conference.make(userId);
          } else {
            notify(e.currentTarget.getAttribute("title"), "warning");
          }
        } else {
          notify(language.phrase("device-not-supported"), "warning");
        }
      });
      $(document).on("click", "#conference-receive-button", function (e) {
        e.preventDefault();
        let chatId = e.currentTarget.getAttribute("data-call-id");
        let callerId = e.currentTarget.getAttribute("data-caller-id");
        conference.receive(callerId, chatId);
      });

      $(document).on("click", "#conference-drop-button", function (e) {
        e.preventDefault();
        conference.drop();
      });
      $(document).on("click", "#conference-end", function (e) {
        e.preventDefault();
        conference.end();
      });

      $(document).on("click", "#conference-toggle-video", function (e) {
        e.preventDefault();
        //conference.toggleVideo();
        console.log("video toggled");
      });

      $(document).on("click", "#conference-toggle-voice", function (e) {
        e.preventDefault();
        //conference.toggleAudio();
        console.log("voice toggled");
      });

      $(document).on("click", "#conference-toggle-fullscreen", function (e) {
        e.preventDefault();
        conference.toggleFullscreen();
      });

      $(document).on("click", ".conference-init-button", function (e) {
        e.preventDefault();
        if (conference.supported()) {
          let streamSupport = !parseInt(
            e.currentTarget.getAttribute("data-disabled")
          );
          if (streamSupport) {
            let userId = e.currentTarget.getAttribute("data-user-id");
            //let type = e.currentTarget.getAttribute("data-type");
            conference.makeConference(userId);
          } else {
            notify(e.currentTarget.getAttribute("title"), "warning");
          }
        } else {
          notify(language.phrase("device-not-supported"), "warning");
        }
      });
    },
    makeConference: function (userId) {
      Pusher.sendMessage({
        type: "conference.call",
        receiverId: userId,
        data: {
          chatId: conference.chatId,
          otherUsers: conference.friends,
        },
      });
    },
    make: function (userId) {
      conference.state = "calling";
      let form = document.getElementById("conference-call-form");
      var chatId = conference.createUUID();
      form.querySelector('[name="id"]').value = chatId;
      form.querySelector('[name="is_caller"]').value = 0;
      form.querySelector('[name="user_id"]').value = userId;
      form.querySelector('[name="session_description"]').value = undefined;
      window.open(
        "",
        "mediachat-conf",
        "width = 870, height = 651, scrollbars = no"
      );
      form.submit();
      conference.state = null;
      Pusher.sendMessage({
        type: "conference.call",
        receiverId: userId,
        data: {
          chatId: chatId,
        },
      });
    },
    receive: function (userId, chatId) {
      if (conference.state === "prompting") {
        conference.state = null;
        document.getElementById("conference-receive-container").style.display =
          "none";
        let form = document.getElementById("conference-call-form");
        mediaChat.ringtone.removeEventListener("ended", mediaChat.playRingtone);
        form.querySelector('[name="id"]').value = chatId;
        form.querySelector('[name="is_caller"]').value = 1;
        form.querySelector('[name="user_id"]').value = userId;
        form.querySelector('[name="session_description"]').value = undefined;
        for (id in conference.friends) {
          var input = document.createElement("input");
          input.setAttribute("type", "hidden");
          input.setAttribute("name", "other_users[]");
          input.setAttribute("value", conference.friends[id]);
          form.append(input);
        }
        window.open(
          "",
          "mediachat-conf",
          "width = 870, height = 651, scrollbars = no"
        );
        form.submit();
      }
    },
    prompt: function (data) {
      conference.state = "prompting";
      conference.chatId = data.chatId;
      mediaChat.ring();
      document
        .getElementById("conference-receive-button")
        .setAttribute("data-call-id", conference.chatId);
      document
        .getElementById("conference-receive-button")
        .setAttribute("data-caller-id", data.caller);
      document.getElementById("conference-receive-container").style.display =
        "flex";
      if (data.otherUsers !== undefined) {
        conference.friends = data.otherUsers;
      }
    },
    end: function () {
      if (
        conference.state === "calling" ||
        conference.state === "connecting" ||
        conference.state === "connected"
      ) {
        conference.state = "ending";
        if (conference.fullscreenEnabled) {
          conference.disableFullscreen();
        }
        if (conference.isCaller) {
          for (id in conference.peerConnections) {
            conference.peerConnections[id].pc
              .getLocalStreams()
              .forEach(function (stream) {
                stream.getTracks().forEach(function (track) {
                  track.stop();
                });
              });
          }
          for (index in conference.friends) {
            Pusher.sendMessage({
              type: "conference.end",
              userId: conference.friends[index],
              data: {
                chatId: conference.chatId,
              },
            });
          }
        }
        Pusher.sendMessage({
          type: "conference.ended",
        });
        window.close();
        mediaChat.state = null;
      }
    },
    drop: function () {
      if (conference.state === "prompting") {
        conference.state = null;
        mediaChat.ringtone.removeEventListener("ended", mediaChat.playRingtone);
        mediaChat.ringtone.removeEventListener("ended", mediaChat.playHumtone);
        document.getElementById("conference-receive-container").style.display =
          "none";
      }
    },
    start: function (data) {
      window.onclose = function (ev) {
        conference.end();
      };
      conference.state = "connecting";
      var username = document
        .getElementById("roomDisplayName")
        .getAttribute("data-room-name");
      var id = document
        .getElementById("roomDisplayName")
        .getAttribute("data-room-id");
      conference.localDisplayName = username;
      conference.localId = id;
      conference.localUuid = conference.createUUID();
      var otherUsers = JSON.parse(data.otherUsers);
      otherUsers.push(data.userId);
      conference.friends = otherUsers;
      conference.chatId = data.chatId;
      // check if "&displayName=xxx" is appended to URL, otherwise alert user to populate
      document
        .getElementById("localVideoContainer")
        .appendChild(conference.makeLabel(conference.localDisplayName));

      // specify no audio for user media
      var constraints = {
        video: {
          width: {
            max: 320,
          },
          height: {
            max: 240,
          },
          frameRate: {
            max: 30,
          },
        },
        audio: false,
      };

      // set up local video stream
      if (navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices
          .getUserMedia(constraints)
          .then((stream) => {
            conference.localStream = stream;
            document.getElementById("localVideo").srcObject = stream;
          })
          .catch(conference.errorHandler)
          .then(function () {
            for (index in conference.friends) {
              conference.friends[index] = parseInt(conference.friends[index]);
              Pusher.sendMessage({
                type: "conference",
                userId: conference.friends[index],
                data: {
                  displayName: conference.localDisplayName,
                  uuid: conference.localUuid,
                  localId: parseInt(conference.localId),
                  dest: "all",
                  chatId: conference.chatId,
                },
              });
            }
          })
          .catch(conference.errorHandler);
      } else {
        alert("Your browser does not support getUserMedia API");
      }
    },

    setUpPeer: function (peerUuid, displayName, initCall = false) {
      conference.peerConnections[peerUuid] = {
        displayName: displayName,
        pc: new RTCPeerConnection(conference.peerConnectionConfig),
      };

      conference.peerConnections[peerUuid].pc.onicecandidate = function (
        event
      ) {
        conference.gotIceCandidate(event, peerUuid);
      };
      conference.peerConnections[peerUuid].pc.ontrack = function (event) {
        conference.gotRemoteStream(event, peerUuid);
      };
      conference.peerConnections[
        peerUuid
      ].pc.oniceconnectionstatechange = function (event) {
        conference.checkPeerDisconnect(event, peerUuid);
      };

      conference.peerConnections[peerUuid].pc.addStream(conference.localStream);

      if (initCall) {
        conference.peerConnections[peerUuid].pc
          .createOffer()
          .then((description) =>
            conference.createdDescription(description, peerUuid)
          )
          .catch(conference.errorHandler);
      }
    },

    gotIceCandidate: function (event, peerUuid) {
      console.log("got ice candidate");
      if (event.candidate != null) {
        for (index in conference.friends) {
          Pusher.sendMessage({
            type: "conference",
            userId: conference.friends[index],
            data: {
              ice: event.candidate,
              uuid: conference.localUuid,
              dest: peerUuid,
              chatId: conference.chatId,
            },
          });
        }
      }
    },

    createdDescription: function (description, peerUuid) {
      console.log(
        `got description, peer ${peerUuid}, description ${description}`
      );
      conference.peerConnections[peerUuid].pc
        .setLocalDescription(description)
        .then(function () {
          for (index in conference.friends) {
            Pusher.sendMessage({
              type: "conference",
              userId: conference.friends[index],
              data: {
                sdp: conference.peerConnections[peerUuid].pc.localDescription,
                uuid: conference.localUuid,
                dest: peerUuid,
                chatId: conference.chatId,
              },
            });
          }
        })
        .catch(conference.errorHandler);
      conference.state = "connecting";
    },

    gotRemoteStream: function (event, peerUuid) {
      console.log(`got remote stream, peer ${peerUuid}`);
      //conference.state = "connected";
      //assign stream to new HTML video element
      var vidElement = document.createElement("video");
      vidElement.setAttribute("autoplay", "");
      vidElement.setAttribute("muted", "");
      vidElement.srcObject = event.streams[0];

      var vidContainer = document.createElement("div");
      vidContainer.setAttribute("id", "remoteVideo_" + peerUuid);
      vidContainer.setAttribute("class", "videoContainer");
      vidContainer.appendChild(vidElement);
      vidContainer.appendChild(
        conference.makeLabel(conference.peerConnections[peerUuid].displayName)
      );

      document.getElementById("videos").appendChild(vidContainer);

      conference.updateLayout();
    },

    checkPeerDisconnect: function (event, peerUuid) {
      var state = conference.peerConnections[peerUuid].pc.iceConnectionState;
      console.log(`connection with peer ${peerUuid} ${state}`);
      if (
        state === "failed" ||
        state === "closed" ||
        state === "disconnected"
      ) {
        delete conference.peerConnections[peerUuid];
        document.getElementById("remoteVideo_" + peerUuid).remove();
        conference.updateLayout();
        if (conference.peerConnections == {}) {
          conference.end();
        }
      }
    },

    updateLayout: function () {
      // update CSS grid based on number of diplayed videos
      var rowHeight = "98vh";
      var colWidth = "98vw";

      var numVideos = Object.keys(conference.peerConnections).length + 1; // add one to include local video

      if (numVideos > 1 && numVideos <= 4) {
        // 2x2 grid
        rowHeight = "48vh";
        colWidth = "48vw";
      } else if (numVideos > 4) {
        // 3x3 grid
        rowHeight = "32vh";
        colWidth = "32vw";
      }

      document.documentElement.style.setProperty(`--rowHeight`, rowHeight);
      document.documentElement.style.setProperty(`--colWidth`, colWidth);
    },

    makeLabel: function (label) {
      var vidLabel = document.createElement("div");
      vidLabel.appendChild(document.createTextNode(label));
      vidLabel.setAttribute("class", "videoLabel");
      return vidLabel;
    },

    errorHandler: function (error) {
      console.log(error);
    },

    gotMessageFromServer: function (type, message) {
      if (type == "conference") {
        for (id in message) {
          if (message.hasOwnProperty(id) && !Pusher.hasPushId(id)) {
            var signal = message[id];
            if (signal.chatId == conference.chatId) {
              var peerUuid = signal.uuid;
              // Ignore messages that are not for us or from ourselves
              if (
                peerUuid == conference.localUuid ||
                (signal.dest != conference.localUuid && signal.dest != "all")
              )
                return;

              if (signal.displayName && signal.dest == "all") {
                // set up peer connection object for a newcomer peer
                if (
                  !conference.friends.includes(signal.localId) &&
                  signal.localId != conference.localId
                ) {
                  conference.friends.push(signal.localId);
                }
                conference.setUpPeer(peerUuid, signal.displayName);

                Pusher.sendMessage({
                  type: "conference",
                  userId: signal.localId,
                  data: {
                    displayName: conference.localDisplayName,
                    uuid: conference.localUuid,
                    dest: peerUuid,
                    chatId: conference.chatId,
                  },
                });
              } else if (
                signal.displayName &&
                signal.dest == conference.localUuid
              ) {
                // initiate call if we are the newcomer peer
                conference.setUpPeer(peerUuid, signal.displayName, true);
              } else if (signal.sdp) {
                conference.peerConnections[peerUuid].pc
                  .setRemoteDescription(new RTCSessionDescription(signal.sdp))
                  .then(function () {
                    // Only create answers in response to offers
                    console.log(signal.sdp);
                    if (signal.sdp != undefined) {
                      if (signal.sdp.type == "offer") {
                        conference.peerConnections[peerUuid].pc
                          .createAnswer()
                          .then((description) =>
                            conference.createdDescription(description, peerUuid)
                          )
                          .catch(conference.errorHandler);
                      }
                    }
                  })
                  .catch(conference.errorHandler);
              } else if (signal.ice) {
                conference.peerConnections[peerUuid].pc
                  .addIceCandidate(new RTCIceCandidate(signal.ice))
                  .catch(conference.errorHandler);
              }
              Pusher.addPushId(id);
            }
          }
        }
      } else if (type == "conference.call") {
        for (id in message) {
          if (message.hasOwnProperty(id) && !Pusher.hasPushId(id)) {
            var data = message[id];
            if (!conference.state && conference.supported()) {
              conference.prompt(data);
            }
            Pusher.addPushId(id);
          }
        }
      } else if (type == "conference.end") {
        for (id in message) {
          if (message.hasOwnProperty(id) && !Pusher.hasPushId(id)) {
            var signal = message[id];
            if (signal.chatId == conference.chatId) {
              conference.end();
            }
          }
        }
      }
    },
    createUUID: function () {
      function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
          .toString(16)
          .substring(1);
      }

      return (
        s4() +
        s4() +
        "-" +
        s4() +
        "-" +
        s4() +
        "-" +
        s4() +
        "-" +
        s4() +
        s4() +
        s4()
      );
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
      conference.fullscreenEnabled = true;
      $("#conference-toggle-fullscreen").addClass("active");
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
      conference.fullscreenEnabled = false;
      $("#conference-toggle-fullscreen").removeClass("active");
    },

    toggleFullscreen: function () {
      if (!mediaChat.fullscreenEnabled) {
        conference.enableFullscreen();
      } else {
        conference.disableFullscreen();
      }
    },
  };

  $(function () {
    conference.init();
  });
}
