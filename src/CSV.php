<?php

/**
 * Coseva
 * 
 * A friendly, object-oriented alternative for parsing and filtering CSV files with PHP.
 */
namespace Coseva;

use \SplFileObject;
use \LimitIterator;
use \IteratorAggregate;
use \ArrayIterator;
use \Exception;

/**
 * CSV Class
 */
class CSV implements IteratorAggregate
{
    /**
     * Storage for parsed CSV rows
     * 
     * @var array
     */
    protected $_rows = array();

    /**
     * Storage for filter callback 
     * functions to be executed during 
     * the parsing stage
     * 
     * @var array
     */
    protected $_filters = array();

    /**
     * Holds the CSV file pointer
     * 
     * @var SplFileObject intance
     */
    protected $_file;

    /**
     * Holds config options for opening the file
     * 
     * @var array
     */
    protected $_fileConfig = array();

    /**
     * Constructor
     * -----------
     * To read a csv file, just pass the path to the .csv file to the CSV constructor.
     * 
     * @param [type]  $filename         The file to read.
     * @param string  $open_mode        The mode in which to open the file. See fopen() for a list of allowed modes.
     * @param boolean $use_include_path Whether to search in the include_path for filename.
     */
    function __construct($filename, $open_mode = 'r', $use_include_path = FALSE)
    {
        if (!is_readable($filename)) {
            throw new Exception($filename . ' is not readable.');
        }

        $this->_fileConfig = array(
            'filename' => $filename,
            'open_mode' => $open_mode,
            'use_include_path' => $use_include_path
        );
    }

    /**
     * Filter
     * -----------
     * This method allows you to register any number of filters on a particular column or an entire row.
     * 
     * @param  Mixed    $column   Optional: Zero-based column number. If this parameter is preset the $callable will recieve the contents of the current column (as a string), and will receive the entire (array based) row otherwise.
     * @param  callable $callable Callable receives either the current row (as an array) or the current column (as a string) as the first parameter. The callable must return the new filtered row or column. Note: You can also use any native PHP functions that permit one parameter and return the new value, like trim, htmlspecialchars, urlencode, etc.
     * @return object             CSV instance
     */
    public function filter($column, $callable = null )
    {
        if (!empty($callable) && !is_callable($callable)) {
            throw new Exception('The $callable parameter must be callable.');
        }

        // if column is callable assume 
        // this filter is for the entire 
        // row, not just the column
        if (is_callable($column)) {
            $this->_filters[] = ['callable' => $column,'column' => null];
        }
        // otherwise assume this filter 
        // is specific to a column
        else {
            $this->_filters[] = ['callable' => $callable,'column' => $column];
        }

        return $this;
    }

    /**
     * Parse
     * -----------
     * This method will convert the csv to an array and run all registered filters against it.
     * 
     * @param  integer $rowOffset Determines which row the parser will start on. Zero-based index.
     * @return object             CSV instance
     */
    public function parse($rowOffset = 0)
    {
        // open file
        if (null === $this->_file) {
            $this->_file = new SplFileObject($this->_fileConfig['filename'], $this->_fileConfig['open_mode'], $this->_fileConfig['use_include_path']);
            $this->_file->setFlags(SplFileObject::READ_CSV);
        }

        // loop through CSV rows
        foreach(new LimitIterator($this->_file, $rowOffset) as $key => $row)
        {
            // run filters in the same order 
            // they were registered
            foreach ($this->_filters as $filter) {
                $callable = $filter['callable'];
                $column   = $filter['column'];

                // entire row
                if (null === $column) {
                    $row = call_user_func($callable, $row);
                }
                // specific column
                else {
                    $row[$column] = call_user_func($callable, $row[$column]);
                }
            }

            $this->_rows[$key] = $row;
        }

        return $this;
    }

    /**
     * Get Iterator
     * -----------
     * Required for implementing IteratorAggregate
     * 
     * @return object ArrayIterator instance
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_rows);
    }

    /**
     * To Table
     * -----------
     * This is a great way to display the filtered contents of the csv to you during the development process (for debugging purposes).
     * 
     * @return string Html Table of CSV contents
     */
    public function toTable()
    {
        $num_rows = count($this);

        // begin drawing table
        $output = '<table border="1" cellspacing="1" cellpadding="3">';

        if ($num_rows) {

            // thead
            $output .= '<thead><tr><th>&nbsp;</th>';
            foreach ($this as $row) {
                foreach ($row as $key => $col) {
                    $output .= '<th>' . $key .  '</th>';
                }
                break;
            }
            $output .= '</tr></thead>';

            // tbody
            $output .= '<tbody>';
            foreach ($this as $i => $row) {
                $output .= '<tr>';
                $output .= '<th>' . $i . '</th>';
                foreach ($row as $col) {
                     $output .= '<td>' . $col .  '</td>';
                }
                $output .= '</tr>';
            }
            $output .= '</tbody>';
        }
        
        // close table
        $output .= '</table>';

        return $output;
    }

    /**
     * To JSON
     * -----------
     * Use this to get the entire CSV in JSON format.
     * 
     * @return string JSON encoded string
     */
    public function toJSON()
    {
        return json_encode($this->_rows);
    }

    /**
     * To String
     * -----------
     * If you cast a CSV instance as a string it will print the contents on the CSV to an HTML table.
     * 
     * @return string Html Table of CSV contents
     */
    public function __toString()
    {
        return $this->toTable();
    }
}