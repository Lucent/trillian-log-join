<?php
// Look for files with the same username and output commands to concatenate them

$search_dir = $argv[1];
$extension = $argv[2];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($search_dir));
$allFiles = array_filter(iterator_to_array($iterator), function($file) {
	return $file->isFile();
});

$unique_names = [];
foreach ($allFiles as $file) {
	$name = $file->getFileName();
	if ($file->getExtension() == $extension && !preg_match('/-assets.xml$/', $name))
		$unique_names[$name][] = $file->getPathName();
}

$multiples = array_filter($unique_names, "multiples");

foreach ($multiples as $service_nick=>$path_batch) {
	array_walk($path_batch, function(&$x) {$x = "'$x'";});
	$filename_list = implode(" ", $path_batch);
	$service_nick = pathinfo($service_nick, PATHINFO_FILENAME);
	echo "cat $filename_list > $service_nick.xml\n";
	echo "php ~/log-processing/sort-xml.php '$service_nick.xml' > '$service_nick-sorted.xml'\n";
	echo "uniq '$service_nick-sorted.xml' > 'unique-$service_nick.xml'\n";
	echo "php ~/log-processing/local-to-cloud-xml.php 'unique-$service_nick.xml' > 'cloud-$service_nick.xml'\n";
	echo "\n";
}

function multiples($var) {
  return count($var) > 1;
}
?>
