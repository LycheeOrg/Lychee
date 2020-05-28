#!/usr/bin/env bash
set -e

wget https://exiftool.org/Image-ExifTool-11.99.tar.gz
tar -zxvf Image-ExifTool-11.99.tar.gz
cd Image-ExifTool-11.99
perl Makefile.PL
make test
sudo make install
cd ..
rm -rf Image-ExifTool-11.99
