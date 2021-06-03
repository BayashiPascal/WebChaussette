<?php
// ------------------ api.php ---------------------

// Start the PHP session
session_start();

// Switch the display of errors
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(E_NONE);

// Path to the SQLite3 database
$pathDB = "./webchaussette.db";

// Version of the database
$versionDB = "01.00.00";

// Create the database
// Inputs:
//      path: path of the database
//   version: version of the database
// Output:
//   Return the database connection
function CreateDatabase(
  $path,
  $version) {

  try {

    // Create and open the database
    $db = new SQLite3($path);

    // Create the database tables
    $cmds = [
      "CREATE TABLE Version (" .
      "  Ref INTEGER PRIMARY KEY," .
      "  Label TEXT NOT NULL)",
      "CREATE TABLE SessionKey (" .
      "  Ref INTEGER PRIMARY KEY," .
      "  Key TEXT NOT NULL)",
      "CREATE TABLE RequestConnection (" .
      "  Ref INTEGER PRIMARY KEY," .
      "  Key TEXT NOT NULL," .
      "  Name TEXT NOT NULL)",
      "CREATE TABLE Data (" .
      "  Ref INTEGER PRIMARY KEY," .
      "  RefSrcConnection INTEGER NOT NULL," .
      "  Key TEXT NOT NULL," .
      "  Value TEXT NOT NULL)",
      "CREATE TABLE DataDispatch (" .
      "  Ref INTEGER PRIMARY KEY," .
      "  RefData INTEGER NOT NULL," .
      "  RefConnection INTEGER NOT NULL)",
      "CREATE TABLE Connection (" .
      "  Ref INTEGER PRIMARY KEY," .
      "  Key TEXT NOT NULL," .
      "  Name TEXT NOT NULL)"];
    foreach ($cmds as $cmd) {

      $success = $db->exec($cmd);

      if ($success === false) throw new Exception("exec() failed for " . $cmd);

    }

    // Set the version
    $success = 
      $db->exec(
        "INSERT INTO Version (Ref, Label) " .
        "VALUES (NULL, '" . $version . "')");
    if ($success === false) throw new Exception("exec() failed for " . $cmd);

    // Return the database connection
    return $db;

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

}

// Get the version of the database
// Input:
//   db: the database connection
// Output:
//   If successful returns the dictionary {"ret":"0", "version":"..."}.
//   Else, returns the dictionary {"ret":"1", "errMsg":"..."}.
function GetVersion(
  $db) {

  // Initialise the result dictionary
  $res = array();

  try {

    // Get the version
    $rows = $db->query("SELECT Label FROM Version LIMIT 1");
    if ($rows === false) throw new Exception("query() failed");

    // Update the result dictionary
    $res["version"] = ($rows->fetchArray())["Label"];
    $res["ret"] = "0";

  } catch (Exception $e) {

    $res["ret"] = "1";
    $res["errMsg"] = "line " . $e->getLine() . ": " . $e->getMessage();

  }

  // Return the dictionary
  return $res;

}

// Upgrade the database to a given version
// Input:
//           db: the database connection
//   tgtVersion: the requested version
function UpgradeDB(
  $db,
  $tgtVersion) {

  // Get the version of the database
  $rows = $db->query("SELECT Label FROM Version LIMIT 1");
  if ($rows === false) throw new Exception("query() failed");
  $version = ($rows->fetchArray())["Label"];

  // If the database version is older than the version of the API
  if ($version < $tgtVersion) {

    // Upgrade the database
    // placeholder...

  }
}

// Create a session
// Input:
//   key: the session key
function CreateSession(
  $db,
  $key) {

  try {

    // Add the session
    $cmd = "INSERT INTO SessionKey (Key) VALUES ('" . $key . "')";
    $success = $db->exec($cmd);
    if ($success == false)
      throw new Exception("exec() failed for " . $cmd);

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

  return "";

}

// Close a session
// Input:
//   key: the session key
function CloseSession(
  $db,
  $key) {

  try {

    // Delete the session
    $cmd = "DELETE FROM Connection WHERE Key = '" . $key . "'";
    $success = $db->exec($cmd);
    if ($success == false)
      throw new Exception("exec() failed for " . $cmd);
    $cmd = "DELETE FROM RequestConnection WHERE Key = '" . $key . "'";
    $success = $db->exec($cmd);
    if ($success == false)
      throw new Exception("exec() failed for " . $cmd);
    $cmd = "DELETE FROM SessionKey WHERE Key = '" . $key . "'";
    $success = $db->exec($cmd);
    if ($success == false)
      throw new Exception("exec() failed for " . $cmd);
    $cmd = "DELETE FROM DataDispatch WHERE RefData IN " .
      "(SELECT Ref FROM Data WHERE Key = '" . $key . "')";
    $success = $db->exec($cmd);
    if ($success == false)
      throw new Exception("exec() failed for " . $cmd);
    $cmd = "DELETE FROM Data WHERE Key = '" . $key . "'";
    $success = $db->exec($cmd);
    if ($success == false)
      throw new Exception("exec() failed for " . $cmd);

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

  return "";

}

// Accept a request for connection
// Input:
//   ref: the request reference
function AcceptRequest(
  $db,
  $ref) {

  try {

    // Create the connection
    
    $cmd = "SELECT Key, Name FROM RequestConnection WHERE Ref = " . $ref;
    $rows = $db->query($cmd);
    if ($rows == false) throw new Exception("query(" . $cmd . ") failed");
    $row = $rows->fetchArray();
    $stmt = $db->prepare(
      "INSERT INTO Connection (Key, Name) VALUES (:key, :name)");
    $stmt->bindValue(":key", $row["Key"], SQLITE3_TEXT);
    $stmt->bindValue(":name", $row["Name"], SQLITE3_TEXT);
    $result = $stmt->execute();
      if ($result == false)
        throw new Exception("query(" . $stmt->getSQL() . ") failed");

    // Delete the request
    $cmd = "DELETE FROM RequestConnection WHERE Ref = '" . $ref . "'";
    $success = $db->exec($cmd);
    if ($success == false)
      throw new Exception("exec() failed for " . $cmd);

    // Add a message to let everyone knows they have a new friend
    $data = array();
    $data["user"] = "the server";
    $data["msg"] = $row["Name"] . " has joined the session.";
    ReceiveData(
      $db,
      $row["Key"],
      $row["Name"],
      json_encode($data));

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

  return "";

}

// Reject a request for connection
// Input:
//   ref: the request reference
function RejectRequest(
  $db,
  $ref) {

  try {

    // Delete the request
    $cmd = "DELETE FROM RequestConnection WHERE Ref = '" . $ref . "'";
    $success = $db->exec($cmd);
    if ($success == false)
      throw new Exception("exec() failed for " . $cmd);

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

  return "";

}

// Process a request for connection
// Input:
//   key: session key
//   name: name of the user
function RequestConnection(
  $db,
  $key,
  $name) {

  $res = array();
  $res["err"] = 0; 

  try {

    // Check the session exists
    $stmt = $db->prepare("SELECT Ref FROM SessionKey WHERE Key = :key");
    $stmt->bindValue(":key", $key, SQLITE3_TEXT);
    $result = $stmt->execute();
      if ($result == false)
        throw new Exception("query(" . $stmt->getSQL() . ") failed");
    $row = $result->fetchArray();
    if ($row == false) {

      $res["err"] = 1;
      return $res;

    }

    // Check the name doesn't exist for this session
    $stmt = $db->prepare(
      "SELECT Ref FROM RequestConnection WHERE Key = :key AND Name = :name");
    $stmt->bindValue(":key", $key, SQLITE3_TEXT);
    $stmt->bindValue(":name", $name, SQLITE3_TEXT);
    $result = $stmt->execute();
      if ($result == false)
        throw new Exception("query(" . $stmt->getSQL() . ") failed");
    $row = $result->fetchArray();
    if ($row != false) {

      $res["err"] = 2;
      return $res;

    }

    $stmt = $db->prepare(
      "SELECT Ref FROM Connection WHERE Key = :key AND Name = :name");
    $stmt->bindValue(":key", $key, SQLITE3_TEXT);
    $stmt->bindValue(":name", $name, SQLITE3_TEXT);
    $result = $stmt->execute();
      if ($result == false)
        throw new Exception("query(" . $stmt->getSQL() . ") failed");
    $row = $result->fetchArray();
    if ($row != false) {

      $res["err"] = 3;
      return $res;

    }

    // Add the request
    $stmt = $db->prepare(
      "INSERT INTO RequestConnection (Key, Name) VALUES (:key, :name)");
    $stmt->bindValue(":key", $key, SQLITE3_TEXT);
    $stmt->bindValue(":name", $name, SQLITE3_TEXT);
    $result = $stmt->execute();
      if ($result == false)
        throw new Exception("query(" . $stmt->getSQL() . ") failed");

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

  return $res;

}

// Check the connection status of a user
// Input:
//   key: session key
//   name: name of the user
function CheckConnection(
  $db,
  $key,
  $name) {

  $res = array();
  $res["err"] = 0; 

  try {

    // Check the Connection
    $stmt = $db->prepare(
      "SELECT Ref FROM Connection WHERE Key = :key AND Name = :name");
    $stmt->bindValue(":key", $key, SQLITE3_TEXT);
    $stmt->bindValue(":name", $name, SQLITE3_TEXT);
    $result = $stmt->execute();
    if ($result == false)
      throw new Exception("query(" . $stmt->getSQL() . ") failed");
    $row = $result->fetchArray();
    if ($row == false) {

      $stmt = $db->prepare(
        "SELECT Ref FROM RequestConnection WHERE Key = :key AND Name = :name");
      $stmt->bindValue(":key", $key, SQLITE3_TEXT);
      $stmt->bindValue(":name", $name, SQLITE3_TEXT);
      $result = $stmt->execute();
      if ($result == false)
        throw new Exception("query(" . $stmt->getSQL() . ") failed");
      $row = $result->fetchArray();
      if ($row == false) {
        $res["err"] = 2;
      } else {
        $res["err"] = 1;
      }
    }

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

  return $res;

}

// Receive data
// Input:
//   key: session key
//   name: name of the user
//   data: json encoded data
function ReceiveData(
  $db,
  $key,
  $name,
  $data) {

  $res = array();
  $res["err"] = 0; 

  try {

    // Check the Connection
    $stmt = $db->prepare(
      "SELECT Ref FROM Connection WHERE Key = :key AND Name = :name");
    $stmt->bindValue(":key", $key, SQLITE3_TEXT);
    $stmt->bindValue(":name", $name, SQLITE3_TEXT);
    $result = $stmt->execute();
      if ($result == false)
        throw new Exception("query(" . $stmt->getSQL() . ") failed");
    $row = $result->fetchArray();
    if ($row !== false) {

      $ref = $row["Ref"];

      // Save the data
      $stmt = $db->prepare(
        "INSERT INTO Data (RefSrcConnection, Key, Value)" .
        "VALUES (:ref, :key, :data)");
      $stmt->bindValue(":ref", $ref, SQLITE3_INTEGER);
      $stmt->bindValue(":key", $key, SQLITE3_TEXT);
      $stmt->bindValue(":data", $data, SQLITE3_TEXT);
      $result = $stmt->execute();
      if ($result == false)
        throw new Exception("query(" . $stmt->getSQL() . ") failed");
      $refData = $db->lastInsertRowID();

      $stmt = $db->prepare(
        "INSERT INTO DataDispatch (RefData, RefConnection)" .
        "SELECT :refData as RefData, Ref as RefConnection " .
        "FROM Connection WHERE Key = :key and Ref <> :refConnection");
      $stmt->bindValue(":refData", $refData, SQLITE3_INTEGER);
      $stmt->bindValue(":key", $key, SQLITE3_TEXT);
      $stmt->bindValue(":refConnection", $ref, SQLITE3_INTEGER);
      $result = $stmt->execute();
      if ($result == false)
        throw new Exception("query(" . $stmt->getSQL() . ") failed");

    }

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

  return $res;

}

// Send data
// Input:
//   key: session key
//   name: name of the user
function SendData(
  $db,
  $key,
  $name) {

  $res = array();
  $res["err"] = 0; 

  try {

    // Check the Connection
    $stmt = $db->prepare(
      "SELECT Ref FROM Connection WHERE Key = :key AND Name = :name");
    $stmt->bindValue(":key", $key, SQLITE3_TEXT);
    $stmt->bindValue(":name", $name, SQLITE3_TEXT);
    $result = $stmt->execute();
    if ($result == false)
      throw new Exception("query(" . $stmt->getSQL() . ") failed");
    $row = $result->fetchArray();
    if ($row !== false) {

      $ref = $row["Ref"];
      $stmt = $db->prepare(
        "SELECT Data.Value FROM Data, DataDispatch " .
        "WHERE Data.ref = DataDispatch.RefData AND " .
        "DataDispatch.RefConnection = :ref");
      $stmt->bindValue(":ref", $ref, SQLITE3_INTEGER);
      $result = $stmt->execute();
      if ($result == false)
        throw new Exception("query(" . $stmt->getSQL() . ") failed");
      $res["data"] = array();
      while ($row = $result->fetchArray())
        array_push($res["data"], $row["Value"]);

      $stmt = $db->prepare(
        "DELETE FROM DataDispatch WHERE RefConnection = :ref");
      $stmt->bindValue(":ref", $ref, SQLITE3_INTEGER);
      $result = $stmt->execute();
      if ($result == false)
        throw new Exception("query(" . $stmt->getSQL() . ") failed");

      $stmt = $db->prepare(
        "DELETE FROM Data WHERE Ref NOT IN (" .
        "SELECT RefData FROM DataDispatch)");
      $stmt->bindValue(":ref", $ref, SQLITE3_INTEGER);
      $result = $stmt->execute();
      if ($result == false)
        throw new Exception("query(" . $stmt->getSQL() . ") failed");

    } else {

      $res["err"] = 1; 

    }

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

  return $res;

}

// Get the opened session
function GetSession(
  $db) {

  $res = array();

  try {

    // Get the sessions
    $cmd = "SELECT Ref, Key FROM SessionKey";
    $rows = $db->query($cmd);
    if ($rows == false) throw new Exception("query(" . $cmd . ") failed");
    while ($row = $rows->fetchArray())
      array_push($res, $row);

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

  return $res;

}

// Get the waiting request
function GetRequest(
  $db,
  $key) {

  $res = array();

  try {

    // Get the requests
    $cmd = "SELECT Ref, Name FROM RequestConnection WHERE Key = '" . $key . "'";
    $rows = $db->query($cmd);
    if ($rows == false) throw new Exception("query(" . $cmd . ") failed");
    while ($row = $rows->fetchArray()) {
      array_push($res, $row);
    }

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

  return $res;

}

// Get the connected users
function GetUser(
  $db,
  $key) {

  $res = array();

  try {

    // Get the users
    $cmd = "SELECT Ref, Name FROM Connection WHERE Key = '" . $key . "'";
    $rows = $db->query($cmd);
    if ($rows == false) throw new Exception("query(" . $cmd . ") failed");
    while ($row = $rows->fetchArray())
      array_push($res, $row);

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

  return $res;

}

// -------------------------------- Main block --------------------------

try {

  // Try to open the database
  try {

    $db =
      new SQLite3(
        $pathDB,
        SQLITE3_OPEN_READWRITE);

  // If we couldn't open it, it means it doesn't exist yet
  } catch (Exception $e) {

    // Create the database
    $db =
      CreateDatabase(
        $pathDB,
        $versionDB);

  }

  // Automatically upgrade the database if necessary
  UpgradeDB(
    $db,
    $versionDB);

  // If an action has been requested
  if (isset($_POST["action"])) {

    // If the user requested the version
    if ($_POST["action"] == "version") {

      $res = GetVersion($db);
      echo json_encode($res);

    // Else, if the server requested the session
    } else if ($_POST["action"] == "getSession") {

      $res = GetSession($db);
      echo json_encode($res);

    // Else, if the server requested a new session
    } else if ($_POST["action"] == "createSession" and
      isset($_POST["key"]) and $_POST["key"] != "") {

      $res = CreateSession($db, $_POST["key"]);
      echo json_encode($res);

    // Else, if the server requested to close session
    } else if ($_POST["action"] == "closeSession" and
      isset($_POST["key"]) and $_POST["key"] != "") {

      $res = CloseSession($db, $_POST["key"]);
      echo json_encode($res);

    // Else, if the server accept a request
    } else if ($_POST["action"] == "acceptRequest" and
      isset($_POST["ref"]) and $_POST["ref"] != "") {

      $res = AcceptRequest($db, $_POST["ref"]);
      echo json_encode($res);

    // Else, if the server reject a request
    } else if ($_POST["action"] == "rejectRequest" and
      isset($_POST["ref"]) and $_POST["ref"] != "") {

      $res = RejectRequest($db, $_POST["ref"]);
      echo json_encode($res);

    // Else, if the user requested to connect
    } else if ($_POST["action"] == "connect" and
      isset($_POST["key"]) and isset($_POST["name"]) and
      $_POST["key"] != "" and $_POST["name"] != "") {

      $res = RequestConnection($db, $_POST["key"], $_POST["name"]);
      echo json_encode($res);

    // Else, if the server requested the list of request
    } else if ($_POST["action"] == "getRequest" and
      isset($_POST["key"]) and $_POST["key"] != "") {

      $res = GetRequest($db, $_POST["key"]);
      echo json_encode($res);

    // Else, if the server requested the list of request
    } else if ($_POST["action"] == "getUser" and
      isset($_POST["key"]) and $_POST["key"] != "") {

      $res = GetUser($db, $_POST["key"]);
      echo json_encode($res);

    // Else, if the user requested to check its login
    } else if ($_POST["action"] == "checkConnection" and
      isset($_POST["key"]) and isset($_POST["name"]) and
      $_POST["key"] != "" and $_POST["name"] != "") {

      $res = CheckConnection($db, $_POST["key"], $_POST["name"]);
      echo json_encode($res);

    // Else, if the user requested to send data
    } else if ($_POST["action"] == "sendData" and
      isset($_POST["key"]) and isset($_POST["name"]) and
      isset($_POST["data"]) and $_POST["key"] != "" and
      $_POST["name"] != "" and $_POST["data"] != "") {

      $res = ReceiveData($db,
        $_POST["key"], $_POST["name"], $_POST["data"]);
      echo json_encode($res);

    // Else, if the user requested to receive data
    } else if ($_POST["action"] == "recvData" and
      isset($_POST["key"]) and isset($_POST["name"]) and
      $_POST["key"] != "" and $_POST["name"] != "") {

      $res = SendData($db, $_POST["key"], $_POST["name"]);
      echo json_encode($res);

    // If the user/server requested an unknown or invalid action
    } else {

      echo '{"ret":"1","errMsg":"Invalid action ' . $_POST["action"] . '"}';

    }

  // Else, nothing to do
  } else {

    echo '{"ret":"0"}';

  }

  // Close the database connection
  $db->close();

} catch (Exception $e) {

    $res["ret"] = "1";
    $res["errMsg"] = "line " . $e->getLine() . ": " . $e->getMessage();

}

?>
