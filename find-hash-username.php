<?php
$hash_dir = $argv[1];
$search_dir = $argv[2];

$all_files = array_diff(scandir($hash_dir), [".",".."]);
foreach ($all_files as $hash) {
  $hash_fullpath = $hash_dir . "/" . $hash;
  $new_filename = get_new_filename($hash_fullpath, $search_dir);
  if ($new_filename !== FALSE) {
    if (file_exists($new_filename))
      echo "# OVERWRITE WARNING: $new_filename\n";
    else
      echo "mv -n '$hash_fullpath' '$new_filename'\n";
  }
}

function get_new_filename($hash, $search_dir) {
  $reg_message = "/\<message timestamp='(\d+)' from='\w+'\>(.*)\<\/message\>$/";
  $message = get_long_line($hash, $reg_message);
//  echo "# ", $message, "\n";
  if (strlen($message) === 0) {
    echo "# $hash : no lines long enough\n";
    return FALSE;
  }
  $match = `grep -Fr "$message" $search_dir`;
  $match_count = substr_count($match, "\n");
  if ($match_count !== 1) {
    echo "# $hash : bad number of matches: $match_count\n";
    return FALSE;
  }
  $split = explode(":", $match, 2);
  $dir_username = $split[0];
  $service = strtolower(explode("-", explode("/", $dir_username)[1])[0]);
  $username = pathinfo($dir_username, PATHINFO_BASENAME);
  return pathinfo($hash, PATHINFO_DIRNAME) . "/" . $service . "-" . $username;
}

function get_long_line($file, $reg) {
  $MIN_LINE_LENGTH = 20;
  $handle = file($file);
  $handle = array_reverse($handle);
//  $handle = fopen($file, "r");
//  while ($line = fgets($handle)) {
  foreach ($handle as $line) {
    $line = trim(remove_utf8_bom($line));
    preg_match($reg, $line, $matches);
    if ($matches && strlen($matches[2]) > $MIN_LINE_LENGTH && preg_match('/^[\w\s\.\:\)\(\,\?\!\'\@\-\=\&\;\/]+$/', $matches[2]))
      return $matches[2];
  }
}

function remove_utf8_bom($text) {
	$bom = pack('H*','EFBBBF');
	$text = preg_replace("/^$bom/", '', $text);
	return $text;
}
?>
