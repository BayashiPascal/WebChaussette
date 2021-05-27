<?php
// ------------------ server.php ---------------------

// Start the PHP session
session_start();

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <meta name="description" content="WebChaussette - server"/>
      <link href="webchaussette.css" rel="stylesheet">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
      <script src="./webchaussette.js"></script>
      <title>WebChaussette - server</title>
  </head>
  <body>
    <div id="divTitle">WebChaussette - server</div>
    <div id="divMain">
      <div id="divSessionKey" class="serverInfo">
        <div>Sessions:</div>
        <div><input type="button" value="Create" onClick="WCServerCreateSession();"></div>
        <div id="divSessions"></div>
      </div>
      <div id="divLoginRequest" class="serverInfo">
        <div>Waiting requests:</div>
        <div id="divWaitingRequests"></div>
      </div>
      <div id="divConnectedUser" class="serverInfo">
        <div>Connected users:</div>
        <div id="divConnectedUsers"></div>
      </div>
    </div>
  </body>
  <script>
    window.onload = function(){
      try {

        // Start the WebChaussette server
        WCServerMain();

      } catch (err) {
        console.log(err.stack);
      }
    };

  </script> 
</html>
