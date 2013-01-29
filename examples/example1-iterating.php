<?php

// load
require('../src/CSV.php');

// read
$csv = new Coseva\CSV('example1.csv');

// parse
$csv->parse();

// disco
foreach ($csv as $row) {
	// persist row to datastore or something
}