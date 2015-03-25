<?php
/******************************************************************************/
/*                                                                            */
/*                                                                            */
/*                                                                            */
/*                                                                            */
/******************************************************************************/


// Path to sonerezh's database config file.
define ('DATABASE_CONFIG_FILE', '../sonerezh/app/Config/database.php');

// Name of the account. If evaluates to false, all accounts will be concerned.
//define ('USER',                 'nicojpub@gmail.com');
define ('USER',                 false);

// Name of the created playlist.
define ('PLAYLIST',             'All songs');

// Size of the chunks in add-song part of the script.
define ('CHUNK_SIZE', 100);




// Get database config
require DATABASE_CONFIG_FILE;
$conf = (new DATABASE_CONFIG) ->  default;
if ($conf['datasource'] != 'Database/Mysql')
  die ('Datasource `'. $conf['datasource'] .'` not supported');



// Instantiate PDO
$dsn = 'mysql:dbname=' . $conf['database'] . ';host=' . $conf['host'];
$pdo = new PDO ($dsn, $conf['login'], $conf['password']);



function query_or_die ($sql, $die_message = 'Fatal error', $exec = false)
{
  global $pdo;
  $s = $exec ? $pdo -> exec ($sql) : $pdo -> query ($sql);
  if ($s === false)
  {
    $errorInfos = $pdo -> errorInfo ();
    die ($die_message . ': ' . $errorInfos[2]);
  }
  return $exec ? true : $s -> fetchAll (PDO::FETCH_ASSOC);
}



// Get user, or users
echo 'Fetching users… ';
if (USER)
{
  $sql0 = 'SELECT email, id '
	. 'FROM ' . $conf['prefix'] . 'users '
	. 'WHERE email = "' . USER . '"';
  $users = query_or_die ($sql0, 'Error while retrieving user\'s id');
  if (empty ($users))
    die ('User `' . USER . '` doesn\'t exist.');
  echo 'only user ' . USER . ' [' . $users[0]['id'] . '] concerned.' . PHP_EOL;
}

else
{
  $sql0 = 'SELECT email, id '
	. 'FROM ' . $conf['prefix'] . 'users ';
  $users = query_or_die ($sql0, 'Error while retrieving users\' ids');
  echo count ($users) . ' users concerned.' . PHP_EOL;
}



// Loop on users
foreach ($users as $user)
{
  echo PHP_EOL . 'User ' . $user['email'] . ' [' . $user['id'] . ']:' . PHP_EOL;



  // Get playlist's id. Create it if needed.
  echo 'Fetching playlist… ';
  $sql1 = 'SELECT id '
	. 'FROM ' . $conf['prefix'] . 'playlists '
	. 'WHERE title = "' . PLAYLIST . '" '
	. 'AND user_id = "' . $user['id'] . '"';
  $playlists = query_or_die ($sql1, 'Error while retrieving playlists');
  $now = date ('Y-m-d H:s:s');

  if (empty ($playlists))
  {
    echo 'Playlist does not exist.' . PHP_EOL . 'Creating playlist… ';
    $sql2 = 'INSERT INTO ' . $conf['prefix'] . 'playlists '
	  . '(title, created, modified, user_id) '
	  . 'VALUES ("'.PLAYLIST.'", "'.$now.'", "'.$now.'", '.$user['id'].')';
    query_or_die ($sql2, 'Error while inserting playlist', true);
    echo 'Done.' . PHP_EOL . 'Fecthing playlist… ';
    $playlists = query_or_die ($sql1, 'Error while retrieving playlists');
    if (empty ($playlists))
      die ('Fatal error: case is not supposed to happen.');
    $playlist_id = $playlists[0]['id'];
    echo 'Found playlist ' . PLAYLIST . ' [' . $playlist_id . ']' . PHP_EOL;
  }
  else
  {
    $playlist_id = $playlists[0]['id'];
    echo 'Found playlist ' . PLAYLIST . ' [' . $playlist_id . ']' . PHP_EOL;
    $sql3 = 'UPDATE ' . $conf['prefix'] . 'playlists '
	  . 'SET modified = "' . $now . '" '
	  . 'WHERE id = ' . $playlist_id;
    query_or_die ($sql3, 'Error while updating playlist', true);
    echo 'Updated playlist\'s modified date.' . PHP_EOL;
  }



  // Get all songs ids
  echo 'Fetching all songs ids… ';
  $sql4 = 'SELECT id '
	. 'FROM ' . $conf['prefix'] . 'songs';
  $songs = query_or_die ($sql4, 'Error while retrieving songs ids');
  $songs_ids = array_map(function($e){return $e['id'];}, $songs);
  // or if you have PHP>5.5: $songs_ids = array_column ($songs, 'id'));
  echo 'Found ' . count ($songs_ids) . ' songs.' . PHP_EOL;



  // Get all songs-in-playlist ids
  echo 'Fetching all songs-in-playlist ids… ';
  $sql5 = 'SELECT song_id '
	.	'FROM ' . $conf['prefix'] . 'playlist_memberships '
	. 'WHERE playlist_id = ' . $playlist_id;
  $songs_pl = query_or_die ($sql5, 'Error while retrieving songs-in-playlist ids');
  $songs_pl_ids = array_map(function($e){return $e['song_id'];}, $songs_pl);
  // or if you have PHP>5.5: $songs_pl_ids = array_column ($songs_pl, 'song_id'));
  echo 'Found ' . count ($songs_pl_ids) . ' songs.' . PHP_EOL;

  $songs_ids = array_diff ($songs_ids, $songs_pl_ids);
  echo count ($songs_ids) . ' songs remaining.' . PHP_EOL;



  // Add all new songs
  echo 'Adding all songs to playlist… ';
  $sql6 = 'INSERT INTO ' . $conf['prefix'] . 'playlist_memberships '
	. '(playlist_id, song_id, sort) VALUES ';
  $total = count ($songs_ids);
  $songs_ids_chunks = array_chunk ($songs_ids, CHUNK_SIZE);
  echo "\r";
  foreach ($songs_ids_chunks as $key => $chunk)
  {
    echo 'Adding all songs to playlist… ' . ($key * CHUNK_SIZE) . ' on ' . $total . "\r";
    $chunk = array_map (function ($id) use ($playlist_id){return "($playlist_id,$id,$id)";}, $chunk);
    query_or_die ($sql6 . implode (',', $chunk), 'Error while adding songs to playlist.', true);
  }
  echo 'Adding all songs to playlist… Done.' . PHP_EOL;



  // Job done
  echo 'Job done.' . PHP_EOL;
}
