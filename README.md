find . -type f -exec sed '1s/^\xEF\xBB\xBFsion/Session/' -i {} \;
# to get rid of BOM clobbering first 3 letters of Session start

grep -m1 -v "<log version" *.log
# find messages spilling into non-XML files

awk '!seen[$0]++' icq-714253580.log > icq-714253580dupes.log
# get rid of duplicate lines to minimize diffs
