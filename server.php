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
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
      <script src="./webchaussette.js"></script>
      <title>WebChaussette - server</title>
  </head>
  <body>
    <div id="divTitle">WebChaussette - server</div>
    <div id="divMain">
      <div id="divSessionKey" class="serverInfo">
        <div>Sessions:</div>
        <div><input id="btnCreateSession" type="button" value="Create" onClick="CreateSessionClick();"></div>
        <div id="divSessions"></div>
      </div>
      <div id="divLoginRequest" class="serverInfo">
        <div>Waiting requests:</div>
        <div id="divPendings"></div>
      </div>
      <div id="divConnectedUser" class="serverInfo">
        <div>Connected users:</div>
        <div id="divConnections"></div>
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
