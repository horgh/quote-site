<?= _get_template('_view_head', array('page_title' => $page_title, 'js' => array('Chart.min.js'))); ?>

<h2>Quotes added in the past year</h2>
<canvas id="chart" height="400" width="800"></canvas>

<script>
var month_counts = <?= $month_counts_json; ?>;
</script>

<script>
var ctx = document.getElementById('chart').getContext('2d');

var data = {
	labels: [],
	datasets: [
		{
			label: 'Quotes',
			fillColor: "rgba(220,220,220,0.2)",
			strokeColor: "rgba(220,220,220,1)",
			pointColor: "rgba(220,220,220,1)",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(220,220,220,1)",
			data: []
		}
	]
};

for (var i = 0; i < month_counts.length; i++) {
	var month_count = month_counts[i];

	var label = month_count.year + '-';
	if (month_count.month < 10) {
		label += '0' + month_count.month;
	} else {
		label += month_count.month;
	}

	data.labels.push(label);

	data.datasets[0].data.push(month_count.count);
}

var options = {};
var chart = new Chart(ctx, {
	'type': 'line',
	'data': data,
	'options': options
});
</script>
