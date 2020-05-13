#!/usr/bin/env bash
set -e

wget http://www.sno.phy.queensu.ca/~phil/exiftool/Image-ExifTool-11.77.tar.gz
tar -zxvf Image-ExifTool-11.77.tar.gz
cd Image-ExifTool-11.77
perl Makefile.PL
make test
sudo make install
cd ..
rm -rf Image-ExifTool-11.77