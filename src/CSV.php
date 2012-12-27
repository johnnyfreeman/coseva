<?php

/**
 * Coseva
 * 
 * A friendly, object-oriented alternative for parsing and filtering CSV files with PHP.
 */
namespace Coseva;

use \SplFileObject;
use \LimitIterator;
use \Closure;
use \ArrayAccess;
use \Iterator;
use \Countable;

/**
 * CSV Class
 */
class CSV implements ArrayAccess, Iterator, Countable
{
    protected $_rows = array();
    protected $_filters = array();
    protected $_file;

    function __construct($filename, $open_mode = 'r', $use_include_path = FALSE)
    {
        $this->_file = new SplFileObject($filename, $open_mode, $use_include_path);
        $this->_file->setFlags(SplFileObject::READ_CSV);
    }

    public function filter($column, callable $callable = null )
    {
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
    }

    public function parse($rowOffset = 0)
    {
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
    }

    // implement ArrayAccess
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_rows[] = $value;
        } else {
            $this->_rows[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->_rows[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_rows[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_rows[$offset]) ? $this->_rows[$offset] : null;
    }

    // implement Countable
    public function count()
    {
        return count($this->_rows);
    }

    // implement Iterator
    public function rewind() {
        return reset($this->_rows);
    }

    public function current() {
        return current($this->_rows);
    }

    public function key() {
        return key($this->_rows);
    }

    public function next() {
        return next($this->_rows);
    }

    public function valid() {
        return false !== current($this->_rows);
    }

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
}