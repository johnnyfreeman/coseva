<?php

/**
* Coseva
* 
* A friendly, object-oriented alternative for parsing CSV files with PHP.
*/
class CSV
{
    protected $_rows = array();
    protected $_filters = array();
    protected $_file;

    function __construct($filename, $open_mode = 'r', $use_include_path = FALSE)
    {
        $this->_file = new SplFileObject($filename, $open_mode, $use_include_path);
        $this->_file->setFlags(SplFileObject::READ_CSV);
    }

    public function filter(Closure $callable, Integer $column = null)
    {
        $this->_filters[] = [
            'callable' => $callable,
            'column'   => $column
        ];
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

    public function toArray()
    {
        return $this->_rows;
    }

    public function toTable()
    {
        $rows = $this->toArray();
        $num_rows = count($rows);

        // begin drawing table
        $output = '<table border="1" cellspacing="1" cellpadding="1">';

        // thead
        if (count($this->_rows)) {
            $output .= '<thead><tr><th>&nbsp;</th>';
            foreach ($rows as $row) {
                foreach ($row as $key => $col) {
                    $output .= '<th>' . $key .  '</th>';
                }
                break;
            }
            $output .= '</tr></thead>';

            // tbody
            $output .= '<tbody>';
            foreach ($rows as $i => $row) {
                $output .= '<tr>';
                $output .= '<td>' . $i . '</td>';
                foreach ($row as $col) {
                     $output .= '<td>' . $col .  '</td>';
                }
                $output .= '</tr>';
            }
            $output .= '</tbody>';
        }
        
        $output .= '</table>';

        return $output;
    }
}