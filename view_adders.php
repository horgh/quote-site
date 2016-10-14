<?= _get_template('_view_head', array('page_title' => $page_title)); ?>

<h2>All time</h2>
<?php foreach ($adders as $adder => $count): ?>
	<div class="adder">
		<div class="name">
			<?= htmlspecialchars($adder); ?>:
		</div>
		<div class="count">
			<?= htmlspecialchars($count); ?>
		</div>
	</div>
<?php endforeach; ?>

<h2>Past 6 months</h2>
<?php foreach ($adders_6_months as $adder => $count): ?>
	<div class="adder">
		<div class="name">
			<?= htmlspecialchars($adder); ?>:
		</div>
		<div class="count">
			<?= htmlspecialchars($count); ?>
		</div>
	</div>
<?php endforeach; ?>
