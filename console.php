<?php

require 'init.php';

if (isset ($argv[1]))
{
  switch ($argv[1])
  {
    case 'help':
      require 'scripts/help.php';
      //exit; // No exit after help: print global help
      break;  // So, don't forget break

    case 'playlist_memberships':
      require 'scripts/playlist_memberships.php';
      exit;

    case 'playlists':
      require 'scripts/playlists.php';
      exit;

    case 'settings':
      require 'scripts/settings.php';
      exit;

    case 'songs':
      require 'scripts/songs.php';
      exit;

    case 'users':
      require 'scripts/users.php';
      exit;
  }
}

echo <<<END
Usage: ${argv[0]} [command] [arguments]

Command:
   help
   playlist_memberships
   playlists
   settings
   songs
   users

Try: ${argv[0]} help [command].

END;
