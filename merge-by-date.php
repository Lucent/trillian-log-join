<?php
$active = [];
$inactive = [];
$active["file"] = fopen($argv[1], "r");
$inactive["file"] = fopen($argv[2], "r");

find_next_start($active);
find_next_start($inactive);

if ($active["start"] > $inactive["start"])
	swap($active, $inactive);

while (!feof($active["file"]) && !feof($inactive["file"])) {
	echo $active["line"];
	while ($active["end"] < $inactive["start"]) {
		stream_until_close($active);
	}
	swap($active, $inactive);
}

function stream_until_close(&$handle) {
	$close = '/^Session Close \((.*)\): (.*)$/';
	while ($line = remove_utf8_bom(fgets($handle["file"]))) {
		preg_match($close, $line, $matches);
		echo $line;
		if ($matches) {
			$handle["end"] = DateTime::createFromFormat('D M d H:i:s Y', trim($matches[2]));
			return TRUE;
		}
	}
}

function find_next_start(&$handle) {
	$start = '/^Session Start \((.*)\): (.*)$/';
	while ($line = remove_utf8_bom(fgets($handle["file"]))) {
		preg_match($start, $line, $matches);
		if ($matches) {
			$handle["end"] = date("r", 0);
			$handle["start"] = DateTime::createFromFormat('D M d H:i:s Y', trim($matches[2]));
			$handle["line"] = $line;
			return TRUE;
		}
	}
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
	echo "swapped!\n";
}
?>
