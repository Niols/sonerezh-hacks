Sonerezh hacks
==============

Some hacky stuff for [Sonerezh](https://sonerezh.bzh).


Install
-------

You don't need to do much to install these scripts. Just clone or download this repository, and write in `inc/database_config.php` where you can find the Sonerezh database config file. The path can be absolute or relative, **from the root of the Sonerezh-hacks dir location** to this file, that, by default, is in `app/Config/database.php`.

All these scripts are CLI. Use them directly in console:

    php randomalbum_playlist.php
    php randomalbum_playlist.php > randomalbum_playlist.log


Detail
------

### clean_database

Deletes from database songs that aren't on disk anymore. It's cool since Sonerezh's database update doesn't do that for the moment.
TODO: delete doubles from database.

### playall_playlist

Creates or updates a playlist containing all songs. It can be cool since there's no "Play all" button in Sonerezh. That's really laggy, and it's a bad idea to put it as first playlist, because it makes the playlist part or Sonerezh really slow (at least when you have a lot of songs).

You can change the user concerned (a specific user or all of them) and the playlist name (default: All songs) by modifying the constants at the begining of the file.

### randomalbum_playlist

Creates or updates a playlist containing all songs from an album. It can be cool since there's no "Random album" solution in Sonerezh.

You can change the user concerned (a specific user or all of them), the playlist start name (default: Album of the day), and the minimum number of songs necessary to say it's an album (default: 5).

Crontab
-------

Personnaly I'm using them in a crontab. Here is this file:

    50 23 * * *     cd /path/to/sonerezh-hacks/ && php clean_database.php >> clean_database.log
     0  0 * * *     cd /path/to/sonerezh-hacks/ && php randomalbum_playlist.php >> randomalbum_playlist.log
    10  0 * * *     cd /path/to/sonerezh-hacks/ && php playall_playlist.php >> playall_playlist.log

The first line says that the database will be cleaned everyday at 23:50.
The second line says that the random album (Album of the day) will change everyday at 0:00.
And the third line says that the All songs playlist will be updated everyday at 0:10 (so that I'm almost sure I'll have Album of the day higher than All songs in playlists list, which means Sonerezh wont lag for other playlists).
