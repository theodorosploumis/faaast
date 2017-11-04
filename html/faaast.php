<?php

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/functions.php';

// General options
$debug_message = "";
$error = FALSE; // Boolean
$cache = ""; // Docker caches to use as volumes
$folder = "/home"; // Default folder to compress

if (isset($_GET['compress'])) {
    $compress_method = $_GET['compress'];
} else {
    $compress_method = "tar.gz";
}
$filename = "error." . $compress_method;

// Currently, support only these package managers/tools
$software = ["composer", "drush", "gem", "ied", "pip", "npm", "pnpm", "yarn"];

// Get cmd from url
if (isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];

    if ($debug) {
        debugConsole("Command=" . $cmd);
    }

    $filename = normalizeString($cmd) . "." . $compress_method;

    $cmd_array = explode(" ", $cmd);
    $cmd_software = strtolower($cmd_array[0]); // eg npm
    $cmd_command = strtolower($cmd_array[1]); // eg install

    //  unset($cmd_array[0]);
    //  unset($cmd_array[1]);
    //  $cmd_following = $cmd_array; // eg react-native (package name)

    if (!in_array($cmd_software, $software)) {
        $debug_message .= 'Command ' . $cmd_software . ' is not supported.<br>';
        $error = TRUE;
    }

    if (stringContains($cmd, [";", "||", "& ", "&&"])) {
        $debug_message .= 'Chained commands are not supported.<br>';
        $error = TRUE;
    }

    switch ($cmd_software) {
    case "gem":
        if (!in_array($cmd_command, ["install"])) {
            $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.<br>";
            $debug_message .= " Use 'gem install'.<br>";
            $error = TRUE;
        }
        $cmd = $cmd . " --install-dir /home";
        $cache = " -v /caches/gem:/.gem";
        break;

    case "pip":
        if (!in_array($cmd_command, ["install"])) {
            $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.<br>";
            $debug_message .= " Use 'pip install'.<br>";
            $error = TRUE;
        }
        $cmd = $cmd . " --target=/home";
        $cache = " -v /caches/pip:/root/.cache/pip";
        break;

    case "npm":
        if (!in_array($cmd_command, ["install", "add"])) {
            $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.<br>";
            $debug_message .= " Use 'npm install/add'.<br>";
            $error = TRUE;
        }
        $cmd = $cmd_software . " set progress=false; " . $cmd . " --silent";
        $cache = " -v /caches/npm/:/.npm";
        break;

    case "yarn":
        if (!in_array($cmd_command, ["add"])) {
            $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.<br>";
            $debug_message .= "Use 'yarn add'.<br>";
            $error = TRUE;
        }
        $cmd = $cmd . " --no-progress --silent --prefer-online --ignore-optional --non-interactive";
        $cache = " -v /caches/yarn:/usr/local/share/.cache/yarn/v1";
        break;

    case "pnpm":
        if ($cmd_command != "install") {
            $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.<br>";
            $debug_message .= " Use 'pnpm install'.<br>";
            $error = TRUE;
        }
        $cmd = "echo '{}' > package.json && " . $cmd;
        break;

    case "ied":
        if ($cmd_command != "install") {
            $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.<br>";
            $debug_message .= " Use 'ied install'.<br`>";
            $error = TRUE;
        }
        break;

    case "composer":
        if (!in_array($cmd_command, ["require", "create-project"])) {
            $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.<br>";
            $debug_message .= "Use 'composer require/create-project'.<br>";
            $error = TRUE;
        }
        $cmd = $cmd . " --quiet --no-ansi --no-interaction --working-dir=/home";
        $cache = " -v /caches/composer:/.composer/cache";
        break;

    case "drush":
        if (!in_array($cmd_command, ["pm-download", "dl"])) {
            $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.<br>";
            $debug_message .= " Use 'drush dl/pm-download'.<br>";
            $error = TRUE;
        }
        $cache = " -v /caches/drush:/.drush/cache/download";
        break;
    }

    if ($error == TRUE) {
        echo $debug_message;
        exit();
    }

    $cmd_main = "echo " . $cmd . " \n >> /error/command.log && " .$cmd. " > /error/command.log 2>&1";
    //$cmd_exit = "$(if [ $(du -shb /home | awk '{print $1}') -lt 8000 ]; then exit; fi)"; // 8000 bytes
    //$cmd_exit = "$(if [ (echo $?) != 0 ]; then exit; fi)";
    $cmd_cd = " cd " . $folder;
    $cmd_chown_home = " chown -R www-data:www-data " . $folder;

    if ($compress_method == "tar.gz") {
        $cmd_compress = " tar -zcvf /downloads/" . $filename . " *";
    } else {
        $cmd_compress = " zip -r /downloads/" . $filename . " *";
    }
    $cmd_chown_compressed = " chown -R www-data:www-data /downloads/";

    $command = ' /bin/bash -c "'.$cmd_main.' && '.$cmd_chown_home.' && '.$cmd_cd.' && '.$cmd_compress.' && '.$cmd_chown_compressed.'"';

} else {
    debugConsole("Command is not defined.");
    echo "ID is not defined";
    exit();
}

if (isset($_GET['id']) && (strlen($_GET['id']) == 20)) {
    $id = $_GET['id'];
    $id = preg_replace('/[^a-z]/', '', $id);

    if ($debug) {
        debugConsole("ID=" . $id);
    }
} else {
    echo "ID is not defined";
    exit();
}

// Host files to create volumes
if (!file_exists($builds_path)) {
    mkdir($builds_path . $id, 0777);
}
$host_files = $builds_path . $id . "/";
$initial_compressed_path = $host_files . $filename;

// Set final (desirable) host file path
$compressed_path = $builds_path . $filename;
$compressed_url = "https://" . $domain . "/builds/" . $filename;

// Download the file if exists
if (file_exists($compressed_path)) {
    //downloadFile($compressed_path);
    redirectTo($compressed_url);
    exit();
}

// Run docker and create the file if not exist
if (!file_exists($compressed_path) && $error == FALSE) {

    if (!is_dir($host_files)) {
        mkdir($host_files, 0777);
    }

    $volumes = $cache . " -v " . $host_files . ":/downloads ";
    $error_volumes = " -v " . $host_files . "/error:/error ";
    $error_file = "https://" . $domain ."/builds/" . $id . "/error/command.log";

    $name = " --name " . $id;
    $workdir = " -w /home ";
    $docker = "docker run --rm " . $name . $workdir . $volumes . $error_volumes . $docker_image . $command;

    // Run docker
    if ($debug) {
        debugConsole("Docker=" . $docker);
    }

    exec($docker);

    // Move file into a new place/name
    if (file_exists($initial_compressed_path)) {
        //downloadFile($initial_compressed_path);
        rename($initial_compressed_path, $compressed_path);
        redirectTo($compressed_url);
    } else {
        redirectTo($error_file);
    }

    // Remove volumed host folder
    if (is_dir($host_files)) {
        rmdir($host_files);
    }

} else {
    print $debug_message;
    exit();
}
