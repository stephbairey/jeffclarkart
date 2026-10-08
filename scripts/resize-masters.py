#!/usr/bin/env python3
"""Resize painting masters to web-ingest size before uploading to WordPress.

Masters are ~9000px, 30-66 MB. WordPress would scale them to 2560 on upload
anyway, and shared-host Imagick may run out of memory on the way. So: 3000px
long edge, sRGB, quality 88, EXIF stripped, filenames preserved.

Usage: resize-masters.py SRC_DIR OUT_DIR [--max 3000]
Run with the venv at ~/jca-images/.venv (has Pillow).
"""
import argparse
import sys
from pathlib import Path

from PIL import Image, ImageCms, ImageOps

Image.MAX_IMAGE_PIXELS = None  # masters are 9000x9000; silence the bomb check


def to_srgb(im: Image.Image) -> Image.Image:
    icc = im.info.get("icc_profile")
    if icc:
        try:
            src = ImageCms.ImageCmsProfile(__import__("io").BytesIO(icc))
            dst = ImageCms.createProfile("sRGB")
            return ImageCms.profileToProfile(im, src, dst, outputMode="RGB")
        except Exception as e:  # noqa: BLE001
            print(f"  (icc convert failed, using as-is: {e})", file=sys.stderr)
    return im.convert("RGB")


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("src")
    ap.add_argument("out")
    ap.add_argument("--max", type=int, default=3000)
    ap.add_argument("--quality", type=int, default=88)
    a = ap.parse_args()

    src, out = Path(a.src), Path(a.out)
    out.mkdir(parents=True, exist_ok=True)
    files = sorted(p for p in src.iterdir() if p.suffix.lower() in {".jpg", ".jpeg", ".png", ".tif", ".tiff"})
    if not files:
        print("no images found", file=sys.stderr)
        return 1

    for p in files:
        dest = out / (p.stem + ".jpg")
        if dest.exists() and dest.stat().st_mtime >= p.stat().st_mtime:
            print(f"skip {p.name}")
            continue
        with Image.open(p) as im:
            im = ImageOps.exif_transpose(im)
            w, h = im.size
            im = to_srgb(im)
            scale = a.max / max(w, h)
            if scale < 1:
                im = im.resize((round(w * scale), round(h * scale)), Image.LANCZOS)
            im.save(dest, "JPEG", quality=a.quality, optimize=True, progressive=True)
        print(f"{p.name}: {w}x{h} -> {im.size[0]}x{im.size[1]}  {dest.stat().st_size // 1024} KB")
    return 0


if __name__ == "__main__":
    sys.exit(main())
