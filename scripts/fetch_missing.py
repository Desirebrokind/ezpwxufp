#!/usr/bin/env python3
"""Fetch only the missing product images."""
import io
import os
import sys
import time
import urllib.parse
import requests
from PIL import Image

from fetch_images import COMMONS_FILES, FALLBACK_TITLES, HEADERS, fetch_commons, process_image

OUT_DIR = "/home/ubuntu/repos/store/scripts/images"

# Identify missing ones
missing = [pid for pid in range(1, 29) if not os.path.exists(f"{OUT_DIR}/product_{pid:02d}.jpg")]
print(f"Missing: {missing}", flush=True)

# Long initial wait to clear any rate limit
print("Waiting 90s to clear rate limits...", flush=True)
time.sleep(90)

for pid in missing:
    candidates = []
    if pid in COMMONS_FILES:
        candidates.append(COMMONS_FILES[pid])
    candidates.extend(FALLBACK_TITLES.get(pid, []))

    raw = None
    chosen = None
    for fname in candidates:
        print(f"#{pid}: try '{fname}'", flush=True)
        raw = fetch_commons(fname)
        if raw:
            chosen = fname
            break
        time.sleep(3.0)

    if not raw:
        print(f"#{pid}: SKIPPED", flush=True)
        continue
    try:
        jpeg = process_image(raw)
    except Exception as e:
        print(f"#{pid}: process err: {e}", flush=True)
        continue
    path = f"{OUT_DIR}/product_{pid:02d}.jpg"
    with open(path, "wb") as f:
        f.write(jpeg)
    print(f"#{pid}: SAVED ({chosen}) -> {len(jpeg)} bytes", flush=True)
    time.sleep(3.0)

print("Done.", flush=True)
