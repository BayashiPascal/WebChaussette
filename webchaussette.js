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
function WCServerLoop() {window.wcServer.Loop();}
function WCServerGetRequest(ret) {window.wcServer.GetRequest(ret);}

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

        handler(returnedData);

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

function WCClient() {
  try {

    this.status = "request login";

  } catch (err) {
    console.log(err.stack);
  }
}

WCClient.prototype.Loop = function() {
  try {

    if (this.status == "request login") {

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
      name.setAttribute("value","pascal");
      form.appendChild(name);
      HTTPPostRequest(url, form, WCClientLogin);

    } else if (this.status == "wait login") {

    } else if (this.status == "idle") {
    }

  } catch (err) {
    console.log(err.stack);
  }
}

WCClient.prototype.Login = function(ret) {
  try {

    this.status = "wait login";

  } catch (err) {
    console.log(err.stack);
  }
}

// ------------ WCServer class

function WCServer() {
  try {


  } catch (err) {
    console.log(err.stack);
  }
}

WCServer.prototype.Loop = function() {
  try {

    var url = "./api.php";
    var form = document.createElement("form");
    form.setAttribute("method", "post");
    var action = document.createElement("input");
    action.setAttribute("type", "text");
    action.setAttribute("name", "action");
    action.setAttribute("value","getRequest");
    form.appendChild(action);
    HTTPPostRequest(url, form, WCServerGetRequest);

  } catch (err) {
    console.log(err.stack);
  }
}

WCServer.prototype.GetRequest = function(ret) {
  try {

    $("#divMain").html(JSON.stringify(ret));

  } catch (err) {
    console.log(err.stack);
  }
}

