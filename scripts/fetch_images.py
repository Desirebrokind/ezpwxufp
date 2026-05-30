#!/usr/bin/env python3
"""Fetch real product images from Wikimedia Commons (via Special:FilePath) and store as JPEG."""
import io
import os
import sys
import time
import urllib.parse
import requests
from PIL import Image

# Map product id -> Wikimedia Commons filename (without "File:" prefix)
COMMONS_FILES = {
    1: "MacBook_Pro_16_(M1_Pro,_2021)_-_Wikipedia.jpg",
    2: "ROG_Phone_6_display_model.png",
    3: "Lenovo_ThinkPad_X1_Ultrabook.jpg",
    4: "HP_Pavilion_15-N028eg.jpg",
    5: "DELL_XPS_13_and_15_(37080596413).jpg",
    6: "IPhone_15_Pro_Vector.svg",
    7: "Samsung_Galaxy_S24,_Sperrbildschirm.JPG",
    8: "23116PN5BC.jpg",
    9: "Pixel_8_Obsidian.png",
    10: "Honor_Magic_6_Pro.jpg",
    11: "About_iPad_Air_13-inch_(M2).jpg",
    12: "Samsung_Galaxy_Tab_S9.png",
    13: "Xiaomi_Pad_6_display.jpg",
    14: "AirPods_Pro_3_with_case.jpg",
    15: "Sony_WH-1000XM5_Silver.png",
    16: "JBL_Tune_510BT.png",
    17: "JBL_Charge_4.png",
    18: "Bose_SoundLink_Flex_speaker.jpg",
    19: "Apple_Watch_Series_10.jpg",
    20: "Samsung_Galaxy_Watch_6.jpg",
    21: "OEL_right.JPG",
    22: "QD_S.jpg",
    23: "MonitorLCDlcd.svg",
    24: "ASUS_TUF_Gaming_VG279QM.jpg",
    25: "Black_and_white_Playstation_5_base_edition_with_controller.png",
    26: "Xbox_Series_X_S_color.svg",
    27: "Sony_A7_IV_(ILCE-7M4)_-_by_Henry_Söderlund_(51739988735).jpg",
    28: "Sony-PlayStation-DualSense.png",
}

# Fallback titles (general categories) if specific file missing
FALLBACK_TITLES = {
    1: ["MacBook_Air_M2.png", "MacBook_Pro_15_Touch_Bar.jpg"],
    2: ["Asus_ROG.svg", "Asus_logo.svg"],
    3: ["Lenovo_logo_2015.svg"],
    4: ["HP_logo_2012.svg"],
    5: ["Dell_Logo.svg"],
    7: ["Samsung_Galaxy_S24_Ultra.jpg", "Samsung_Galaxy_S22_Ultra.jpg"],
    8: ["Xiaomi_logo_(2021-).svg"],
    9: ["Google_Pixel_8.jpg"],
    10: ["Honor_logo.svg"],
    11: ["IPad_Air_M2_Space_Gray.png", "IPad_Pro_2018_3rd_generation.png"],
    12: ["Samsung_Galaxy_Tab_S8.jpg"],
    13: ["Xiaomi_Pad_5.jpg"],
    14: ["AirPods_Pro_charging_case_open.svg", "AirPods_2.svg"],
    15: ["Sony_logo.svg"],
    16: ["JBL_logo.svg"],
    17: ["JBL_logo.svg"],
    18: ["Bose_logo.svg"],
    19: ["Apple_Watch_S9.svg", "Apple_Watch_Series_8.png"],
    20: ["Samsung_Galaxy_Watch.jpg", "Samsung_logo_blue.svg"],
    21: ["OLED_TV.jpg", "LG_logo_(2015).svg"],
    22: ["Samsung_QLED_TV.jpg", "Samsung_Logo.svg"],
    23: ["LG_logo_(2015).svg"],
    24: ["ASUS_Logo.svg"],
    25: ["PlayStation_logo.svg"],
    26: ["Xbox_logo_2019.svg", "Xbox_one_x_console.jpg"],
    27: ["Sony_Alpha_ILCE-7_(A7)_full-frame_camera_no_body_cap.jpg"],
    28: ["DualSense.png"],
}

HEADERS = {
    "User-Agent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Accept": "image/avif,image/webp,image/png,image/jpeg,*/*",
    "Accept-Language": "en-US,en;q=0.9",
    "Referer": "https://en.wikipedia.org/",
}


def fetch_commons(filename: str) -> bytes | None:
    enc = urllib.parse.quote(filename, safe="")
    url = f"https://commons.wikimedia.org/wiki/Special:FilePath/{enc}?width=800"
    try:
        r = requests.get(url, headers=HEADERS, timeout=20, allow_redirects=True)
        if r.status_code == 200 and len(r.content) > 2000 and "html" not in (r.headers.get("content-type") or ""):
            return r.content
        print(f"   http {r.status_code} len {len(r.content)} ct={r.headers.get('content-type')}", file=sys.stderr)
    except Exception as e:
        print(f"   err: {e}", file=sys.stderr)
    return None


def process_image(raw: bytes) -> bytes:
    img = Image.open(io.BytesIO(raw))
    if img.mode != "RGBA":
        img = img.convert("RGBA")
    bbox = img.getbbox()
    if bbox:
        img = img.crop(bbox)
    max_dim = 720
    w, h = img.size
    if w == 0 or h == 0:
        raise ValueError("empty image")
    scale = min(max_dim / w, max_dim / h)
    new_w, new_h = max(1, int(w * scale)), max(1, int(h * scale))
    img = img.resize((new_w, new_h), Image.LANCZOS)
    canvas = Image.new("RGB", (800, 800), (255, 255, 255))
    canvas.paste(img, ((800 - new_w) // 2, (800 - new_h) // 2), img)
    out = io.BytesIO()
    canvas.save(out, "JPEG", quality=88, optimize=True)
    return out.getvalue()


def main():
    out_dir = "/home/ubuntu/repos/store/scripts/images"
    os.makedirs(out_dir, exist_ok=True)

    for pid in range(1, 29):
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
            time.sleep(1.0)

        if not raw:
            print(f"#{pid}: SKIPPED (no image found)", flush=True)
            continue

        try:
            jpeg = process_image(raw)
        except Exception as e:
            print(f"#{pid}: process error: {e}", flush=True)
            continue

        path = f"{out_dir}/product_{pid:02d}.jpg"
        with open(path, "wb") as f:
            f.write(jpeg)
        print(f"#{pid}: SAVED ({chosen}) -> {len(jpeg)} bytes", flush=True)
        time.sleep(1.2)


if __name__ == "__main__":
    main()
