<?php
$active = [];
$inactive = [];
$active["file"] = fopen($argv[1], "r");
$inactive["file"] = fopen($argv[2], "r");

find_next_start($active);
find_next_start($inactive);

while (!feof($active["file"]) || !feof($inactive["file"])) {
	if ($active["start"] > $inactive["start"])
		swap($active, $inactive);
	stream_until_close($active);
	find_next_start($active);
	echo "\n\n";
}

function stream_until_close(&$handle) {
	$close = '/^Session Close \((.*)\): (.*)$/';
	echo $handle["line"];
	while ($line = fgets($handle["file"])) {
		$line = remove_utf8_bom($line);
		preg_match($close, $line, $matches);
		echo $line;
		if ($matches)
			return TRUE;
	}
}

function find_next_start(&$handle) {
	$start = '/^Session Start \((.*)\): (.*)$/';
	while ($line = fgets($handle["file"])) {
		$line = remove_utf8_bom($line);
		preg_match($start, $line, $matches);
		if ($matches) {
			$handle["start"] = DateTime::createFromFormat('D M d H:i:s Y', trim($matches[2]));
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
