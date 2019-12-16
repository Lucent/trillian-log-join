<?php
// Converts the URL encoded files concatenated byflatten-cloud.php from local logs for comparison to XML export provided by Trillian staff.

$handle = fopen($argv[1], "r");
$from = "lucent";
$to = explode("-", pathinfo($argv[1], PATHINFO_FILENAME), 2)[1];

$reg_display = '/^\<message type="(\w+)_privateMessage(History)?(Offline)?" time(stamp)?="(\d+)" (ms="(\d+)" )?medium="\w+" to="([\'\s\-\(\)\w%]*)" from="([\'\s\-\(\)\w%]*)" (from_display="[\w%]+" )?text="(.*)"\/\>$/';
while ($line = fgets($handle)) {
	$line = trim(remove_utf8_bom($line));
	if (preg_match('/^\<(session |secureim |filetransfer |status |video |audio |icon |message type="information_standard" |message type="outgoing_privateAction" |message type="incoming_privateAction" )/', $line)) {
		// pass through known lines
		echo $line, "\n";
		continue;
	}

	preg_match($reg_display, $line, $matches);
//	print_r($matches);
  if ($matches) {
		if ($matches[7])
			$ms = str_pad($matches[7], 3, "0", STR_PAD_LEFT);
		else
			$ms = "000";
		$text = $matches[11];
	} else {
		fwrite(STDERR, "CAN'T FIND MESSAGE: $line\n");
    exit;
  }

  if ($matches[1] == "outgoing") {
		if (preg_match('/Lucent|Looseint|mdayah/i', $matches[9])) // from
    	$user = $from;
		else
			$user = $to;
  } elseif ($matches[1] == "incoming")
    $user = $to;
  else {
    fwrite(STDERR, "CAN'T DETERMINE TO/FROM: $line\n");
		exit;
	}


	$message = rawurldecode($text);
  echo "<message timestamp='{$matches[5]}$ms' from='$user'>$message</message>\n";
}

function remove_utf8_bom($text) {
	$bom = pack('H*','EFBBBF');
	$text = preg_replace("/^$bom/", '', $text);
	return $text;
}

?>
