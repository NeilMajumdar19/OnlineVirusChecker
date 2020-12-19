<?php


//checks if password is correct
function valid_password($user, $password, $salt)
{
   $input_password = hash_password($password, $salt);

   if($input_password === $user['password'])
      return true;
    return false;
}

//generates random salt
function generate_salt()
{
  $rand_num = rand();
  $salt = hash('ripemd128', $rand_num);
  return $salt;
}

//hashes password
function hash_password($password, $salt)
{
  $new_password = $salt.$password;
  $token = hash('ripemd128', $new_password);
  return $token;
}

//sanitize function
function mysql_entities_fix_string($conn, $string)
{
  return htmlentities(mysql_fix_string($conn, $string));
}

//sanitize helper function
function mysql_fix_string($conn, $string)
{
  if (get_magic_quotes_gpc()) $string = stripslashes($string);
  return $conn->real_escape_string($string);
}

//prints error message when mysql connection fails, or result query fails
function mysql_fatal_error()
{
  echo <<<_END
  We are sorry but it was not possible to complete the requested task.
  Please try again, and thank you for your patience.<br>
  _END;

}

?>
