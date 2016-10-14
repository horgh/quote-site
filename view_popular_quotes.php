<?= _get_template('_view_head', array('page_title' => $page_title)); ?>

<p>There are <?= htmlspecialchars($count_quotes); ?> quotes with at least one search.</p>

<?php $count = 0; ?>
<?php foreach ($quotes as $quote): ?>
	<?php if ($count !== $quote['count']): ?>
		<h2><?= htmlspecialchars($quote['count']); ?>
				<?php if ($quote['count'] === 1): ?>
					search:
				<?php else: ?>
					searches:
				<?php endif; ?>
		</h2>
		<?php $count = $quote['count']; ?>
	<?php endif; ?>
	<?php _show_template('_view_quote', array('quote' => $quote)); ?>
<?php endforeach; ?>

<?php if ($total_pages > 1): ?>
	<p>There are <?= htmlspecialchars($total_pages); ?> pages of popular quotes.</p>
<?php endif; ?>

<?php if ($page > 1): ?>
	<a href="index.php?action=popular-quotes&amp;page=<?= $page-1; ?>">Previous page</a>
<?php endif; ?>

<?php if ($page < $total_pages): ?>
	<a href="index.php?action=popular-quotes&amp;page=<?= $page+1; ?>">Next page</a>
<?php endif; ?>
