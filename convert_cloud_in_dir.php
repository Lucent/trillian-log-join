<?php
$cloud_dir = $argv[1];

$all_files = array_diff(scandir($cloud_dir), [".",".."]);
foreach ($all_files as $filename) {
  $fullpath = $cloud_dir . $filename;
  $file_only = pathinfo($fullpath, PATHINFO_FILENAME);
  $converted_name = $file_only . "-converted.xml";
  $nodupes_name = $file_only . "-nodupes.xml";
  echo "php ~/log-processing/convert-cloud-to-xml.php '$fullpath' > ", $converted_name, "\n";
  echo "awk '!seen[$0]++' $converted_name > $nodupes_name\n";
}
