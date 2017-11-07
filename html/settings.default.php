<?php

// Docker hub image
$docker_image = "tplcom/faaast";

// Set you main domain here
// For local development this can be "localhost"
// Do not use the "http://" prefix
$domain = "localhost:8899";

$files_path = "/var/www/html/";
$builds_files_path = $files_path . "builds/";
$error_files_path = $files_path . "error/";

// Set debugging variable
$debug = TRUE;

if ($debug) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

// Google Analytics code
$google_analytics_code = "";

// Sharethis property
$sharethis = "";

// Infolinks pid
$infolinks_pid = "";
