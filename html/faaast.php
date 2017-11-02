<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/settings.php';

// Docker volumes
$cache = "";
$filename = "error.tar.gz";

// Get variables from url
if (isset($_GET['cmd'])) {
  $cmd = $_GET['cmd'];

  if ($debug) {
    print "Command: " . $cmd . "<br>";
  }

  $filename = normalizeString($cmd) . ".tar.gz";
  
  $cmd_array = explode(" ", $cmd);
  $cmd_software = strtolower($cmd_array[0]); // eg npm
  $cmd_command = strtolower($cmd_array[1]); // eg install
  
//  unset($cmd_array[0]);
//  unset($cmd_array[1]);
//  $cmd_following = $cmd_array; // eg react-native (package name)
  
  $software = [
    "npm",
    "pnpm",
    "yarn",
    "ied",
    "gem",
    "composer",
    "drush",
    "pip"
  ];
  
  if (!in_array($cmd_software, $software)) {
    httpError('Command '.$cmd_software.' is not supported.');
  }
  
  if (stringContains($cmd, [";", "||", "& ", "&&"])) {
    httpError('Chained commands are not supported.');
  }
  
  // Default folder to tar
  $folder = "/home";
  
  switch ($cmd_software) {
    case "gem":
      if (!in_array($cmd_command, ["install"])) {
        httpError($cmd_software . " " .$cmd_command . " is not a valid command. Use 'gem install'.");
      }
      $cmd = $cmd . " --install-dir /home";
      $cache = " -v /caches/gem:/.gem";
      break;
      
    case "pip":
      if (!in_array($cmd_command, ["install"])) {
        httpError($cmd_software . " " .$cmd_command . " is not a valid command. Use 'pip install'.");
      }
      $cmd = $cmd . " --target=/home";
      $cache = " -v /caches/pip:/root/.cache/pip";
      break;
      
    case "npm":
      if (!in_array($cmd_command, ["install", "add"])) {
        httpError($cmd_software . " " .$cmd_command . " is not a valid command. Use 'npm install/add'.");
      }
      $cmd = $cmd_software . " set progress=false && " . $cmd . " --silent";
      $cache = " -v /caches/npm/:/.npm";
      break;
      
    case "yarn":
      if (!in_array($cmd_command, ["add"])) {
        httpError($cmd_software . " " .$cmd_command . " is not a valid command. Use 'yarn add'.");
      }
      $cmd = $cmd . " --no-progress --silent --prefer-online --ignore-optional --non-interactive";
      $cache = " -v /caches/yarn:/usr/local/share/.cache/yarn/v1";
      break;
      
    case "pnpm":
      if ($cmd_command != "install") {
        httpError($cmd_software . " " .$cmd_command . " is not a valid command. Use 'pnpm install'.");
      }
      $cmd = "echo '{}' > package.json && " . $cmd;
      break;
    
    case "composer":
      if (!in_array($cmd_command, ["require", "create-project"])) {
        httpError($cmd_software . " " .$cmd_command . " is not a valid command. Use 'composer require/create-project'.");
      }
      $cmd = $cmd . " --quiet --no-ansi --no-interaction --working-dir=/home";
      $cache = " -v /caches/composer:/.composer/cache";
      break;
    
    case "drush":
      if (!in_array($cmd_command, ["pm-download", "dl"])) {
        httpError($cmd_software . " " .$cmd_command . " is not a valid command. Use 'drush dl/pm-download'.");
      }
      $cache = " -v /caches/drush:/.drush/cache/download";
      break;
  }
  
  $cmd = $cmd . ";";
  $cmd_cd = " cd ". $folder .";";
  $cmd_chown_home = " chown -R www-data:www-data ". $folder . ";";
  $cmd_tar = " tar -zcvf /downloads/".$filename." *;";
  $cmd_chown_tar = " chown -R www-data:www-data /downloads/";

  $command = ' /bin/bash -c "' . $cmd . $cmd_chown_home . $cmd_cd . $cmd_tar . $cmd_chown_tar . '"';

} else {
  httpError('Command is not defined.');
}

if (isset($_GET['id']) && (strlen($_GET['id']) == 20)) {

  $id = $_GET['id'];
  $id = preg_replace('/[^a-z]/', '', $id);

  if ($debug) {
    print "ID: " . $id . "<br>";
  }

  // Host files to create volumes
  if (!file_exists($builds_path)) {
    mkdir($builds_path . $id, 0777);
  }
  $host_files = $builds_path . $id . "/";
  $initial_tar_path = $host_files . $filename;

  // Set final (desirable) host file path
  $tar_path = $builds_path . $filename;
  $tar_url = "https://" . $domain . "/builds/" . $filename;

  // Download the file if exists
  if (file_exists($tar_path)) {
    //downloadFile($tar_path);
    redirectTo($tar_url);
  }

  // Run docker and create the file if not exist
  if (!file_exists($host_files)) {
    mkdir($host_files, 0777);
    
    $volumes = $cache . " -v " . $host_files . ":/downloads ";
    $name = " --name " . $id;
    $workdir = " -w /home ";
    $docker = "docker run --rm " . $name . $workdir . $volumes . $docker_image . $command;

    // Run docker
    exec($docker);
  
    // Move file into a new place/name
    rename($initial_tar_path, $tar_path);

    // Remove volumed host folder
    if (is_dir($host_files)) {
      rmdir($host_files);
    }
    
    // Download file
    //downloadFile($initial_tar_path);
    redirectTo($tar_url);
  }

} else {
  httpError('ID is not defined.');
}

