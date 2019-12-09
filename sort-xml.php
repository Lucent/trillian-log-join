<?php
// Sort XML logs by time and millisecond attributes

$file = fopen($argv[1], "r");
$reg_ms = '/^\<.* time="(\d+)" ms="(\d+)".*\/\>$/';
$reg = '/^\<.* time="(\d+)" .*\/\>$/';

$all_lines = [];
while ($line = fgets($file)) {
  $line = trim(remove_utf8_bom($line));
  if (preg_match('/\>\</', $line)) {
    fwrite(STDERR, "DOUBLE LINE: $line\n");
  //  exit;
  }
  preg_match($reg_ms, $line, $matches);
  if ($matches)
    $time = $matches[1] . str_pad($matches[2], 3, "0", STR_PAD_LEFT);
  else {
    preg_match($reg, $line, $matches);
    if ($matches)
      $time = $matches[1] . "000";
    else {
      fwrite(STDERR, "CAN'T FIND TIME: $line\n");
      exit;
    }
  }

  if (array_key_exists($time, $all_lines))
    $all_lines[$time][] = $line;
  else
    $all_lines[$time] = [$line];
}

//print_r($all_lines);
ksort($all_lines);
//print_r($all_lines);
foreach ($all_lines as $timeblock)
  foreach ($timeblock as $line)
    echo $line, "\n";

function remove_utf8_bom($text) {
	$bom = pack('H*','EFBBBF');
	$text = preg_replace("/^$bom/", '', $text);
	return $text;
}
