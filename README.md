Sonerezh hacks
==============

Some hacky stuff for [Sonerezh](https://sonerezh.bzh).


Install
-------

You don't need to do much to install these scripts. Just clone or download this repository, and write in `inc/database_config.php` where you can find the Sonerezh database config file. The path can be absolute or relative, **from the root of the Sonerezh-hacks dir location** to this file, that, by default, is in `app/Config/database.php`.

All these scripts are CLI. Use them directly in console:

    php randomalbum_playlist.php
    php randomalbum_playlist.php > randomalbum_playlist.php


Detail
------

### clean_database

**WIP. Not functionnal yet.**  
Should give a script that deletes from database files that aren't anymore present on disk.

### playall_playlist

Creates or updates a playlist containing all songs. It can be cool since there's no "Play all" button in Sonerezh. That's really laggy, and it's a bad idea to put it as first playlist, because it makes the playlist part or Sonerezh really slow (at least when you have a lot of songs).

You can change the user concerned (a specific user or all of them) and the playlist name (default: All songs) by modifying the constants at the begining of the file.

### randomalbum_playlist

Creates or updates a playlist containing all songs from an album. It can be cool since there's no "Random album" solution in Sonerezh.

You can change the user concerned (a specific user or all of them), the playlist start name (default: Album of the day), and the minimum number of songs necessary to say it's an album (default: 5).

Crontab
-------

Personnaly I'm using them in a crontab. Here is this file:

     0 0 * * *     cd /path/to/sonerezh-hacks/ && php randomalbum_playlist.php >> randomalbum_playlist.log
    10 0 * * *     cd /path/to/sonerezh-hacks/ && php playall_playlist.php >> playall_playlist.log

The first says that the random album (album of the day) changes everyday at 0:00. The second that the play all playlist is updated everyday at 0:10 (so that I'm almost sure you will have Album of the day higher than Play All in list, and Sonerezh wont lag ;) ).
