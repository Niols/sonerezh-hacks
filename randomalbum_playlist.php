<?php
/******************************************************************************/
/*                                                                            */
/*  This file creates a playlist for Sonerezh <www.sonerezh.bzh> containing   */
/*  all songs from on album in the database. You should be carefull while     */
/*  using third-party scripts.                                                */
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
// This script is matching on name, so make it unique !
define ('PLAYLIST',             'Album of the day');

// Number of song in an album to make it selectable.
define ('MIN_ALBUM_SIZE',       5);



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



// Select random album
echo 'Finding album… ';
$sql = 'SELECT band, album, COUNT(*) AS nb'
     .' FROM ' . $conf->prefix . 'songs'
     .' GROUP BY band, album ';
$sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $conf->prefix . 'hack_album_cardinal'
     .' AS (' . $sql . ')';
query_or_die ($sql, 'Error while creating temporary hack_album_cardinal table', true);

$sql = 'SELECT band, album'
     .' FROM ' . $conf->prefix . 'hack_album_cardinal'
     .' WHERE nb >= ' . MIN_ALBUM_SIZE
     .' ORDER BY RAND()'
     .' LIMIT 1';
$album = query_or_die ($sql, 'Error while retrieving random album');
if (empty ($album))
  die ('Impossible to select an album.');
$album = $album[0];
echo 'Found: ' . $album['band'] . ' - ' . $album['album'] . PHP_EOL;

$now = date ('Y-m-d H:s:s');
$playlist_title = PLAYLIST . ' (' . $album['band'] . ' - ' . $album['album'] . ')';



// Fetch random album's songs.
echo 'Fetching songs… ';
$sql = 'SELECT id, track_number'
     .' FROM ' . $conf->prefix . 'songs'
     .' WHERE band = "' . $album['band'] . '"'
     .' AND album = "' . $album['album'] . '"'
     .' ORDER BY track_number';
$songs = query_or_die ($sql, 'Error while retrieving songs from album');
if (count ($songs) < MIN_ALBUM_SIZE)
  die ('Error: this case is not supposed to happen.');
echo 'Done.' . PHP_EOL;



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

  // Delete all album playlists
  echo 'Fetching playlist… ';
  $sql = 'SELECT id, title'
       .' FROM ' . $conf->prefix . 'playlists'
       .' WHERE title LIKE "' . PLAYLIST . '%"' // note the wildcard
       .' AND user_id = "' . $user['id'] . '"';
  $playlists = query_or_die ($sql, 'Error while retrieving playlists');
  echo 'Found ' . count ($playlists) . ' playlists.' . PHP_EOL;


  if (! empty ($playlists))
  {
    // Delete all playlists memberships
    echo 'Deleting playlist memberships… ';
    $sql = 'DELETE FROM ' . $conf->prefix . 'playlist_memberships'
	 .' WHERE ('
	 . (implode (' OR ', array_map (function($pl){return 'playlist_id='.$pl['id'];}, $playlists)))
         . ')';
    query_or_die ($sql, 'Error while deleting playlist_memberships', true);
    echo 'Done.' . PHP_EOL;

    echo 'Keeping one playlist… ';
    $playlist = array_shift ($playlists);
    $playlist_id = $playlist['id'];
    echo 'Found: ' . $playlist['title'] . ' [' . $playlist['id'] . '].' . PHP_EOL;

    if (! empty ($playlists))
    {
      echo 'Deleting other playlists… ';
      // Deleting other playlists
      $sql = 'DELETE FROM ' . $conf->prefix . 'playlists'
	   .' WHERE ('
	   . (implode (' OR ', array_map (function($pl){return 'id='.$pl['id'];}, $playlists)))
           . ')';
      query_or_die ($sql, 'Error while deleting playlists', true);
      echo 'Done.' . PHP_EOL;
    }

    // Update playlist
    echo 'Updating playlist to ' . $playlist_title . '… ';
    $sql = 'UPDATE ' . $conf->prefix . 'playlists'
	 .' SET title = "' . $playlist_title . '",'
	 .'     created = "' . $now . '",'
	 .'     modified = "' . $now . '"'
	 .' WHERE id = ' . $playlist_id;
    query_or_die ($sql, 'Error while updating playlist', true);
    echo 'Done.' . PHP_EOL;
  }

  else
  {
    // Create playlist
    echo 'Creating playlist ' . $playlist_title . '… ';
    $sql = 'INSERT INTO ' . $conf->prefix . 'playlists (title, created, modified, user_id)'
	 .' VALUES ("' . $playlist_title . '","' . $now . '","' . $now . '",' . $user['id'] . ')';
    query_or_die ($sql, 'Error while creating new playlist', true);
    echo 'Done.' . PHP_EOL;

    // Fetching playlist's id.
    echo 'Fetching playlist\'s id… ';
    $sql = 'SELECT id FROM ' . $conf->prefix . 'playlists'
	 .' WHERE title = "' . $playlist_title . '"'
	 .' AND user_id = ' . $user['id'];
    $playlists = query_or_die ($sql, 'Error while fetching playlist\'s id');
    $playlist_id = $playlists[0]['id'];
    echo 'Found id ' . $playlist_id . PHP_EOL;
  }



  // Fill playlist
  echo 'Adding content to playlist… ';
  $sql = 'INSERT INTO ' . $conf->prefix . 'playlist_memberships'
       .' (playlist_id, song_id, sort) VALUES ';
  $chunks = array_map (function($song)use($playlist_id){return '('.$playlist_id.','.$song['id'].','.$song['track_number'].')';}, $songs);
  query_or_die ($sql . implode (',', $chunks), 'Error while inserting playlist_memberships', true);
  echo 'Done.' . PHP_EOL;


  // Job done
  echo 'Job done.' . PHP_EOL;
}
