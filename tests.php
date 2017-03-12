<?php
//
// Unit tests.
//

require_once(__DIR__ . '/db.php');

exit(main() ? 0 : 1);

function main()
{
	if (php_sapi_name() !== 'cli') {
		print "Run this from CLI\n";
		return false;
	}

	$failures = 0;

	if (!_test_db_escape_like_parameter()) {
		$failures++;
	}

	if ($failures === 0) {
		print "All tests completed successfully\n";
		return true;
	}

	print "FAILURE: Some tests failed!\n";
	return false;
}

function _test_db_escape_like_parameter()
{
	$tests = array(
		array(
      'input'  => 'abc',
      'output' => 'abc',
    ),
		array(
      'input'  => 'ab%_\\c',
      'output' => 'ab\\%\\_\\\\c',
    ),
		array(
      'input'  => '%_\\',
      'output' => '\\%\\_\\\\',
		),
		array(
      'input'  => '%_\\%_\\',
      'output' => '\\%\\_\\\\\\%\\_\\\\',
		),
	);

	$failures = 0;

	foreach ($tests as $test) {
		$output = _db_escape_like_parameter($test['input']);
		if ($output !== $test['output']) {
			printf("FAILURE: _db_escape_like_parameter(%s) = %s, wanted %s\n",
				$test['input'], $output, $test['output']);
			$failures++;
			continue;
		}
	}

	if ($failures === 0) {
		return true;
	}

	print "TEST FAILURE: _db_escape_like_parameter() $failures/" . count($tests) . " tests failed\n";
	return false;
}
