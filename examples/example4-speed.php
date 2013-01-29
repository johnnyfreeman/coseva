<?php
/**
 * Example parse times for Coseva CSV with instances.
 *
 * As a testing file, we used http://openurl.ac.uk/data/L2_2012-01.zip so we
 * could actually test against a proper data set.
 *
 * @package Coseva
 * @subpackage Examples
 */

require_once '../src/Coseva/CSV.php';
use \Coseva\CSV;

// We use a 103.2 MB CSV file for testing.
$testFile = '/tmp/L2_2012-01.csv';

/**
 * Apply a basic filter to each row.
 *
 * @param array $row the current row
 * @return array $row the filtered row
 */
$testFilter = function(array $row) {
  // Cast each cell as an integer or float.
  foreach ($row as &$cell) $cell += 0;

  return $row;
};

// Get an instance of CSV.
$csv = CSV::getInstance($testFile);

// Get a duplicate reference.
$dupe = CSV::getInstance($testFile);

// Add a filter.
$csv->filter($testFilter);

// Parse the CSV for the first time. This should open the file and apply filters.
$start = microtime(true);
$csv->parse();
$end = microtime(true);

echo 'Round #1: Parsing took '
  . number_format(($end - $start) * 1000, 2) . ' ms' . PHP_EOL;

// Add the filter again, since parse will flush the filters.
$dupe->filter($testFilter);

// Parse it again. We use $dupe for this, but it's the same instance as $csv.
// It shouldn't have to parse the file again, but will iterate over each row.
$start = microtime(true);
$dupe->parse();
$end = microtime(true);

echo 'Round #2: Parsing took '
  . number_format(($end - $start) * 1000, 2) . ' ms' . PHP_EOL;

// Parse it again. This time, no filters applied.
$start = microtime(true);
$dupe->parse();
$end = microtime(true);

echo 'Round #3: Parsing took '
  . number_format(($end - $start) * 1000, 2) . ' ms' . PHP_EOL;


// Test results on development workstation:
// Round #1: Parsing took 18,095.08 ms
// Round #2: Parsing took 180.32 ms
// Round #3: Parsing took 0.01 ms