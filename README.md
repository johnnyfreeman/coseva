![Coseva](https://fbb955dbd2c46c6e3194-d04b1cd5219d2087606844a09815488f.ssl.cf2.rackcdn.com/logos/coseva.png "Coseva")

A classy object-oriented alternative for parsing CSV files with [PHP](http://www.php.net/).

![Screenshot](https://fbb955dbd2c46c6e3194-d04b1cd5219d2087606844a09815488f.ssl.cf2.rackcdn.com/coseva-screeny.png)

This (above) is an example of how to use Coseva in the simplest form. However, it is much more capable than this. :)

# What is it?

Coseva (pronounced co&bull;see&bull;vah) is a class for converting a .csv file into a PHP [Array](http://php.net/manual/en/language.types.array.php). It also has the capability of allowing you to register filters on rows and columns during the parsing stage.

# Getting Started

There are a few principles you should know before diving right in. The first is that Coseva doesn't do anything other than parse a csv, and give you the results; no querying a database, no jumping on one foot, etc. That's left to you. The second is the order in which filters are ran. This is very important. The parser loops through the file line by line, from top to bottom. At each line it runs all row filters first and then all the column filters.

# Installation

The recommended way to install Coseva is through [composer](http://getcomposer.org/). Just create a composer.json file and run the php composer.phar install command to install it:

	{
	    "minimum-stability": "dev",
	    "require": {
	        "johnnyfreeman/coseva": "*"
	    }
	}

Alternatively, you can download the [coseva.zip](https://github.com/johnnyfreeman/coseva/zipball/master) file and extract it.

# Examples

	$csv = new CSV('path/to/file.csv');

    // parse first column as date
	$csv->filterColumn(0, function(value) {
	    return (new DateTime(value))->format('Y-m-d H:i:s');
	});

	// split column five at every colon and serialize
	$csv->filterColumn(4, function($value) {
	    return serialize(explode(':', $value));
	});
    
    $csv->parse(1);

# API

### __construct( filename, open_mode = 'r', $use_include_path = FALSE )

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
	        <th>filename</th>
	        <td><a href="http://www.php.net/manual/en/language.types.string.php">String</a></td>
	        <td>...</td>
	    </tr>
        <tr>
	        <th>open_mode</th>
	        <td><a href="http://www.php.net/manual/en/language.types.string.php">String</a></td>
	        <td>...</td>
	    </tr>
        <tr>
	        <th>use_include_path</th>
	        <td><a href="http://www.php.net/manual/en/language.types.boolean.php">Boolean</a></td>
	        <td>...</td>
	    </tr>
	</tbody>
</table>

###### Returns

Returns object id.

###### Example

    $csv = new CSV('path/to/file.csv');

### filterColumn( csv_column, callable)

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
	        <th>csv_column</th>
	        <td><a href="http://www.php.net/manual/en/language.types.integer.php">Integer</a></td>
	        <td>Zero-based column number.</td>
	    </tr>
	    <tr>
	        <th>callable</th>
	        <td><a href="http://www.php.net/manual/en/language.types.callable.php">Callable</a></td>
	        <td>Callable receives the current value as the first parameter. Callable must return the new value.</td>
	    </tr>
	</tbody>
</table>

###### Returns

Returns `TRUE` if *callable* is callable, `FALSE` otherwise.

###### Example

	// split column four at every colon and serialize
	$csv->filterColumn(4, function($value) {
	    return serialize(explode(':', $value));
	});