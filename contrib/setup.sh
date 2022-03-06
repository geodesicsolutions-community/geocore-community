#!/bin/bash

DIR=`php -r "echo dirname(realpath('$0'));"`

# Copy git hook(s)
cp $DIR/pre-commit $DIR/../.git/hooks/pre-commit
chmod +x $DIR/../.git/hooks/pre-commit
