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
function normalizeString($str = '') {
  $str = strip_tags($str);
  $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
  $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
  $str = strtolower($str);
  $str = html_entity_decode($str, ENT_QUOTES, "utf-8");
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
 * @param  string $url
 * @param  boolean $permanent
 * @return http redirect
 */
function redirectTo($url, $permanent = FALSE) {
  if (headers_sent() === FALSE) {
    header('Location: ' . $url, TRUE, ($permanent === TRUE) ? 301 : 302);
  }
  exit();
}

/**
 * Show error page with custom message and die
 * @param string $message
 */
function httpError($message = "Not found") {
  header("HTTP/1.0 404 Not Found");
  die('Error: ' . $message);
}

/**
 * @param string $string
 * @param array $array (array of values to check if exist)
 * @return bool
 */
function stringContains($string, array $array) {
  foreach ($array as $ar) {
    if (stripos($string, $ar) !== FALSE) {
      return TRUE;
    }
  }
  return FALSE;
}

/**
 * @param string $code
 * @return string
 */
function googleAnalytics($code) {
  $script = "";
  
  $script .= '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $code . '"></script>';
  $script .= '<script>';
  $script .= 'window.dataLayer = window.dataLayer || []; function gtag() { dataLayer.push(arguments); }';
  $script .= "gtag('js', new Date());";
  $script .= "gtag('config', '" . $code . "');";
  $script .= '</script>';
  
  return $script;
}

/**
 * @param string $id
 * @return string
 */
function shareThis($id) {
  $script = "";
  $script .= "<script type='text/javascript' ";
  $script .= "src='//platform-api.sharethis.com/js/sharethis.js#property=" . $id . "&product=sticky-share-buttons' ";
  $script .= "async='async'>";
  $script .= "</script>";
  
  return $script;
}

/**
 * @return string
 */
function googleFonts() {
  return "https://fonts.googleapis.com/css?family=Cutive+Mono|Lato|Source+Sans+Pro:300,400";
}

/**
 * @return string
 */
function footerMessage() {
  $text = "";
  $text .= '&copy; <a href="https://www.theodorosploumis.com/en">TheodorosPloumis</a>';
  $text .= ' | ';
  $text .= "<a href='/faqs.php'>FAQs</a>";
  $text .= ' | ';
  $text .= "<a href='/builds'>Builds</a>";
  $text .= ' | ';
  $text .= "<a href='https://github.com/theodorosploumis/faaast/'>GitHub</a>";
  $text .= ' | ';
  $text .= 'Hosted on <a href="https://www.vultr.com/?ref=7258956">Vultr.com</a>';
  
  return $text;
}

/**
 * @param string $dir
 */
function rmdirRecursive($dir) {
  foreach (scandir($dir) as $file) {
    if ('.' === $file || '..' === $file) {
      continue;
    }
    if (is_dir("$dir/$file")) {
      rmdirRecursive("$dir/$file");
    }
    else {
      unlink("$dir/$file");
    }
  }
  rmdir($dir);
}

/**
 * @param $filename
 * @param string $separator
 * @return string
 */
function fileWithLines($filename, $separator = "\n") {
  $file_lines = file($filename);
  $result = "";
  $number = 0;
  
  foreach ($file_lines as $line_num => $line) {
    if (trim($line) != "") {
      $number += 1;
      $result .= "{$number}: " . $line . $separator;
      $result .= $separator;
    }
  }
  
  return $result;
}

/**
 * @param string $title
 * @param string $body
 * @return string
 */
function simpleHtml($title = "", $body = "") {
  $text = '<html><head>';
  $text .= '<link rel="stylesheet" href="css/style.css">';
  $text .= '<link href="' . googleFonts() . '" rel="stylesheet">';
  $text .= '</head><body>';
  $text .= '<div class="logo"><a href="/"><img src="logo.png" title="Faaast logo"></a></div>';
  $text .= '<h1 class="hidden">';
  $text .= $title;
  $text .= '</h1>';
  $text .= '<section class="wrapper error-wrapper">';
  $text .= '<section class="faq"><h2>';
  $text .= $title;
  $text .= '</h2>';
  $text .= '<div class="error">'. $body . '</div>';
  $text .= '</section></section></body></html>';
  
  return $text;
}

/**
 * @param boolean $error
 * @param string $message
 * @param string $fileurl
 */
function jsonResult($error = FALSE, $message = "", $fileurl = "") {
  header("Content-Type: application/json");
  
  if ($error) {
    $response = array(
      'status' => FALSE,
      'message' => "An error occured",
      'file' => $message
    );
  }
  else {
    $response = array(
      'status' => TRUE,
      'message' => $message,
      'file' => $fileurl
    );
  }
  
  echo json_encode($response);
  exit();
}
