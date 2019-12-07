<?php
$active = [];
$inactive = [];
$active["file"] = fopen($argv[1], "r");
$inactive["file"] = fopen($argv[2], "r");

while (!feof($active["file"]) || !feof($inactive["file"])) {
	find_next_start($active);
	if ($active["start"] > $inactive["start"])
		swap($active, $inactive);
	stream_until_close($active);
}

function stream_until_close(&$handle) {
	$close = '/^Session Close \((.*)\): (.*)$/';
	echo $handle["line"];
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
			$handle["end"] = 0;
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
	echo "\n";
	$tmp = $x;
	$x = $y;
	$y = $tmp;
}
?>
