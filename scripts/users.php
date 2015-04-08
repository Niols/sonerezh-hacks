<?php

class users
{

  static function _list ($args)
  {
    global $pdo;
    global $conf;

    if (! empty ($args))
      die ('');
    
    return $pdo -> query ("SELECT id, email, role FROM {$conf->prefix}users")
		-> fetchAll (PDO::FETCH_ASSOC);
  }


  static function _add ($args)
  {
    die ("Not implemented: need to understand where cake salt is first.\n");
    $email = ask_for ('Email: ', FILTER_VALIDATE_EMAIL);
    $password = ask_for ('Password: ');
    $status = ask_for ('Status [admin, listener]: ', ['admin', 'listener']);
    $pdo -> exec ("INSERT INTO {$conf->prefix}users (email, password, status)"
		 ."VALUES ('$email', '', '$status');");
  }


  static function _delete ($args)
  {
    // Find mail
    if (isset ($argv[3]))
    {
      if (filter_var ($argv[3], FILTER_VALIDATE_EMAIL))
	$email = $argv[3];
      else
	die ("Not a valid email: {$argv[3]}\n");
    }
    else
    {
      $email = ask_for ('Email: ', FILTER_VALIDATE_EMAIL);
    }

    // Find confirmation
    if ((isset ($argv[4]) && $argv[4] == 'force') || (ask_for ('Are you sure? [y,n] ', ['y', 'n']) == 'y'))
    {
      $pdo -> exec ("DELETE FROM {$conf->prefix}users WHERE email = '$email'");
      echo "The user has been deleted.\n";
      exit;
    }
    else
    {
      die ("Aborting\n");
    }
  }

}
