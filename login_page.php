<?php

require_once 'login.php';
require_once 'common_functions.php';

$conn = new mysqli($hn, $un, $pw, $db);

if($conn->connect_error) die(mysql_fatal_error());

echo "<!DOCTYPE html>\n<html><head><title>Login</title>";

echo <<<_END
  <form method='post' action='login_page.php' enctype='multipart/form-data'><pre>
         <h1>Login</h1>
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
    $user = get_user_info($username, $conn);
    if($user === false)
    {
      echo "Invalid username/password combination <br>";
    }
    else
    {
      $salt = $user['salt'];
      if(valid_password($user, $password, $salt))
      {
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['password'] = $password;
        $_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] .$_SERVER['HTTP_USER_AGENT']);
        echo "Login successful";
        die("<p><a href=virus_checker.php>Click here to continue</a></p>");

      }
      else
      {
        echo "Invalid username/password combination <br>";
      }

    }

  }
}

echo "<p><a href=signup_page.php>Click here to register</a></p>";
echo "<p><a href=admin_login.php>Click here to login as admin</a></p>";
$conn->close();

//returns info associated with username
function get_user_info($username, $conn)
{
  $query = "SELECT * FROM users WHERE username = '$username'";
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


?>
