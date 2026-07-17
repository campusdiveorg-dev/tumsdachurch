#!/usr/bin/env bash
# optimize_images.sh
# Move originals into assets/img/original/ and generate webp + jpg fallbacks.
# Requirements: bash, ImageMagick (convert), cwebp (optional but recommended), jpegoptim (optional)

set -euo pipefail

# Ensure we're running under bash
if [ -z "${BASH_VERSION-}" ]; then
  echo "Please run this script with bash." >&2
  exit 1
fi

# Create target directories
mkdir -p assets/img/original
mkdir -p assets/img/webp
mkdir -p assets/img/jpg

# Copy images from assets/ -> assets/img/ (non-destructive)
shopt -s nullglob
for f in assets/*.{png,PNG,jpg,JPG,jpeg,JPEG}; do
  [ -e "$f" ] || continue
  base=$(basename "$f")
  if [ ! -e "assets/img/$base" ]; then
    cp -p "$f" "assets/img/$base"
    echo "Copied $f -> assets/img/$base"
  fi
done
shopt -u nullglob

# Move files from assets/img/ into assets/img/original/ (if not already moved)
shopt -s nullglob
for f in assets/img/*.{png,PNG,jpg,JPG,jpeg,JPEG}; do
  [ -e "$f" ] || continue
  dest="assets/img/original/$(basename "$f")"
  if [ ! -e "$dest" ]; then
    mv "$f" "$dest"
    echo "Moved $f -> $dest"
  else
    echo "Skipping move; $dest already exists"
  fi
done
shopt -u nullglob

# Generate JPEG fallback and WebP for each original
for orig in assets/img/original/*; do
  [ -e "$orig" ] || continue
  name=$(basename "$orig")
  base="${name%.*}"
  ext="${name##*.}"
  dest_jpg="assets/img/jpg/${base}.jpg"
  dest_webp="assets/img/webp/${base}.webp"

  echo "Processing $orig -> jpg: $dest_jpg  webp: $dest_webp"

  # Create JPEG fallback
  if [[ "${ext,,}" == "png" ]]; then
    # Convert PNG -> JPG (stripping metadata)
    if command -v convert >/dev/null 2>&1; then
      convert "$orig" -strip -interlace Plane -quality 85 "$dest_jpg"
    else
      echo "ImageMagick 'convert' not found. Cannot convert PNG to JPG." >&2
      cp -p "$orig" "$dest_jpg"
    fi
  else
    cp -p "$orig" "$dest_jpg"
    if command -v jpegoptim >/dev/null 2>&1; then
      # optimize in place
      jpegoptim --strip-all --max=85 "$dest_jpg" >/dev/null 2>&1 || true
    fi
  fi

  # Create WebP (prefer cwebp, fallback to convert)
  if command -v cwebp >/dev/null 2>&1; then
    cwebp -q 80 "$orig" -o "$dest_webp" >/dev/null 2>&1 || {
      echo "cwebp failed for $orig, trying convert"
      convert "$orig" -quality 80 "$dest_webp"
    }
  else
    if command -v convert >/dev/null 2>&1; then
      convert "$orig" -quality 80 "$dest_webp"
    else
      echo "No cwebp or convert available; cannot produce WebP for $orig" >&2
    fi
  fi
done

echo "Optimization complete. Originals are in assets/img/original/; webp in assets/img/webp/; jpg fallbacks in assets/img/jpg/."