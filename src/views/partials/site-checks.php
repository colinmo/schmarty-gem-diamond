<table class="site-checks-table">
  <thead>
    <tr><th>Active?</th><th>Checked</th><th>Details</th></tr>
  </thead>
  <tbody>
    <?php foreach ($checks as $check): ?>
      <tr>
        <td><?= $check['active'] ? '✅ ' : '❌ ' ?></td>
        <td><?= $this->insert('partials/time-ago', ['time' => $check['datetime']]) ?></td>
        <td class="check-results">
          <?php if ( !empty($check['errors']) ): ?>
            <ul>
              <?php foreach ($check['errors'] as $error): ?>
                <?= $this->insert('partials/check-error', ['error' => $error]) ?>
              <?php endforeach ?>
            </ul>
          <?php endif ?>
        </td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>
