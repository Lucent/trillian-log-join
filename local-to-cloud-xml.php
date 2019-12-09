<?php
// Converts the URL encoded files concatenated byflatten-cloud.php from local logs for comparison to XML export provided by Trillian staff.

$handle = fopen($argv[1], "r");
$from = "lucent";
$to = explode("-", pathinfo($argv[1], PATHINFO_FILENAME), 2)[1];

$reg_display = '/^\<message type="(\w+)_privateMessage" time="(\d+)" ms="(\d+)" medium="\w+" to="\w+" from="\w+" from_display="\w+" text="(.*)"\/\>$/';
$reg_noms = '/^\<message type="(\w+)_privateMessage" time="(\d+)" medium="\w+" to="\w+" from="\w+" from_display="\w+" text="(.*)"\/\>$/';
$reg = '/^\<message type="(\w+)_privateMessage" time="(\d+)" ms="(\d+)" medium="\w+" to="\w+" from="\w+" text="(.*)"\/\>$/';
while ($line = fgets($handle)) {
	$line = trim(remove_utf8_bom($line));
	if (preg_match('/^\<(session |secureim |filetransfer |status |video |audio |message type="information_standard" |message type="outgoing_privateAction" )/', $line)) {
		// pass through known lines
		echo $line, "\n";
		continue;
	}

	preg_match($reg_display, $line, $matches);
  if ($matches) {
		$ms = str_pad($matches[3], 3, "0", STR_PAD_LEFT);
		$text = $matches[4];
	} else {
  	preg_match($reg_noms, $line, $matches); // fallback to no "from_display"
		if ($matches) {
			$ms = "000";
			$text = $matches[3];
		} else {
			preg_match($reg, $line, $matches); // fallback to no "from_display"
			if ($matches) {
				$ms = str_pad($matches[3], 3, "0", STR_PAD_LEFT);
				$text = $matches[4];
			} else {
    		fwrite(STDERR, "CAN'T FIND MESSAGE: $line\n");
    		exit;
			}
		}
  }

  if ($matches[1] == "outgoing")
    $user = $from;
  elseif ($matches[1] == "incoming")
    $user = $to;
  else {
    fwrite(STDERR, "CAN'T DETERMINE TO/FROM: $line\n");
		exit;
	}


	$message = rawurldecode($text);
  echo "<message timestamp='{$matches[2]}$ms' from='$user'>$message</message>\n";
}

function remove_utf8_bom($text) {
	$bom = pack('H*','EFBBBF');
	$text = preg_replace("/^$bom/", '', $text);
	return $text;
}

?>
