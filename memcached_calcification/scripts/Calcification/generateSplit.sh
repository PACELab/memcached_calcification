#!/bin/bash

inp=$1
prefix=$2
partitions=$3

size="$(wc -c < $inp)"
echo $size

n_keys="$((size / 12))"
echo $n_keys

p_keys="$(((n_keys / partitions) + 1))"
echo $p_keys

out="$(split --separator=',' -l $p_keys --numeric-suffixes $inp $prefix)"
