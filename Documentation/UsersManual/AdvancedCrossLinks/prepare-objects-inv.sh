#!/bin/bash

SOURCE_DIR=./xml
OUTPUT_DIR=./html
PROJECT="<name of the project>"
VERSION="<version>"

# BEWARE: Header is actually needed by Intersphinx!!! (1st line to be exact)
cat > $OUTPUT_DIR/objects.inv <<EOT
# Sphinx inventory version 2
# Project: $PROJECT
# Version: $VERSION
# The remainder of this file is compressed using zlib.
EOT

TMPFILE=`mktemp /tmp/sphinx-objects-inv.XXXXXX` || exit 1

# A few general anchors as specified by:
# - Intersphinx
cat >> $TMPFILE <<EOT
modindex std:label -1 annotated.html# Classes
genindex std:label -1 classes.html# Class Index
EOT
# - Doxygen
cat >> $TMPFILE <<EOT
namespaces std:label -1 namespaces.html# Namespaces
hierarchy std:label -1 hierarchy.html# Class Hierarchy
functions std:label -1 functions.html# Class Members
functions-func std:label -1 functions_func.html# Functions
variables std:label -1 functions_vars.html# Variables
deprecated std:label -1 deprecated.html# Deprecated List
todo std:label -1 todo.html# Todo List
test std:label -1 test.html# Test List
pages std:label -1 pages.html# Related Pages
examples std:label -1 examples.html# Examples
EOT
# - TYPO3 Documentation Team
cat >> $TMPFILE <<EOT
start std:label -1 index.html# $PROJECT
EOT

for XML in $(find $SOURCE_DIR -type f -name \*.xml | grep "/class_");
do
    echo "Processing $XML"

    FILE=$(cat $XML | xmlstarlet sel -t -v "//doxygen/compounddef/@id")
    CLASS_INTERNAL_NAME=$(cat $XML | xmlstarlet sel -t -v "//doxygen/compounddef/compoundname")
    CLASS_NAME="${CLASS_INTERNAL_NAME//::/\\}"
    if [[ "$CLASS_NAME" == *\\* ]]; then
        LABEL_CLASS_NAME="\\$CLASS_NAME"
    else
        LABEL_CLASS_NAME="$CLASS_NAME"
    fi

    # Pseudo anchor for the whole class
    ANCHOR=$(echo "$CLASS_NAME" | tr '[A-Z]' '[a-z]')
    echo "$ANCHOR std:label -1 $FILE.html# $LABEL_CLASS_NAME" >> $TMPFILE

    for ID in $(cat $XML | xmlstarlet sel -t -v "//doxygen/compounddef//memberdef[@kind='function']/@id");
    do
        # Beware there's a "1" (for colon) at the beginning of the anchor
        METHOD_ANCHOR=$(echo $ID | sed "s/^${FILE}_1//")
        METHOD=$(cat $XML | xmlstarlet sel -t -v "//doxygen/compounddef//memberdef[@id='$ID']/name")
        if [ -n "$METHOD" ]; then
            # Pseudo anchor for the method
            ANCHOR2=$(echo "$ANCHOR::$METHOD" | tr '[A-Z]' '[a-z]')
            echo "$ANCHOR2 std:label -1 $FILE.html#$METHOD_ANCHOR $LABEL_CLASS_NAME::$METHOD()" >> $TMPFILE
        fi
    done
done

# Compress the index
php -r "echo gzcompress(file_get_contents('$TMPFILE'));" >> $OUTPUT_DIR/objects.inv
rm $TMPFILE