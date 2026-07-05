#!/usr/bin/env bash
# generate_savings.sh
# Requires tmp/before_sizes.csv (created by capture_before_sizes.sh)
# Produces tmp/savings.csv mapping original -> best optimized (webp preferred)

set -euo pipefail

filesize() {
  if stat --version >/dev/null 2>&1; then
    stat -c%s "$1"
  elif stat -f%z "$1" >/dev/null 2>&1; then
    stat -f%z "$1"
  else
    wc -c < "$1" | tr -d ' '
  fi
}

BEFORE="${1:-tmp/before_sizes.csv}"
OUT="tmp/savings.csv"

if [ ! -f "$BEFORE" ]; then
  echo "Missing $BEFORE. Run scripts/capture_before_sizes.sh before optimization." >&2
  exit 1
fi

mkdir -p tmp
echo "original,after_file,orig_size,after_size,savings_bytes,savings_pct" > "$OUT"

# read CSV (path,size)
tail -n +2 "$BEFORE" | while IFS=, read -r quotedpath size; do
  # quotedpath is like "assets/xxx.png"
  # strip quotes
  path="${quotedpath%\"}"
  path="${path#\"}"
  f=$(basename "$path")
  base="${f%.*}"

  # prefer webp then jpg (under assets/img/)
  if [ -e "assets/img/webp/${base}.webp" ]; then
    after="assets/img/webp/${base}.webp"
  elif [ -e "assets/img/jpg/${base}.jpg" ]; then
    after="assets/img/jpg/${base}.jpg"
  else
    after=""
  fi

  if [ -n "$after" ]; then
    after_size=$(filesize "$after")
    savings=$((size - after_size))
    # compute pct with awk to one decimal place
    pct=$(awk -v s="$savings" -v o="$size" 'BEGIN{ if (o>0) printf("%.1f", (s/o)*100); else print "0" }')
    printf '%s,%s,%s,%s,%s,%s\n' "\"$path\"" "\"$after\"" "$size" "$after_size" "$savings" "$pct" >> "$OUT"
  else
    # No optimized file found
    printf '%s,%s,%s,%s,%s,%s\n' "\"$path\"" "" "$size" "" "" "" >> "$OUT"
  fi
done

echo "Wrote $OUT"
echo "Preview:"
column -s, -t < "$OUT" | sed -n '1,200p'