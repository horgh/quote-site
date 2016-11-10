	<div class="quote">
		<div class="number">
			<a href="index.php?action=view-quote&amp;id=<?= htmlspecialchars(rawurlencode($quote['id'])); ?>">Quote #<?= htmlspecialchars($quote['id']); ?></a>
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
			<?php if ($quote['create_time'] === null): ?>
				Missing
			<?php else: ?>
				<?= htmlspecialchars($quote['create_time']); ?> (Vancouver)
			<?php endif; ?>
		</div>

		<div class="quote_text">
			<?= nl2br(htmlspecialchars($quote['quote'])); ?>
		</div>

		<?php if (isset($show_update_notes) && $show_update_notes): ?>
			<div class="update_notes">
				Update notes: <?= htmlspecialchars($quote['update_notes']); ?>
			</div>
		<?php endif; ?>
	</div>
