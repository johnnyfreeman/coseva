![Coseva](https://fbb955dbd2c46c6e3194-d04b1cd5219d2087606844a09815488f.ssl.cf2.rackcdn.com/logos/coseva.png "Coseva")

A classy object-oriented alternative for parsing CSV files with [PHP](http://www.php.net/).

![Screenshot](https://fbb955dbd2c46c6e3194-d04b1cd5219d2087606844a09815488f.ssl.cf2.rackcdn.com/coseva-screeny.png)

This (above) is an example of how to use Coseva in the simplest form. However, it is much more capable than this. :)

# What is it?

Coseva (pronounced co&bull;see&bull;vah) is a class for converting a .csv file into a PHP [Array](http://php.net/manual/en/language.types.array.php). It also has the capability of allowing you to register filters on rows and columns during the parsing stage.

# Installation

The recommended way to install Coseva is through [composer](http://getcomposer.org/). Just create a composer.json file and run the php composer.phar install command to install it:

	{
	    "minimum-stability": "dev",
	    "require": {
	        "johnnyfreeman/coseva": "*"
	    }
	}

Alternatively, you can download the [coseva.zip](https://github.com/johnnyfreeman/coseva/zipball/master) file and extract it.

# Getting Started

There are a few things you should know before diving in. 

The first thing, is that Coseva doesn't do anything other than parse a csv, and give you the results; no querying a database, no jumping on one foot, etc. That's left up to you. 

The second thing is the order in which filters are run. The parser loops through the csv file, line by line, from top to bottom. At each line it runs all <del>row filters first and then all the column filters</del> <ins>filters in the same order they were registered in</ins>.

# Examples

	$csv = new CSV('path/to/file.csv');

    // parse first column as date
	$csv->filter(function(value) {
	    return (new DateTime(value))->format('Y-m-d H:i:s');
	}, 0);

	// split column five at every colon and serialize
	$csv->filter(function($value) {
	    return serialize(explode(':', $value));
	}, 4);
    
    $csv->parse();

# API

### __construct( $filename, $open_mode = 'r', $use_include_path = FALSE )

To read a csv file, just pass the path to the .csv file to the `CSV` constructor.
    
###### Parameters

<table>
    <thead>
	    <tr>
	        <th>name</th>
	        <th>type</th>
	        <th>description</th>
	    </tr>
	</thead>
	<tbody>
	    <tr>
	        <th>$filename</th>
	        <td><a href="http://www.php.net/manual/en/language.types.string.php">String</a></td>
	        <td>...</td>
	    </tr>
        <tr>
	        <th>$open_mode</th>
	        <td><a href="http://www.php.net/manual/en/language.types.string.php">String</a></td>
	        <td>...</td>
	    </tr>
        <tr>
	        <th>$use_include_path</th>
	        <td><a href="http://www.php.net/manual/en/language.types.boolean.php">Boolean</a></td>
	        <td>...</td>
	    </tr>
	</tbody>
</table>

###### Returns

Returns object id.

###### Example

    $csv = new CSV('path/to/file.csv');

### filter( $callable, $column = null)

This method allows you to run a filter on a particular column of every row.

###### Parameters

<table>
	<thead>
	    <tr>
	        <th>name</th>
	        <th>type</th>
	        <th>description</th>
	    </tr>
	</thead>
	<tbody>
	    <tr>
	        <th>$callable</th>
	        <td><a href="http://www.php.net/manual/en/language.types.callable.php">Callable</a></td>
	        <td>Callable receives either the current row (as an array) or the current column (as a string) as the first parameter. The callable must return the new filtered row or column.</td>
	    </tr>
	    <tr>
	        <th>$column</th>
	        <td><a href="http://www.php.net/manual/en/language.types.integer.php">Integer</a></td>
	        <td>Optional: Zero-based column number. If this parameter is preset the $callable will recieve the contents of the current column (as a string), and will receive the entire (array based) row otherwise.</td>
	    </tr>
	</tbody>
</table>

###### Returns

Returns `NULL`

###### Example

	// split column four at every colon and serialize
	$csv->filter(function($column4) {
	    return serialize(explode(':', $column4));
	}, 3);

	// remove the first column from the results
	$csv->filter(function($row) {
		unset($row[0]);
	    return $row;
	});

### parse( $offset = 0 )

This method...

###### Parameters

<table>
	<thead>
	    <tr>
	        <th>name</th>
	        <th>type</th>
	        <th>description</th>
	    </tr>
	</thead>
	<tbody>
	    <tr>
	        <th>$offset</th>
	        <td><a href="http://www.php.net/manual/en/language.types.integer.php">Integer</a></td>
	        <td>...</td>
	    </tr>
	</tbody>
</table>

###### Returns

Returns `NULL`

###### Example

	// parse csv while executing any filters that may have been registered.
	$csv->parse();

### toArray()

This method returns the parsed csv as a native PHP array.

###### Returns

Returns <a href="http://www.php.net/manual/en/language.types.array.php">Array</a>

###### Example

	foreach($csv->toArray() as $row) {
		// persist each row to your datastore
	}

### toTable()

This is a great way to display the csv to you during the development process for debugging purposes.

###### Returns

Returns `<a href="http://www.php.net/manual/en/language.types.string.php">String</a>`

###### Example

	echo $csv->toTable();