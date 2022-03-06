#!/bin/bash

# This runs the checkers and outputs to files to make it easier to clean things up

echo
echo Starting phpcs and dumping results in cs-check.txt
echo --------------------------------------------------
echo

composer cs-check > cs-check.txt

echo
echo Done!
echo -----
echo
echo Starting phpstan and dumping results in stan.txt
echo ------------------------------------------------
echo

composer stan > stan.txt

echo
echo Done!
echo -----
echo
echo Look in the files for complete scan results:
echo
echo ' * cs-check.txt'
echo ' * stan.txt'
echo
