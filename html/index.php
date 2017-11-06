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

<div class="logo">
    <img src="logo.png" title="Faaast logo">
</div>

<h1 class="hidden">Faaast Download</h1>

<section class="wrapper">

    <p class="info">
        1. Type your "Command"<br>
        2. Select compress type<br>
        3. Click "Download"<br><br>
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

    <form id="submit-form" class="form" action="faaast.php" method="get">

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

    </form>

</section>

<footer>
    <p>
        <?php print footerMessage(); ?>
    </p>
</footer>

</body>

</html>
