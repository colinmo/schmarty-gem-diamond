<?php $this->layout('base') ?>
<?php $this->start('title') ?>Directory | <?php $this->end() ?>
<h2>
  A directory of active sites with profiles
</h2>
<div class="directory">
  <?php foreach($profiles as $profile): ?>
    <?= $this->insert('partials/profile', $profile) ?>
  <?php endforeach ?>
</div>
