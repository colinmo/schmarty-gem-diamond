<section class="profile h-card">
  <?php if (isset($photo)): ?><img src="<?= $photo ?>" /><?php endif ?>
  <a class="u-url" href="<?= $url ?>"><?= $cute_url ?></a>
  <?php if (isset($name)): ?><div class="name p-name"><?= $name ?></div><?php endif ?>
  <?php if (isset($note)): ?><div class="note p-note"><?= $note ?></div><?php endif ?>
</section>
