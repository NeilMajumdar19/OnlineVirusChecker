<?php

require_once 'login.php';
require_once 'common_functions.php';

$conn = new mysqli($hn, $un, $pw, $db);

if($conn->connect_error) die(mysql_fatal_error());

if(!credentials_exist($conn))
   add_admin_credentials("admin", "password", $conn);

echo "<!DOCTYPE html>\n<html><head><title>Admin Login</title>";

echo <<<_END
  <form method='post' action='admin_login.php' enctype='multipart/form-data'><pre>
        <h1>Admin Login</h1>
        Username: <input type = "text" name = "username">
        Password: <input type = "password" name = "password">
        <input type='submit' value='Login'></pre>
  </form>
_END;

   $username = $password = null;

if(isset($_POST['username']) && isset($_POST['password']))
{
  $username = mysql_entities_fix_string($conn, $_POST['username']);
  $password = mysql_entities_fix_string($conn, $_POST['password']);

  if(($username == null) || ($password == null))
  {
    echo "Must fill out all fields to login <br>";
  }
  else
  {
    $admin = get_admin_info($username, $conn);
    if($admin === false)
    {
      echo "Invalid username/password combination <br>";
    }
    else
    {
      $salt = $admin['salt'];
      if(valid_password($admin, $password, $salt))
      {
        session_start();
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_password'] = $password;
        $_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] .$_SERVER['HTTP_USER_AGENT']);
        echo "Login successful";
        die("<p><a href=malware_upload.php>Click here to continue</a></p>");

      }
      else
      {
        echo "Invalid username/password combination <br>";
      }

    }

  }
}

echo "<p><a href=login_page.php>Click here to login as user</a></p>";
$conn->close();

//returns info associated with username
function get_admin_info($username, $conn)
{
  $query = "SELECT * FROM admins WHERE username = '$username'";
  $result = $conn->query($query);
  if(!$result) die(mysql_fatal_error());

  $rows = $result->num_rows;
  if($rows == 0)
  {
    $result->close();
    return false;
  }
  else
  {
    $result->data_seek(0);
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $result->close();
    return $row;
  }
}

function credentials_exist($conn)
{
  $query = "SELECT COUNT(*) FROM admins";
  $result = $conn->query($query);

  if(!$result) die(mysql_fatal_error());

  $rows = $result->num_rows;

  $result->data_seek(0);
  $row = $result->fetch_array(MYSQLI_NUM);

  if($row[0] == 0)
  {
     $result->close();
     return false;
  }

  $result->close();
  return true;
}

function add_admin_credentials($username, $password, $conn)
{
  $salt = generate_salt();
  $hashed_password = hash_password($password, $salt);

  $stmt = $conn->prepare('INSERT INTO admins VALUES(?,?,?)');
  $stmt->bind_param('sss', $username, $password, $salt);

  $username = $username;
  $password = $hashed_password;
  $salt = $salt;

  $stmt->execute();
  if($stmt->affected_rows == 0)
     mysql_fatal_error();

  $stmt->close();
}




?>
