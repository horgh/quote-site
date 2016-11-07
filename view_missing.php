<?= _get_template('_view_head', array('page_title' => $page_title)); ?>

<p>There are
<?= htmlspecialchars($total_missing); ?>/<?= htmlspecialchars($total_quotes); ?>
 (<?= htmlspecialchars(sprintf('%.2f', ($total_missing/$total_quotes*100))); ?>%)
quotes missing this information.</p>

<p>Here are <?= htmlspecialchars(count($quotes)); ?> of them.</p>

<?php foreach ($quotes as $quote): ?>
	<?php _show_template('_view_quote', array('quote' => $quote)); ?>
<?php endforeach; ?>
