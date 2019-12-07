<?php
$hash_dir = $argv[1];
$search_dir = $argv[2];

$all_files = array_diff(scandir($hash_dir), [".",".."]);
foreach ($all_files as $hash) {
  $hash_fullpath = $hash_dir . "/" . $hash;
  echo get_new_filename($hash_fullpath, $search_dir), "\n";
}

function get_new_filename($hash, $search_dir) {
  $reg_message = "/\<message timestamp='(\d+)' from='\w+'\>(.*)\<\/message\>$/";
  $message = get_long_line($hash, $reg_message);
//  echo "# ", $message, "\n";
  if (strlen($message) === 0)
    return "# $hash : no lines long enough";
  $match = `grep -r "$message" $search_dir`;
  $match_count = substr_count($match, "\n");
  if ($match_count !== 1)
    return "# $hash : bad number of matches: $match_count";
  $split = explode(":", $match, 2);
  $dir_username = $split[0];
  $service = strtolower(explode("-", explode("/", $dir_username)[1])[0]);
  $username = pathinfo($dir_username, PATHINFO_BASENAME);
  $new_name = pathinfo($hash, PATHINFO_DIRNAME) . "/" . $service . "-" . $username;
  return "mv '$hash' '$new_name'";
}

function get_long_line($file, $reg) {
  $MIN_LINE_LENGTH = 30;
  $handle = fopen($file, "r");
  while ($line = fgets($handle)) {
    $line = trim(remove_utf8_bom($line));
    preg_match($reg, $line, $matches);
    if ($matches && strlen($matches[2]) > $MIN_LINE_LENGTH && preg_match('/^[\w\s]+$/', $matches[2]))
      return $matches[2];
  }
}

function remove_utf8_bom($text) {
	$bom = pack('H*','EFBBBF');
	$text = preg_replace("/^$bom/", '', $text);
	return $text;
}
?>
