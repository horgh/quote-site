<?php
require_once('config.php');
require_once('util.php');
require_once('db.php');
require_once('controllers-api.php');

main();

function main()
{
	// Version. URL parameter.
	if (array_key_exists('version', $_GET) && is_string($_GET['version'])) {
		$version = trim($_GET['version']);
	}

	if ($version !== '1') {
		_api_request_invalid_version();
		return;
	}

	// Object. URL parameter.
	$object = '';
	if (array_key_exists('object', $_GET) && is_string($_GET['object'])) {
		$object = trim($_GET['object']);
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$version = '';
		// POST quote. Add a quote.
		if ('quote' === $object) {
			_api_add_quote();
			return;
		}

		_api_request_invalid();
		return;
	}

	_api_request_invalid();
}
