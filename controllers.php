<?php
// Try to add a quote.
//
// We ensure we have our parameters.
//
// We check if they've already confirmed the quote.
// If so, add it.
//
// If not, clean it up, and ask them to confirm.
//
// This function will always redirect us back to the add quote screen.
//
// If we do not add to the database yet (either requiring confirmation or due
// to an error), then we put everything into session for simplicity (as opposed
// to having some in query and some not). The quote must be in the session as
// it is likely too long for a reasonable query string. However we always reach
// this function from submitting the form, so our input will come from the
// request body.
function _request_add_quote()
{
	// Pull out parameters.

	$added_by = '';
	if (array_key_exists('added_by', $_POST) && is_string($_POST['added_by'])) {
		$added_by = trim($_POST['added_by']);
	}

	$quote = '';
	if (array_key_exists('quote', $_POST) && is_string($_POST['quote'])) {
		// Don't bother trimming it. We clean it up more extensively later.
		$quote = $_POST['quote'];
	}

	$quote_image = '';
	if (array_key_exists('quote_image', $_FILES) &&
		$_FILES['quote_image']['size'] > 0) {
		$quote_image = _get_image_upload();
		if (false === $quote_image) {
			_save_in_session('added_by', $added_by);
			_save_in_session('quote', $quote);
			_redirect('index.php', array());
			return;
		}
	} else if (array_key_exists('quote_image', $_POST) &&
		is_string($_POST['quote_image'])) {
		$quote_image = $_POST['quote_image'];
	}


	// Validate them.

	if (strlen($quote) === 0) {
		_add_flash_error("No quote given.");
		_save_in_session('added_by', $added_by);
		_save_in_session('quote_image', $quote_image);
		_redirect('index.php', array());
		return;
	}

	if (strlen($added_by) === 0) {
		_add_flash_error("Please enter your name.");
		_save_in_session('quote', $quote);
		_save_in_session('quote_image', $quote_image);
		_redirect('index.php', array());
		return;
	}

	if (strlen($quote_image) > 0 && !file_exists($quote_image)) {
		_add_flash_error("Image not found.");
		_save_in_session('added_by', $added_by);
		_save_in_session('quote', $quote);
		_redirect('index.php', array());
		return;
	}


	// If we've confirmed to add the quote as is (checkbox), try to do so.

	if (array_key_exists('confirm_quote', $_POST) &&
		$_POST['confirm_quote'] === 'on') {
		if (!_add_quote($quote, $added_by, $quote_image)) {
			_add_flash_error("Failure adding the quote to the database.");
			_save_in_session('added_by', $added_by);
			_save_in_session('quote', $quote);
			_save_in_session('quote_image', $quote_image);
			_redirect('index.php', array());
			return;
		}

		_add_flash_success("Added the quote to the database.");
		_notify_to_irc("$added_by added a quote");
		_redirect('index.php', array());
		return;
	}


	// We're not saving the quote yet. Clean it up a bit and re-display.

	$quote_clean = _clean_quote($quote);
	if (false === $quote_clean) {
		_add_flash_error("Failure cleaning up the quote.");
		_save_in_session('added_by', $added_by);
		_save_in_session('quote', $quote);
		_save_in_session('quote_image', $quote_image);
		_redirect('index.php', array());
		return;
	}

	_add_flash_success("Please confirm you want to add the quote as it now appears.");
	_save_in_session('added_by', $added_by);
	_save_in_session('quote', $quote_clean);
	_save_in_session('quote_image', $quote_image);
	_redirect('index.php', array());
}

function _request_get_show_top_adders()
{
	$adders_all_time = _get_top_adders_all_time();
	if (is_string($adders_all_time)) {
		echo $adders_all_time;
		return;
	}

	$adders_6mo = _get_top_adders_past_6_months();
	if (is_string($adders_6mo)) {
		echo $adders_6mo;
		return;
	}

	_show_template('view_adders', array(
		'page_title'      => 'Top quote adders',
		'adders'          => $adders_all_time,
		'adders_6_months' => $adders_6mo,
	));
}

function _request_get_quote_stats()
{
	$month_counts = _get_quotes_by_months();
	if (is_string($month_counts)) {
		echo $month_counts;
		return;
	}

	$month_counts_json = json_encode($month_counts);
	if ($month_counts_json === false) {
		echo "Unable to create month counts json";
		return;
	}

	_show_template('view_stats', array(
		'page_title'        => 'Quote stats',
		'month_counts_json' => $month_counts_json,
	));
}

function _request_get_popular_quotes()
{
	$page = 1;
	if (array_key_exists('page', $_GET) &&
		is_string($_GET['page']) &&
		is_numeric($_GET['page']))
	{
		$page = intval($_GET['page']);
	}

	$page_size = 20;

	$popular_quotes = _get_popular_quotes($page, $page_size);
	if (!is_array($popular_quotes)) {
		echo "Unable to look up popular quotes.";
		return;
	}

	$count_popular_quotes = _count_popular_quotes();
	if (!is_int($count_popular_quotes)) {
		echo "Unable to look up count of popular quotes.";
		return;
	}

	$total_pages = intval(ceil($count_popular_quotes/$page_size));

	_show_template(
		'view_popular_quotes',
		array(
			'page_title'   => 'Popular quotes',
			'page'         => $page,
			'page_size'    => $page_size,
			'quotes'       => $popular_quotes,
			'count_quotes' => $count_popular_quotes,
			'total_pages'  => $total_pages,
		)
	);
}

function _request_get_missing_adder()
{
	$quotes = _get_quotes_missing_adder();
	if (is_string($quotes)) {
		echo $quotes;
		return;
	}

	$missing_count = count($quotes);

	$total_quotes = _count_quotes();
	if (!is_int($total_quotes)) {
		echo $total_quotes;
		return;
	}

	$total_missing = _count_quotes_missing_adder();
	if (!is_int($total_missing)) {
		echo $total_missing;
		return;
	}

	_show_template('view_missing', array(
		'page_title'    => 'Quotes missing adder',
		'quotes'        => $quotes,
		'total_quotes'  => $total_quotes,
		'total_missing' => $total_missing,
	));
}

function _request_get_missing_date()
{
	$quotes = _get_quotes_missing_date();
	if (is_string($quotes)) {
		echo $quotes;
		return;
	}

	$missing_count = count($quotes);

	$total_quotes = _count_quotes();
	if (!is_int($total_quotes)) {
		echo $total_quotes;
		return;
	}

	$total_missing = _count_quotes_missing_date();
	if (!is_int($total_missing)) {
		echo $total_missing;
		return;
	}

	_show_template('view_missing', array(
		'page_title'    => 'Quotes missing date',
		'quotes'        => $quotes,
		'total_quotes'  => $total_quotes,
		'total_missing' => $total_missing,
	));
}

function _request_get_browse_quotes()
{
	$page = 1;
	if (array_key_exists('page', $_GET) && is_string($_GET['page']) &&
		is_numeric($_GET['page'])) {
		$page = intval($_GET['page']);
	}

	$page_size = 20;
	if (array_key_exists('page-size', $_GET) && is_string($_GET['page-size']) &&
		is_numeric($_GET['page-size'])) {
		$page_size = intval($_GET['page-size']);
	}

	$quotes = _get_quotes($page, $page_size);
	if (false === $quotes) {
		echo "Unable to retrieve quotes.";
		return;
	}

	$count_quotes = _count_quotes();
	if (!is_int($count_quotes)) {
		echo "Unable to retrieve quote count.";
		return;
	}

	$pages = intval(ceil($count_quotes/$page_size));
	if ($page > $pages) {
		echo "Invalid page.";
		return;
	}

	$next_page_url = 'index.php?action=browse-quotes&page=' . ($page+1);
	$prev_page_url = 'index.php?action=browse-quotes&page=' . ($page-1);

	_show_template('view_browse', array(
		'page_title'    => 'Quotes',
		'quotes'        => $quotes,
		'count_quotes'  => $count_quotes,
		'page'          => $page,
		'pages'         => $pages,
		'next_page_url' => $next_page_url,
		'prev_page_url' => $prev_page_url,
	));
}

function _request_get_random_quotes()
{
	$quotes = _get_random_quotes();
	if (!is_array($quotes)) {
		return;
	}

	_show_template('view_random', array(
		'page_title' => 'Random quotes',
		'quotes'     => $quotes,
	));
}

function _request_get_quote()
{
	if (!array_key_exists('id', $_GET) || !is_string($_GET['id']) ||
		!is_numeric($_GET['id'])) {
		echo "You must provide an ID.";
		return;
	}

	$quote = _get_quote_by_id(intval($_GET['id']));
	if (!is_array($quote)) {
		return;
	}

	_show_template('view_single', array(
		'page_title' => 'Quote #' . $quote['id'],
		'quote'      => $quote,
	));
}

function _request_latest_quotes()
{
	$quotes = _get_latest_quotes();
	if (!is_array($quotes)) {
		echo "Unable to look up quotes: $quotes";
		return;
	}

	_show_template('view_latest', array(
		'page_title' => 'Latest quotes',
		'quotes'     => $quotes,
	));
}

function _request_latest_quotes_by_id()
{
	$quotes = _get_latest_quotes_by_id();
	if (!is_array($quotes)) {
		echo "Unable to look up quotes: $quotes";
		return;
	}

	_show_template('view_latest', array(
		'page_title' => 'Latest quotes',
		'quotes'     => $quotes,
	));
}

// Show a page where we can download the site and its database.
// We display the last time these were modified.
function _request_download()
{
	$db_file = 'files/quote.pgsql.sql.bin';

	$db_sbuf = @stat($db_file);
	if (false === $db_sbuf) {
		echo "Unable to stat file $db_file";
		return;
	}

	$db_mtime = strftime("%F %T %Z", $db_sbuf[9]);

	$images_file = 'files/images.tar.gz';

	$images_sbuf = @stat($images_file);
	if (false === $images_sbuf) {
		echo "Unable to stat $images_file";
		return;
	}

	$images_mtime = strftime("%F %T %Z", $images_sbuf[9]);

	_show_template('view_download', array(
		'page_title'   => 'Download',
		'db_file'      => $db_file,
		'db_mtime'     => $db_mtime,
		'images_file'  => $images_file,
		'images_mtime' => $images_mtime,
	));
}

// Search quotes.
//
// Right now we can search only in the quote body.
function _request_search()
{
	$query = '';
	if (array_key_exists('query', $_GET) && is_string($_GET['query'])) {
		$query = trim($_GET['query']);
	}

	// May have pagination information.
	$page = 1;
	if (array_key_exists('page', $_GET) && is_numeric($_GET['page'])) {
		$page = intval($_GET['page']);
	}

	if (strlen($query) === 0) {
		_show_template('view_search', array(
			'page_title'  => 'Search',
			'errors'      => array('You must provide a search query.'),
			'quotes'      => array(),
			'count'       => 0,
			'query'       => $query,
			'page'        => $page,
			'total_pages' => 1,
		));
		return;
	}


	// We're using the postgresql LIKE function. It has these metacharacters
	// we need to escape: _, %

	$db_query = str_replace('_', '\\_', $query);
	$db_query = str_replace('%', '\\%', $db_query);

	// Support glob style.
	$db_query = str_replace('*', '%', $db_query);

	// *query*
	$db_query = '%' . $db_query . '%';

	$page_size = 20;

	$quotes = _search_quotes($db_query, $page, $page_size);
	$count = _count_matching_quotes($db_query);

	if (false === $quotes || false === $count) {
		_show_template('view_search', array(
			'page_title'  => 'Search',
			'errors'      => array('There was a problem performing the search.'),
			'quotes'      => array(),
			'count'       => 0,
			'query'       => $query,
			'page'        => $page,
			'total_pages' => 1,
		));
		return;
	}

	$total_pages = ceil($count/$page_size);

	$prev_page_url= 'index.php?action=search&query=' . rawurlencode($query)
		. '&page=' . ($page-1);
	$next_page_url = 'index.php?action=search&query=' . rawurlencode($query)
		. '&page=' . ($page+1);

	_show_template('view_search', array(
		'page_title'    => 'Search',
		'errors'        => array(),
		'quotes'        => $quotes,
		'count'         => $count,
		'query'         => $query,
		'page'          => $page,
		'total_pages'   => $total_pages,
		'prev_page_url' => $prev_page_url,
		'next_page_url' => $next_page_url,
	));
}

// Show page with add quote form.
//
// We may reach here from being redirected to either confirm the quote or on
// error.
//
// All parameters get passed along to us (if previously set) in the session.
function _request_view_add_quote()
{
	$successes = _get_success_flashes();
	$errors = _get_error_flashes();

	$added_by = '';
	if (isset($_SESSION['added_by']) && is_string($_SESSION['added_by'])) {
		$added_by = $_SESSION['added_by'];
		unset($_SESSION['added_by']);
	}

	$quote = '';
	if (isset($_SESSION['quote']) && is_string($_SESSION['quote'])) {
		$quote = $_SESSION['quote'];
		unset($_SESSION['quote']);
	}

	$quote_image = '';
	if (isset($_SESSION['quote_image']) && is_string($_SESSION['quote_image'])) {
		$quote_image = $_SESSION['quote_image'];
		unset($_SESSION['quote_image']);
	}

	_show_template('view_index', array(
		'page_title'  => 'Add quote',
		'successes'   => $successes,
		'errors'      => $errors,
		'quote'       => $quote,
		'added_by'    => $added_by,
		'quote_image' => $quote_image,
	));
}

function _request_invalid()
{
	echo "Invalid request.";
}
