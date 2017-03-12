<?php
/*
 * Interface for adding/viewing quotes
 *
 * See also irssi-scripts/sqlquote.pl. That script defines the schema we use
 * here, and is another interface for the quotes.
 */

require_once('config.php');
require_once('util.php');
require_once('db.php');
require_once('controllers.php');

main();

// Request handler entry.
function main()
{
	$action = array_key_exists('action', $_GET) ? $_GET['action'] : '';
	$action = array_key_exists('action', $_POST) ? $_POST['action'] : $action;

	switch ($action) {
		case '':
			_request_view_add_quote();
			break;

		case 'add_quote':
			_request_add_quote();
			break;

		case 'show-top-adders':
			_request_get_show_top_adders();
			break;

		case 'quote-stats':
			_request_get_quote_stats();
			break;

		case 'popular-quotes':
			_request_get_popular_quotes();
			break;

		case 'missing-adder':
			_request_get_missing_adder();
			break;

		case 'missing-date':
			_request_get_missing_date();
			break;

		case 'browse-quotes':
			_request_get_browse_quotes();
			break;

		case 'random-quotes':
			_request_get_random_quotes();
			break;

		case 'view-quote':
			_request_get_quote();
			break;

		case 'view-edit-quote':
			_request_view_edit_quote();
			break;

		case 'edit-quote':
			_request_edit_quote();
			break;

		case 'latest-quotes':
			_request_latest_quotes();
			break;

		case 'latest-quotes-by-id':
			_request_latest_quotes_by_id();
			break;

		case 'download':
			_request_download();
			break;

		case 'search':
			_request_search();
			break;

		default:
			_request_invalid();
			break;
	}
}
