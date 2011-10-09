#!/bin/bash
 
INPUT=$1
 
if [ -n "$2" ]
then
    OUTPUT=$2
else
    OUTPUT=${INPUT%.*}.eps
fi

echo Converting $INPUT to $OUTPUT...

inkscape --without-gui --file=$INPUT --export-eps=$OUTPUT &> /dev/null
