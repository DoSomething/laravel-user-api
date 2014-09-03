<?php

/**
 * @file TiG-specific global function declarations
 *
 * Functions here are appended to all PHP executions in the TiG environment.
 * We need to declare them conditionally here so that we can run this app
 * in other environments.
 */

if (!function_exists('tigcache)'))
{
  /**
   * Mock cached query result function. If $assume_single is truthy, return
   * a single result row. If not, return associative array of results.
   *
   * @param string $query
   * @param int $cache_time
   * @param int $assume_single
   * @return array
   */
  function tigcache($query, $cache_time, $assume_single)
  {
    $result = DB::select(DB::raw($query));

    if ($assume_single && !empty($result))
    {
      return get_object_vars($result[0]);
    }

    $result_array = array();
    foreach ($result as $row)
    {
      $result_array[] = get_object_vars($row);
    }

    return $result_array;
  }
}
