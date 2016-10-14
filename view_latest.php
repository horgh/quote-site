<?= _get_template('_view_head', array('page_title' => $page_title)); ?>

<?php foreach ($quotes as $quote): ?>
	<?php _show_template('_view_quote', array('quote' => $quote)); ?>
<?php endforeach; ?>
