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

/**
 * CSV Class
 */
class CSV implements IteratorAggregate
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

    // implements IteratorAggregate
    public function getIterator()
    {
        return new ArrayIterator($this->_rows);
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