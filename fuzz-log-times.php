<?php
// Fuzz the times and see if .log files next to .xml have any extra messages not in .xml

$file1 = $argv[1];
$file2 = $argv[2];

$reg = '/^\<message timestamp=\'(\d{7})(\d{6})\' /'; //  from=\'[^\']*\'
$reg = '/^\<message timestamp=\'(\d{7})(\d{6})\' from=\'[^\']*\'/';
$replace = '<message time="$1" ';

$file1_arr = array_map("trim", file($file1));
$file2_arr = array_map("trim", file($file2));

$file1_arr = preg_grep('/^\<(session |secureim |filetransfer |status |video |audio |message type="information_standard" |message type="outgoing_privateAction" )/', $file1_arr, PREG_GREP_INVERT);
$file2_arr = preg_grep('/^\<(session |secureim |filetransfer |status |video |audio |message type="information_standard" |message type="outgoing_privateAction" )/', $file2_arr, PREG_GREP_INVERT);

$file1_arr_fuzzed = preg_replace($reg, $replace, $file1_arr);
$file2_arr_fuzzed = preg_replace($reg, $replace, $file2_arr);

//$result1 = reset(preg_grep('/whole%20world%20agrees/', $file1_arr_fuzzed));
//$result2 = reset(preg_grep('/whole%20world%20agrees/', $file2_arr_fuzzed));

$not_in_xml = array_diff($file1_arr_fuzzed, $file2_arr_fuzzed);
$with_breaks = implode("\n", $not_in_xml);
echo $with_breaks, "\n";
