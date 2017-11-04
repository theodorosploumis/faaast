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

    $error_filename = normalizeString($cmd) . ".error.log";

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

    // Capture command output on a file and append the command
    $cmd_main = $cmd. " > /error/".$error_filename." 2>&1";

    //$cmd_exit = "$(if [ $(du -shb /home | awk '{print $1}') -lt 8000 ]; then exit; fi)"; // 8000 bytes
    //$cmd_exit = "$(if [ (echo $?) != 0 ]; then exit; fi)";
    $cmd_cd = " cd " . $folder;
    $cmd_chown_home = " chown -R www-data:www-data " . $folder;

//    $cmd_debug = "printf '" . nl2br(trim(strip_tags($cmd))) . "' >> /error/command.log";

    if ($compress_method == "tar.gz") {
        $cmd_compress = " tar -zcvf /downloads/" . $filename . " *";
    } else {
        $cmd_compress = " zip -r /downloads/" . $filename . " *";
    }
    $cmd_chown_compressed = " chown -R www-data:www-data /downloads/";

    $command = ' /bin/bash -c "';
    $command .= $cmd_main.' && ';
    $command .= $cmd_chown_home.' && ';
    $command .= $cmd_cd.' && ';
    $command .= $cmd_compress.' && ';
    $command .= $cmd_chown_compressed;
    // $command .= $cmd_debug;
    $command .= '"';

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
$current_build_folder = $builds_path . $id . "/"; // the folder that will be volumed

$initial_compressed_path = $current_build_folder . $filename;

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

    if (!is_dir($current_build_folder)) {
        mkdir($current_build_folder, 0777);
    }

    $volumes = $cache . " -v " . $current_build_folder . ":/downloads ";
    
    // Error log etc
    $error_volumes = " -v " . $current_build_folder . "/error:/error ";
    $error_initial_file_path = $current_build_folder ."/error/" . $error_filename;
    $error_file_path = $builds_path ."/error/" . $error_filename;
    $error_file_url = "https://" . $domain . "/builds/error/" . $error_filename;

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
        rmdir($current_build_folder);
        redirectTo($compressed_url);

    } else {
        if (file_exists($error_file_path)){
            redirectTo($error_file_url);
        } else {
            rename($error_initial_file_path, $error_file_path);
            rmdir($current_build_folder);
            redirectTo($error_file_url);
        }
    }


} else {
    print $debug_message;
    exit();
}
