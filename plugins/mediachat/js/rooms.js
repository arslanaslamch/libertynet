if (typeof window.rooms === "undefined") {
  window.rooms = {
    localUuid: null,
    localDisplayName: null,
    localStream: null,
    serverConnection: null,
    peerConnections: {},
    friends: null,
    peerConnectionConfig: {
      iceServers: [
        { urls: "stun:stun.l.google.com:19302" },
        { urls: "stun:stun.services.mozilla.com:3478" },
      ],
      iceTransportPolicy: "all",
    },
    init: function () {
      if (document.getElementById("roomDisplayName") !== null) {
        var username = document
          .getElementById("roomDisplayName")
          .getAttribute("data-room-name");
        var id = document
          .getElementById("roomDisplayName")
          .getAttribute("data-room-id");
        rooms.start(username, id);
      }
    },
    start: function (username, id) {
      Pusher.addHook("rooms.gotMessageFromServer");
      rooms.localUuid = rooms.createUUID();
      // check if "&displayName=xxx" is appended to URL, otherwise alert user to populate
      rooms.localDisplayName = username;
      document
        .getElementById("localVideoContainer")
        .appendChild(rooms.makeLabel(rooms.localDisplayName));

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
            rooms.localStream = stream;
            document.getElementById("localVideo").srcObject = stream;
          })
          .catch(rooms.errorHandler)
          .then(function () {
            Pusher.sendMessage({
              type: "rooms",
              data: {
                displayName: rooms.localDisplayName,
                uuid: rooms.localUuid,
                dest: "all",
              },
            });
          })
          .catch(rooms.errorHandler);
      } else {
        alert("Your browser does not support getUserMedia API");
      }
    },

    setUpPeer: function (peerUuid, displayName, initCall = false) {
      rooms.peerConnections[peerUuid] = {
        displayName: displayName,
        pc: new RTCPeerConnection(rooms.peerConnectionConfig),
      };

      rooms.peerConnections[peerUuid].pc.onicecandidate = function (event) {
        rooms.gotIceCandidate(event, peerUuid);
      };
      rooms.peerConnections[peerUuid].pc.ontrack = function (event) {
        rooms.gotRemoteStream(event, peerUuid);
      };
      rooms.peerConnections[peerUuid].pc.oniceconnectionstatechange = function (
        event
      ) {
        rooms.checkPeerDisconnect(event, peerUuid);
      };

      rooms.peerConnections[peerUuid].pc.addStream(rooms.localStream);

      if (initCall) {
        rooms.peerConnections[peerUuid].pc
          .createOffer()
          .then((description) =>
            rooms.createdDescription(description, peerUuid)
          )
          .catch(rooms.errorHandler);
      }
    },

    gotIceCandidate: function (event, peerUuid) {
      console.log("got ice candidate");
      if (event.candidate != null) {
        Pusher.sendMessage({
          type: "rooms",
          data: {
            ice: event.candidate,
            uuid: rooms.localUuid,
            dest: peerUuid,
          },
        });
      }
    },

    createdDescription: function (description, peerUuid) {
      console.log(`got description, peer ${peerUuid}`);
      rooms.peerConnections[peerUuid].pc
        .setLocalDescription(description)
        .then(function () {
          Pusher.sendMessage({
            type: "rooms",
            data: {
              sdp: rooms.peerConnections[peerUuid].pc.localDescription,
              uuid: rooms.localUuid,
              dest: peerUuid,
            },
          });
        })
        .catch(rooms.errorHandler);
    },

    gotRemoteStream: function (event, peerUuid) {
      console.log(`got remote stream, peer ${peerUuid}`);
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
        rooms.makeLabel(rooms.peerConnections[peerUuid].displayName)
      );

      document.getElementById("videos").appendChild(vidContainer);

      rooms.updateLayout();
    },

    checkPeerDisconnect: function (event, peerUuid) {
      var state = rooms.peerConnections[peerUuid].pc.iceConnectionState;
      console.log(`connection with peer ${peerUuid} ${state}`);
      if (
        state === "failed" ||
        state === "closed" ||
        state === "disconnected"
      ) {
        delete rooms.peerConnections[peerUuid];
        document.getElementById("remoteVideo_" + peerUuid).remove();
        rooms.updateLayout();
      }
    },

    updateLayout: function () {
      // update CSS grid based on number of diplayed videos
      var rowHeight = "98vh";
      var colWidth = "98vw";

      var numVideos = Object.keys(rooms.peerConnections).length + 1; // add one to include local video

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
      if (type == "rooms") {
        for (id in message) {
          if (message.hasOwnProperty(id) && !Pusher.hasPushId(id)) {
            var signal = message[id];
            var peerUuid = signal.uuid;
            // Ignore messages that are not for us or from ourselves
            if (
              peerUuid == rooms.localUuid ||
              (signal.dest != rooms.localUuid && signal.dest != "all")
            )
              return;

            if (signal.displayName && signal.dest == "all") {
              // set up peer connection object for a newcomer peer
              rooms.setUpPeer(peerUuid, signal.displayName);
              Pusher.sendMessage({
                type: "rooms",
                data: {
                  displayName: rooms.localDisplayName,
                  uuid: rooms.localUuid,
                  dest: peerUuid,
                },
              });
            } else if (signal.displayName && signal.dest == rooms.localUuid) {
              // initiate call if we are the newcomer peer
              rooms.setUpPeer(peerUuid, signal.displayName, true);
            } else if (signal.sdp) {
              rooms.peerConnections[peerUuid].pc
                .setRemoteDescription(new RTCSessionDescription(signal.sdp))
                .then(function () {
                  // Only create answers in response to offers
                  if (signal.sdp.type == "offer") {
                    rooms.peerConnections[peerUuid].pc
                      .createAnswer()
                      .then((description) =>
                        rooms.createdDescription(description, peerUuid)
                      )
                      .catch(rooms.errorHandler);
                  }
                })
                .catch(rooms.errorHandler);
            } else if (signal.ice) {
              rooms.peerConnections[peerUuid].pc
                .addIceCandidate(new RTCIceCandidate(signal.ice))
                .catch(rooms.errorHandler);
            }
            Pusher.addPushId(id);
          }
        }
      } else if (type == "mediachat.friends.available") {
        rooms.friends = message;
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
  };

  $(function () {
    rooms.init();
  });
}
