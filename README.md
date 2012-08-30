![Coseva](https://fbb955dbd2c46c6e3194-d04b1cd5219d2087606844a09815488f.ssl.cf2.rackcdn.com/logos/coseva.png "Coseva")

A classy object-oriented alternative for parsing CSV files.

# Example

	$csv = new CSV('path/to/file.csv');

# API

## __construct( filename, open_mode = 'r', $use_include_path = FALSE )

To read a csv file, just pass the path to the .csv file to the `CSV` constructor.

	$csv = new CSV('path/to/file.csv');

## filterColumn( csv_column, callable)

This method allows you to run a filter on a particular column of every row.

### Parameters

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
	        <td>int</td>
	        <td>int</td>
	    </tr>
	</tbody>
</table>

### Returns

`NULL`

### Example

	// split column four at every colon and serialize
	$csv->filterColumn(4, function($value) {
	    return serialize(explode(':', $value));
	});