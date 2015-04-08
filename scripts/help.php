<?php

class help
{

  static function _help ($args)
  {
    echo "Prints the following help:\n\n";
  }

  static function _users ($args)
  {
    global $argv0;

    echo <<<END
Usages:

   {$argv0} users list

   {$argv0} users delete [email] [force]
      If you don't give email, it'll be prompted.
      If you don't add 'force', confirmation will be asked.


END;
  }

}

