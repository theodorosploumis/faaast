<?php

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/functions.php';

?>

<html>

<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="Description"
          content="A tiny tool that let's you package software on the cloud and download it. Useful for low bandwidth connections, testing etc.">
    <meta name="Keywords" content="package manager, faaast, saas, docker">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@theoploumis">
    <meta name="twitter:title" content="Faaast">
    <meta name="twitter:description"
          content="A tiny tool that let's you package software on the cloud and
          download it. Useful for low bandwidth connections, testing etc.">

    <title>Faaast - Packager as a Service</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="<?php print googleFonts(); ?>" rel="stylesheet">
    <?php print googleAnalytics($google_analytics_code); ?>
    <?php print shareThis($sharethis); ?>

</head>

<body>

<div class="logo">
    <a href="/"><img src="logo.png" title="Faaast logo"></a>
    <a class="gh-stars">
        <a href="https://hub.docker.com/r/tplcom/faaast/"><img src="https://img.shields.io/docker/stars/tplcom/faaast.svg?style=plastic"></a>
        <a href="https://github.com/theodorosploumis/faaast/"><img src="https://img.shields.io/github/stars/theodorosploumis/faaast.svg?style=social&label=Stars&style=plastic"></a>
    </div>
</div>

<h1 class="hidden">Faaast - Packager as a Service</h1>

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
            <a href="https://pip.pypa.io/">pip/pip3</a>
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

        <!-- <div class="form-item" id="running">
            <img src="loading.gif">
            <span> Packaging...</span>
        </div> -->

        <input class="form-item" id="submit-button" type="submit"
               value="Download">

    </form>

    <p>Prefer the <b>cli</b>? Get it <a href="https://github.com/theodorosploumis/faaast#cli-tool">here</a>.
    </p>

</section>

<footer>
    <p>
      <?php print footerMessage(); ?>
    </p>
</footer>

<!-- <script type="text/javascript">
  document.getElementById("submit-button").addEventListener('click',function(){
    document.getElementById("running").style.display = "block";
  });
</script> -->

</body>

</html>
