<?php
$className = 'mystery';
if( preg_match('/^Missing*/', $error) ) {
	$className = 'missing';
}
else if ( preg_match('/^Old emoji-style.*/', $error) ) {
	$className = 'warn';
}
?>
<li class="<?= $className ?>">
	<?= htmlspecialchars($error) ?>
</li>
