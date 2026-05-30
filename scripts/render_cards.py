"""Render uniform 800x800 product cards: brand stripe + vector silhouette + name/model on white."""
import os
from PIL import Image, ImageDraw, ImageFont, ImageFilter

FONT_REG  = "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"
FONT_BOLD = "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"

W, H = 800, 800
STRIPE_H = 70
IMG_TOP, IMG_BOTTOM = STRIPE_H + 30, 600
IMG_CY = (IMG_TOP + IMG_BOTTOM) // 2
NAME_Y = 640
MODEL_Y = 712

BG = (255, 255, 255)
TEXT_DARK = (28, 32, 40)
TEXT_GREY = (110, 118, 130)
LINE = (62, 70, 84)
SHADE = (215, 220, 228)

BRAND_COLOR = {
    "Apple":     (28, 32, 40),
    "Asus":      (33, 56, 138),
    "Lenovo":    (228, 30, 38),
    "HP":        (0, 150, 214),
    "Dell":      (0, 125, 184),
    "Samsung":   (20, 40, 160),
    "Xiaomi":    (255, 103, 0),
    "Google":    (66, 133, 244),
    "Honor":     (201, 37, 43),
    "Sony":      (28, 32, 40),
    "JBL":       (255, 102, 0),
    "Bose":      (28, 32, 40),
    "LG":        (165, 0, 52),
    "Microsoft": (16, 124, 16),
    "Razer":     (68, 214, 44),
    "Acer":      (122, 187, 67),
    "Huawei":    (199, 0, 11),
    "Logitech":  (0, 178, 226),
}

def rrect(draw, box, r, fill=None, outline=None, width=1):
    draw.rounded_rectangle(box, radius=r, fill=fill, outline=outline, width=width)

def soft_shadow(canvas, cx, cy, rx, ry):
    layer = Image.new("RGBA", (W, H), (0,0,0,0))
    d = ImageDraw.Draw(layer)
    d.ellipse((cx-rx, cy-ry, cx+rx, cy+ry), fill=(0,0,0,55))
    layer = layer.filter(ImageFilter.GaussianBlur(18))
    canvas.alpha_composite(layer)

def draw_laptop(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY - 20
    sw, sh = 460, 280
    soft_shadow(canvas, cx, cy + sh//2 + 36, 320, 16)
    sx1, sy1 = cx - sw//2, cy - sh//2 - 30
    sx2, sy2 = cx + sw//2, cy + sh//2 - 30
    rrect(d, (sx1, sy1, sx2, sy2), 16, fill=(33, 38, 48), outline=LINE, width=2)
    rrect(d, (sx1+16, sy1+16, sx2-16, sy2-16), 8, fill=(22, 26, 34))
    d.ellipse((cx-3, sy1+8, cx+3, sy1+14), fill=(60,70,80))
    base_top = sy2 + 4
    base_bot = sy2 + 34
    base_w_top = sw + 20
    base_w_bot = sw + 80
    pts = [
        (cx - base_w_top//2, base_top),
        (cx + base_w_top//2, base_top),
        (cx + base_w_bot//2, base_bot),
        (cx - base_w_bot//2, base_bot),
    ]
    d.polygon(pts, fill=(220, 224, 232), outline=LINE)
    d.rectangle((cx-30, (base_top+base_bot)//2-1, cx+30, (base_top+base_bot)//2+1), fill=(180,186,196))

def draw_phone(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY - 20
    pw, ph = 200, 380
    soft_shadow(canvas, cx, cy + ph//2 + 16, 105, 14)
    bx1, by1 = cx - pw//2, cy - ph//2
    bx2, by2 = cx + pw//2, cy + ph//2
    rrect(d, (bx1, by1, bx2, by2), 36, fill=(33, 38, 48), outline=LINE, width=2)
    rrect(d, (bx1+9, by1+9, bx2-9, by2-9), 28, fill=(22, 26, 34))
    pill_w, pill_h = 78, 20
    rrect(d, (cx-pill_w//2, by1+24, cx+pill_w//2, by1+24+pill_h), 10, fill=(8,10,14))
    d.rectangle((bx2-1, cy-26, bx2+4, cy+34), fill=(60,66,78))
    d.rectangle((bx1-4, cy-46, bx1+1, cy-16), fill=(60,66,78))
    d.rectangle((bx1-4, cy-6, bx1+1, cy+24), fill=(60,66,78))

def draw_tablet(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY
    tw, th = 450, 340
    soft_shadow(canvas, cx, cy + th//2 + 18, 240, 16)
    bx1, by1 = cx - tw//2, cy - th//2
    bx2, by2 = cx + tw//2, cy + th//2
    rrect(d, (bx1, by1, bx2, by2), 24, fill=(33, 38, 48), outline=LINE, width=2)
    rrect(d, (bx1+12, by1+12, bx2-12, by2-12), 12, fill=(22, 26, 34))
    d.ellipse((cx-3, by1+4, cx+3, by1+10), fill=(70,75,85))

def draw_earbuds(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY + 10
    soft_shadow(canvas, cx, cy + 130, 170, 14)
    rrect(d, (cx-150, cy+20, cx+150, cy+160), 65, fill=(248,249,251), outline=LINE, width=2)
    rrect(d, (cx-150, cy+18, cx+150, cy+30), 4, fill=(225, 230, 238))
    for dx in (-80, 80):
        d.ellipse((cx+dx-36, cy-110, cx+dx+36, cy-40), fill=(248,249,251), outline=LINE, width=2)
        rrect(d, (cx+dx-12, cy-60, cx+dx+12, cy+22), 6, fill=(248,249,251), outline=LINE, width=2)
        d.ellipse((cx+dx-9, cy-88, cx+dx+9, cy-72), fill=(40,46,58))
        d.ellipse((cx+dx-4, cy+6, cx+dx+4, cy+18), fill=(180,186,196))

def draw_headphones(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY
    cup_y = cy + 30
    soft_shadow(canvas, cx, cup_y + 130, 230, 16)
    d.arc((cx-175, cy-200, cx+175, cy+120), start=180, end=360, fill=(33,38,48), width=30)
    d.arc((cx-160, cy-185, cx+160, cy+105), start=185, end=355, fill=(78,86,104), width=4)
    for dx in (-175, 175):
        d.rectangle((cx+dx-8, cup_y-110, cx+dx+8, cup_y-50), fill=(60,66,80))
        d.ellipse((cx+dx-92, cup_y-92, cx+dx+92, cup_y+92), fill=(33,38,48), outline=LINE, width=2)
        d.ellipse((cx+dx-66, cup_y-66, cx+dx+66, cup_y+66), fill=(54,60,74))
        d.ellipse((cx+dx-40, cup_y-40, cx+dx+40, cup_y+40), fill=(80,88,106))
        d.ellipse((cx+dx-9, cup_y-9, cx+dx+9, cup_y+9), fill=(150,158,172))

def draw_speaker(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY
    sw, sh = 440, 200
    soft_shadow(canvas, cx, cy + sh//2 + 22, 250, 16)
    rrect(d, (cx-sw//2, cy-sh//2, cx+sw//2, cy+sh//2), sh//2, fill=(33,38,48), outline=LINE, width=2)
    rrect(d, (cx-sw//2+14, cy-sh//2+14, cx+sw//2-14, cy+sh//2-14), (sh-28)//2, fill=(40,46,58))
    for ix in range(-5, 6):
        for iy in range(-2, 3):
            d.ellipse((cx+ix*34-4, cy+iy*34-4, cx+ix*34+4, cy+iy*34+4), fill=(86,94,110))
    rrect(d, (cx-60, cy-sh//2-16, cx+60, cy-sh//2+8), 11, fill=(50,56,70), outline=LINE)
    for bx in (-26, 0, 26):
        d.ellipse((cx+bx-6, cy-sh//2-10, cx+bx+6, cy-sh//2+2), fill=(150,158,172))

def draw_watch(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY
    body_w, body_h = 220, 260
    soft_shadow(canvas, cx, cy + body_h//2 + 16, 130, 14)
    d.rectangle((cx-75, cy-240, cx+75, cy-100), fill=(180,186,196), outline=LINE)
    d.rectangle((cx-75, cy+100, cx+75, cy+240), fill=(180,186,196), outline=LINE)
    rrect(d, (cx-body_w//2, cy-body_h//2, cx+body_w//2, cy+body_h//2), 54, fill=(33,38,48), outline=LINE, width=2)
    rrect(d, (cx-body_w//2+16, cy-body_h//2+16, cx+body_w//2-16, cy+body_h//2-16), 42, fill=(22,26,34))
    d.rectangle((cx+body_w//2-2, cy-22, cx+body_w//2+16, cy+22), fill=(170,176,186), outline=LINE)
    d.line((cx, cy, cx, cy-50), fill=(220,225,235), width=4)
    d.line((cx, cy, cx+34, cy+10), fill=(220,225,235), width=3)
    d.ellipse((cx-5, cy-5, cx+5, cy+5), fill=(220,225,235))

def draw_tv(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY - 30
    tvw, tvh = 560, 340
    soft_shadow(canvas, cx, cy + tvh//2 + 70, 340, 18)
    rrect(d, (cx-tvw//2, cy-tvh//2, cx+tvw//2, cy+tvh//2), 12, fill=(22,26,34), outline=LINE, width=2)
    rrect(d, (cx-tvw//2+10, cy-tvh//2+10, cx+tvw//2-10, cy+tvh//2-10), 4, fill=(38,44,56))
    d.polygon([(cx-100, cy+tvh//2), (cx+100, cy+tvh//2), (cx+50, cy+tvh//2+46), (cx-50, cy+tvh//2+46)], fill=(180,186,196), outline=LINE)
    d.rectangle((cx-160, cy+tvh//2+46, cx+160, cy+tvh//2+58), fill=(180,186,196), outline=LINE)

def draw_monitor(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY - 30
    mw, mh = 500, 320
    soft_shadow(canvas, cx, cy + mh//2 + 90, 240, 16)
    rrect(d, (cx-mw//2, cy-mh//2, cx+mw//2, cy+mh//2), 12, fill=(33,38,48), outline=LINE, width=2)
    rrect(d, (cx-mw//2+12, cy-mh//2+12, cx+mw//2-12, cy+mh//2-12), 6, fill=(22,26,34))
    d.rectangle((cx-26, cy+mh//2, cx+26, cy+mh//2+54), fill=(140,146,156), outline=LINE)
    d.polygon([(cx-140, cy+mh//2+86), (cx+140, cy+mh//2+86), (cx+100, cy+mh//2+54), (cx-100, cy+mh//2+54)], fill=(160,166,176), outline=LINE)

def draw_console_ps5(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY
    bw, bh = 260, 420
    soft_shadow(canvas, cx, cy + bh//2 + 16, 140, 14)
    pts_left = [(cx-bw//2, cy-bh//2+30), (cx-26, cy-bh//2-8), (cx-26, cy+bh//2), (cx-bw//2, cy+bh//2)]
    pts_right = [(cx+26, cy-bh//2-8), (cx+bw//2, cy-bh//2+30), (cx+bw//2, cy+bh//2), (cx+26, cy+bh//2)]
    d.polygon(pts_left, fill=(248,249,251), outline=LINE)
    d.polygon(pts_right, fill=(248,249,251), outline=LINE)
    rrect(d, (cx-26, cy-bh//2-8, cx+26, cy+bh//2), 4, fill=(33,38,48), outline=LINE)
    d.ellipse((cx-5, cy+bh//2-22, cx+5, cy+bh//2-12), fill=(80,86,98))
    d.ellipse((cx-130, cy+bh//2-12, cx+130, cy+bh//2+18), fill=(220,224,232), outline=LINE)

def draw_console_xbox(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY
    bw, bh = 300, 400
    soft_shadow(canvas, cx, cy + bh//2 + 14, 170, 14)
    rrect(d, (cx-bw//2, cy-bh//2, cx+bw//2, cy+bh//2), 14, fill=(33,38,48), outline=LINE, width=2)
    rrect(d, (cx-bw//2+18, cy-bh//2+18, cx+bw//2-18, cy-bh//2+82), 4, fill=(22,26,34))
    for i in range(8):
        d.ellipse((cx-bw//2+30+i*30-3, cy-bh//2+44-3, cx-bw//2+30+i*30+3, cy-bh//2+44+3), fill=(60,66,78))
    d.ellipse((cx-22, cy+bh//2-54, cx+22, cy+bh//2-12), outline=(140,146,156), width=3)
    d.line((cx-10, cy+bh//2-34, cx+10, cy+bh//2-34), fill=(140,146,156), width=3)
    d.line((cx, cy+bh//2-44, cx, cy+bh//2-24), fill=(140,146,156), width=3)

def draw_camera(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY
    bw, bh = 500, 300
    soft_shadow(canvas, cx, cy + bh//2 + 16, 260, 16)
    rrect(d, (cx-bw//2, cy-bh//2+50, cx+bw//2, cy+bh//2), 14, fill=(33,38,48), outline=LINE, width=2)
    rrect(d, (cx-66, cy-bh//2, cx+66, cy-bh//2+80), 8, fill=(33,38,48), outline=LINE, width=2)
    d.rectangle((cx-28, cy-bh//2-10, cx+28, cy-bh//2), fill=(80,86,98))
    d.ellipse((cx-110, cy-20, cx+110, cy+200), fill=(22,26,34), outline=LINE, width=2)
    d.ellipse((cx-82, cy+6, cx+82, cy+172), fill=(40,44,55), outline=LINE, width=2)
    d.ellipse((cx-50, cy+38, cx+50, cy+140), fill=(8,10,14))
    rrect(d, (cx+bw//2-30, cy-bh//2+50, cx+bw//2+8, cy+bh//2), 14, fill=(50,55,68), outline=LINE)
    d.ellipse((cx+bw//2-46, cy-bh//2+34, cx+bw//2-22, cy-bh//2+58), fill=(180,186,196), outline=LINE)

def draw_controller(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY
    body = (248, 249, 251)
    soft_shadow(canvas, cx, cy + 150, 230, 16)
    rrect(d, (cx-185, cy-30, cx-95, cy+150), 46, fill=body, outline=LINE, width=2)
    rrect(d, (cx+95, cy-30, cx+185, cy+150), 46, fill=body, outline=LINE, width=2)
    rrect(d, (cx-160, cy-90, cx+160, cy+70), 50, fill=body, outline=LINE, width=2)
    rrect(d, (cx-158, cy-12, cx+158, cy+70), 0, fill=body)
    rrect(d, (cx-42, cy-78, cx+42, cy-40), 8, fill=(214,219,228), outline=LINE)
    px, py = cx-95, cy-2
    d.rectangle((px-9, py-26, px+9, py+26), fill=(54,60,74))
    d.rectangle((px-26, py-9, px+26, py+9), fill=(54,60,74))
    bx0, by0 = cx+95, cy-2
    for dxb, dyb, col in [(0,-28,(80,90,180)),(28,0,(220,40,52)),(-28,0,(176,84,160)),(0,28,(70,170,205))]:
        d.ellipse((bx0+dxb-13, by0+dyb-13, bx0+dxb+13, by0+dyb+13), fill=col, outline=(255,255,255), width=2)
    for sx in (cx-46, cx+46):
        d.ellipse((sx-32, cy+44, sx+32, cy+108), fill=(50,56,70), outline=LINE)
        d.ellipse((sx-22, cy+54, sx+22, cy+98), fill=(28,32,42))
    rrect(d, (cx-14, cy+6, cx+14, cy+26), 4, fill=(150,158,172))

DRAWERS = {
    "laptop":   draw_laptop,
    "phone":    draw_phone,
    "tablet":   draw_tablet,
    "earbuds":  draw_earbuds,
    "headphones": draw_headphones,
    "speaker":  draw_speaker,
    "watch":    draw_watch,
    "tv":       draw_tv,
    "monitor":  draw_monitor,
    "ps5":      draw_console_ps5,
    "xbox":     draw_console_xbox,
    "camera":   draw_camera,
    "controller": draw_controller,
}

PRODUCTS = [
    (1,  "Apple",     "MacBook Pro 14",      "M3 · 2024",     "laptop"),
    (2,  "Asus",      "ROG Strix G15",       "G513RC",        "laptop"),
    (3,  "Lenovo",    "ThinkPad X1 Carbon",  "Gen 11",        "laptop"),
    (4,  "HP",        "Pavilion 15",         "15-eg2055ci",   "laptop"),
    (5,  "Dell",      "XPS 13",              "9340",          "laptop"),
    (6,  "Apple",     "iPhone 15 Pro",       "A2848",         "phone"),
    (7,  "Samsung",   "Galaxy S24 Ultra",    "SM-S928B",      "phone"),
    (8,  "Xiaomi",    "14 Pro",              "23116PN5BR",    "phone"),
    (9,  "Google",    "Pixel 8",             "GZPF0",         "phone"),
    (10, "Honor",     "Magic 6 Pro",         "BVL-N49",       "phone"),
    (11, "Apple",     "iPad Air 11",         "M2 · 2024",     "tablet"),
    (12, "Samsung",   "Galaxy Tab S9",       "SM-X710",       "tablet"),
    (13, "Xiaomi",    "Pad 6",               "23043RP34G",    "tablet"),
    (14, "Apple",     "AirPods Pro 2",       "USB-C",         "earbuds"),
    (15, "Sony",      "WH-1000XM5",          "WH1000XM5/B",   "headphones"),
    (16, "JBL",       "Tune 770NC",          "JBLT770NCBLK",  "headphones"),
    (17, "JBL",       "Charge 5",            "JBLCHARGE5BLK", "speaker"),
    (18, "Bose",      "SoundLink Flex",      "SLF",           "speaker"),
    (19, "Apple",     "Watch Series 9",      "GPS 45mm",      "watch"),
    (20, "Samsung",   "Galaxy Watch 6",      "SM-R940",       "watch"),
    (21, "LG",        "OLED55C3",            "OLED55C3RLA",   "tv"),
    (22, "Samsung",   "Neo QLED QN85C",      "QE65QN85CAU",   "tv"),
    (23, "LG",        "UltraGear 27GP850",   "27GP850-B",     "monitor"),
    (24, "Asus",      "TUF Gaming VG27AQ",   "VG27AQ",        "monitor"),
    (25, "Sony",      "PlayStation 5 Slim",  "CFI-2000",      "ps5"),
    (26, "Microsoft", "Xbox Series X",       "RRT-00010",     "xbox"),
    (27, "Sony",      "Alpha A7 IV",         "ILCE-7M4",      "camera"),
    (28, "Sony",      "DualSense Edge",      "CFI-ZCP1W",     "controller"),
]

def fit_text(draw, text, max_width, font_path, max_size, min_size=18):
    size = max_size
    while size >= min_size:
        f = ImageFont.truetype(font_path, size)
        w = draw.textlength(text, font=f)
        if w <= max_width:
            return f
        size -= 2
    return ImageFont.truetype(font_path, min_size)

def render_card(pid, brand, name, model, kind):
    canvas = Image.new("RGBA", (W, H), (255,255,255,255))
    d = ImageDraw.Draw(canvas)
    color = BRAND_COLOR.get(brand, (28,32,40))
    d.rectangle((0, 0, W, STRIPE_H), fill=color + (255,))
    f_brand = ImageFont.truetype(FONT_BOLD, 30)
    brand_text = brand.upper()
    d.text((40, (STRIPE_H - 30)//2 - 2), brand_text, font=f_brand, fill=(255,255,255))
    f_meta = ImageFont.truetype(FONT_REG, 17)
    meta_text = "Digital Store"
    mw = d.textlength(meta_text, font=f_meta)
    d.text((W - 40 - mw, (STRIPE_H - 17)//2 - 1), meta_text, font=f_meta, fill=(255,255,255,180))
    d.rectangle((0, STRIPE_H, W, STRIPE_H+2), fill=tuple(min(255, int(c*1.25)) for c in color))
    drawer = DRAWERS.get(kind, draw_phone)
    drawer(canvas)
    d.rectangle((0, NAME_Y - 20, W, NAME_Y - 19), fill=(238, 240, 245))
    f_name = fit_text(d, name, W - 80, FONT_BOLD, 50, 28)
    nw = d.textlength(name, font=f_name)
    d.text(((W - nw)//2, NAME_Y), name, font=f_name, fill=TEXT_DARK)
    f_model = ImageFont.truetype(FONT_REG, 24)
    mw2 = d.textlength(model, font=f_model)
    d.text(((W - mw2)//2, MODEL_Y), model, font=f_model, fill=TEXT_GREY)
    out = canvas.convert("RGB")
    path = f"/tmp/cards/p{pid}.jpg"
    out.save(path, "JPEG", quality=88, optimize=True)
    return path

def main():
    os.makedirs("/tmp/cards", exist_ok=True)
    for p in PRODUCTS:
        pid, brand, name, model, kind = p
        path = render_card(pid, brand, name, model, kind)
        print(f"#{pid:2d} {path}")

if __name__ == "__main__":
    main()
