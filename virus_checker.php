<?php

require_once 'login.php';
require_once 'common_functions.php';

$conn = new mysqli($hn, $un, $pw, $db);

if($conn->connect_error) die(mysql_fatal_error());

session_start();
if((isset($_SESSION['username'])) && ($_SESSION['check'] == hash('ripemd128', $_SERVER['REMOTE_ADDR'] .$_SERVER['HTTP_USER_AGENT'])))
{

  if(!isset($_SESSION['initiated']))
  {
    session_regenerate_id();
    $_SESSION['initiated'] = 1;
  }

  echo "<!DOCTYPE html>\n<html><head><title>Virus Checker</title>";
  if(isset($_POST['logout']))
  {
    echo "<h1>Logged out</h1><br>";
    echo "<a href='login_page.php'>Click here</a> to login";
    destroy_session_and_data();
  }
  else
  {
    echo "<h1>Virus Checker</h1>";
    echo <<<_END
           <form method='post' action='virus_checker.php' enctype='multipart/form-data'>
                  Select File: <input type='file' name='filename' size='10'>
                  <input type='submit' value='Upload'>
           </form>
   _END;
   echo "<br>";


    if($_FILES)
    {
        if($_FILES['filename']['name'] == null)
        {
          echo "No file has been uploaded <br>";
        }
        else
        {
          $filename = $_FILES['filename']['name'];
          $filename = preg_replace("/[^A-Za-z0-9.]/", "", $filename);
          $content = get_file_contents($filename);

          if(file_infected($content, $conn))
             echo "File infected <br>";
          else
             echo "File not infected <br>";


        }

    }
    echo "<br>";
    echo <<<_END
        <form method='post' action='virus_checker.php' enctype='multipart/form-data'>
               <input type = 'hidden' name = 'logout'>
               <input type='submit' value='Logout'>
        </form>
  _END;
  }
}
else
{
  echo "<!DOCTYPE html>\n<html><head><title>Not logged in</title>";
  echo "<h1>Not logged in </h1><br>";
  echo "Please <a href='login_page.php'>click here</a> to login";
}

$conn->close();

//gets contents of file
function get_file_contents($filename)
{
  $fh = fopen($filename, 'r') or die("Failed to open file");
  $contents = "";
  while(!feof($fh))
  {
    $line = fgets($fh);
    $contents .= $line;
  }
  fclose($fh);
  return $contents;
}

function file_infected($content, $conn)
{
  $query = "SELECT signature FROM malware_info";
  $result = $conn->query($query);

  if(!$result) die(mysql_fatal_error());

  $rows = $result->num_rows;

  for($i = 0; $i < $rows; $i++)
  {
    $result->data_seek($i);
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $pos = strpos($content, $row['signature']);
    if($pos !== false)
      return true;
  }

  $result->close();
  return false;
}

//destroy session
function destroy_session_and_data()
{
  $_SESSION = array();
  setcookie(session_name(), '', time() - 2592000, '/');
  session_destroy();
}



?>
