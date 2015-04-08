<?php

function read_array ($input, $keys = null)
{
  $array = array ();
  foreach (array_filter (explode ("\n", $input)) as $line)
  {
    $line = array_filter (array_map ('trim', explode ("\t", $line)));
    $array[] = ($keys === null) ? $line : array_combine ($keys, $line);
  }
  return $array;
}