<?php

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/functions.php';

?>

<html>

<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Faaast Download</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="<?php print googleFonts(); ?>" rel="stylesheet">
    <?php print googleAnalytics($google_analytics_code); ?>
    <?php print shareThis($sharethis); ?>

</head>

<body>
<!--<img src="logo.png" class="logo">-->

<h1>Faaast Download</h1>

<section class="wrapper">

    <p class="info">
        1. Type your "Command"<br>
        2. Select compress type<br>
        3. Click "Download"<br>
        4. Get the <a href="/builds">packaged file</a>
        <br><br>
        <small>
            <a href="https://www.npmjs.com/">npm</a> |
            <a href="https://yarnpkg.com/">yarn</a> |
            <a href="https://pnpm.js.org/">pnpm</a> |
            <a href="https://github.com/alexanderGugel/ied">ied</a> |
            <a href="https://rubygems.org/">gem</a> |
            <a href="https://getcomposer.org">composer</a> |
            <a href="https://github.com/drush-ops/drush">drush</a> |
            <a href="https://pip.pypa.io/">pip</a>
        </small>
    </p>

    <form id="submit-form" class="form" action="" method="post">

        <label class="hidden">Command*:</label>
        <input class="form-item" type="text" name="cmd" id="command"
               placeholder="eg. npm install visionmedia/express"
               required="required">

        <label class="hidden">ID*:</label>
        <input class="form-item" type="hidden" name="id"
               value="<?php echo randomGenerator(); ?>" readonly="readonly"
               required="required">

        <label class="hidden">Compress type:</label>
        <select class="form-item" name="compress" form="submit-form">
            <option value="tar.gz">tar.gz</option>
            <option value="zip">zip</option>
        </select>

        <div class="form-item" id="running">
            <img src="loading.gif">
            <span> Packaging...</span>
        </div>

        <input class="form-item" id="submit-button" type="submit" value="Download">

        <?php

        // General options
        $debug_message = "";
        $error = FALSE;
        $cache = "";

        if (isset($_POST['compress'])) {
            $compress_method = $_POST['compress'];
        } else {
            $compress_method = "tar.gz";
        }
        $filename = "error." . $compress_method;

        // Currently, support only these package managers/tools
        $software = ["composer", "drush", "gem", "ied", "pip", "npm", "pnpm", "yarn"];

        // Get cmd from url
        if (isset($_POST['cmd'])) {
            $cmd = $_POST['cmd'];

            if ($debug) {
                $debug_message .= "Command: " . $cmd . "<br>";
            }

            $filename = normalizeString($cmd) . "." . $compress_method;

            $cmd_array = explode(" ", $cmd);
            $cmd_software = strtolower($cmd_array[0]); // eg npm
            $cmd_command = strtolower($cmd_array[1]); // eg install

            //  unset($cmd_array[0]);
            //  unset($cmd_array[1]);
            //  $cmd_following = $cmd_array; // eg react-native (package name)

            if (!in_array($cmd_software, $software)) {
                $debug_message .= 'Command ' . $cmd_software . ' is not supported.';
            }

            if (stringContains($cmd, [";", "||", "& ", "&&"])) {
                $debug_message .= 'Chained commands are not supported.';
            }

            // Default folder to compress
            $folder = "/home";

            switch ($cmd_software) {
            case "gem":
                if (!in_array($cmd_command, ["install"])) {
                    $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
                    $debug_message .= " Use 'gem install'.";
                    $error = TRUE;
                }
                $cmd = $cmd . " --install-dir /home";
                $cache = " -v /caches/gem:/.gem";
                break;

            case "pip":
                if (!in_array($cmd_command, ["install"])) {
                    $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
                    $debug_message .= " Use 'pip install'.";
                    $error = TRUE;
                }
                $cmd = $cmd . " --target=/home";
                $cache = " -v /caches/pip:/root/.cache/pip";
                break;

            case "npm":
                if (!in_array($cmd_command, ["install", "add"])) {
                    $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
                    $debug_message .= " Use 'npm install/add'.";
                    $error = TRUE;
                }
                $cmd = $cmd_software . " set progress=false; " . $cmd . " --silent";
                $cache = " -v /caches/npm/:/.npm";
                break;

            case "yarn":
                if (!in_array($cmd_command, ["add"])) {
                    $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
                    $debug_message .= "Use 'yarn add'.";
                    $error = TRUE;
                }
                $cmd = $cmd . " --no-progress --silent --prefer-online --ignore-optional --non-interactive";
                $cache = " -v /caches/yarn:/usr/local/share/.cache/yarn/v1";
                break;

            case "pnpm":
                if ($cmd_command != "install") {
                    $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
                    $debug_message .= " Use 'pnpm install'.";
                    $error = TRUE;
                }
                $cmd = "echo '{}' > package.json && " . $cmd;
                break;

            case "ied":
                if ($cmd_command != "install") {
                    $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
                    $debug_message .= " Use 'ied install'.";
                    $error = TRUE;
                }
                break;

            case "composer":
                if (!in_array($cmd_command, ["require", "create-project"])) {
                    $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
                    $debug_message .= "Use 'composer require/create-project'.";
                    $error = TRUE;
                }
                $cmd = $cmd . " --quiet --no-ansi --no-interaction --working-dir=/home";
                $cache = " -v /caches/composer:/.composer/cache";
                break;

            case "drush":
                if (!in_array($cmd_command, ["pm-download", "dl"])) {
                    $debug_message .= $cmd_software . " " . $cmd_command . " is not a valid command.\n";
                    $debug_message .= " Use 'drush dl/pm-download'.";
                    $error = TRUE;
                }
                $cache = " -v /caches/drush:/.drush/cache/download";
                break;
            }

            $cmd = $cmd . ";";
            $cmd_cd = " cd " . $folder . ";";
            $cmd_chown_home = " chown -R www-data:www-data " . $folder . ";";
            if ($compress_method == "tar") {
                $cmd_compress = " tar -zcvf /downloads/" . $filename . " *;";
            } else {
                $cmd_compress = " zip -r /downloads/" . $filename . " *;";
            }
            $cmd_chown_compressed = " chown -R www-data:www-data /downloads/";

            $command = ' /bin/bash -c "' . $cmd . $cmd_chown_home . $cmd_cd . $cmd_compress . $cmd_chown_compressed . '"';

        } else {
            $debug_message .= 'Command is not defined.';
            //$error = TRUE;
        }

        if (isset($_POST['id']) && (strlen($_POST['id']) == 20)) {

            $id = $_POST['id'];
            $id = preg_replace('/[^a-z]/', '', $id);

            if ($debug) {
                $debug_message .= "ID: " . $id . "<br>";
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
            if (file_exists($compressed_path) && $error == FALSE) {
                //downloadFile($compressed_path);
                redirectTo($compressed_url);
                exit();
            }

            // Run docker and create the file if not exist
            if (!file_exists($compressed_path) && $error == FALSE) {

                if (!file_exists($host_files)) {
                    mkdir($host_files, 0777);
                }

                $volumes = $cache . " -v " . $host_files . ":/downloads ";
                $name = " --name " . $id;
                $workdir = " -w /home ";
                $docker = "docker run --rm " . $name . $workdir . $volumes . $docker_image . $command;

                // Run docker
                exec($docker);

                // Move file into a new place/name
                rename($initial_compressed_path, $compressed_path);

                // Remove volumed host folder
                if (is_dir($host_files)) {
                    rmdir($host_files);
                }

                // Download file
                //downloadFile($initial_tar_path);
                redirectTo($compressed_url);
            }

        } else {
            $debug_message .= 'ID is not defined.';
            //$error = TRUE;
        }

        ?>

        <?php if ($error == TRUE) { ?>
            <div class="form-item">
                <span>
                    <?php print $debug_message; ?>
                </span>
            </div>
        <?php } ?>

    </form>

</section>

<footer>
    <p>
        <?php print footerMessage(); ?>
    </p>
</footer>

</body>

</html>
