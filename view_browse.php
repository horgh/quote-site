<?= _get_template('_view_head', array('page_title' => $page_title)); ?>

<p>There are <?= $count_quotes; ?> quotes.
This is page <?= $page; ?>/<?= $pages; ?>.</p>

<?php foreach ($quotes as $quote): ?>
	<?php _show_template('_view_quote', array('quote' => $quote)); ?>
<?php endforeach; ?>

<?php if ($pages > 1): ?>
	<p>
	<?php if ($page > 1): ?>
		<a href="<?= htmlspecialchars($prev_page_url); ?>">Page <?= $page-1; ?></a>
	<?php endif; ?>

	Page <?= $page; ?>

	<?php if ($page !== $pages): ?>
		<a href="<?= htmlspecialchars($next_page_url); ?>">Page <?= $page+1; ?></a>
	<?php endif; ?>

	(<?= $pages; ?> pages)
	</p>
<?php endif; ?>
