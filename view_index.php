<?= _get_template('_view_head', array('page_title' => $page_title)); ?>

<p>
	I will try to remove timestamps from the quote.
	You must confirm the quote after I process it.
</p>
<p>
	If your timestamps are not removed correctly, let me know.
</p>

<div class="success">
	<?php foreach ($successes as $success): ?>
		<?= htmlspecialchars($success); ?>
		<br>
	<?php endforeach; ?>
</div>
<div class="error">
	<?php foreach ($errors as $error): ?>
		<?= htmlspecialchars($error); ?>
		<br>
	<?php endforeach; ?>
</div>

<form method="POST" action="index.php">
	<input type="hidden" name="action" value="add_quote">

	<?php if (strlen($added_by) > 0): ?>
		<input type="text" name="added_by"
			value="<?= htmlspecialchars($added_by); ?>"
			placeholder="Enter your name"
			>
	<?php else: ?>
		<input type="text" name="added_by"
			placeholder="Enter your name"
			>
	<?php endif; ?>

	<br>

	<?php if (strlen($quote) > 0): ?>
		<textarea name="quote" cols="90" rows="20"
			placeholder="Enter the quote"
			><?= htmlspecialchars($quote); ?></textarea>
	<?php else: ?>
		<textarea name="quote" cols="90" rows="20"
			placeholder="Enter the quote"
			></textarea>
	<?php endif; ?>

	<br>

	<?php if (strlen($quote) > 0 && strlen($added_by) > 0): ?>
		Confirm
		<input type="checkbox" name="confirm_quote">
		<br>
	<?php endif; ?>

	<input type="submit" value="Add">
</form>
