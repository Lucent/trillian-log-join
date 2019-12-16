<?php
// Use existing log message contents to find real filenames of staff-provided hashed filenames
// php ~/log-processing/find-hash-username.php Hash/ ../Logs

$hash_dir = $argv[1];
$search_dir = $argv[2];

$all_files = array_diff(scandir($hash_dir), [".",".."]);
foreach ($all_files as $hash) {
  $hash_fullpath = $hash_dir . $hash;
  $new_filename = get_new_filename($hash_fullpath, $search_dir);
  if ($new_filename !== FALSE) {
    if (file_exists($new_filename))
      fwrite(STDERR, "# OVERWRITE WARNING: $new_filename\n");
    else
      echo "mv -n '$hash_fullpath' '$new_filename'\n";
  }
}

function get_new_filename($hash, $search_dir) {
  $reg_message = "/\<message timestamp='(\d+)' from='\w+'\>(.*)\<\/message\>$/";
  $message = get_long_line($hash, $reg_message);
//  echo "# ", $message, "\n";
  if (strlen($message) === 0) {
    fwrite(STDERR, "# $hash : no lines long enough\n");
    return FALSE;
  }
  echo "# $message\n";
  $match = `grep -Fr "$message" $search_dir`;
  $match_count = substr_count($match, "\n");
  if ($match_count !== 1) {
    fwrite(STDERR, "# $hash : bad number of matches: $match_count\n");
    return FALSE;
  }
  $split = explode(":", $match, 2);
  $dir_username = $split[0];
  $service = explode("-", explode("/", $dir_username)[2])[0];
  $service_lower = strtolower($service);
  $username = pathinfo($dir_username, PATHINFO_FILENAME);
  return "$service/$service_lower-$username.xml";
}

function get_long_line($file, $reg) {
  $MIN_LINE_LENGTH = 40;
  $handle = file($file);
//  $handle = array_reverse($handle); FLIP THIS ON AND OFF
  foreach ($handle as $line) {
    $line = trim(remove_utf8_bom($line));
    preg_match($reg, $line, $matches);
    if ($matches && strlen($matches[2]) > $MIN_LINE_LENGTH && preg_match('/^[\w\s\.\:\)\(\,\?\!\'\@\-\=\&\;\/]+$/', $matches[2]))
      return substr(rawurlencode($matches[2]), 0, $MIN_LINE_LENGTH);
  }
}

function remove_utf8_bom($text) {
	$bom = pack('H*','EFBBBF');
	$text = preg_replace("/^$bom/", '', $text);
	return $text;
}
?>
