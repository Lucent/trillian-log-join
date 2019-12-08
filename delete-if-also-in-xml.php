<?php
// Fuzz the times and see if .log files next to .xml have any extra messages not in .xml
$xml_file = $argv[1];
$log_file = $argv[2];

if (pathinfo($xml_file, PATHINFO_EXTENSION) !== "xml" || pathinfo($log_file, PATHINFO_EXTENSION) !== "log") {
  echo "WRONG ARGUMENT ORDER!\n";
  exit;
}

$reg = '/^\<message time="(\d{7})(\d{6})" /';
$replace = '<message time="$1" ';

$xml_arr = array_map("trim", file($xml_file));
$log_arr = array_map("trim", file($log_file));

$log_arr_fuzzed = preg_replace($reg, $replace, $log_arr);
$xml_arr_fuzzed = preg_replace($reg, $replace, $xml_arr);

$result1 = reset(preg_grep('/whole%20world%20agrees/', $log_arr_fuzzed));
$result2 = reset(preg_grep('/whole%20world%20agrees/', $xml_arr_fuzzed));

$not_in_xml = array_diff($log_arr_fuzzed, $xml_arr_fuzzed);
$with_breaks = implode("\n", $not_in_xml);
echo $with_breaks, "\n";
