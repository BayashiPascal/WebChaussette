<?php
// ------------------ client.php ---------------------

// Start the PHP session
session_start();

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <meta name="description" content="WebChaussette"/>
      <link href="webchaussette.css" rel="stylesheet"/>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
      <script src="./webchaussette.js"></script>
      <title>WebChaussette - client</title>
  </head>
  <body>
    <div id="divTitle">WebChaussette - client</div>
    <div id="divMain">
      <div id="divLoginRequest" class="loginStatus">
        Session key:<input id="inpKey" type="text"><br>
        Your name:<input id="inpName" type="text"><br>
        <input type="button" value="Request connection" onClick="WCClientRequestLogin();">
      </div>
      <div id="divLoginWait" class="loginStatus" style="display:none;">
        Waiting for the host to grant your request.<br>
        <input type="button" value="Give up ?" onClick="WCClientGiveupLogin();">
      </div>
      <div id="divLoginDenied" class="loginStatus" style="display:none;">
        The host denied your request.<br>
        <input type="button" value="Try again ?" onClick="WCClientRequestAgain();">
      </div>
      <div id="divLoginDisconnected" class="loginStatus" style="display:none;">
        You've been disconnected.<br>
        <input type="button" value="Connect again ?" onClick="WCClientReconnect();">
      </div>
      <div id="divLoginGranted" class="loginStatus" style="display:none;">
        You're connected!
      </div>
    </div>
  </body>
  <script>
    window.onload = function(){
      try {

        // Start the WebChaussette client
        WCClientMain();

      } catch (err) {
        console.log(err.stack);
      }
    };
  </script> 
</html>
