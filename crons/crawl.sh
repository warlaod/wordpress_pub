#!/bin/sh

files="$PWD/crons/crawler/*"
# files=`find $dir -maxdepth 0 -type f -name *.php`
for filepath in $files; do
  eval "php $filepath &";
  wait $!
done
