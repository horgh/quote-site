<!DOCTYPE html>
<meta charset="UTF-8">
<title><?= htmlspecialchars($page_title); ?></title>
<?= _include_css('quote.css'); ?>
<?php if (isset($js)): ?>
<?php foreach ($js as $j): ?>
<?= _include_js($j); ?>
<?php endforeach; ?>
<?php endif; ?>

<?= _get_template('_view_menu', array()); ?>

<h1><?= htmlspecialchars($page_title); ?></h1>
