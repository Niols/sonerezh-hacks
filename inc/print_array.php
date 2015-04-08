<?php

function print_array ($array, $keys = null)
{
  if ($keys === null) $keys = array_keys ($array);
  $sizes = array_fill_keys ($keys, 0);
  foreach ($array as $line)
  {
    foreach ($keys as $key)
    {
      $l = strlen ($line[$key]);
      if ($sizes[$key] < $l) $sizes[$key] = $l;
    }
  }
  foreach ($array as $line)
  {
    foreach ($keys as $key)
      printf (" %{$sizes[$key]}s\t", $line[$key]);
    echo PHP_EOL;
  }
}