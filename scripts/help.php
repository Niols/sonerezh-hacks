<?php

if (isset ($argv[2]))
{
  switch ($argv[2])
  {
    case 'help':
      echo 'Prints the following help:' . PHP_EOL . PHP_EOL;
      break;

    default:
      echo "Help: Unknown argument: ${argv[2]}" . PHP_EOL . PHP_EOL;
  }
}