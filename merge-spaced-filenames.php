<?php
// Find same users but in multiple files due to spacing differences and issue commands to merge them

$search_dir = $argv[1];
$uniform_names = [];

$all_files = array_diff(scandir($search_dir), [".",".."]);
foreach ($all_files as $filename) {
  $plain_name = strtolower(pathinfo(preg_replace('/\s+/', '', $filename), PATHINFO_FILENAME));
  if (array_key_exists($plain_name, $uniform_names))
    $uniform_names[$plain_name][] = $filename;
  else
    $uniform_names[$plain_name] = [$filename];
/*  $hash_fullpath = $search_dir . "/" . $filename;
  $new_filename = get_new_filename($hash_fullpath, $search_dir);
  if ($new_filename !== FALSE) {
    if (file_exists($new_filename))
      echo "# OVERWRITE WARNING: $new_filename\n";
    else
      echo "mv -n '$hash_fullpath' '$new_filename'\n";
  }
  */
}
$multiples = array_filter($uniform_names, "multiples");
print_r($multiples); // make sure there's only 2 each!

foreach ($multiples as $plain=>$filenames) {
	//system("cat $filename_list > $output_dir$service_nick");
  array_walk($filenames, function(&$x) {$x = "'$x'";});
  $pair = implode(" ", $filenames);
  echo "php ~/log-processing/merge-by-date.php ", $pair, " > ", $plain, "-final.log", "\n";
}

function multiples($var) {
  return count($var) > 1;
}
?>
