# Image Optimization Guide

This repository's Phase 2 image optimization guide and scripts.

Goal
- Move originals to `assets/img/original/`
- Generate optimized WebP versions in `assets/img/webp/`
- Generate JPEG fallbacks in `assets/img/jpg/`
- Update hero/carousel images in templates to use `<picture>` blocks

Prereqs
- Linux/macOS (or CI runner) with these tools installed:
  - cwebp (from libwebp)
  - ImageMagick (convert)
  - jpegoptim (optional) or mozjpeg (cjpeg)

On macOS: brew install webp imagemagick jpegoptim

Files created by this guide
- scripts/optimize_images.sh — main batch script to move originals and generate webp/jpg
- scripts/generate_size_report.sh — captures before/after sizes and produces CSV

How to run (safe workflow)
1) Capture BEFORE sizes

```bash
mkdir -p tmp
echo "path,size" > tmp/before_sizes.csv
find assets -type f \( -iname '*.png' -o -iname '*.jpg' -o -iname '*.jpeg' \) -print0 \
  | while IFS= read -r -d '' file; do
      echo "\"$file\",$(stat -c%s "$file")" >> tmp/before_sizes.csv
    done
```

2) Make a backup (optional)

```bash
# Optional: create a tarball backup of assets
tar -czf assets-backup-$(date +%Y%m%d).tgz assets/
```

3) Run the batch script (scripts/optimize_images.sh)

```bash
bash scripts/optimize_images.sh
```

This will:
- Ensure `assets/img/original/`, `assets/img/webp/`, and `assets/img/jpg/` exist
- Copy files from `assets/` into `assets/img/` if they don't already exist
- Move originals into `assets/img/original/`
- Produce `assets/img/webp/<name>.webp` and `assets/img/jpg/<name>.jpg`

4) Capture AFTER sizes and create savings report

Run `scripts/generate_size_report.sh` (or follow the commands below):

```bash
# After sizes
echo "path,size" > tmp/after_sizes.csv
find assets/img -type f \( -iname '*.webp' -o -iname '*.jpg' -o -iname '*.jpeg' \) -print0 \
  | while IFS= read -r -d '' file; do
      echo "\"$file\",$(stat -c%s "$file")" >> tmp/after_sizes.csv
    done

# Create savings.csv
echo "original,after_file,orig_size,after_size,savings_bytes,savings_pct" > tmp/savings.csv
while IFS=, read -r path size; do
  f=$(basename "$path")
  base="${f%.*}"
  # prefer webp
  if [ -e "assets/img/webp/${base}.webp" ]; then
    after="assets/img/webp/${base}.webp"
  elif [ -e "assets/img/jpg/${base}.jpg" ]; then
    after="assets/img/jpg/${base}.jpg"
  else
    after=""
  fi
  if [ -n "$after" ]; then
    after_size=$(stat -c%s "$after")
    savings=$((size - after_size))
    pct=$(awk -v s="$savings" -v o="$size" 'BEGIN{ if (o>0) printf("%.1f", (s/o)*100); else print "0" }')
    echo "\"$path\",\"$after\",$size,$after_size,$savings,$pct" >> tmp/savings.csv
  fi
done < tmp/before_sizes.csv

column -s, -t < tmp/savings.csv | less -S
```

Picture markup pattern (hero/carousel)

Use this pattern for hero / carousel images. Replace `Sabbath` with the base filename, matching the files you generate.

```html
<picture>
  <source type="image/webp" srcset="assets/img/webp/Sabbath.webp">
  <source type="image/jpeg" srcset="assets/img/jpg/Sabbath.jpg">
  <img src="assets/img/original/Sabbath.png" class="d-block w-100" alt="Worship">
</picture>
```

Notes
- Keep `alt` attributes on the inner `<img>` for accessibility.
- For the first hero/slide (LCP), avoid `loading="lazy"` to prioritize LCP. For subsequent slides, add `loading="lazy"` to the `<img>`.
- If you prefer the optimized files directly under `assets/img/` (no webp/jpg subfolders), adjust the paths accordingly.

What I changed in this repo
- I added the guide (this file) and the scripts that implement the workflow. I also updated the hero/carousel templates (index.php and about.php) to use the `<picture>` pattern. After you run the script locally, run the size capture commands and paste `tmp/savings.csv` into your PR.

Manual checks to perform after running scripts
- Visual quality of optimized images on mobile and desktop (spot-check hero images)
- Carousel behavior and LCP (first slide should load quickly)
- No broken paths: run `grep -R "assets/img/" .`
- Favicon and logos display correctly

