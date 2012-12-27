<?php

// load
require('../src/CSV.php');

// read
$csv = new Coseva\CSV('example1.csv');

// trucate text in 1s column
$csv->filter(function($row) {
	unset($row[0]);
	return $row;
});

// Capitalize first letter of 1st column
$csv->filter(function($col) {
    return $col . '-first column';
}, 0);

// parse
$csv->parse();

// disco
echo $csv->toTable();