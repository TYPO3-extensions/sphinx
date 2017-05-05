#!/bin/bash

SOURCE=~/Downloads/codemirror-5.25.2

cp $SOURCE/lib/codemirror.js ../Public/JavaScript/
cp $SOURCE/lib/codemirror.css ../Public/Css/

echo "/** mode/rst/rst.js */" >> ../Public/JavaScript/codemirror.js
cat $SOURCE/mode/rst/rst.js >> ../Public/JavaScript/codemirror.js

echo "/** mode/yaml/yaml.js */" >> ../Public/JavaScript/codemirror.js
cat $SOURCE/mode/yaml/yaml.js >> ../Public/JavaScript/codemirror.js

echo "/** addon/edit/trailingspace.js */" >> ../Public/JavaScript/codemirror.js
cat $SOURCE/addon/edit/trailingspace.js >> ../Public/JavaScript/codemirror.js
