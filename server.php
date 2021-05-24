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
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
      <script src="./webchaussette.js"></script>
      <style>
body {
  background-color: #aaaaaa;
  color: #433126;
  text-align: center;
}

#divTitle {
  text-align: center;
  font-size: 25px;
  margin: 10px;
  padding: 10px;
}

#divMain {
  margin: 10px;
  min-width: 250px;
  width: 90%;
  min-height: 300px;
  border: 1px solid #888888;
  background-color: #cccccc;
  display: inline-block;
  vertical-align: middle;
  overflow: auto;
  text-align: center;
}

      </style>
      <title>WebChaussette - server</title>
  </head>
  <body>
    <div id="divTitle">WebChaussette - server</div>
    <div id="divMain"></div>
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
