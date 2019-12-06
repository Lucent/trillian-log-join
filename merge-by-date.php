<?php
$file1 = $argv[1];
$file2 = $argv[2];
$start = '/^Session Start \((.*)\): (.*)$/';
$handle1 = fopen($file1, "r");
$handle2 = fopen($file2, "r");
while (($line = fgets($handle1)) !== false) {
	$line = remove_utf8_bom($line);

	preg_match($start, $line, $matches);
	if ($matches) {
		$begin = DateTime::createFromFormat('D M d H:i:s Y', trim($matches[2]));
		$file1_begin = $begin;
		break;
	}
}

while (($line = fgets($handle2)) !== false) {
	$line = remove_utf8_bom($line);

	preg_match($start, $line, $matches);
	if ($matches) {
		$begin = DateTime::createFromFormat('D M d H:i:s Y', trim($matches[2]));
		$file2_begin = $begin;
		break;
	}
}

if ($file1_begin > $file2_begin) {
	$tmp = $handle1;
	$handle1 = $handle2;
	$handle2 = $tmp;
}

while ($file1_begin < $file2_begin)
	$block_end = stream_until_close_and_return_end_time($handle1);

/*
	preg_match($close, $line, $matches);
	if ($matches) {
		$end = DateTime::createFromFormat('D M d H:i:s Y', trim($matches[2]));
		if ($begin < $end)
			echo "good\n";
	}
}
 */
fclose($handle1);
fclose($handle2);

function stream_until_close_and_return_end_time($handle) {
	$close = '/^Session Close \((.*)\): (.*)$/';
	while ($line = remove_utf8_bom(fgets($handle))) {
		preg_match($close, $line, $matches);
		echo $line;
		if ($matches)
			return DateTime::createFromFormat('D M d H:i:s Y', trim($matches[2]));
	}
}

function remove_utf8_bom($text) {
	$bom = pack('H*','EFBBBF');
	$text = preg_replace("/^$bom/", '', $text);
	return $text;
}
?>
