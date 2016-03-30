#!/bin/bash

SOURCE=~/Downloads/codemirror-5.13.2

cp $SOURCE/lib/codemirror.js .
cp $SOURCE/lib/codemirror.css ../Css/

echo "/** mode/rst/rst.js */" >> codemirror.js
cat $SOURCE/mode/rst/rst.js >> codemirror.js

echo "/** mode/yaml/yaml.js */" >> codemirror.js
cat $SOURCE/mode/yaml/yaml.js >> codemirror.js

echo "/** addon/edit/trailingspace.js */" >> codemirror.js
cat $SOURCE/addon/edit/trailingspace.js >> codemirror.js
