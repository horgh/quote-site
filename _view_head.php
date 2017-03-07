<!DOCTYPE html>
<meta charset="utf-8">
<?php global $SITE_TITLE; ?>
<title><?= htmlspecialchars($page_title); ?> - <?= htmlspecialchars($SITE_TITLE); ?></title>
<?= _include_css('quote.css'); ?>
<?php if (isset($js)): ?>
<?php foreach ($js as $j): ?>
<?= _include_js($j); ?>
<?php endforeach; ?>
<?php endif; ?>

<h1><?= htmlspecialchars($SITE_TITLE); ?></h1>

<?= _get_template('_view_menu', array()); ?>

<h2><?= htmlspecialchars($page_title); ?></h2>
