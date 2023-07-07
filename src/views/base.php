<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?= $this->section('title') ?>An IndieWeb Webring</title>
    <meta name="description" content="An IndieWeb Webring">
    <!--<link id="favicon" rel="icon" href="FIXME" type="image/x-icon">-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/css/style.css">
    <?= $this->section('head') ?>
  </head>
  <body>
    <header>
      <div>
        <img src="/images/iwc-logo-lockup-color.svg" alt="IndieWebCamp logo" />🕸💍 –&nbsp; <h1><a href="/">An IndieWeb Webring</a></h1>
      </div>
      <small>Or: if you like it then you should put it on a 🕸💍</small>
    </header>

    <?php if( isset($flash_error) ): ?>
      <section id="flash-error" aria-label="Error">
        <strong><?= $flash_error ?></strong>
        <?php if( isset($flash_error_description) ): ?>
          <p><?= $flash_error_description ?></p>
        <?php endif ?>
      </section>
    <?php endif ?>

    <main>
      <?= $this->section('content') ?>
    </main>

    <footer>
      <?php if(isset($me)): ?>
        <p>
          You're signed in as <code><?= $me ?> &ndash; <a href="/auth/logout">Sign out</a></code>
        </p>
      <?php endif ?>
      <p>
        Made with ❤️ by <a href="https://martymcgui.re/">schmarty</a>.
      </p>
      <p>
        Check out the <a href="https://git.schmarty.net/schmarty/gem-diamond">project source code</a>.
      </p>
      <p><small>
        Check out the original version <a href="https://glitch.com/edit/#!/steady-sundial">on Glitch</a> or <a href="https://github.com/martymcguire/indiewebring.ws">GitHub</a> or <a href="https://git.schmarty.net/schmarty/indiewebring.ws">my git mirror</a>.
      </small></p>
      <p>
        Having trouble? Find schmarty in the <a href="https://indieweb.org/discuss">IndieWeb chat</a>!
      </p>
    </footer>
    <script src="/js/client.js"></script>
  </body>
</html>

