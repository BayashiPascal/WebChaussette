<?php
// ------------------ api.php ---------------------

// Start the PHP session
session_start();

// Switch the display of errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//error_reporting(E_NONE);

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
      "CREATE TABLE RequestLogin (" .
      "  Ref INTEGER PRIMARY KEY," .
      "  Key TEXT NOT NULL," .
      "  Name TEXT NOT NULL)",
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

// Process a request for login
// Input:
//   key: session key
//   name: name of the user
function RequestLogin(
  $db,
  $key,
  $name) {

  $res = array();
  $res["err"] = 0; 

  try {

    // Check the session exists
    $cmd = "SELECT Ref FROM SessionKey WHERE Key = '" . $key . "'";
    $rows = $db->query($cmd);
    if ($rows == false) throw new Exception("query(" . $cmd . ") failed");
    $row = $rows->fetchArray();
    if ($row != false)
      $res["err"] = 1;

    // Check the name doesn't exist for this session
    $cmd = "SELECT Ref FROM RequestLogin WHERE Key = '" . $key . "' ";
    $cmd .= "AND Name = '" . $name . "'";
    $rows = $db->query($cmd);
    if ($rows == false) throw new Exception("query(" . $cmd . ") failed");
    $row = $rows->fetchArray();
    if ($row != false)
      $res["err"] = 2;

    $cmd = "SELECT Ref FROM Connection WHERE Key = '" . $key . "' ";
    $cmd .= "AND Name = '" . $name . "'";
    $rows = $db->query($cmd);
    if ($rows == false) throw new Exception("query(" . $cmd . ") failed");
    $row = $rows->fetchArray();
    if ($row != false)
      $res["err"] = 2;

    if ($res["err"] == 0) {

      // Add the request
      $cmd = "INSERT INTO RequestLogin (Key, Name) ";
      $cmd .= "VALUES ('" . $key . "', '" . $name . "')";
      $success = $db->exec($cmd);
      if ($success == false)
        throw new Exception("exec() failed for " . $cmd);

    }

  } catch (Exception $e) {

    // Rethrow the exception it will be managed in the main block
    throw($e);

  }

  return $res;

}

// Check if a user has login
// Input:
//   key: session key
//   name: name of the user
function CheckLogin(
  $db,
  $key,
  $name) {

  $res = array();
  $res["err"] = 0; 

  try {

    // Check the login
    $cmd = "SELECT Ref FROM Connection WHERE Key = '" . $key . "' ";
    $cmd .= "AND Name = '" . $name . "'";
    $rows = $db->query($cmd);
    if ($rows == false) throw new Exception("query(" . $cmd . ") failed");
    $row = $rows->fetchArray();
    if ($row == false)
      $res["err"] = 1;

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

    // Get the session
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
    $cmd = "SELECT Ref, Name FROM RequestLogin WHERE Key = '" . $key . "'";
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

      $res = CreateSession($db);
      echo json_encode($res);

    // Else, if the server requested a new session
    } else if ($_POST["action"] == "createSession" and
      isset($_POST["key"])) {

      $res = CreateSession($db, $_POST["key"]);
      echo json_encode($res);

    // Else, if the user requested to login
    } else if ($_POST["action"] == "login" and
      isset($_POST["key"]) and isset($_POST["name"])) {

      $res = RequestLogin($db, $_POST["key"], $_POST["name"]);
      echo json_encode($res);

    // Else, if the server requested the list of request
    } else if ($_POST["action"] == "getRequest" and
      isset($_POST["key"])) {

      $res = GetRequest($db, $_POST["key"]);
      echo json_encode($res);

    // Else, if the server requested the list of request
    } else if ($_POST["action"] == "getUser" and
      isset($_POST["key"])) {

      $res = GetUser($db, $_POST["key"]);
      echo json_encode($res);

    // Else, if the user requested to check its login
    } else if ($_POST["action"] == "checkLogin" and
      isset($_POST["key"]) and isset($_POST["name"])) {

      $res = CheckLogin($db, $_POST["key"], $_POST["name"]);
      echo json_encode($res);

    // If the user/server requested an unknown or invalid action
    } else {

      echo '{"ret":"1","errMsg":"Invalid action"}';

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
