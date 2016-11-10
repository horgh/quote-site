<?= _get_template('_view_head', array('page_title' => $page_title)); ?>

<a href="index.php?action=latest-quotes">Latest by create time</a> |
<a href="index.php?action=latest-quotes-by-id">Latest by number</a>

<?php foreach ($quotes as $quote): ?>
	<?php _show_template('_view_quote', array('quote' => $quote)); ?>
<?php endforeach; ?>
