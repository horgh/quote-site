<?= _get_template('_view_head', array('page_title' => $page_title)); ?>

<p>There are <?= $count_quotes; ?> quotes.</p>

<?php foreach ($quotes as $quote): ?>
	<?php _show_template('_view_quote', array('quote' => $quote)); ?>
<?php endforeach; ?>

<?php if ($pages > 1): ?>
	<p>There are <?= $pages; ?> pages of quotes.</p>
	<p>
	<?php if ($page > 1): ?>
		<a href="<?= htmlspecialchars($prev_page_url); ?>">Previous page</a>
	<?php endif; ?>

	<?php if ($page !== $pages): ?>
		<a href="<?= htmlspecialchars($next_page_url); ?>">Next page</a>
	<?php endif; ?>
	</p>
<?php endif; ?>
