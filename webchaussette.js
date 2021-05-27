const animateCSS = (element, animation, prefix = 'animate__') =>
  // We create a Promise and return it
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

function WCClientMain() {
  try {

    // Create the WebChaussette client
    window.wcClient = new WCClient();

    // Start the main loop of the client
    setInterval(WCClientLoop, 5000);

  } catch (err) {
    console.log(err.stack);
  }
}

function WCServerMain() {
  try {

    // Create the WebChaussette server
    window.wcServer = new WCServer();

    // Start the main loop of the server
    setInterval(WCServerLoop, 5000);

  } catch (err) {
    console.log(err.stack);
  }
}

function WCClientLoop() {window.wcClient.Loop();}
function WCClientLogin(ret) {window.wcClient.Login(ret);}
function WCClientCheckLogin(ret) {window.wcClient.CheckLogin(ret);}
function WCServerCreateSession() {window.wcServer.CreateSession();}
function WCServerLoop() {window.wcServer.Loop();}
function WCServerGetSession(ret) {window.wcServer.GetSession(ret);}
function WCServerGetRequest(ret) {window.wcServer.GetRequest(ret);}
function WCServerGetUser(ret) {window.wcServer.GetUser(ret);}
function WCServerSessionShow(event) {window.wcServer.SessionShow(event.target.sessionRef);};
function WCServerSessionClose(event) {window.wcServer.SessionClose(event.target.sessionRef);};

function WCClientRequestLogin() {

  window.wcClient.name = $("#inpName").val();
  window.wcClient.key = $("#inpKey").val();

  var url = "./api.php";
  var form = document.createElement("form");
  form.setAttribute("method", "post");
  var action = document.createElement("input");
  action.setAttribute("type", "text");
  action.setAttribute("name", "action");
  action.setAttribute("value","login");
  form.appendChild(action);
  var name = document.createElement("input");
  name.setAttribute("type", "text");
  name.setAttribute("name", "name");
  name.setAttribute("value", window.wcClient.name);
  form.appendChild(name);
  var key = document.createElement("input");
  key.setAttribute("type", "text");
  key.setAttribute("name", "key");
  key.setAttribute("value", window.wcClient.key);
  form.appendChild(key);
  HTTPPostRequest(url, form, WCClientLogin);

  animateCSS('#divLoginRequest', 'bounceOut').then((message) => {
    $("#divLoginRequest").css("display", "none");
    animateCSS('#divLoginWait', 'bounceIn');
    $("#divLoginWait").css("display", "block");
  });

  window.wcClient.status = "wait login";

}

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

      this.status = "request login";
      this.name = "";
      this.key = "";

    } catch (err) {
      console.log(err.stack);
    }
  }

  Loop() {
    try {

      if (this.status == "request login") {


      } else if (this.status == "wait login") {

        var url = "./api.php";
        var form = document.createElement("form");
        form.setAttribute("method", "post");
        var action = document.createElement("input");
        action.setAttribute("type", "text");
        action.setAttribute("name", "action");
        action.setAttribute("value","checkLogin");
        form.appendChild(action);
        var name = document.createElement("input");
        name.setAttribute("type", "text");
        name.setAttribute("name", "name");
        name.setAttribute("value","pascal");
        form.appendChild(name);
        var key = document.createElement("input");
        key.setAttribute("type", "text");
        key.setAttribute("name", "key");
        key.setAttribute("value", this.key);
        form.appendChild(key);
        HTTPPostRequest(url, form, WCClientCheckLogin);

      } else if (this.status == "denied login") {

      } else if (this.status == "active") {

      }

    } catch (err) {
      console.log(err.stack);
    }
  }

  Login(ret) {
    try {

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

  CheckLogin(ret) {
    try {

      if (ret["err"] == 0) {

        animateCSS('#divLoginWait', 'bounceOut').then((message) => {
          $("#divLoginWait").css("display", "none");
          animateCSS('#divLoginGranted', 'bounceIn');
          $("#divLoginGranted").css("display", "block");
        });
        this.status = "active";

      }

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

  CreateSession() {
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

    } catch (err) {
      console.log(err.stack);
    }
  }

  Loop() {
    try {
console.log(this.sessionKey);
      var url = "./api.php";
      var form = document.createElement("form");
      form.setAttribute("method", "post");
      var action = document.createElement("input");
      action.setAttribute("type", "text");
      action.setAttribute("name", "action");
      action.setAttribute("value","getSession");
      form.appendChild(action);
      HTTPPostRequest(url, form, WCServerGetSession);

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
      HTTPPostRequest(url, form, WCServerGetRequest);

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
      HTTPPostRequest(url, form, WCServerGetUser);

    } catch (err) {
      console.log(err.stack);
    }
  }

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

  GetSession(ret) {
    try {

      this.sessions = ret;

      for (const [iSession, session] of Object.entries(this.sessions)) {

        if ($("#divSession" + session["Ref"]).length == 0) {

          var divSession = document.createElement("div");
          divSession.id = "divSession" + session["Ref"];
          var sessionKey = document.createTextNode(session["Key"]);
          divSession.append(sessionKey);
          var btnShow = document.createElement("input");
          btnShow.type = "button";
          btnShow.value = "Show";
          btnShow.sessionRef = session["Ref"];
          btnShow.onclick = WCServerSessionShow;
          divSession.append(btnShow);
          var btnClose = document.createElement("input");
          btnClose.type = "button";
          btnClose.value = "Close";
          btnClose.sessionRef = session["Ref"];
          btnClose.onclick = WCServerSessionClose;
          divSession.append(btnClose);
          $("#divSessions").append(divSession);

        }

        if (this.sessionRef == 0) {

          this.sessionRef = session["Ref"];
          this.sessionKey = session["Key"];
          $("#divSession" + this.sessionRef).addClass("divCurrentSession");

        }

      }

    } catch (err) {
      console.log(err.stack);
    }
  }

  SessionShow(sessionRef) {
    try {

      $("#divSession" + this.sessionRef).removeClass("divCurrentSession");
      this.sessionKey = this.GetSessionKey(sessionRef);
      this.sessionRef = sessionRef;
      $("#divSession" + this.sessionRef).addClass("divCurrentSession");
      $("#divWaitingRequests").empty();
      $("#divConnectedUsers").empty();

    } catch (err) {
      console.log(err.stack);
    }
  }

  SessionClose(sessionRef) {
    try {

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
      var sessionKey = this.GetSessionKey(sessionRef);
      key.setAttribute("value", sessionKey);
      form.appendChild(key);
      HTTPPostRequest(url, form, null);

      if (this.sessionRef == sessionRef) {

        this.sessionKey = "0";
        this.sessionRef = 0;

      }

      $("#divSession" + sessionRef).remove();

    } catch (err) {
      console.log(err.stack);
    }
  }

  GetRequest(ret) {
    try {
console.log(ret);
      //$("#divWaitingRequests").html(JSON.stringify(ret));

    } catch (err) {
      console.log(err.stack);
    }
  }

  GetUser(ret) {
    try {

      //$("#divConnectedUsers").html(JSON.stringify(ret));

    } catch (err) {
      console.log(err.stack);
    }
  }

}
