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

    // If the user requested an unknown or invalid action
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
