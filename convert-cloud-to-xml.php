<?php
$handle = fopen($argv[1], "r");
$from = "lucent";
$to = explode("-", pathinfo($argv[1], PATHINFO_FILENAME), 2)[1];

$reg = '/^\<message time="(\d+)" type="(\w+)_privateMessage" text="(.*)"\/\>$/';
while ($line = fgets($handle)) {
	$line = trim(remove_utf8_bom($line));
	preg_match($reg, $line, $matches);
  if (!preg_match('/\<message /', $line))
    continue;
  elseif (!$matches) {
    echo "CAN'T FIND MESSAGE: $line\n";
    exit;
  }

  $message = rawurldecode($matches[3]);
  if ($matches[2] == "outgoing")
    $user = $from;
  elseif ($matches[2] == "incoming")
    $user = $to;
  else
    echo "CAN'T DETERMINE TO/FROM: $line\n";

  echo "<message timestamp='{$matches[1]}' from='$user'>$message</message>\n";
}

function remove_utf8_bom($text) {
	$bom = pack('H*','EFBBBF');
	$text = preg_replace("/^$bom/", '', $text);
	return $text;
}

?>
