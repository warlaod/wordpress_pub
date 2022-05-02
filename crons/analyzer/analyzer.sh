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

CMD="$workdir/crons/analyzer/esacs.com.php"
PROCID=$(pgrep -fo $CMD)

if [ $PROCID ] >0; then
  # 既に起動済みのため終了
  exit 0
fi

php $CMD
