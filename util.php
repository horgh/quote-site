<?php
//! write a message to stdout (html)
/*!
 * @param string $level
 * @param string $msg
 *
 * @return void
 */
function _log_message($level, $msg)
{
	if (!DEBUG && $level === 'debug') {
		return;
	}

	// get backtrace to find calling function name
	$backtrace_array = debug_backtrace();
	if (count($backtrace_array) < 2) {
		$function = 'unknown function';
	} else {
		$function = $backtrace_array[1]['function'];
	}

	error_log("Quote site: $function: $msg");
}

//! clean up a quote
/*!
 * @param string $quote
 *
 * @return mixed string quote or bool false
 *
 * we try to trim timestamps from the quote
 */
function _clean_quote($quote)
{
	if (strlen($quote) === 0) {
		_log_message('error', "invalid parameter");
		return false;
	}

	// possible patterns for timestamps. must be at beginning of the line.
	// these will be stripped off the beginning of each line.
	$timestamps = array(
		'/^\d{2}\/\d{2} \d{2}:\d{2}/',
		'/^ *[\[(]? *\d{1,2}:\d{2}(?::\d{2})? *[\])]?/',
	);

	$lines = preg_split('/\r?\n/', $quote);
	$cleanedLines = array();
	foreach ($lines as $line) {
		_log_message('debug', "looking at line: $line");

		// trim timestamps.
		foreach ($timestamps as $timestamp) {
			if (($line = preg_replace($timestamp, '', $line)) === null) {
				_log_message('error', "failure replacing timestamp. pattern: $timestamp");
				return false;
			}
		}

		// trim any start/end whitespace.
		$line = trim($line);

		if (strlen($line) === 0) {
			continue;
		}

		$cleanedLines[] = $line;
	}

	$quote = implode("\n", $cleanedLines);
	_log_message('debug', "cleaned lines: " . print_r($cleanedLines, 1));
	return $quote;
}

// Send a message to IRC.
function _notify_to_irc($message)
{
	if (!is_string($message) || strlen($message) === 0) {
		return false;
	}

	global $NOTIFY_NICK, $NOTIFY_HOST, $NOTIFY_CHAN, $NOTIFY_BIN;

	$cmd = escapeshellarg($NOTIFY_BIN)
		. ' -channel ' . escapeshellarg($NOTIFY_CHAN)
		. ' -host ' . escapeshellarg($NOTIFY_HOST)
		. ' -message ' . escapeshellarg($message)
		. ' -nick ' . escapeshellarg($NOTIFY_NICK);

	$output = `$cmd`;
}

//! render a template
/*!
 * @param string $name template name (without .php)
 * @param array $params associative array of params. will be included into
 * the local scope.
 *
 * @return void
 *
 * SIDE EFFECT: print to stdout
 */
function _show_template($name, array $params)
{
	$s = _get_template($name, $params);
	if ($s === false) {
		return;
	}

	echo $s;
}

function _get_template($name, array $params)
{
	if (strlen($name) === 0) {
		_log_message('error', 'invalid parameter');
		return false;
	}

	// Bring params into the local scope.
	foreach ($params as $key => $value) {
		$$key = $value;
	}

	$name .= '.php';
	if (!file_exists($name) || !is_readable($name)) {
		_log_message('error', "template not found or not readable: $name");
		return false;
	}

	if (!ob_start()) {
		_log_message('error', "failure starting buffering");
		return false;
	}

	if ((include $name) === false) {
		_log_message('error', "failure including template: $name");
		ob_get_clean();
		return false;
	}

	if (($output = ob_get_clean()) === false) {
		_log_message('error', "failure ending buffering");
		return false;
	}
	return $output;
}

// Build HTML <link> tag for including a CSS file.
// We append a mtime query string to this file to cache bust.
function _include_css($filename)
{
	if (!isset($filename) || !is_string($filename) || strlen($filename) === 0) {
		return '';
	}

	$mtime = @filemtime($filename);
	if (false === $mtime) {
		return '';
	}

	$href = $filename . '?mtime=' . $mtime;

	$html = '<link href="' . htmlspecialchars($href) . '" rel="stylesheet">';

	return $html;
}

// Build HTML for a <script> tag.
// We append a mtime query string to this file to cache bust.
function _include_js($filename)
{
	if (!isset($filename) || !is_string($filename) || strlen($filename) === 0) {
		return '';
	}

	$mtime = @filemtime($filename);
	if (false === $mtime) {
		return '';
	}

	$href = $filename . '?mtime=' . $mtime;

	$html = '<script src="' . htmlspecialchars($href) . '"></script>';

	return $html;
}

// Redirect to a given URL and exist.
function _redirect($url, $params)
{
	if (!isset($url) || !is_string($url) || strlen($url) === 0) {
		echo "_redirect: missing URL";
		return;
	}

	$fullURL = $url;

	$first = true;
	foreach ($params as $name => $value) {
		if ($first) {
			$first = false;
			$fullURL .= '?';
		} else {
			$fullURL .= '&';
		}
		$fullURL .= rawurlencode($name) . '=' . rawurlencode($value);
	}

	header('Location: ' . $fullURL);
	exit;
}

// Add a flash error message.
function _add_flash_error($msg)
{
	if (!isset($msg) || !is_string($msg) || strlen($msg) === 0) {
		return;
	}

	_session_start();

	if (!array_key_exists('errors', $_SESSION)) {
		$_SESSION['errors'] = array();
	}

	$_SESSION['errors'][] = $msg;
}

// Add a flash success message.
function _add_flash_success($msg)
{
	if (!isset($msg) || !is_string($msg) || strlen($msg) === 0) {
		return;
	}

	_session_start();

	if (!array_key_exists('successes', $_SESSION)) {
		$_SESSION['successes'] = array();
	}

	$_SESSION['successes'][] = $msg;
}

// Get and clear flash error messages.
function _get_error_flashes()
{
	_session_start();

	if (!array_key_exists('errors', $_SESSION)) {
		return array();
	}

	$msgs = $_SESSION['errors'];
	unset($_SESSION['errors']);
	return $msgs;
}

// Get and clear flash success messages.
function _get_success_flashes()
{
	_session_start();

	if (!array_key_exists('successes', $_SESSION)) {
		return array();
	}

	$msgs = $_SESSION['successes'];
	unset($_SESSION['successes']);
	return $msgs;
}

// Save a value in the session.
function _save_in_session($key, $value)
{
	if (!isset($key) || !is_string($key) || strlen($key) === 0) {
		return;
	}

	if (!isset($value)) {
		return;
	}

	_session_start();

	$_SESSION[$key] = $value;
}

// Start a session if we haven't started one.
//
// Because calling session_start() when we have one will raise a warning.
function _session_start()
{
	$status = session_status();
	if (PHP_SESSION_ACTIVE === $status) {
		return;
	}

	session_start();
}

// We've been given an image upload to associate with a quote.
//
// Validate it and store it somewhere persistently. Return path to it. This path
// is HTTP accessible as well as a disk path.
function _get_image_upload()
{
	if (!array_key_exists('quote_image', $_FILES) ||
		!is_array($_FILES['quote_image'])) {
		_add_flash_error("No image provided.");
		return false;
	}

	$file = $_FILES['quote_image'];

	global $IMAGE_MAX_SIZE;
	if ($file['size'] > $IMAGE_MAX_SIZE) {
		_add_flash_error("Image is too large.");
		return false;
	}

	try {
		$image = new Imagick($file['tmp_name']);
	} catch (Exception $e) {
		_add_flash_error("Invalid image.");
		return false;
	}

	$suffix = '';
	switch ($image->getImageFormat()) {
	case 'PNG':
		$suffix = '.png';
		break;
	case 'JPEG':
	case 'JPG':
		$suffix = '.jpg';
		break;
	case 'GIF':
		$suffix = '.gif';
		break;
	default:
		_add_flash_error("Invalid image format. Please use PNG/JPG/GIF.");
		return false;
	}

	$id = uniqid('quote-', true);

	global $IMAGES_DIR;
	$dest_path = $IMAGES_DIR . '/' . $id . $suffix;
	if (file_exists($dest_path)) {
		_add_flash_error("Filename collision.");
		return false;
	}

	if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
		_add_flash_error("Unable to save image.");
		return false;
	}

	return $dest_path;
}
