<?php
/**
 * Coseva CSV.
 *
 * A friendly, object-oriented alternative for parsing and filtering CSV files
 * with PHP.
 *
 * @package Coseva
 * @subpackage CSV
 * @copyright 2013 Johnny Freeman
 */

namespace Coseva;

use \SplFileObject;
use \LimitIterator;
use \IteratorAggregate;
use \ArrayIterator;
use \InvalidArgumentException;

/**
 * CSV.
 */
class CSV implements IteratorAggregate {

  /**
   * Storage for parsed CSV rows.
   *
   * @var array $_rows the rows found in the CSV resource
   */
  protected $_rows = array();

  /**
   * Storage for filter callbacks to be executed during the parsing stage.
   *
   * @var array $_filters filter callbacks
   */
  protected $_filters = array();

  /**
   * Holds the CSV file pointer.
   *
   * @var SplFileObject $_file the active CSV file
   */
  protected $_file;

  /**
   * Holds config options for opening the file.
   *
   * @var array $_fileConfig configuration
   */
  protected $_fileConfig = array();

  /**
   * A list of open modes that are accepted by our file handler.
   *
   * @see http://php.net/manual/en/function.fopen.php for a list of modes
   * @var array $_availableOpenModes
   */
  private static $_availableOpenModes = array(
    'r', 'r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'
  );

  /**
   * Constructor for CSV.
   *
   * To read a csv file, just pass the path to the .csv file.
   *
   * @param string $filename The file to read. Should be readable.
   * @param string $open_mode The mode in which to open the file
   * @param boolean $use_include_path Whether to search through include_path
   * @see http://php.net/manual/en/function.fopen.php for a list of modes
   * @throws InvalidArgumentException when the given file could not be read
   * @throws InvalidArgumentException when the given open mode does not exist
   * @return CSV $this
   */
  function __construct($filename, $open_mode = 'r', $use_include_path = false) {
    // Check if the given filename was readable.
    if (!is_readable($filename)) {
      throw new InvalidArgumentException($filename . ' is not readable.');
    }

    // Check if the given open mode was valid.
    if (!in_array($open_mode, self::$_availableOpenModes)) {
      throw new InvalidArgumentException(
        'Unknown open mode "' . $open_mode . '".'
      );
    }

    // Store the configuration.
    $this->_fileConfig = array(
      'filename' => $filename,
      'open_mode' => $open_mode,
      // Explicitely cast this as a boolean to ensure proper bevahior.
      'use_include_path' => (bool) $use_include_path
    );
  }

  /**
   * Allows you to register any number of filters on a particular column or an
   * entire row.
   *
   * @param integer|callable $column Specific column number or the callable to
   *  be applied. Optional: Zero-based column number. If this parameter is
   *  preset the $callable will recieve the contents of the current column
   *  (as a string), and will receive the entire (array based) row otherwise.
   * @param callable $callable Either the current row (as an array) or the
   *  current column (as a string) as the first parameter. The callable must
   *  return the new filtered row or column.
   *  Note: You can also use any native PHP functions that permit one parameter
   *  and return the new value, like trim, htmlspecialchars, urlencode, etc.
   * @throws InvalidArgumentException when no valid callable was given
   * @throws InvalidArgumentException when no proper column index was supplied
   * @return CSV $this
   */
  public function filter($column, $callable = null) {
    // Check the function arguments.
    if (!empty($callable)) {
      if (!is_callable($callable)) throw new InvalidArgumentException(
        'The $callable parameter must be callable.'
      );

      if (!is_numeric($column)) throw new InvalidArgumentException(
        'No proper column index provided. Expected a numeric, while given "'
        . $column . '"'
      );
    }

    // Add the filter to our stack. Apply it to the whole row when our column
    // appears to be the callable, being the only present argument.
    $this->_filters[] = is_callable($column)
      ? array(
        'callable' => $column,
        'column' => null
      )
      : array(
        'callable' => $callable,
        // Explicitely cast the column as an integer.
        'column' => (int) $column
      );

    return $this;
  }

  /**
   * This method will convert the csv to an array and will run all registered
   * filters against it.
   *
   * @param integer $rowOffset Determines which row the parser will start on.
   *   Zero-based index.
   * @return CSV $this
   */
  public function parse($rowOffset = 0) {
    // Cast the row offset as an integer.
    $rowOffset = (int) $rowOffset;

    // Open the file if there is no SplFIleObject present.
    if (!($this->_file instanceof SplFileObject)) {
      $this->_file = new SplFileObject(
        $this->_fileConfig['filename'],
        $this->_fileConfig['open_mode'],
        $this->_fileConfig['use_include_path']
      );

      // Set the flag to parse CSV.
      $this->_file->setFlags(SplFileObject::READ_CSV);
    }

    // Loop through the CSV rows.
    foreach(new LimitIterator($this->_file, $rowOffset) as $key => $row) {
      // Run filters in the same order they were registered.
      foreach ($this->_filters as &$filter) {
        $callable =& $filter['callable'];
        $column =& $filter['column'];

        // Apply to the entire row.
        if (empty($column)) {
          $row = call_user_func_array($callable, array(&$row));
        } else {
          $row[$column] = call_user_func_array(
            $callable,
            array(&$row[$column])
          );
        }
      }

      // Unset references.
      unset($filter, $callable, $column);

      // Do some garbage collection to free memory of garbage we won't use.
      // @see http://php.net/manual/en/function.gc-collect-cycles.php
      gc_collect_cycles();

      $this->_rows[$key] = $row;
    }

    return $this;
  }

  /**
   * Get an array iterator for the CSV rows.
   *
   * Required for implementing IteratorAggregate
   *
   * @return ArrayIterator
   */
  public function getIterator() {
    return new ArrayIterator($this->_rows);
  }

  /**
   * This is a great way to display the filtered contents of the csv to you
   * during the development process (for debugging purposes).
   *
   * @return string $output HTML table of CSV contents
   */
  public function toTable() {
    $num_rows = count($this);

    // Begin table.
    $output = '<table border="1" cellspacing="1" cellpadding="3">';

    if ($num_rows) {

      // Table head.
      $output .= '<thead><tr><th>&nbsp;</th>';
      foreach ($this as $row) {
        foreach ($row as $key => $col) {
          $output .= '<th>' . $key .  '</th>';
        }
        break;
      }
      $output .= '</tr></thead>';

      // Table body.
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

    // Close table.
    $output .= '</table>';

    return $output;
  }

  /**
   * Use this to get the entire CSV in JSON format.
   *
   * @return string JSON encoded string
   */
  public function toJSON() {
    return json_encode($this->_rows);
  }

  /**
   * If you cast a CSV instance as a string it will print the contents on the
   * CSV to an HTML table.
   *
   * @return string $this->toTable() HTML table of CSV contents
   */
  public function __toString() {
    return $this->toTable();
  }

}