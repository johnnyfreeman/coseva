<?php

/**
* Coseva
* 
* A classy, object-oriented alternative for parsing CSV files with PHP.
*/
class CSV
{
    protected $_rows = array();
    protected $_columnFilters = array();
    protected $_rowFilters = array();
    protected $_file;

    function __construct($filename, $open_mode = 'r', $use_include_path = FALSE)
    {
        $this->_file = new SplFileObject($filename, $open_mode, $use_include_path);
        $this->_file->setFlags(SplFileObject::READ_CSV);
    }

    public function filterColumn($csv_column, $callable)
    {
        if (is_callable($callable)) {
            $this->_columnFilters[$csv_column][] = $callable;
        }
    }

    public function filterRow($callable, $position = 'before')
    {
        if (is_callable($callable)) {
            $this->_rowFilters[] = $callable;
        }
    }

    public function parse($rowOffset = 0)
    {
        foreach(new LimitIterator($this->_file, $rowOffset) as $row => $columns)
        {
            foreach ($columns as $col => &$value)
            {
                // run column filters
                if (isset($this->_columnFilters[$col]))
                {
                    foreach ($this->_columnFilters[$col] as $filter) {
                        $value = call_user_func($filter, $value, $col);
                    }
                }
            }

            // run row filters
            foreach ($this->_rowFilters as $filter) {
                $columns = call_user_func($filter, $columns, $row);
            }

            $this->_rows[$row] = $columns;
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