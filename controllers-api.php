<?php

// Take request body with JSON payload and add a quote.
//
// The JSON must have properties:
// - added_by
// - title
// - quote
//
// Output JSON. On success we include the quote as it was added.
function _api_add_quote()
{
	// Retrieve and parse JSON payload.

	$json = file_get_contents('php://input');
	if (null === $json) {
		_send_json_response(400, array('errors' => 'No request body found.'));
		return;
	}

	$payload = json_decode($json, true);
	if (null === $payload) {
		_send_json_response(400, array('errors' => 'Unable to parse request body as JSON.'));
		return;
	}


	// Pull out parameters.

	$added_by = '';
	if (array_key_exists('added_by', $payload) && is_string($payload['added_by'])) {
		$added_by = trim($payload['added_by']);
	}

	$title = '';
	if (array_key_exists('title', $payload) && is_string($payload['title'])) {
		$title = trim($payload['title']);
	}

	$quote = '';
	if (array_key_exists('quote', $payload) && is_string($payload['quote'])) {
		$quote = trim($payload['quote']);
	}

	// TODO(horgh): Support quote_image
	$quote_image = '';


	// Validate them.

	$errors = array();

	if (strlen($added_by) === 0) {
		$errors[] = 'You must provide your name.';
	}

	if (strlen($title) === 0) {
		$errors[] = 'You must provide a title.';
	}

	if (strlen($quote) === 0) {
		$errors[] = 'You must provide a quote';
	} else {
		$quote = _clean_quote($quote);
		if (false === $quote) {
			$errors[] = 'Unable to clean the quote.';
		}
	}

	if (count($errors) !== 0) {
		_send_json_response(400, array('errors' => $errors));
		return;
	}


	// Add it.

	$record = _add_quote($quote, $added_by, $title, $quote_image);
	if (false === $record) {
		_send_json_response(400, array('errors' => 'Failed to add the quote to the database.'));
		return;
	}

	_send_json_response(200, array('quote' => $record));
	_notify_to_irc("$added_by added a quote");
}

function _api_request_invalid_version()
{
	_send_json_response(404, array("errors" => array("Invalid API version.")));
}

function _api_request_invalid()
{
	_send_json_response(404, array("errors" => array("Invalid request.")));
}
