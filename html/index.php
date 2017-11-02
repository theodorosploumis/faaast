<?php

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/functions.php';

?>

<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Faaast Download</title>

    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Cutive+Mono" rel="stylesheet">

    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $google_analytics_code; ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?php echo $google_analytics_code; ?>');
    </script>

    <script type='text/javascript' src='//platform-api.sharethis.com/js/sharethis.js#property=<?php print $sharethis; ?>&product=sticky-share-buttons' async='async'>
    </script>
    
</head>

<body>
<!--<img src="logo.png" class="logo">-->
<h1>Faaast Download</h1>

<section class="wrapper">

    <p class="info">
        1. Set a package "Command"<br>
        2. Click "Download"<br>
        3. Get the <a href="/builds">packaged files</a><br>
        <br>
        <small>
            Packagers:<br>
            <a href="https://www.npmjs.com/">npm</a>,
            <a href="https://yarnpkg.com/">yarn</a>,
            <a href="https://pnpm.js.org/">pnpm</a>,
            <a href="https://github.com/alexanderGugel/ied">ied</a>,
            <a href="https://rubygems.org/">gem</a>,
            <a href="https://getcomposer.org">composer</a>,
            <a href="https://github.com/drush-ops/drush">drush</a>,
            <a href="https://pip.pypa.io/">pip</a>.
        </small>
    </p>

    <form id="submit-form" class="form" action="faaast.php" method="get">
      <label>Command*:</label> <input type="text" name="cmd" placeholder="eg. npm install visionmedia/express" required="required"><br>
<!--<label>Email:</label> <input type="email" name="email"><br>-->
      <label>ID*:</label> <input type="text" name="id" value="<?php echo randomGenerator(); ?>" readonly="readonly" required="required"><br>
      <input class="submit" id="submit-button" type="submit" value="Download">
    </form>

</section>

<footer>
  <p>
    Created by <a href="https://www.theodorosploumis.com/en">TheodorosPloumis</a>
    | Hosted on <a href="https://m.do.co/c/1123d0854c8f">DigitalOcean</a>
  </p>
</footer>

</body>

</html>