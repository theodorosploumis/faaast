<?php

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/functions.php';

// General options
$api = 0;
$debug_message = "";
$error = FALSE; // Boolean
$cache = ""; // Docker caches to use as volumes
$folder = "/home"; // Default folder to compress

if (isset($_GET['api'])) {
  if ($_GET['api'] != 0) {
    $api = $_GET['api'];
  }
}

if (isset($_GET['compress'])) {
  $compress_method = $_GET['compress'];
}
else {
  $compress_method = "tar.gz";
}

// Currently, support only these package managers/tools
$software = [
  "composer",
  "drush",
  "gem",
  "ied",
  "pip",
  "pip3",
  "npm",
  "pnpm",
  "yarn"
];

// Get cmd from url
if (isset($_GET['cmd'])) {
  $cmd = $_GET['cmd'];
  
  $filename = normalizeString($cmd) . "." . $compress_method;
  
  $cmd_array = explode(" ", $cmd);
  $cmd_software = strtolower($cmd_array[0]); // eg npm
  $cmd_command = strtolower($cmd_array[1]); // eg install
  
  $error_filename = normalizeString($cmd) . ".error.log";
  $error_initial_file_path = $current_error_folder . "/" . $error_filename;
  
  //  unset($cmd_array[0]);
  //  unset($cmd_array[1]);
  //  $cmd_following = $cmd_array; // eg react-native (package name)
  
  if (!in_array($cmd_software, $software)) {
    $debug_message .= 'Command ' . $cmd_software . ' is not supported.\n';
    $error = TRUE;
  }
  
  if (stringContains($cmd, [";", "||", "& ", "&&"])) {
    $debug_message .= 'Chained commands are not supported.\n';
    $error = TRUE;
  }
  
  switch ($cmd_software) {
    case "gem":
      if (!in_array($cmd_command, ["install"])) {
        $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
        $debug_message .= " Use 'gem install'.\n";
        $error = TRUE;
      }
      $cmd = $cmd . " --install-dir /home";
      $cache = " -v /caches/gem:/.gem";
      break;
    
    case "pip":
      if (!in_array($cmd_command, ["install"])) {
        $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
        $debug_message .= " Use 'pip install'.\n";
        $error = TRUE;
      }
      $cmd = $cmd . " --target=/home --no-cache-dir";
      $cache = " -v /caches/pip:/root/.cache/pip";
      break;
    
    case "pip3":
      if (!in_array($cmd_command, ["install"])) {
        $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
        $debug_message .= " Use 'pip3 install'.\n";
        $error = TRUE;
      }
      $cmd = $cmd . " --target=/home --no-cache-dir";
      $cache = " -v /caches/pip:/root/.cache/pip";
      break;
    
    case "npm":
      if (!in_array($cmd_command, ["install", "add"])) {
        $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
        $debug_message .= " Use 'npm install/add'.\n";
        $error = TRUE;
      }
      $cmd = $cmd_software . " set progress=false; " . $cmd . " --silent";
      $cache = " -v /caches/npm/:/.npm";
      break;
    
    case "yarn":
      if (!in_array($cmd_command, ["add"])) {
        $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
        $debug_message .= "Use 'yarn add'.\n";
        $error = TRUE;
      }
      $cmd = $cmd . " --no-progress --silent --prefer-online --ignore-optional --non-interactive";
      $cache = " -v /caches/yarn:/usr/local/share/.cache/yarn/v1";
      break;
    
    case "pnpm":
      if ($cmd_command != "install") {
        $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
        $debug_message .= " Use 'pnpm install'.\n";
        $error = TRUE;
      }
      $cmd = "echo '{}' > package.json && " . $cmd;
      break;
    
    case "ied":
      if ($cmd_command != "install") {
        $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
        $debug_message .= " Use 'ied install'.<br`>";
        $error = TRUE;
      }
      break;
    
    case "composer":
      if (!in_array($cmd_command, ["require", "create-project"])) {
        $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
        $debug_message .= "Use 'composer require/create-project'.\n";
        $error = TRUE;
      }
      $cmd = $cmd . " --quiet --no-ansi --no-interaction --working-dir=/home";
      $cache = " -v /caches/composer:/.composer/cache";
      break;
    
    case "drush":
      if (!in_array($cmd_command, ["pm-download", "dl"])) {
        $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
        $debug_message .= " Use 'drush dl/pm-download'.\n";
        $error = TRUE;
      }
      $cache = " -v /caches/drush:/.drush/cache/download";
      break;
  }
  
  if ($error == TRUE) {
    if ($api == 0) {
      echo $debug_message;
      exit();
    }
    else {
      jsonResult(TRUE, $debug_message);
      exit();
    }
  }
  
  // Capture command output on a file and append the command
  $cmd_main = $cmd . " > /error/" . $error_filename . " 2>&1";
  
  //$cmd_exit = "$(if [ $(du -shb /home | awk '{print $1}') -lt 8000 ]; then exit; fi)"; // 8000 bytes
  //$cmd_exit = "$(if [ (echo $?) != 0 ]; then exit; fi)";
  $cmd_cd = " cd " . $folder;
  $cmd_chown_home = " chown -R www-data:www-data " . $folder;

//    $cmd_debug = "printf '" . nl2br(trim(strip_tags($cmd))) . "' >> /error/command.log";
  
  if ($compress_method == "tar.gz") {
    $cmd_compress = " tar -zcvf /downloads/" . $filename . " *";
  }
  else {
    $cmd_compress = " zip -r /downloads/" . $filename . " *";
  }
  $cmd_chown_compressed = " chown -R www-data:www-data /downloads/ && chown -R www-data:www-data /error/ ";
  
  $command = ' /bin/bash -c "';
  $command .= $cmd_main . ' && ';
  $command .= $cmd_chown_home . ' && ';
  $command .= $cmd_cd . ' && ';
  $command .= $cmd_compress . ' && ';
  $command .= $cmd_chown_compressed;
  // $command .= $cmd_debug;
  $command .= '"';
  
} else {
  $debug_message = "Command is not defined";
  if ($api == 0) {
    echo $debug_message;
    exit();
  }
  else {
    jsonResult(TRUE, $debug_message);
  }
}

if (isset($_GET['id']) && (strlen($_GET['id']) == 20)) {
  $id = $_GET['id'];
  $id = preg_replace('/[^a-z]/', '', $id);
} else {
  $debug_message = "ID is not defined";
  if ($api == 0) {
    echo "API=" . $api;
    echo $debug_message;
    exit();
  }
  else {
    jsonResult(TRUE, $debug_message);
  }
}

// If error is set show error result immediately
if (isset($_GET["error"]) && file_exists($error_initial_file_path)) {
  if ($api == 0) {
    print simpleHtml("An error occured", fileWithLines($error_initial_file_path, "<br>"));
    exit();
  }
  else {
    jsonResult(TRUE, fileWithLines($error_initial_file_path));
    exit();
  }
}

// Host files to create volumes
$current_build_folder = $builds_files_path . $id . "/"; // the folder that will be volumed
$current_error_folder = $error_files_path . $id;

if (!file_exists($current_build_folder)) {
  mkdir($current_build_folder, 0777);
}

// Initial and final (desirable) packaged file path
$initial_compressed_path = $current_build_folder . $filename;
$compressed_path = $builds_files_path . $filename;
$compressed_url = "https://" . $domain . "/builds/" . $filename;

// Download the file if exists
if (file_exists($compressed_path)) {
  
  if ($api == 0) {
    //downloadFile($compressed_path);
    redirectTo($compressed_url);
    exit();
  }
  else {
    jsonResult(FALSE, "File exists", $compressed_url);
    exit();
  }
}
else {
  if ($error == FALSE) {
    // Run docker and create the file if not exist
    $volumes = $cache . " -v " . $current_build_folder . ":/downloads ";
    
    // Error log etc
    $error_volumes = " -v " . $current_error_folder . ":/error ";
    
    $name = " --name " . $id;
    $workdir = " -w /home ";
    $docker = "docker run --rm " . $name . $workdir . $volumes . $error_volumes . $docker_image . $command;
    
    exec($docker);
    
    // Move file into a new place/name
    if (file_exists($initial_compressed_path)) {
      rename($initial_compressed_path, $compressed_path);
      sleep(10);
      rmdirRecursive($current_build_folder);
      if ($api == 0) {
        //downloadFile($initial_compressed_path);
        redirectTo($compressed_url);
        exit();
      }
      else {
        jsonResult(FALSE, "File exists", $compressed_url);
        exit();
      }
    }
    
    // If error file exists show that error
    if (file_exists($error_initial_file_path)) {
      if ($api == 0) {
        print simpleHtml("An error occured", fileWithLines($error_initial_file_path, "<br>"));
        // Sleep 10s to allow cron change new folders owner
        sleep(10);
        rmdirRecursive($current_build_folder);
        exit();
      }
      else {
        jsonResult(TRUE, fileWithLines($error_initial_file_path));
        sleep(10);
        rmdirRecursive($current_build_folder);
        exit();
      }
    }
  }
}

print $debug_message;
exit();
