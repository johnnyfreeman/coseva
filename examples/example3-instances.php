<?php
/**
 * Example of using Coseva CSV with instances.
 *
 * @package Coseva
 * @subpackage Examples
 */

require_once '../src/Coseva/CSV.php';
use \Coseva\CSV;

// Get an instance of CSV.
$csv = CSV::getInstance('example1.csv');

// Get a duplicate reference.
$dupe = CSV::getInstance('example1.csv');

// Add a filter.
$csv->filter(
  /**
   * Apply a basic filter to each row.
   *
   * @param array $row the current row
   * @return array $row the filtered row
   */
  function(array $row) {
    // Cast each cell as an integer.
    foreach ($row as &$cell) $cell += 0;

    return $row;
  }

);

// Parse the CSV.
$dupe->parse();

// Output the CSV as JSON.
$json = $csv->toJSON();

echo $json; // [[1,2,3,4],[1,2,3,4]]

// No matter in which scope you currently are, you can always fetch a previous
// instance and use currently parsed data. This allows for data to be parsed
// only once and to be used all over your code base.
