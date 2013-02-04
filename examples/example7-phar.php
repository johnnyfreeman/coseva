<?php
/**
 * Example of a package file.
 *
 * To create the corresponding package, simply run:
 * ./package examples/example6.csv examples/example7-phar.php
 *
 * @package Coseva
 * @subpackage Examples
 */

// Coseva will be automatically included by the created package.
use \Coseva\CSV;

$fromCurrency = 'EUR';
$toCurrency = 'USD';

// Fetch the current conversion from Google Finance.
$conversionRate = file_get_contents(
  'http://www.google.com/finance/converter?'
  . http_build_query(
    array(
      // Number of initial units.
      'a' => 1,

      // Starting currency.
      'from' => $fromCurrency,

      // Ending currency.
      'to' => $toCurrency
    )
  )
);

// Extract the conversion rate from the HTML.
$conversionRate = explode('<span class=bld>', $conversionRate, 2);
$conversionRate = explode('</span>', $conversionRate[1], 2);
$conversionRate = $conversionRate[0] + 0;

// Open the examples file with income for a week.
// SOURCE_FILE will be defined by the package bootstrap code.
$csv = CSV::getInstance(SOURCE_FILE);

// Filter the income.
$csv->filter(
  function(array $day, $conversionRate, $toCurrency) {
    // Convert the currency.
    $day[1] = number_format($day[1] * $conversionRate, 2);

    // Overwrite the currency unit.
    $day[2] = $toCurrency;

    return $day;
  },
  $conversionRate,
  $toCurrency
);

// Output the converted income.
echo '<h1>Income in ' . $toCurrency . '</h1>' . $csv;
