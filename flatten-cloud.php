<?php
// Collapse the Trillian _CLOUD directory by concatenating all year/month directories into a single file for each contact.

$cloud_path = $argv[1];
$output_dir = $argv[2];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cloud_path));
$allFiles = array_filter(iterator_to_array($iterator), function($file) {
	return $file->isFile();
});

$unique_names = [];
foreach ($allFiles as $file) {
	$unique_names[$file->getFileName()][] = $file->getPathName();
}

foreach ($unique_names as $service_nick=>$path_batch) {
	$filename_list = implode(" ", $path_batch);
	system("cat $filename_list > $output_dir$service_nick");
}
?>
