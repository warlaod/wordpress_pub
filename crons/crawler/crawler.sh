#! /bin/bash

workdir='/home/afagras/public_html'

while getopts "e:" optKey; do
  case "$optKey" in
  e)
    if [ $OPTARG = "dev" ]; then
      workdir="/var/www/html"
    fi
    ;;
  esac
done

CMD="$workdir/crons/crawler/esacs.com.js"
PROCID=$(pgrep -fo $CMD)

if [ $PROCID ] >0; then
  # 既に起動済みのため終了
  echo $PROCID
  exit 0
fi

node $CMD
