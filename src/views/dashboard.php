<?php $this->layout('base') ?>
<?php $this->start('title') ?>Dashboard | <?php $this->end() ?>
<h2>
  Hello, <?= $site['url'] ?>
</h2>

<section id="dashboard-profile">
  <h2>Your Profile</h2>
  <p>If you have one, your profile will be displayed on <a href="/directory">the webring directory</a>.</p>

  <div class="directory">
    <?php if($site['profile']): ?>
      <?= $this->insert('partials/profile', $site['profile']) ?>
      <form action="/dashboard/remove-profile" method="POST">
        <input type="hidden" name="csrf-token" value="<?= $csrf_token ?>" />
        <input type="submit" value="🗑 Remove my profile."/>
        <small>
          You'll still be in the webring, but will not appear in the directory.
        </small>
      </form>
    <?php else: ?>
      <div class="profile">You currently have no profile information.</div>
    <?php endif ?>
  </div>
  <form action="/dashboard/check-profile" method="POST">
    <input type="hidden" name="csrf-token" value="<?= $csrf_token ?>" />
    <input type="submit" value="🔃 Check for updated profile."/>
    <small>
        We'll look for your name, photo, and note on the <a href="http://microformats.org/wiki/representative-h-card-authoring">representative h-card</a>
        on your page. If you're having trouble, you can <a href="https://indiewebify.me/validate-h-card/?url=<?=$site['url']?>">test your page with indiewebify.me</a>.
    </small>
  </form>
</section>

<section id="webring-links">
  <h2>Your Webring Links</h2>
  <div class="status <?= $site['active'] ? 'active' : 'inactive' ?>">
    Your site is currently: <?= $site['active'] ? '' : 'NOT '?>ACTIVE
  </div>

  <p>
    To stay active, make sure links like these are visible on your site:
  </p>

  <div>
    <textarea rows="6" cols="60" id="urls-compatible">
<a href="https://<?= $hostname ?>/previous">&larr;</a>
An <a href="https://<?= $hostname ?>">IndieWeb Webring</a> 🕸💍
<a href="https://<?= $hostname ?>/next">&rarr;</a></textarea>
  </div>

  <form action="/dashboard/check-links" method="POST">
    <input type="hidden" name="csrf-token" value="<?= $csrf_token ?>" />
    <input type="submit" value="Check links now!"/>
  </form>

  <?php if (isset($checks)): ?>
    <?= $this->insert('partials/site-checks') ?>
  <?php endif ?>
</section>
