#!/bin/sh
path=$1
rsync --progress --recursive --compress --perms celibero@updates.celibero.com::celibero/ $1