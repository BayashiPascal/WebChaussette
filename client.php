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
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
      <script src="./webchaussette.js"></script>
      <style>
body {
  background-color: #aaaaaa;
  color: #433126;
  text-align: center;
}

select {
  background-color: #fecb5e; 
  box-shadow: 2px 2px 10px #888888;
  margin: 2px 5px;
  padding: 2px 4px;
  font: 13px sans-serif;
  text-decoration: none;
  border: 1px solid #fee9aa;
  border-radius: 5px;
  color: #624838;
  font: 13px sans-serif;
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

#divData {
  display: inline-block;
}

table {
  margin: 5px;
  border-spacing: 0;
}

th {
  border: 1px solid #aaaaaa;
  border-bottom: 2px solid #aaaaaa;
  margin: 0;
  padding: 3px;
}

td {
  border: 1px solid #aaaaaa;
  margin: 0;
  padding: 3px;
}

      </style>
      <title>RunRecorder</title>
  </head>
  <body>
    <div id="divTitle">WebChaussette</div>
    <div id="divMain">
      <div id="divSel">
        <select id="selProject" onchange="SelProject();"></select>
      </div>
      <div id="divData"></div>
    </div>
  </body>
  <script>
    window.onload = function(){
      try {

        // Create the WebChaussette client
        window.wcClient = new WCClient();

        // Start the auto refresh of displayed data every 5s
        setInterval(WCClientMain, 5000);

      } catch (err) {
        console.log(err.stack);
      }
    };
  </script> 
</html>
