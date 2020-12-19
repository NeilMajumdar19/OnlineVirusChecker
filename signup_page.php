<?php

require_once 'login.php';
require_once 'common_functions.php';

$conn = new mysqli($hn, $un, $pw, $db);

if($conn->connect_error) die(mysql_fatal_error());

echo "<!DOCTYPE html>\n<html><head><title>Sign Up</title>";

echo <<<_END
   <script>
     function validateCredentials()
     {
        let username = document.forms["signup"]["username"].value;
        let errorFlag = true;

        if(username == "")
        {
           alert("No username was entered");
           errorFlag = false;
        }
        else if(username.length < 5)
        {
           alert("Usernames must be at least 5 characters");
           errorFlag = false;
        }

        let password = document.forms["signup"]["password"].value;

        if(password == "")
        {
           alert("No password was entered");
           errorFlag = false;
        }
        else if(password.length < 6)
        {
           alert("Passwords must be at least 6 characters");
           errorFlag = false;
        }

        return errorFlag;


     }

    </script>
  </head>
  <body>
    <form method='post'  name= "signup" action='signup_page.php' onSubmit= "return validateCredentials()" enctype='multipart/form-data'><pre>
           <h1>Sign Up</h1>
           Username: <input type = "text" name = "username">
           Password: <input type = "password" name = "password">
           <input type='submit' value='Register'></pre>
    </form></body></html>
_END;

$username = $password = null;
if(isset($_POST['username']) && isset($_POST['password']))
{
  $username = mysql_entities_fix_string($conn, $_POST['username']);
  $password = mysql_entities_fix_string($conn, $_POST['password']);

  if(username_taken($username, $conn))
  {
    echo "This username is taken. Please choose a different one <br>";
  }
  else
  {
    if(valid_credentials($username, $password))
    {
       $salt = generate_salt();
       $hashed_password = hash_password($password, $salt);
       add_user($username, $hashed_password, $salt, $conn);
       echo "Successful registration <br>";

    }
  }

}

echo "<p><a href=login_page.php>Click here to login</a></p>";

$conn->close();

//insert user credentials into table
function add_user($username, $password, $salt, $conn)
{
  $stmt = $conn->prepare('INSERT INTO users VALUES(?,?,?)');
  $stmt->bind_param('sss', $username, $password, $salt);

  $username = $username;
  $password = $password;
  $salt = $salt;

  $stmt->execute();
  if($stmt->affected_rows == 0)
     mysql_fatal_error();

  $stmt->close();
}

//check if username is taken
function username_taken($username, $conn)
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
  $result->close();
  return true;
}

//check if credentials meet requirements
function valid_credentials($username, $password)
{
  if(($username == "") || (strlen($username) < 5) || ($password == "") || (strlen($password) < 6))
     return false;
   return true;
}


?>
