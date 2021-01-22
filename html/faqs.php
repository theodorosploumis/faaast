<?php

require_once __DIR__ . '/functions.php';

?>

<html>

<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Faaast Download - FAQs</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="<?php print googleFonts(); ?>" rel="stylesheet">
</head>

<body>

<div class="logo">
    <a href="/"><img src="logo.png" title="Faaast logo"></a>
</div>

<section class="wrapper">

    <h1>Faaast - FAQs</h1>

    <section class="faq">
        <h2>About</h2>

        <p>
            Faaast is a package manager as a service (SaaS). Faaaster than your
            local machine!
        </p>

        <p>
            Instead of using your local machine to build software from
            repositories you can use Faaast.
        </p>
    </section>

    <section class="faq">
        <h2>What package managers are supported?</h2>

        <p> Currently we support only these "package managers":<br>

            <b>
                <a href="https://www.npmjs.com/">npm</a>,
                <a href="https://yarnpkg.com/">yarn</a>,
                <a href="https://pnpm.js.org/">pnpm</a>,
                <a href="https://rubygems.org/">gem</a>,
                <a href="https://getcomposer.org">composer</a>,
                <a href="https://github.com/drush-ops/drush">drush</a>,
                <a href="https://pip.pypa.io/">pip3</a>
            </b>.

        </p>
        The commands that are currently allowed are:
        <p>
            <small>
                - composer1 install/create-project<br>
                - composer2 install/create-project<br>
                - drush dl/pm-download<br>
                - gem install<br>
                - npm install/add<br>
                - pnpm install<br>
                - pip3 install<br>
                - yarn add<br>
            </small>
        </p>
    </section>

    <section class="faq">
        <h2>Can I write chained commands?</h2>
        <p>Currently NO.</p>
    </section>

    <section class="faq">
        <h2>Why use it?</h2>

        <ul>
            <li>Because sometimes there unknown errors with package managers.
            </li>
            <li>Because WiFi issues may corrupt packaging.</li>
            <li>Because sometimes you don't want to spend time for packaging.
            </li>
            <li>Because not every machine can use package managers.</li>
            <li>To experiment with package managers.</li>
            <li>Because Docker can do this!</li>
        </ul>
    </section>

    <section class="faq">
        <h2>Which is the software behind this?</h2>

        <p>
            I have built this tool as a proof of concept of a simple SaaS based
            on <b>Docker</b> and <b>php</b>.
        </p>

        <p>
            Everytime you use a package command you run a public
            <a href="https://hub.docker.com/r/tplcom/faaast">Docker image</a> that contains:
        </p>

        <p>
            <small>
                - bundler<br>
                - composer<br>
                - drush<br>
                - gem<br>
                - node<br>
                - npm<br>
                - yarn<br>
                - pnpm<br>
                - python3<br>
                - pip3<br>
                - php<br>
                - ruby<br>
            </small>
        </p>
        <p>
          Please check on <a href="https://github.com/theodorosploumis/faaast/releases">releases</a> for binary versions.
        </p>
    </section>

    <section class="faq">
        <h2>Who created this?</h2>

        <p>
            I am <b>Theodoros Ploumis</b>.
        </p>

        <p>
            A <a href="https://www.drupal.org/u/theodorosploumis">Drupal developer</a> with some devops skills. See <a href="https://theodorosploumis.github.io/portfolio/">my work here</a>.
        </p>
    </section>

</section>

<footer>
    <p>
      <?php print footerMessage(); ?>
    </p>
</footer>

</body>

</html>
