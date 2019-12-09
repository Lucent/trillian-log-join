<?php
// Use session start and stop times in .log or .xml files to merge them

$active = [];
$inactive = [];
$active["file"] = fopen($argv[1], "r");
$inactive["file"] = fopen($argv[2], "r");

if (pathinfo($argv[1], PATHINFO_EXTENSION) === "log" && pathinfo($argv[2], PATHINFO_EXTENSION) === "log") {
	$filetype = "LOG";
	$timeformat = "D M d H:i:s Y";
	$reg_start = '/^Session Start \((.*)\): (.*)$/';
	$reg_close = '/^Session Close \((.*)\): (.*)$/';
}

if (pathinfo($argv[1], PATHINFO_EXTENSION) === "xml" && pathinfo($argv[2], PATHINFO_EXTENSION) === "xml") {
	$filetype = "XML";
	$timeformat = "U";
	$reg_start = '/^\<session type="([Ss]tart)" time="(\d+)".*\/\>$/';
	$reg_close = '/^\<session type="([Ss]top)" time="(\d+)".*\/\>$/';
}

find_next_start($active, $reg_start);
find_next_start($inactive, $reg_start);

while (!feof($active["file"]) || !feof($inactive["file"])) {
	if ($active["start"] > $inactive["start"])
		swap($active, $inactive);
	stream_until_close($active, $reg_close);
	find_next_start($active, $reg_start);
	if ($filetype === "LOG")
		echo "\n\n";
}

function stream_until_close(&$handle, $reg) {
	echo $handle["line"], "\n";
	while ($line = fgets($handle["file"])) {
		$line = trim(remove_utf8_bom($line));
		preg_match($reg, $line, $matches);
		echo $line, "\n";
		if ($matches)
			return TRUE;
	}
}

function find_next_start(&$handle, $reg) {
	global $timeformat;
	while ($line = fgets($handle["file"])) {
		$line = trim(remove_utf8_bom($line));
		preg_match($reg, $line, $matches);
		if ($matches) {
			$handle["start"] = DateTime::createFromFormat($timeformat, trim($matches[2]));
//			echo $handle["start"]->format('Y-m-d');
			$handle["line"] = $line;
			return TRUE;
		}
	}
	$handle["start"] = new DateTime();
}

function remove_utf8_bom($text) {
	$bom = pack('H*','EFBBBF');
	$text = preg_replace("/^$bom/", '', $text);
	return $text;
}

function swap(&$x, &$y) {
	$tmp = $x;
	$x = $y;
	$y = $tmp;
}
?>
