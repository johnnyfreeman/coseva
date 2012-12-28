	<?php

	// load
	require('../src/CSV.php');

	// read
	$csv = new Coseva\CSV('example1.csv');

	$csv->filter('array_shift');

	// parse
	$csv->parse();

	// disco
	echo $csv->toJSON();