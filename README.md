![Coseva](http://coseva.s3.amazonaws.com/logo.png "Coseva")

A friendly, object-oriented library for parsing and filtering CSV files with [PHP](http://www.php.net/).

![Screenshot](http://coseva.s3.amazonaws.com/editor.png)

This (above) is an example of how to use Coseva in the simplest form. However, it is much more capable than this. :)

# What is it?

Coseva (pronounced co&bull;see&bull;vah) is an abstraction library for making `.csv` files easier to work with. But what makes it special is it allows you to have a clean [separation of concerns](http://en.wikipedia.org/wiki/Separation_of_concerns) in your data-filtering logic. This is one of the main points of Coseva, to give the developer the ability to keep unrelated logic separate from each other. In many cases, all data-filtering logic is contained in one big loop. This "spaghetti code" leads to a codebase that is increasingly difficult to maintain and update, and near impossible to read months later when you come back to it. But by breaking down your logic into smaller distinct chunks (Coseva calls these chunks "[filters](https://github.com/johnnyfreeman/coseva#filter-column-callable-)"), you can avoid many of the headaches that come with the everything-in-one-giant-loop approach.

# Installation

The recommended way to install Coseva is through [composer](http://getcomposer.org/). Just create a composer.json file and run the `composer install` command to install it:

```javascript
{
    "minimum-stability": "dev",
    "require": {
        "johnnyfreeman/coseva": "*"
    }
}
```

Or, you can download the [coseva.zip](https://github.com/johnnyfreeman/coseva/zipball/master) file and extract it.

# Getting Started

There are a few things you should know before diving in.

The first thing, is that Coseva doesn't do anything other than parse a csv, and give you the results; no querying a database, no jumping on one foot, etc. That's left up to you.

The second thing is the order in which filters are run. The parser loops through the csv file, line by line, from top to bottom, and at each line it runs all filters in the same order they were registered in.

So if we were to register two filters like this:

```php
<?php

// delete 1st column
$csv->filter(function($columns) {
	unset($columns[0]);
	return $columns;
});

// Capitalize first letter of 1st column (which used to be the 2nd column)
$csv->filter(0, function($column) {
    return ucfirst($column);
});
```

First, the first column will be deleted from the array, then during execution of the second filter a "PHP Notice: Undefined offset" will be raised because the column no longer exists.

# Example

```php
<?php

$csv = new CSV('path/to/file.csv');

// parse first column as date
$csv->filter(0, function($column1) {
    return (new DateTime($column1))->format('Y-m-d H:i:s');
});

// split column five at every colon and serialize
$csv->filter(4, function($column5) {
    return serialize(explode(':', $column5));
});

$csv->parse();
```

# API

### __construct( $filename, $open_mode = 'r', $use_include_path = false )

To instantiate the `CSV` object, just pass the path to the .csv file to the `CSV` constructor.

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

Returns CSV instance.

###### Example

```php
<?php

$csv = new CSV('path/to/file.csv');
```

### filter( $column, $callable[, mixed $argument1, ...] )

This method allows you to register any number of filters on your CSV content. But there are two ways you can utilize this method.

The first method, you'll pass a column number and a callable, like so:

```php
// convert data in column 2 to a `number` if it is numeric
$csv->filter(1, function($value) {
	return is_numeric($value) ? (float) $value : $value;
});

// trim the whitespace around column 1
$csv->filter(0, 'trim');

// Format a numeric column to always display 2 decimals.
$csv->filter(1, 'number_format', 2);
```

The second method, you'll pass only a callback, like so:

```php
// overwrite column three based on values from columns 1 and 2
$csv->filter(function($columns) {
	if ($columns[0] == 'this' && $columns[1] == 'that') {
		$columns[2] = 'something';
	}

	return $columns;
});

// reverse the order of all columns
$csv->filter('array_reverse');
```

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

Returns the `CSV` instance to allow [method chaining](http://en.wikipedia.org/wiki/Method_chaining).

###### Example

```php
<?php

// split column four at every colon and serialize
$csv->filter(3, function($column4) {
    return serialize(explode(':', $column4));
});

// remove the first column from the results
$csv->filter(function($columns) {
	unset($columns[0]);
    return $columns;
});
```

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

Returns the `CSV` instance to allow [method chaining](http://en.wikipedia.org/wiki/Method_chaining).

###### Example

```php
<?php

// parse csv while executing any filters that may have been registered.
$csv->parse();
```

### toJSON()

Use this to get the entire CSV in JSON format.

###### Returns

Returns a JSON `String`.

###### Example

```php
<?php

// to JSON
echo $csv->toJSON();
```

### toTable()

This is a great way to display the filtered contents of the csv to you during the development process (for debugging purposes).

###### Returns

Returns an HTML `String`.

###### Example

```php
<?php

// let's take a look
echo $csv->toTable();
```

### getInstance( $filename, $open_mode = 'r', $use_include_path = false )

We also allow for instances, preserving memory and maintaining reachability across scopes.

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
	        <td>A readable filename to reference the instances by. The filename will be resolved to the absolute path, following symlinks, to improve chances of finding a matching instance.</td>
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

###### Example

```php
<?php

use \Coseva\CSV;

// Create an instance of CSV.
$csv = CSV::getInstance('comma-separated-nonsense.csv');

// Fetch another one.
$dupe = CSV::getInstance('comma-separated-nonsense.csv');

// Parse the CSV.
$csv->parse();

// And display that parsed CSV as JSON.
echo $dupe->toJSON();

```

# Packaging / Standalone CSV parser

For convenience's sake, we created a packager so you can combine your script and CSV in one compressed and executable bundle. We make use of the Phar technology that has been available since 5.3.0.

The benefits are simple:

- Only one file to keep around
- Your data is compressed
- You keep all your scripting functionality alongside your CSV file
- No need for Coseva as an external library
- PHP 5.2 and above are able to execute it

###### Usage

```
NAME
			packager - Combine Coseva, your script and CSV

SYNOPSIS
			packager input.csv script.php [output.phar [alias.phar]]

DESCRIPTION
			input.csv
						The input file which holds the CSV data.

			script.php
						The PHP script which uses Coseva to do filtering and parsing.
						It can make use of the automatically defined constant SOURCE_FILE
						to succesfully target the CSV file for file operations.
						Also, including / requiring Coseva is no longer needed, as the
						package will do so when bootstrapping.

			output.phar [optional]
						The output location of the package.

			alias.phar [optional]
						The internal alias of the package. Really useful when wanting to
						target the package from within your script.
						E.g. "phar://alias.phar/input.csv"
```

###### Configuration

packager should be able to run as it is. On some machines, certain flags may have to be set.
No worries, though. Packager will automatically detect which of these flags need to be set and where you can find them.

On most machines, setting `phar.readonly = Off` will suffice. For machines running suhosin, there is an instruction for
adding "phar" to the executor whitelist of suhosin when needed.

# Updates

Want to stay updated? Follow me on [Github](https://github.com/johnnyfreeman) and [Twitter](http://twitter.com/prsjohnny).
