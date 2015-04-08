<?php

if (isset ($argv[2]))
{
  switch ($argv[2])
  {
    case 'list':
      $users = $pdo -> query ("SELECT * FROM {$conf->prefix}users")
		    -> fetchAll (PDO::FETCH_ASSOC);
      //array_unshift ($users, ['id'=>'id', 'email'=>'email', 'role'=>'role']);
      print_array ($users, ['id', 'email', 'role']);
      exit;

    default:
      echo 'Users: Unkown argument: ' . $argv[2];
  }
}