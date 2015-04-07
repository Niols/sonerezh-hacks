<?php
/******************************************************************************/
/*                                                                            */
/*  This file creates a playlist for Sonerezh <www.sonerezh.bzh> containing   */
/*  all songs in the database. You should be carefull while using             */
/*  third-party scripts.                                                      */
/*                                                                            */
/*                                                                            */
/*  THE BEER-WARE LICENSE  (Revision 42):                                     */
/*                                                                            */
/*  Nicolas Jeannerod <niols@niols.net> wrote this file. As long as you       */
/*  retain this notice you can do whatever you want with this stuff. If we    */
/*  meet some day, and you think this stuff is worth it, you can buy me a     */
/*  beer in return.                                                           */
/*                                                      -- Poul-Henning Kamp  */
/*                                                                            */
/******************************************************************************/


// Name of the account. If evaluates to false, all accounts will be concerned.
//define ('USER',                 'nicojpub@gmail.com');
define ('USER',                 false);

// Name of the created playlist.
define ('PLAYLIST',             'All songs');

// Size of the chunks in add-song part of the script.
define ('CHUNK_SIZE', 100);



// Get database config
require 'inc/database_config.php';
$conf = get_database_config ();



// Instantiate PDO
$dsn = 'mysql:dbname=' . $conf->database . ';host=' . $conf->host;
$pdo = new PDO ($dsn, $conf->login, $conf->password);



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

$sql = 'SELECT email, id'
     .' FROM ' . $conf->prefix . 'users';
if (USER) $sql .= ' WHERE email = "' . USER . '"';
$users = query_or_die ($sql, 'Error while retrieving user\'s id');

if (USER)
{
  if (empty ($users))
    die ('User `' . USER . '` doesn\'t exist.');
  echo 'only user ' . USER . ' [' . $users[0]['id'] . '] concerned.' . PHP_EOL;
}

else
  echo count ($users) . ' users concerned.' . PHP_EOL;



// Loop on users
foreach ($users as $user)
{
  echo PHP_EOL . 'User ' . $user['email'] . ' [' . $user['id'] . ']:' . PHP_EOL;



  // Get playlist's id. Create it if needed.
  echo 'Fetching playlist… ';
  $sql1 = 'SELECT id '
	. 'FROM ' . $conf->prefix . 'playlists '
	. 'WHERE title = "' . PLAYLIST . '" '
	. 'AND user_id = "' . $user['id'] . '"';
  $playlists = query_or_die ($sql1, 'Error while retrieving playlists');
  $now = date ('Y-m-d H:s:s');

  if (empty ($playlists))
  {
    echo 'Playlist does not exist.' . PHP_EOL . 'Creating playlist… ';
    $sql2 = 'INSERT INTO ' . $conf->prefix . 'playlists '
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
    $sql3 = 'UPDATE ' . $conf->prefix . 'playlists '
	  . 'SET modified = "' . $now . '" '
	  . 'WHERE id = ' . $playlist_id;
    query_or_die ($sql3, 'Error while updating playlist', true);
    echo 'Updated playlist\'s modified date.' . PHP_EOL;
  }



  // Get all songs ids
  echo 'Fetching all songs ids… ';
  $sql = 'SELECT id'
       .' FROM ' . $conf->prefix . 'songs';
  $songs = query_or_die ($sql, 'Error while retrieving songs ids');
  $songs_ids = array_map(function($e){return $e['id'];}, $songs);
  // or if you have PHP>5.5: $songs_ids = array_column ($songs, 'id'));
  echo 'Found ' . count ($songs_ids) . ' songs.' . PHP_EOL;



  // Get all songs-in-playlist ids
  echo 'Fetching all songs-in-playlist ids… ';
  $sql = 'SELECT song_id'
       .' FROM ' . $conf->prefix . 'playlist_memberships'
       .' WHERE playlist_id = ' . $playlist_id;
  $songs_pl = query_or_die ($sql, 'Error while retrieving songs-in-playlist ids');
  $songs_pl_ids = array_map(function($e){return $e['song_id'];}, $songs_pl);
  // or if you have PHP>5.5: $songs_pl_ids = array_column ($songs_pl, 'song_id'));
  echo 'Found ' . count ($songs_pl_ids) . ' songs.' . PHP_EOL;

  $songs_to_add_ids = array_diff ($songs_ids, $songs_pl_ids);
  echo count ($songs_to_add_ids) . ' songs to add.' . PHP_EOL;
  $songs_to_del_ids = array_diff ($songs_pl_ids, $songs_ids);
  echo count ($songs_to_del_ids) . ' songs to delete.' . PHP_EOL;



  // Delete old songs
  echo 'Deleting bad songs from playlist… ';
  $sql = 'DELETE FROM ' . $conf->prefix . 'playlist_memberships'
       .' WHERE playlist_id = ' . $playlist_id . ' AND ';
  $total = count ($songs_to_del_ids);
  $songs_chunks = array_chunk ($songs_to_del_ids, CHUNK_SIZE);
  echo "\r";
  foreach ($songs_chunks as $key => $chunk)
  {
    echo 'Deleting bad songs from playlist… ' . ($key * CHUNK_SIZE) . ' on ' . $total . "\r";
    $chunk = array_map (function ($id){return "song_id = $id";}, $chunk);
    query_or_die ($sql . '(' . implode (' OR ', $chunk) . ')',
		  'Error while deleting songs from playlist.', true);
  }
  echo 'Deleting bad songs from playlist… ' . $total . ' on ' . $total . PHP_EOL;



  // Add all new songs
  echo 'Adding all songs to playlist… ';
  $sql = 'INSERT INTO ' . $conf->prefix . 'playlist_memberships'
       .' (playlist_id, song_id, sort) VALUES ';
  $total = count ($songs_to_add_ids);
  $songs_chunks = array_chunk ($songs_to_add_ids, CHUNK_SIZE);
  echo "\r";
  foreach ($songs_chunks as $key => $chunk)
  {
    echo 'Adding all songs to playlist… ' . ($key * CHUNK_SIZE) . ' on ' . $total . "\r";
    $chunk = array_map (function ($id) use ($playlist_id)
			{return "($playlist_id,$id,$id)";}, $chunk);
    query_or_die ($sql . implode (',', $chunk),
		  'Error while adding songs to playlist.', true);
  }
  echo 'Adding all songs to playlist… ' . $total . ' on ' . $total . PHP_EOL;



  // Job done
  echo 'Job done.' . PHP_EOL;
}
