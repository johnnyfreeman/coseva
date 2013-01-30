<?php
/**
 * Table example
 *
 * @package Coseva
 * @subpackage Examples
 */

require_once '../src/Coseva/CSV.php';
use \Coseva\CSV;

// Get an instance of CSV.
echo CSV::getInstance('example1.csv');
