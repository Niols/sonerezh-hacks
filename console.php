<?php

require 'init.php';

$argv0 = array_shift ($argv);
$command = array_shift ($argv);
$arg = array_shift ($argv);

if (in_array ($command, ['help', 'playlist_memberships', 'playlists', 'settings', 'songs', 'users']))
{
  if (method_exists ($command, "_$arg"))
  {
    $result = call_user_func ([$command, "_$arg"], array_values ($argv));
    if ($result === false)
      die ("Fatal error.\n");

    else if ($result !== null)
    {
      print_array ($result);
      exit;
    }
  }

  else if ($arg)
    echo "Unkown argument: $arg\n\n";
}

else if ($command)
  echo "Unkown command: $command\n\n";


echo <<<END
Usage: ${argv0} [command] [arguments]

Command:
   help
   playlist_memberships
   playlists
   settings
   songs
   users

Try: ${argv0} help [command].

END;
