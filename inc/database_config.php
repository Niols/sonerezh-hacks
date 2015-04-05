<?php

// Path to sonerezh's database config file.
define ('DATABASE_CONFIG_FILE', '../sonerezh/app/Config/database.php');

require DATABASE_CONFIG_FILE;

function get_database_config ($as_object = true)
{
  $conf = (new DATABASE_CONFIG) -> default;
  return $as_object ? (object) $conf : $conf;
}
