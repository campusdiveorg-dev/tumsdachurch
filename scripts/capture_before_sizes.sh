#!/usr/bin/env bash
# capture_before_sizes.sh
# Run BEFORE optimization to capture original file sizes into tmp/before_sizes.csv

set -euo pipefail

filesize() {
  # portable file size: try GNU stat, BSD stat (macOS), fallback to wc
  if stat --version >/dev/null 2>&1; then
    stat -c%s "$1"
  elif stat -f%z "$1" >/dev/null 2>&1; then
    stat -f%z "$1"
  else
    wc -c < "$1" | tr -d ' '
  fi
}

mkdir -p tmp
echo "path,size" > tmp/before_sizes.csv

# find PNG/JPG/JPEG under assets/ (root or subdirs)
find assets -type f \( -iname '*.png' -o -iname '*.jpg' -o -iname '*.jpeg' \) -print0 \
  | while IFS= read -r -d '' file; do
      size=$(filesize "$file")
      printf '%s,%s\n' "\"$file\"" "$size" >> tmp/before_sizes.csv
    done

echo "Wrote tmp/before_sizes.csv"