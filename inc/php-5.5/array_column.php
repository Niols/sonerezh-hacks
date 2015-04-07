<?php

if (! function_exists ('array_column'))
{
  function array_column ($array, $column_key, $index_key = null)
  {
    try
    {
      $output = array ();
      foreach ($array as $key => $value)
	$output [ $index_key === null ? $key : $value[$index_key] ] =
          $column_key === null ? $value : $value[$column_key];
      return $output;
    }
    catch (Exception $e)
    {
      return array ();
    }
  }
}
