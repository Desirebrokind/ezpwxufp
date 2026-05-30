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
    cx, cy = W//2, IMG_CY
    pw, ph = 210, 420
    soft_shadow(canvas, cx, cy + ph//2 + 16, 110, 14)
    bx1, by1 = cx - pw//2, cy - ph//2
    bx2, by2 = cx + pw//2, cy + ph//2
    rrect(d, (bx1, by1, bx2, by2), 34, fill=(33, 38, 48), outline=LINE, width=2)
    rrect(d, (bx1+8, by1+8, bx2-8, by2-8), 28, fill=(22, 26, 34))
    pill_w, pill_h = 80, 20
    rrect(d, (cx-pill_w//2, by1+22, cx+pill_w//2, by1+22+pill_h), 10, fill=(8,10,14))
    d.rectangle((bx2-2, cy-30, bx2+3, cy+30), fill=(60,66,78))
    d.rectangle((bx1-3, cy-50, bx1+2, cy-20), fill=(60,66,78))
    d.rectangle((bx1-3, cy-10, bx1+2, cy+20), fill=(60,66,78))

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
    cx, cy = W//2, IMG_CY - 30
    soft_shadow(canvas, cx, cy+200, 250, 18)
    d.arc((cx-240, cy-220, cx+240, cy+140), start=200, end=340, fill=(33,38,48), width=26)
    d.arc((cx-225, cy-205, cx+225, cy+125), start=205, end=335, fill=(80,90,108), width=3)
    for dx in (-200, 200):
        d.rectangle((cx+dx-7, cy-70, cx+dx+7, cy-10), fill=(60,65,78))
        d.ellipse((cx+dx-100, cy-20, cx+dx+100, cy+200), fill=(33,38,48), outline=LINE, width=2)
        d.ellipse((cx+dx-72, cy+12, cx+dx+72, cy+170), fill=(58,64,78))
        d.ellipse((cx+dx-45, cy+34, cx+dx+45, cy+148), fill=(82,90,108))
        d.ellipse((cx+dx-5, cy+82, cx+dx+5, cy+92), fill=(180,186,196))

def draw_speaker(canvas):
    d = ImageDraw.Draw(canvas)
    cx, cy = W//2, IMG_CY
    sw, sh = 500, 220
    soft_shadow(canvas, cx, cy + sh//2 + 22, 280, 16)
    rrect(d, (cx-sw//2, cy-sh//2, cx+sw//2, cy+sh//2), 100, fill=(33,38,48), outline=LINE, width=2)
    for ix in range(-6, 7):
        for iy in range(-2, 3):
            d.ellipse((cx+ix*32-4, cy+iy*32-4, cx+ix*32+4, cy+iy*32+4), fill=(78,86,100))
    d.ellipse((cx-sw//2-4, cy-sh//2+12, cx-sw//2+18, cy+sh//2-12), fill=(50,55,68), outline=LINE)
    d.ellipse((cx+sw//2-18, cy-sh//2+12, cx+sw//2+4, cy+sh//2-12), fill=(50,55,68), outline=LINE)
    d.arc((cx-sw//2-90, cy-30, cx-sw//2+20, cy+90), start=90, end=270, fill=LINE, width=5)

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
    body_top = cy - 80
    body_bot = cy + 40
    pl = [
        (cx-90, body_top),
        (cx-220, body_top+30),
        (cx-275, body_top+100),
        (cx-260, body_bot+50),
        (cx-200, body_bot+100),
        (cx-110, body_bot+50),
        (cx-50, body_bot),
    ]
    pr = [
        (cx+90, body_top),
        (cx+220, body_top+30),
        (cx+275, body_top+100),
        (cx+260, body_bot+50),
        (cx+200, body_bot+100),
        (cx+110, body_bot+50),
        (cx+50, body_bot),
    ]
    d.polygon(pl, fill=(248,249,251), outline=LINE)
    d.polygon(pr, fill=(248,249,251), outline=LINE)
    d.polygon([(cx-90, body_top), (cx+90, body_top), (cx+50, body_bot), (cx-50, body_bot)], fill=(248,249,251))
    d.line([(cx-90, body_top), (cx+90, body_top)], fill=LINE, width=2)
    d.line([(cx-50, body_bot), (cx+50, body_bot)], fill=LINE, width=2)
    rrect(d, (cx-60, body_top+16, cx+60, body_top+46), 8, fill=(220,224,232), outline=LINE)
    dx0, dy0 = cx-160, body_top+80
    d.rectangle((dx0-10, dy0-30, dx0+10, dy0+30), fill=(50,55,68))
    d.rectangle((dx0-30, dy0-10, dx0+30, dy0+10), fill=(50,55,68))
    bx0, by0 = cx+160, body_top+80
    btns = [(0, -30, (80,90,180)),
            (30, 0,  (220,30,40)),
            (-30, 0, (180,80,160)),
            (0, 30,  (80,170,200))]
    for dxb, dyb, col in btns:
        d.ellipse((bx0+dxb-13, by0+dyb-13, bx0+dxb+13, by0+dyb+13), fill=col, outline=(255,255,255), width=2)
    d.ellipse((cx-95, body_bot+30, cx-35, body_bot+90), fill=(50,55,68))
    d.ellipse((cx-83, body_bot+42, cx-47, body_bot+78), fill=(33,38,48))
    d.ellipse((cx+35, body_bot+30, cx+95, body_bot+90), fill=(50,55,68))
    d.ellipse((cx+47, body_bot+42, cx+83, body_bot+78), fill=(33,38,48))
    rrect(d, (cx-16, body_bot+8, cx+16, body_bot+26), 4, fill=(140,146,156))

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
