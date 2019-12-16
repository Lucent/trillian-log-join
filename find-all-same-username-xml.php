<?php
// Look for files with the same username and output commands to concatenate them

$search_dir = $argv[1];
$extension = $argv[2];
$lower = strtolower(substr($search_dir, 0, -1));

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
	`cat $filename_list > $service_nick.xml; sed ':a;N;$!ba;s/\\r\\r\\n//g' '$service_nick.xml' > 'sed-$service_nick.xml'; php ~/log-processing/sort-xml.php 'sed-$service_nick.xml' > '$service_nick-sorted.xml'; uniq '$service_nick-sorted.xml' > 'unique-$service_nick.xml'; php ~/log-processing/local-to-cloud-xml.php 'unique-$service_nick.xml' > 'local-$service_nick.xml'`;
	`rm '$service_nick.xml' 'sed-$service_nick.xml' '$service_nick-sorted.xml' 'unique-$service_nick.xml'`;
	$server_file = "../Trillian Server XML/$search_dir$service_nick.xml";
	$concat_file = "local-$service_nick.xml";
	if (!file_exists($server_file)) {
		echo "# No server version, copy concatenated file:\n";
		echo "mv -n '$concat_file' '../Logs canonical/$search_dir$service_nick.xml'\n";
	} else {
		echo "# $concat_file\n";
		$unique_local = `php ~/log-processing/fuzz-log-times.php '$concat_file' '$server_file'`;
		$local_count = substr_count($unique_local, "\n");
		echo "# Local has $local_count lines server doesn't have.\n";
		echo $unique_local;
		$unique_server = `php ~/log-processing/fuzz-log-times.php '$server_file' '$concat_file'`;
		$server_count = substr_count($unique_server, "\n");
		echo "# Server has $server_count lines local doesn't have.\n";
	}
	echo "\n";
}

function multiples($var) {
  return count($var) > 0;
}
?>
