<?php

// converting CSV to JSON

// load
require('../src/CSV.php');

// read
$csv = new Coseva\CSV('example1.csv');

// parse
$csv->parse();

// disco
echo $csv->toJSON();