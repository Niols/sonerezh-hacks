<?php
/******************************************************************************/
/*                                                                            */
/*  This file deletes from Sonerezh's database <www.sonerezh.bzh> that are    */
/*  not anymore linked to an existing file. You should be carefull while      */
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

require 'inc/php-5.5/array_column.php';

require 'inc/database_config.php';

$conf = get_database_config ();

$dsn = 'mysql:'
     . 'dbname=' . $conf->database . ';'
     . 'host=' . $conf->host . ';'
     . 'charset=utf8';
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

echo 'Retrieving registered songs… ';
$registered_songs = query_or_die ('SELECT source_path FROM songs');
$registered_songs = array_column ($registered_songs, 'source_path');
echo 'Found ' . count ($registered_songs) . ' songs.' . PHP_EOL;

echo 'Retrieving present songs… ';
exec ('find /home/music/music/', $real_songs);
echo 'Found ' . count ($real_songs) . ' songs.' . PHP_EOL;

echo 'Calculating songs to delete… ';
$songs_to_delete = array_diff ($registered_songs, $real_songs);
echo 'Found ' . count ($songs_to_delete) . ' songs.' . PHP_EOL;

echo 'Deleting ' . count ($songs_to_delete) . ' songs. ';
$sql = 'DELETE FROM songs WHERE source_path = "'
     . implode ('" OR source_path = "', $songs_to_delete) . '"';
query_or_die ($sql, 'Error while deleting songs', true);
echo 'Done.' . PHP_EOL;

/*
echo 'Calculating songs to add… ';
$songs_to_add = array_diff ($real_songs, $registered_songs);
echo 'Found ' . count ($songs_to_add) . ' songs.' . PHP_EOL;
*/
