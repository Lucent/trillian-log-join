find . -type f -exec sed '1s/^\xEF\xBB\xBFsion/Session/' -i {} \;
# to get rid of BOM clobbering first 3 letters of Session start
