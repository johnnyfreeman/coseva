<?php

// load
require('../src/CSV.php');

// read
$csv = new CSV('example1.csv');

// parse
$csv->parse();

// disco
foreach ($csv->toArray() as $row) {
    // persist row to datastore or something
}