<form method="POST" action="/auth/login">
  <label for="me">Sign in with your domain:</label>
  <input type="url" id="me" name="me" autocomplete="home url"/>
  <input type="hidden" name="csrf-token" value="<?= $csrf_token ?>" />
  <button type="submit">Sign In</button>
</form>
