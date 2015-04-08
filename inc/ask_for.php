<?php

function ask_for ($message, $valid = null)
{
  if (is_array ($valid))
    $validate = function ($v) use ($valid) { return in_array ($v, $valid); };

  else if (is_callable ($valid))
    $validate = $valid;

  else if ($valid === null)
    $validate = function ($v) { return true; };

  else
    $validate = function ($v) use ($valid) { return filter_var ($v, $valid); };

  do
  {
    echo $message;
    $value = trim (fgets (STDIN));
  }
  while (! $validate ($value));
  return $value;
}