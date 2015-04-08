<?php

require 'init.php';

if (isset ($argv[1]))
{
  switch ($argv[1])
  {
    case 'help':
      require 'scripts/help.php';
      break;

    case 'playlist_memberships':
      require 'scripts/playlist_memberships.php';
      break;

    case 'playlists':
      require 'scripts/playlists.php';
      break;

    case 'settings':
      require 'scripts/settings.php';
      break;

    case 'songs':
      require 'scripts/songs.php';
      break;

    case 'users':
      require 'scripts/users.php';
      break;
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
