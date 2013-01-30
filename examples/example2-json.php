<?php

// load
require '../src/Coseva/CSV.php';

// read
$csv = new Coseva\CSV('example1.csv');

// parse
$csv->parse();

// disco
echo $csv->toJSON();