// Function to set the animation with Animate.css
const animateCSS = (element, animation, prefix = 'animate__') =>
  new Promise((resolve, reject) => {
    const animationName = `${prefix}${animation}`;
    const node = document.querySelector(element);

    node.classList.add(`${prefix}animated`, animationName);

    // When the animation ends, we clean the classes and resolve the Promise
    function handleAnimationEnd(event) {
      event.stopPropagation();
      node.classList.remove(`${prefix}animated`, animationName);
      resolve('Animation ended');
    }

    node.addEventListener('animationend', handleAnimationEnd, {once: true});
  });

// Function to start the client
function WCClientMain() {
  try {

    // Create the WebChaussette client
    window.wcClient = new WCClient();

    // Start the main loop of the client
    var refreshRateMs = 5000;
    setInterval(window.wcClient.Loop.bind(window.wcClient), refreshRateMs);

  } catch (err) {
    console.log(err.stack);
  }
}

// Function to start the server
function WCServerMain() {
  try {

    // Create the WebChaussette server
    window.wcServer = new WCServer();

    // Start the main loop of the server
    var refreshRateMs = 5000;
    setInterval(window.wcServer.Loop.bind(window.wcServer), refreshRateMs);

  } catch (err) {
    console.log(err.stack);
  }
}

// Hooks for the events in the DOM
function CreateSessionClick() {window.wcServer.CreateSessionReq();}
function RequestConnectionClick() {window.wcClient.ConnectionReq($("#inpName").val(), $("#inpKey").val());}
function InpMessageKeyPress(event) {window.wcClient.MessageKeyPress(event);}
function SendMessageClick() {window.wcClient.SendMessage();}

function WCClientRequestAgain() {

  animateCSS('#divLoginDenied', 'bounceOut').then((message) => {
    $("#divLoginDenied").css("display", "none");
    animateCSS('#divLoginRequest', 'bounceIn');
    $("#divLoginRequest").css("display", "block");
  });
  window.wcClient.status = "request login";

}

function WCClientGiveupLogin() {

  animateCSS('#divLoginWait', 'bounceOut').then((message) => {
    $("#divLoginWait").css("display", "none");
    animateCSS('#divLoginRequest', 'bounceIn');
    $("#divLoginRequest").css("display", "block");
  });
  window.wcClient.status = "request login";

}

function WCClientReconnect() {

  animateCSS('#divLoginDisconnected', 'bounceOut').then((message) => {
    $("#divLoginDisconnected").css("display", "none");
    animateCSS('#divLoginRequest', 'bounceIn');
    $("#divLoginRequest").css("display", "block");
  });
  window.wcClient.status = "request login";

}

function HTTPPostRequest(url, form, handler) {
  try {

    // Create the request object
    if (window.XMLHttpRequest) {
      var xhr = new XMLHttpRequest();
    } else {
      var xhr = new ActiveXObject('Microsoft.XMLHTTP');
    }

    // Hook for the reply
    xhr._handler = handler;
    xhr.onreadystatechange = function() {

      // If the request is ready
      if (this.readyState == 4) {
        if (this.status == 200) {
          var returnedData = {};
          try {
            returnedData = JSON.parse(this.responseText);
          } catch(err) {
            console.log(this.responseText);
            returnedData = JSON.parse('{"err":"JSON.parse failed."}');
          }
        } else {
          // The request failed, return error as JSON
          var returnedData = 
            JSON.parse('{"err":"HTTPRequest failed : ' + 
              this.status + '"}');
        }

        if (handler !== null) handler(returnedData);

      } else {

      }

    };

    // Send the request
    xhr.open("POST",url);
    var formData = new FormData(form);
    xhr.send(formData);

  } catch (err) {
    console.log(err.stack);
  }

}

// ------------ WCClient class

class WCClient {

  constructor() {
    try {

      this.status = "idle";
      this.name = "";
      this.key = "";
      this.message = "";

    } catch (err) {
      console.log(err.stack);
    }
  }

  ConnectionReq(name, key) {

    console.log("ConnectionReq " + name + " " + key);

    this.name = name;
    this.key = key;

    var url = "./api.php";
    var form = document.createElement("form");
    form.setAttribute("method", "post");
    var action = document.createElement("input");
    action.setAttribute("type", "text");
    action.setAttribute("name", "action");
    action.setAttribute("value","connect");
    form.appendChild(action);
    var name = document.createElement("input");
    name.setAttribute("type", "text");
    name.setAttribute("name", "name");
    name.setAttribute("value", this.name);
    form.appendChild(name);
    var key = document.createElement("input");
    key.setAttribute("type", "text");
    key.setAttribute("name", "key");
    key.setAttribute("value", this.key);
    form.appendChild(key);
    HTTPPostRequest(url, form, this.ConnectionCb.bind(this));

    animateCSS('#divLoginRequest', 'bounceOut').then((message) => {
      $("#divLoginRequest").css("display", "none");
      animateCSS('#divLoginWait', 'bounceIn');
      $("#divLoginWait").css("display", "block");
    });

    window.wcClient.status = "wait connection";

  }

  ConnectionCb(ret) {
    try {

      console.log("ConnectionCb " + JSON.stringify(ret));

      if (ret["err"] != 0) {

        animateCSS('#divLoginWait', 'bounceOut').then((message) => {
          $("#divLoginWait").css("display", "none");
          animateCSS('#divLoginDenied', 'bounceIn');
          $("#divLoginDenied").css("display", "block");
        });
        this.status = "denied login";

      }

    } catch (err) {
      console.log(err.stack);
    }
  }

  Loop() {
    try {

      if (this.status == "idle") {

        // Wait for the user to request a connection

      } else if (this.status == "wait connection") {

        this.ConnectionStatusReq();

      } else if (this.status == "denied login") {

        // Wait for the user to retry or give up

      } else if (this.status == "active") {

        this.RecvDataReq();

      }

    } catch (err) {
      console.log(err.stack);
    }

  }

  ConnectionStatusReq() {
    try {

      console.log("ConnectionStatusReq");

      var url = "./api.php";
      var form = document.createElement("form");
      form.setAttribute("method", "post");
      var action = document.createElement("input");
      action.setAttribute("type", "text");
      action.setAttribute("name", "action");
      action.setAttribute("value","checkConnection");
      form.appendChild(action);
      var name = document.createElement("input");
      name.setAttribute("type", "text");
      name.setAttribute("name", "name");
      name.setAttribute("value",this.name);
      form.appendChild(name);
      var key = document.createElement("input");
      key.setAttribute("type", "text");
      key.setAttribute("name", "key");
      key.setAttribute("value", this.key);
      form.appendChild(key);
      HTTPPostRequest(url, form, this.ConnectionStatusCb.bind(this));

    } catch (err) {
      console.log(err.stack);
    }

  }

  ConnectionStatusCb(ret) {
    try {

      console.log("ConnectionStatusCb " + JSON.stringify(ret));

      if (ret["err"] == 0) {

        animateCSS('#divLoginWait', 'bounceOut').then((message) => {
          $("#divLoginWait").css("display", "none");
          animateCSS('#divLoginGranted', 'bounceIn');
          $("#divLoginGranted").css("display", "block");
          $("#divData").empty();
          animateCSS('#divMessage', 'bounceIn');
          $("#divMessage").css("display", "block");
        });
        this.status = "active";
        $("#divChatUsers").empty();
        $("#divChatMessages").empty();

      } else if (ret["err"] == 2) {

        animateCSS('#divLoginWait', 'bounceOut').then((message) => {
          $("#divLoginWait").css("display", "none");
          animateCSS('#divLoginDenied', 'bounceIn');
          $("#divLoginDenied").css("display", "block");
        });
        this.status = "denied login";

      }

    } catch (err) {
      console.log(err.stack);
    }

  }

  RecvDataReq() {

    try {

      console.log("RecDataReq");

      var url = "./api.php";
      var form = document.createElement("form");
      form.setAttribute("method", "post");
      var action = document.createElement("input");
      action.setAttribute("type", "text");
      action.setAttribute("name", "action");
      action.setAttribute("value","recvData");
      form.appendChild(action);
      var name = document.createElement("input");
      name.setAttribute("type", "text");
      name.setAttribute("name", "name");
      name.setAttribute("value",this.name);
      form.appendChild(name);
      var key = document.createElement("input");
      key.setAttribute("type", "text");
      key.setAttribute("name", "key");
      key.setAttribute("value", this.key);
      form.appendChild(key);
      HTTPPostRequest(url, form, this.RecvDataCb.bind(this));

    } catch (err) {
      console.log(err.stack);
    }

  }

  RecvDataCb(ret) {

    try {

      console.log("RecDataReq " + JSON.stringify(ret));
      if (ret["err"] == 1) {

        animateCSS('#divMessage', 'bounceOut').then((message) => {
          $("#divMessage").css("display", "none");
        });
        animateCSS('#divLoginGranted', 'bounceOut').then((message) => {
          $("#divLoginGranted").css("display", "none");
          animateCSS('#divLoginDisconnected', 'bounceIn');
          $("#divLoginDisconnected").css("display", "block");
        });
        this.status = "idle";

      } else {

        var datas = ret["data"];
        if (datas.length > 0) {

          // Add the messages to the DOM
          for (const [iData, data] of Object.entries(datas)) {

            var divMsg = document.createElement("div");
            var msg = JSON.parse(data["Value"])
            var val = document.createTextNode(msg["msg"]);
            divMsg.append(val);
            $("#divData").append(divMsg);
            //animateCSS("#divSession" + session["Ref"], 'bounceIn');

          }

        }

      }

    } catch (err) {
      console.log(err.stack);
    }

  }

  MessageKeyPress(event) {

    try {

      if (event.keyCode == 13 && event.shiftKey) {

        event.preventDefault();
        this.message = $("#inpMessage").val();
        $("#inpMessage").val("");
        this.SendDataReq();
        return false;

      }

    } catch (err) {
      console.log(err.stack);
    }

  }

  SendMessage() {

    try {

      this.message = $("#inpMessage").val();
      $("#inpMessage").val("");
      this.SendDataReq();

    } catch (err) {
      console.log(err.stack);
    }

  }

  SendDataReq() {

    try {

      console.log("SendDataReq " + this.message);

      var url = "./api.php";
      var form = document.createElement("form");
      form.setAttribute("method", "post");
      var action = document.createElement("input");
      action.setAttribute("type", "text");
      action.setAttribute("name", "action");
      action.setAttribute("value","sendData");
      form.appendChild(action);
      var name = document.createElement("input");
      name.setAttribute("type", "text");
      name.setAttribute("name", "name");
      name.setAttribute("value",this.name);
      form.appendChild(name);
      var key = document.createElement("input");
      key.setAttribute("type", "text");
      key.setAttribute("name", "key");
      key.setAttribute("value", this.key);
      form.appendChild(key);
      var data = document.createElement("input");
      data.setAttribute("type", "text");
      data.setAttribute("name", "data");
      var jsonData = {msg: this.message};
      data.setAttribute("value", JSON.stringify(jsonData));
      form.appendChild(data);
      HTTPPostRequest(url, form, null);

    } catch (err) {
      console.log(err.stack);
    }

  }

}

// ------------ WCServer class

class WCServer {

  constructor() {
    try {

      this.sessionRef = 0;
      this.sessionKey = "0";
      this.sessions = null;
      this.pendings = null;
      this.connections = null;

    } catch (err) {
      console.log(err.stack);
    }
  }

  MakeId(length) {

    var result = [];
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for ( var i = 0; i < length; i++ ) {
      result.push(characters.charAt(Math.floor(Math.random() * 
      characters.length)));
    }
    return result.join('');

  }

  // Request the creation of a new session
  CreateSessionReq() {
    try {

      var newSessionId = this.MakeId(10);

      var url = "./api.php";
      var form = document.createElement("form");
      form.setAttribute("method", "post");
      var action = document.createElement("input");
      action.setAttribute("type", "text");
      action.setAttribute("name", "action");
      action.setAttribute("value","createSession");
      form.appendChild(action);
      var key = document.createElement("input");
      key.setAttribute("type", "text");
      key.setAttribute("name", "key");
      key.setAttribute("value", newSessionId);
      form.appendChild(key);
      HTTPPostRequest(url, form, null);
      animateCSS("#btnCreateSession", 'pulse');

    } catch (err) {
      console.log(err.stack);
    }
  }

  // Request the list of opened sessions
  GetSessionReq() {
    try {

      console.log("GetSessionReq " + this.sessionKey);

      var url = "./api.php";
      var form = document.createElement("form");
      form.setAttribute("method", "post");
      var action = document.createElement("input");
      action.setAttribute("type", "text");
      action.setAttribute("name", "action");
      action.setAttribute("value","getSession");
      form.appendChild(action);
      HTTPPostRequest(url, form, this.GetSessionCb.bind(this));

    } catch (err) {
      console.log(err.stack);
    }
  }

  // Callback for GetSessionReq
  GetSessionCb(ret) {
    try {

      console.log("GetSessionCb " + JSON.stringify(ret));

      // Memorise the sessions
      this.sessions = ret;

      // Loop on the sessions
      for (const [iSession, session] of Object.entries(this.sessions)) {

        // If the session is not in the DOM
        if ($("#divSession" + session["Ref"]).length == 0) {

          // Add the session to the DOM
          var divSession = document.createElement("div");
          divSession.id = "divSession" + session["Ref"];
          var sessionKey = document.createTextNode(session["Key"]);
          divSession.append(sessionKey);
          var btnShow = document.createElement("input");
          btnShow.type = "button";
          btnShow.value = "Show";
          btnShow.sessionRef = session["Ref"];
          btnShow.onclick = (evt) => this.SessionSetCur(evt.target.sessionRef);
          divSession.append(btnShow);
          var btnClose = document.createElement("input");
          btnClose.type = "button";
          btnClose.value = "Close";
          btnClose.sessionRef = session["Ref"];
          btnClose.onclick =
            (evt) => this.SessionCloseReq(evt.target.sessionRef);
          divSession.append(btnClose);
          $("#divSessions").append(divSession);
          animateCSS("#divSession" + session["Ref"], 'bounceIn');

        }

      }

      // If there is no session selected in the DOM, set this session has
      // the current one
      if (this.sessionRef == 0 && this.sessions.length > 0) {

        this.sessionRef = this.sessions[0]["Ref"];
        this.sessionKey = this.sessions[0]["Key"];
        $("#divSession" + this.sessions[0]["Ref"]).addClass("divCurrentSession");

      }


    } catch (err) {
      console.log(err.stack);
    }
  }

  // Set the current session
  SessionSetCur(sessionRef) {
    try {

      $("#divSession" + this.sessionRef).removeClass("divCurrentSession");
      this.sessionKey = this.GetSessionKey(sessionRef);
      this.sessionRef = sessionRef;
      $("#divSession" + this.sessionRef).addClass("divCurrentSession");
      $("#divPendings").empty();
      $("#divConnections").empty();

    } catch (err) {
      console.log(err.stack);
    }
  }

  // Request the list of request for connection
  GetPendingReq() {
    try {

      console.log("GetPendingReq " + this.sessionKey);

      var url = "./api.php";
      var form = document.createElement("form");
      form.setAttribute("method", "post");
      var action = document.createElement("input");
      action.setAttribute("type", "text");
      action.setAttribute("name", "action");
      action.setAttribute("value","getRequest");
      form.appendChild(action);
      var key = document.createElement("input");
      key.setAttribute("type", "text");
      key.setAttribute("name", "key");
      key.setAttribute("value", this.sessionKey);
      form.appendChild(key);
      HTTPPostRequest(url, form, this.GetPendingCb.bind(this));

    } catch (err) {
      console.log(err.stack);
    }
  }

  GetPendingCb(ret) {
    try {

      console.log("GetPendingCb " + JSON.stringify(ret));

      // Memorise the pending requests
      this.pendings = ret;

      // Loop on the requests
      for (const [iPending, pending] of Object.entries(this.pendings)) {

        // If the session is not in the DOM
        if ($("#divPending" + pending["Ref"]).length == 0) {

          // Add the request to the DOM
          var divPending = document.createElement("div");
          divPending.id = "divPending" + pending["Ref"];
          var pendingName = document.createTextNode(pending["Name"]);
          divPending.append(pendingName);
          var btnAccept = document.createElement("input");
          btnAccept.type = "button";
          btnAccept.value = "Accept";
          btnAccept.pendingRef = pending["Ref"];
          btnAccept.onclick =
            (evt) => this.PendingAcceptReq(evt.target.pendingRef);
          divPending.append(btnAccept);
          var btnReject = document.createElement("input");
          btnReject.type = "button";
          btnReject.value = "Reject";
          btnReject.pendingRef = pending["Ref"];
          btnReject.onclick =
            (evt) => this.PendingRejectReq(evt.target.pendingRef);
          divPending.append(btnReject);
          $("#divPendings").append(divPending);
          animateCSS("#divPending" + pending["Ref"], 'bounceIn');

        }

      }

    } catch (err) {
      console.log(err.stack);
    }

  }

  PendingAcceptReq(pendingRef) {
    try {

      console.log("PendingAcceptReq " + pendingRef);

      var url = "./api.php";
      var form = document.createElement("form");
      form.setAttribute("method", "post");
      var action = document.createElement("input");
      action.setAttribute("type", "text");
      action.setAttribute("name", "action");
      action.setAttribute("value","acceptRequest");
      form.appendChild(action);
      var ref = document.createElement("input");
      ref.setAttribute("type", "text");
      ref.setAttribute("name", "ref");
      ref.setAttribute("value", pendingRef);
      form.appendChild(ref);
      HTTPPostRequest(url, form, null);

      animateCSS("#divPending" + pendingRef, 'bounceOut').then((message) => {
        $("#divPending" + pendingRef).remove();
      });


    } catch (err) {
      console.log(err.stack);
    }
  }

  PendingRejectReq(pendingRef) {
    try {

      console.log("PendingRejectReq " + pendingRef);

      var url = "./api.php";
      var form = document.createElement("form");
      form.setAttribute("method", "post");
      var action = document.createElement("input");
      action.setAttribute("type", "text");
      action.setAttribute("name", "action");
      action.setAttribute("value","rejectRequest");
      form.appendChild(action);
      var ref = document.createElement("input");
      ref.setAttribute("type", "text");
      ref.setAttribute("name", "ref");
      ref.setAttribute("value", pendingRef);
      form.appendChild(ref);
      HTTPPostRequest(url, form, null);

      animateCSS("#divPending" + pendingRef, 'bounceOut').then((message) => {
        $("#divPending" + pendingRef).remove();
      });


    } catch (err) {
      console.log(err.stack);
    }
  }

  // Request the list of conneced users
  GetConnectionReq() {
    try {

      console.log("GetConnectionReq " + this.sessionKey);

      var url = "./api.php";
      var form = document.createElement("form");
      form.setAttribute("method", "post");
      var action = document.createElement("input");
      action.setAttribute("type", "text");
      action.setAttribute("name", "action");
      action.setAttribute("value","getUser");
      form.appendChild(action);
      var key = document.createElement("input");
      key.setAttribute("type", "text");
      key.setAttribute("name", "key");
      key.setAttribute("value", this.sessionKey);
      form.appendChild(key);
      HTTPPostRequest(url, form, this.GetConnectionCb.bind(this));

    } catch (err) {
      console.log(err.stack);
    }
  }

  GetConnectionCb(ret) {
    try {

      console.log("GetConnectionCb " + JSON.stringify(ret));

      // Memorise the connection requests
      this.connections = ret;

      // Loop on the connections
      for (const [iConnection, connection] of Object.entries(this.connections)) {

        // If the session is not in the DOM
        if ($("#divConnection" + connection["Ref"]).length == 0) {

          // Add the connection to the DOM
          var divConnection = document.createElement("div");
          divConnection.id = "divConnection" + connection["Ref"];
          divConnection.className = "divConnection";
          divConnection.setAttribute("refConnection", connection["Ref"]);
          var connectionName = document.createTextNode(connection["Name"]);
          divConnection.append(connectionName);
          $("#divConnections").append(divConnection);
          animateCSS("#divConnection" + connection["Ref"], 'bounceIn');

        }

      }

      // Loop on the connections in the DOM
      var server = this;
      $(".divConnection").each(function() {
        if (server.ConnectionExists($(this).attr('refConnection')) == false) {
          var id = "#" + $(this).attr('id');
          animateCSS(id, 'bounceOut').then((message) => {
            $(id).remove();
          });
        }
      });

    } catch (err) {
      console.log(err.stack);
    }

  }

  ConnectionExists(refConnection) {
    try {

      for (const [iConnection, connection] of Object.entries(this.connections)) {

        if (connection["Ref"] == refConnection)
          return true;

      }

      return false;

    } catch (err) {
      console.log(err.stack);
    }

  }

  Loop() {
    try {

      // Send the requests to update data
      this.GetSessionReq();
      this.GetPendingReq();
      this.GetConnectionReq();

    } catch (err) {
      console.log(err.stack);
    }
  }

  // Get the key of a session from its reference
  GetSessionKey(ref) {
    try {
      for (const [iSession, session] of Object.entries(this.sessions)) {
        if (session["Ref"] == ref) return session["Key"];
      }
      return "";
    } catch (err) {
      console.log(err.stack);
    }
  }

  SessionCloseReq(sessionRef) {
    try {

      var sessionKey = this.GetSessionKey(sessionRef);
      console.log("SessionCloseReq " + sessionKey);

      var url = "./api.php";
      var form = document.createElement("form");
      form.setAttribute("method", "post");
      var action = document.createElement("input");
      action.setAttribute("type", "text");
      action.setAttribute("name", "action");
      action.setAttribute("value","closeSession");
      form.appendChild(action);
      var key = document.createElement("input");
      key.setAttribute("type", "text");
      key.setAttribute("name", "key");
      key.setAttribute("value", sessionKey);
      form.appendChild(key);
      HTTPPostRequest(url, form, null);

      if (this.sessionRef == sessionRef) {

        this.sessionKey = "0";
        this.sessionRef = 0;

      }

      animateCSS("#divSession" + sessionRef, 'bounceOut').then((message) => {
        $("#divSession" + sessionRef).remove();
      });


    } catch (err) {
      console.log(err.stack);
    }
  }

}
