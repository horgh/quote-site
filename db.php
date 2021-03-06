<?php
//
// These functions interact with the database.
//

//! connect to the database
/*!
 * @return PDO
 *
 * SIDE EFFECT: may throw exception if failure
 */
function _connect_to_database()
{
	global $DB_NAME;
	global $DB_HOST;
	global $DB_USER;
	global $DB_PASS;

	global $dbh;

	if (isset($dbh) && $dbh) {
		return $dbh;
	}

	$db_dsn = "pgsql:dbname=$DB_NAME;host=$DB_HOST";

	$dbh = new PDO($db_dsn, $DB_USER, $DB_PASS);
	return $dbh;
}

//! add a quote to the database.
/*!
 * @param string $quote
 * @param string $added_by who is adding it
 * @param string $title Title.
 * @param string $quote_image Optional. Path to image.
 *
 * Returns mixed. Either an array with the quote record that was added, or
 * boolean false if there was a failure.
 */
function _add_quote($quote, $added_by, $title, $quote_image)
{
	// Image is optional.
	if (strlen($quote) === 0 || strlen($added_by) === 0 || strlen($title) === 0) {
		_log_message('error', "Invalid parameter");
		return false;
	}

	$dbh = _connect_to_database();

	$sql = '
INSERT INTO quote
(quote, added_by, title, image)
VALUES(?, ?, ?, ?)
RETURNING id
';

	$params = array(
		$quote,
		$added_by,
		$title,
		strlen($quote_image) > 0 ? $quote_image : null,
	);

	$sth = $dbh->prepare($sql);
	if (false === $sth) {
		_log_message('error', "Failure preparing query");
		return false;
	}

	if ($sth->execute($params) === false) {
		_log_message('error', "Failure inserting quote");
		_log_message('error', print_r($sth->errorInfo(), true));
		return false;
	}

	if ($sth->rowCount() !== 1) {
		_log_message('error', "Quote not inserted unexpectedly");
		return false;
	}

	// We get back the quote ID. Look up the quote.

	$rows = $sth->fetchAll(PDO::FETCH_NUM);
	if (false === $rows) {
		_log_message('error', 'Unable to fetch rows after insert.');
		return false;
	}

	if (count($rows) !== 1) {
		_log_message('error', 'Unexpected number of rows returned: ' . count($rows));
		return false;
	}

	if (count($rows[0]) !== 1) {
		_log_message('error', 'Unexpected number of columns in returned row: ' . count($rows[0]));
		return false;
	}

	if (!is_numeric($rows[0][0])) {
		_log_message('error', 'Unexpected ID column value, not numeric: ' . $rows[0][0]);
		return false;
	}

	return _get_quote_by_id(intval($rows[0][0]));
}

// Save the quote into the database.
//
// Right now the only field we support updating is the title.
//
// We expect you've checked the update is sane by this point.
function _update_quote($quote, $editor, $title, $text)
{
	if (!is_array($quote) || strlen($editor) === 0 || strlen($title) === 0 ||
		strlen($text) === 0) {
		_log_message('error', "Invalid parameter");
		return false;
	}

	$now = date('Y-m-d H:i:s O');

	$updates = array();
	if (strlen($quote['update_notes']) > 0) {
		$updates[] = $quote['update_notes'];
	}
	if ($title !== $quote['title']) {
		$updates[] = "$now: $editor changed title from " . $quote['title'] . ' to ' . $title;
	}
	if ($text !== $quote['quote']) {
		$updates[] = "$now: $editor changed quote from " . $quote['quote'] . ' to ' . $text;
	}

	$sql = 'UPDATE quote SET title = ?, update_notes = ?, quote = ? WHERE id = ?';
	$params = array(
		$title,
		implode("\n", $updates),
		$text,
		$quote['id'],
	);

	$dbh = _connect_to_database();

	$sth = $dbh->prepare($sql);
	if (false === $sth) {
		_log_message('error', "Failure preparing query");
		return false;
	}

	if ($sth->execute($params) === false) {
		_log_message('error', "Failure updating quote");
		_log_message('error', print_r($sth->errorInfo(), true));
		return false;
	}

	if ($sth->rowCount() !== 1) {
		_log_message('error', "Quote not updated unexpectedly");
		return false;
	}

	return true;
}

function _get_top_adders_all_time()
{
	$dbh = _connect_to_database();
	if (!$dbh) {
		return "cannot connect to database";
	}

	$sql = '
		SELECT *
		FROM (
			SELECT added_by, COUNT(quote)
			FROM quote
			GROUP BY added_by
		) b
		ORDER BY 2 desc
';

	$rows = $dbh->query($sql, PDO::FETCH_NUM);
	$adders = array();
	foreach ($rows as $row) {
		if ($row[0] === null) {
			$adders[ 'Missing adder' ] = intval($row[1]);
		} else {
			$adders[ $row[0] ] = intval($row[1]);
		}
	}

	return $adders;
}

function _get_top_adders_past_6_months()
{
	$dbh = _connect_to_database();
	if (!$dbh) {
		return "cannot connect to database";
	}

	$sql = '
		SELECT *
		FROM (
			SELECT added_by, COUNT(quote)
			FROM quote
			WHERE create_time IS NOT NULL AND
			create_time > NOW() - CAST(\'6 months\' AS INTERVAL)
			GROUP BY added_by
		) b
		ORDER BY 2 desc
';

	$rows = $dbh->query($sql, PDO::FETCH_NUM);
	$adders = array();
	foreach ($rows as $row) {
		if ($row[0] === null) {
			$adders[ 'Missing adder' ] = intval($row[1]);
		} else {
			$adders[ $row[0] ] = intval($row[1]);
		}
	}

	return $adders;
}

function _get_quotes_by_months()
{
	$dbh = _connect_to_database();
	if (!$dbh) {
		return "cannot connect to database";
	}

	// The first query gets a count of quotes for each year+month. However, if a
	// year+month has no quotes, then it does not appear. To ensure year+months
	// with zero quotes appear, add a row for each with a count of zero.

	$sql = "
SELECT
year, month, SUM(count)
FROM
(

  (
  SELECT
  EXTRACT(YEAR FROM create_time) AS year,
  EXTRACT(MONTH FROM create_time) AS month,
  COUNT(quote) AS count
  FROM quote
  WHERE create_time IS NOT NULL AND
  create_time > NOW() - CAST('5 years 1 month' AS INTERVAL)
  GROUP BY year, month
  )

  UNION

  (
  SELECT
  EXTRACT(YEAR FROM month) AS year,
  EXTRACT(MONTH FROM month) AS month,
  0 AS count
  FROM
  GENERATE_SERIES(NOW() - CAST('5 years 1 month' AS INTERVAL), NOW(), '1 month')
  AS month
  )

) AS month_counts

GROUP BY year, month
ORDER BY year, month
";

	$rows = $dbh->query($sql, PDO::FETCH_NUM);

	$month_counts = array();
	foreach ($rows as $row) {
		$month_counts[] = array(
			'year'  => intval($row[0]),
			'month' => intval($row[1]),
			'count' => intval($row[2]),
		);
	}

	return $month_counts;
}

function _get_popular_quotes($page, $page_size)
{
	if (!is_int($page) || $page < 1) {
		return false;
	}
	if (!is_int($page_size) || $page_size < 1) {
		return false;
	}

	$sql = "
		SELECT

		COUNT(*) AS count,
		q.id AS quote_id,
		q.quote AS quote,
		EXTRACT(EPOCH FROM q.create_time),
		q.added_by,
		q.title,
		q.image

		FROM quote_search qs
		LEFT JOIN quote q
		ON qs.quote_id = q.id

		WHERE q.sensitive = false

		GROUP BY q.id

		ORDER BY count DESC, q.id ASC
		LIMIT ? OFFSET ?
	";

	$dbh = _connect_to_database();
	if (!$dbh) {
		return false;
	}

	$sth = $dbh->prepare($sql);
	if (false === $sth) {
		echo '<pre>' . htmlspecialchars(print_r($dbh->errorInfo(), true)) . '</pre>';
		return false;
	}

	$params = array(
		$page_size,
		($page-1)*$page_size,
	);

	if (!$sth->execute($params)) {
		echo '<pre>' . htmlspecialchars(print_r($dbh->errorInfo(), true)) . '</pre>';
		return false;
	}

	$rows = $sth->fetchAll(PDO::FETCH_NUM);
	if (false === $rows) {
		echo '<pre>' . htmlspecialchars(print_r($dbh->errorInfo(), true)) . '</pre>';
		return false;
	}

	$quotes = array();
	foreach ($rows as $row) {
		$create_time = $row[3];
		if (null !== $create_time) {
			$create_time = intval($create_time);
		}

		$quotes[] = array(
			'count'       => intval($row[0]),
			'id'          => intval($row[1]),
			'quote'       => $row[2],
			'create_time' => $create_time,
			'added_by'    => $row[4],
			'title'       => $row[5],
			'image'       => $row[6],
		);
	}

	return $quotes;
}

function _count_popular_quotes()
{
	$sql = '
		SELECT COUNT(1) FROM
		(
			SELECT COUNT(1) FROM quote_search qs
			LEFT JOIN quote q ON q.id = qs.quote_id
			WHERE q.sensitive = false
		  GROUP BY quote_id
		) c
	';

	$dbh = _connect_to_database();
	if (!$dbh) {
		return false;
	}

	$rows = $dbh->query($sql, PDO::FETCH_NUM);
	if (false === $rows) {
		echo '<pre>' . htmlspecialchars(print_r($dbh->errorInfo(), true)) . '</pre>';
		return false;
	}

	$count = 0;
	// UGLY, getting out of statement. Can't just index like an array. It's a
	// PDOStatement object.
	foreach ($rows as $row) {
		$count = intval($row[0]);
	}

	return $count;
}

function _get_random_quotes()
{
	$sql = "
		SELECT
		id,
		quote,
		EXTRACT(EPOCH FROM create_time),
		added_by,
		title,
		image,
		update_notes
		FROM quote
		WHERE sensitive = false
		ORDER BY RANDOM()
		LIMIT 20
";

	$quotes = _db_fetch_quotes($sql, array());

	return $quotes;
}

function _get_quotes($page, $page_size)
{
	if (!is_int($page) || $page < 1 ||
		!is_int($page_size) || $page_size < 1) {
		_log_message('error', "Invalid parameter");
		return false;
	}

	$sql = "
		SELECT
		id,
		quote,
		EXTRACT(EPOCH FROM create_time),
		added_by,
		title,
		image,
		update_notes
		FROM quote
		WHERE sensitive = false
		ORDER BY id
		LIMIT ? OFFSET ?
";

	$params = array(
		$page_size,
		($page-1)*$page_size,
	);

	$quotes = _db_fetch_quotes($sql, $params);

	return $quotes;
}

function _get_quote_by_id($id)
{
	if (!is_int($id)) {
		return false;
	}

	$sql = "
		SELECT
		id,
		quote,
		EXTRACT(EPOCH FROM create_time),
		added_by,
		title,
		image,
		update_notes
		FROM quote
		WHERE id = ?
		AND sensitive = false
	";

	$params = array(
		$id,
	);

	$quotes = _db_fetch_quotes($sql, $params);
	if (false === $quotes) {
		return false;
	}

	if (count($quotes) !== 1) {
		return false;
	}

	return $quotes[0];
}

function _get_latest_quotes()
{
	$sql = "
		SELECT
		id,
		quote,
		EXTRACT(EPOCH FROM create_time),
		added_by,
		title,
		image,
		update_notes
		FROM quote
		WHERE create_time IS NOT NULL
		AND sensitive = false
		ORDER BY create_time DESC
		LIMIT 50
";

	$quotes = _db_fetch_quotes($sql, array());

	return $quotes;
}

function _get_latest_quotes_by_id()
{
	$sql = "
		SELECT
		id,
		quote,
		EXTRACT(EPOCH FROM create_time),
		added_by,
		title,
		image,
		update_notes
		FROM quote
		WHERE create_time IS NOT NULL
		AND sensitive = false
		ORDER BY id DESC
		LIMIT 50
";

	$quotes = _db_fetch_quotes($sql, array());

	return $quotes;
}

function _get_quotes_missing_adder()
{
	// Choosing to order by id descending with the idea that
	// filling in more recent ones is easier
	$sql = "
		SELECT
		id,
		quote,
		EXTRACT(EPOCH FROM create_time),
		added_by,
		title,
		image,
		update_notes
		FROM quote
		WHERE added_by IS NULL
		AND sensitive = false
		ORDER BY 1 DESC
		LIMIT 20
";

	$quotes = _db_fetch_quotes($sql, array());

	return $quotes;
}

function _get_quotes_missing_date()
{
	// Choosing to order by id descending with the idea that
	// filling in more recent ones is easier
	$sql = "
		SELECT
		id,
		quote,
		EXTRACT(EPOCH FROM create_time),
		added_by,
		title,
		image,
		update_notes
		FROM quote
		WHERE create_time IS NULL
		AND sensitive = false
		ORDER BY 1 DESC
";

	$quotes = _db_fetch_quotes($sql, array());

	return $quotes;
}

function _count_quotes()
{
	$dbh = _connect_to_database();
	if (!$dbh) {
		return "cannot connect to database";
	}

	$sql = "
		SELECT COUNT(1) FROM quote
		WHERE sensitive = false
";

	$rows = $dbh->query($sql, PDO::FETCH_NUM);

	foreach ($rows as $row) {
		return intval($row[0]);
	}

	return 'Count not found';
}

function _count_quotes_missing_adder()
{
	$dbh = _connect_to_database();
	if (!$dbh) {
		return "cannot connect to database";
	}

	$sql = "
SELECT COUNT(1)
FROM quote
WHERE added_by IS NULL
AND sensitive = false
";

	$rows = $dbh->query($sql, PDO::FETCH_NUM);

	foreach ($rows as $row) {
		return intval($row[0]);
	}

	return 'Count not found';
}

function _count_quotes_missing_date()
{
	$dbh = _connect_to_database();
	if (!$dbh) {
		return "cannot connect to database";
	}

	$sql = "
SELECT COUNT(1)
FROM quote
WHERE create_time IS NULL
AND sensitive = false
";

	$rows = $dbh->query($sql, PDO::FETCH_NUM);

	foreach ($rows as $row) {
		return intval($row[0]);
	}

	return 'Count not found';
}

// Return a page of quotes matching the query.
//
// The query must already be prepared to be sent to the database.
function _search_quotes($query, $page, $page_size)
{
	if (!is_string($query) || strlen($query) === 0 ||
		!is_int($page) || $page <= 0 ||
		!is_int($page_size) || $page_size <= 0) {
		return false;
	}

	$offset = ($page-1)*$page_size;

	$sql = "
		SELECT
		id,
		quote,
		EXTRACT(EPOCH FROM create_time),
		added_by,
		title,
		image,
		update_notes
		FROM quote
		WHERE (quote ILIKE ? OR title ILIKE ?) AND
		sensitive = false
		ORDER BY COALESCE(create_time, '1970-01-01') ASC, id ASC
		LIMIT ? OFFSET ?
	";

	$params = array($query, $query, $page_size, $offset);

	$quotes = _db_fetch_quotes($sql, $params);

	return $quotes;
}

// Count how many quotes match the given query.
//
// The query must already be prepared to be sent to the database.
function _count_matching_quotes($query)
{
	if (!is_string($query) || strlen($query) === 0) {
		return false;
	}

	$sql = "
		SELECT COUNT(1)
		FROM quote
		WHERE (quote ILIKE ? OR title ILIKE ?) AND
		sensitive = false
	";

	$params = array($query, $query);

	$dbh = _connect_to_database();
	if (!$dbh) {
		echo "Cannot connect to database";
		return false;
	}

	$sth = $dbh->prepare($sql);
	if (false === $sth) {
		echo "Failure preparing query";
		echo '<pre>' . htmlspecialchars(print_r($dbh->errorInfo(), true)) . '</pre>';
		return false;
	}

	if (!$sth->execute($params)) {
		echo "Failure executing query";
		echo '<pre>' . htmlspecialchars(print_r($sth->errorInfo(), true)) . '</pre>';
		return false;
	}

	$rows = $sth->fetchAll(PDO::FETCH_NUM);
	if (false === $rows) {
		echo "Failure fetching rows";
		echo '<pre>' . htmlspecialchars(print_r($sth->errorInfo(), true)) . '</pre>';
		return false;
	}

	$count = 0;
	foreach ($rows as $row) {
		$count = intval($row[0]);
	}

	return $count;
}

// Perform a query for one or more quotes, and fetch all rows.
function _db_fetch_quotes($sql, $params)
{
	$dbh = _connect_to_database();
	if (!$dbh) {
		echo "Cannot connect to database";
		return false;
	}

	$sth = $dbh->prepare($sql);
	if (false === $sth) {
		echo "Failure preparing query";
		return false;
	}

	if (!$sth->execute($params)) {
		echo "Failure executing query";
		return false;
	}

	$rows = $sth->fetchAll(PDO::FETCH_NUM);
	if (false === $rows) {
		echo "Failure fetching rows";
		return false;
	}

	$quotes = array();
	foreach ($rows as $row) {
		$create_time = $row[2];
		if (null !== $create_time) {
			$create_time = intval($create_time);
		}

		$quotes[] = array(
			'id'           => intval($row[0]),
			'quote'        => $row[1],
			'create_time'  => $create_time,
			'added_by'     => $row[3],
			'title'        => $row[4],
			'image'        => $row[5],
			'update_notes' => $row[6],
		);
	}

	return $quotes;
}

// Take a string to be used for a LIKE parameter. Escape all of the
// metacharacters: %, _, and the escape character itself, \.
function _db_escape_like_parameter($s)
{
	$s = str_replace('\\', '\\\\', $s);
	$s = str_replace('_', '\\_', $s);
	$s = str_replace('%', '\\%', $s);

	return $s;
}
