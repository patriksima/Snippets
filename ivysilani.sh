#!/bin/sh

if [ $# -ne 2 ] ; then
    echo "usage: ivysilani.sh url dest"
    exit 1
fi

URL="$1"
DEST="$2"

PHP=$(which php)
DUMP=$(which rtmpdump)

CLASS="/path/to/ivysilani.class.php"

RTMP=$($PHP -r "include \"$CLASS\";echo iVysilani::GetRTMP(\"$URL\",2000);")
	
$DUMP --live -r "$RTMP" -o "$DEST"
