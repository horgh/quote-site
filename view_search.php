<?= _get_template('_view_head', array('page_title' => $page_title)); ?>

<form method="GET" action="index.php">
	<input type="hidden" name="action" value="search">
	<input type="text" name="query" value="<?= htmlspecialchars($query); ?>"
		placeholder="Text to search for">
	<input type="submit" value="Search">
</form>

<?php if (strlen($query) > 0): ?>
	<h2>Quotes matching *<?= htmlspecialchars($query); ?>*:</h2>

	<?php if ($count === 1): ?>
	<p>There is <?= $count; ?> matching quote.</p>
	<?php else: ?>
	<p>There are <?= $count; ?> matching quotes.</p>
	<?php endif; ?>

	<?php foreach ($quotes as $quote): ?>
		<?php _show_template('_view_quote', array('quote' => $quote)); ?>
	<?php endforeach; ?>

	<?php if ($total_pages > 1): ?>
		<p>There are <?= $total_pages; ?> pages of results.</p>
		<p>
		<?php if ($page > 1): ?>
			<a href="<?= htmlspecialchars($prev_page_url); ?>">Previous page</a>
		<?php endif; ?>
		<?php if ($page !== $total_pages): ?>
			<a href="<?= htmlspecialchars($next_page_url); ?>">Next page</a>
		<?php endif; ?>
		</p>
	<?php endif; ?>
<?php endif; ?>
