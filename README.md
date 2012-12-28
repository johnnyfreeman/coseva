![Coseva](http://coseva.s3.amazonaws.com/logo.png "Coseva")

A friendly, object-oriented library for parsing and filtering CSV files with [PHP](http://www.php.net/).

![Screenshot](http://coseva.s3.amazonaws.com/editor.png)

This (above) is an example of how to use Coseva in the simplest form. However, it is much more capable than this. :)

# What is it?

Coseva (pronounced co&bull;see&bull;vah) is an abstraction library for making .csv files easier to work with. But what makes it special is it allows you to have a [separation of concerns](http://en.wikipedia.org/wiki/Separation_of_concerns) in your data-filtering logic. This means that instead of having a bunch of spaghetti code wrapped up in a huge loop, you can easily encupsulate any task specific logic in it's own callback (Coseva calls this callback a "filter"), keeping your code maintainable and DRY.

# Installation

The recommended way to install Coseva is through [composer](http://getcomposer.org/). Just create a composer.json file and run the `composer install` command to install it:

	{
	    "minimum-stability": "dev",
	    "require": {
	        "johnnyfreeman/coseva": "*"
	    }
	}

Or, you can download the [coseva.zip](https://github.com/johnnyfreeman/coseva/zipball/master) file and extract it.

# Getting Started

There are a few things you should know before diving in. 

The first thing, is that Coseva doesn't do anything other than parse a csv, and give you the results; no querying a database, no jumping on one foot, etc. That's left up to you. 

The second thing is the order in which filters are run. The parser loops through the csv file, line by line, from top to bottom, and at each line it runs all filters in the same order they were registered in.

So if we were to register two filters like this:

    // trucate text in 1s column
	$csv->filter(function($row) {
		unset($row[0]);
		return $row;
	});

	// Capitalize first letter of 1st column
	$csv->filter(0, function($col) {
	    return ucfirst($col);
	});

First, the first column will be deleted from the array, then during execution of the second filter a "PHP Notice: Undefined offset" will be raised because the column no longer exists.

# Examples

	$csv = new CSV('path/to/file.csv');

    // parse first column as date
	$csv->filter(0, function($col1) {
	    return (new DateTime($col1))->format('Y-m-d H:i:s');
	});

	// split column five at every colon and serialize
	$csv->filter(4, function($col5) {
	    return serialize(explode(':', $col5));
	});
    
    $csv->parse();

# API

### __construct( $filename, $open_mode = 'r', $use_include_path = false )

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
	        <td>The file to read.</td>
	    </tr>
        <tr>
	        <th>$open_mode</th>
	        <td><a href="http://www.php.net/manual/en/language.types.string.php">String</a></td>
	        <td>The mode in which to open the file. See <a href="http://php.net/manual/en/function.fopen.php">fopen()</a> for a list of allowed modes.</td>
	    </tr>
        <tr>
	        <th>$use_include_path</th>
	        <td><a href="http://www.php.net/manual/en/language.types.boolean.php">Boolean</a></td>
	        <td>Whether to search in the <a href="http://php.net/manual/en/ini.core.php#ini.include-path">include_path</a> for filename.</td>
	    </tr>
	</tbody>
</table>

###### Returns

Returns object id.

###### Example

    $csv = new CSV('path/to/file.csv');

### filter( $column, $callable )

This method allows you to register any number of filters on your CSV content. But there are two ways you can utilize this method. 

The first method, you'll pass a column number and a callable, like so:
	
	// convert data in column 2 to a `number` if it is numeric
    $csv->filter(1, function($value) {
    	return is_numeric($value) ? (float) $value : $value;
	});

	// trim the whitespace around column 1
	$csv->filter(0, 'trim');

The second method, you'll pass only a callback, like so:

	// overwrite column three based on values from columns 1 and 2
	$csv->filter(function($row) {
		if ($row[0] == 'this' && $row[1] == 'that') {
			$row[2] = 'something';
		}

		return $row;
	});

	// completely remove the last column from the row
	$csv->filter('array_pop');

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
	        <th>$column</th>
	        <td><a href="http://www.php.net/manual/en/language.types.integer.php">Integer</a></td>
	        <td>Optional: Zero-based column number. If this parameter is present the $callable will recieve the contents of the current column (as a string), and will receive the entire (array based) row otherwise.</td>
	    </tr>
	    <tr>
	        <th>$callable</th>
	        <td><a href="http://www.php.net/manual/en/language.types.callable.php">Callable</a></td>
	        <td>Callable receives either the current row (as an array) or the current column (as a string) as the first parameter. The callable must return the new filtered row or column. Note: You can also use any native PHP functions that permit one parameter and return the new value, like <a href="http://us1.php.net/manual/en/function.trim.php">trim</a>, <a href="http://us1.php.net/manual/en/function.htmlspecialchars.php">htmlspecialchars</a>, <a href="http://us1.php.net/manual/en/function.urlencode.php">urlencode</a>, etc.</td>
	    </tr>
	</tbody>
</table>

###### Returns

Returns `NULL`

###### Example

	// split column four at every colon and serialize
	$csv->filter(3, function($column4) {
	    return serialize(explode(':', $column4));
	});

	// remove the first column from the results
	$csv->filter(function($row) {
		unset($row[0]);
	    return $row;
	});

### parse( $offset = 0 )

This method will convert the csv to an array and run all registered filters against it.

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
	        <td>Determines which row the parser will start on. Zero-based index.</td>
	    </tr>
	</tbody>
</table>

###### Returns

Returns `NULL`.

###### Example

	// parse csv while executing any filters that may have been registered.
	$csv->parse();

### toJSON()

Use this to get the entire CSV in JSON format.

###### Returns

Returns a JSON `String`.

###### Example

    // register filters
    // parse csv

    // to JSON
	echo $csv->toJSON();

### toTable()

This is a great way to display the filtered contents of the csv to you during the development process (for debugging purposes).

###### Returns

Returns an HTML `String`.

###### Example

    // register filters
    // parse csv

    // let's take a look
	echo $csv->toTable();

# Updates

Want to stay updated? Follow me on [Github](https://github.com/johnnyfreeman) and [Twitter](http://twitter.com/prsjohnny).