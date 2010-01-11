<?
// application bottom

// close session (store variables)
//  wrap_session_close();

  if (STORE_PAGE_PARSE_TIME == '1') {
    $parse_end_time = microtime();
    $time_start = explode(' ', $parse_start_time);
    $time_end = explode(' ', $parse_end_time);
    $parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
    error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' - ' . getenv(REQUEST_URI) . ' (' . $parse_time . 'ms)' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    //echo '<p> </p>Parse Time: ' . $parse_time . 'ms';
  }
?>