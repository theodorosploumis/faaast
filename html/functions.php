<?php

/**
 * Generates a random string
 * @param string $length
 * @param string $keyspace
 * @return string
 */
function randomGenerator($length = '20', $keyspace = 'abcdefghijklmnopqrstuvwxyz') {

  $str = '';
  $max = mb_strlen($keyspace, '8bit') - 1;

  for ($i = 0; $i < $length; ++$i) {
    $str .= $keyspace[mt_rand(0, $max)];
  }
  return $str;
}

/**
 * @param string $str
 * @return string
 */
function normalizeString ($str = '') {
  $str = strip_tags($str);
  $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
  $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
  $str = strtolower($str);
  $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
  $str = htmlentities($str, ENT_QUOTES, "utf-8");
  $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
  $str = str_replace(' ', '_', $str);
  $str = rawurlencode($str);
  $str = str_replace('%', '_', $str);
  return $str;
}

/**
 * Downloads a file from the server
 * @param string $file_path
 */
function downloadFile($file_path) {
  $mime = mime_content_type($file_path);

  header("Content-Type: " . $mime);
  header("Content-Transfer-Encoding: Binary");
  header("Content-disposition: attachment; filename=\"" . basename($file_path) . "\"");
  readfile($file_path);
}

/**
 * Short function to rediect page
 * @param  string  $url
 * @param  boolean $permanent
 * @return http redirect
 */
function redirectTo($url, $permanent = false) {
  if (headers_sent() === false) {
    header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
  }
  exit();
}

/**
 * Show error page with custom message and die
 * @param string $message
 */
function httpError($message = "Not found"){
  header("HTTP/1.0 404 Not Found");
  die('Error: '.$message);
}

/**
 * @param string $string
 * @param array $array (array of values to check if exist)
 * @return bool
 */
function stringContains($string, array $array) {
  foreach($array as $ar) {
    if (stripos($string,$ar) !== false) {
      return true;
    }
  }
  return false;
}
