<?php

//
if (file_exists ('config.php'))
  require 'config.php';
else
  die ("Can't find the config.php file.\nCopy config.sample.php and personalize it.\n");

//
require 'database.php';

// The array_column function, if PHP < 5.5
if (! function_exists ('array_column'))
  require 'inc/php-5.5/array_column.php';

// The print_array function and his collegue read_array.
require 'inc/print_array.php';
require 'inc/read_array.php';

//
require 'inc/ask_for.php';

// Scripts
require 'scripts/help.php';
require 'scripts/playlist_memberships.php';
require 'scripts/playlists.php';
require 'scripts/settings.php';
require 'scripts/songs.php';
require 'scripts/users.php';
