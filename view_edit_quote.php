<?= _get_template('_view_head', array('page_title' => $page_title)); ?>

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

<form class="edit-quote" method="POST" action="index.php">
	<input type="hidden" name="action" value="edit-quote">
	<input type="hidden" name="id" value="<?= htmlspecialchars($quote['id']); ?>">

	<div class="number">
		<a href="index.php?action=view-quote&amp;id=<?= htmlspecialchars(rawurlencode($quote['id'])); ?>"
			>Quote #<?= htmlspecialchars($quote['id']); ?></a>
	</div>

	<div class="title">
		Title:
		<input type="text" name="title"
			value="<?= htmlspecialchars($quote['title']); ?>"
			placeholder="Title" required>
	</div>

	<div class="added_by">
		Added by:
		<?php if ($quote['added_by'] === null): ?>
			Missing
		<?php else: ?>
			<?= htmlspecialchars($quote['added_by']); ?>
		<?php endif; ?>
	</div>

	<div class="create_time">
		Date:
		<?= htmlspecialchars(_quote_time_to_string($quote['create_time'])); ?>
	</div>

	<textarea name="quote" cols="90" rows="20" required
		><?= htmlspecialchars($quote['quote']); ?></textarea>

	<?php if (strlen($quote['image']) > 0): ?>
		<img src="<?= htmlspecialchars($quote['image']); ?>" class="quote_image">
	<?php endif; ?>

	<?php if (isset($show_update_notes) && $show_update_notes &&
		strlen($quote['update_notes']) > 0): ?>
		<div class="update_notes">
			Update notes:
			<br>
			<?= nl2br(htmlspecialchars($quote['update_notes'])); ?>
		</div>
	<?php endif; ?>

	<div class="editor">
		Editor's name:
		<input type="text" name="editor" value="<?= htmlspecialchars($editor); ?>"
			placeholder="Your name" required>
	</div>

	<button>Save</button>
</form>
