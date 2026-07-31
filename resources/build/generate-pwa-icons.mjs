// One-off/reproducible generator for the PWA icon set. No image libraries
// are available in this environment, so this builds raw PNG bytes by hand
// (zlib for the DEFLATE stream is Node's built-in). Re-run with `node
// resources/build/generate-pwa-icons.mjs` any time the brand mark changes.
import { deflateSync, crc32 as zCrc32 } from 'node:zlib';
import { writeFileSync, mkdirSync } from 'node:fs';

const OUT_DIR = new URL('../../public/icons/', import.meta.url);
mkdirSync(OUT_DIR, { recursive: true });

const BLUE = [37, 99, 235]; // matches the app's blue-600 brand accent
const WHITE = [255, 255, 255];
const ORANGE = [249, 115, 22]; // matches the "Gen" wordmark accent

function roundedRectMask(x, y, w, h, r, px, py) {
    const cx = Math.min(Math.max(px, x + r), x + w - r);
    const cy = Math.min(Math.max(py, y + r), y + h - r);
    const dx = px - cx;
    const dy = py - cy;
    if (px < x + r && py < y + r) return dx * dx + dy * dy <= r * r;
    if (px > x + w - r && py < y + r) return dx * dx + dy * dy <= r * r;
    if (px < x + r && py > y + h - r) return dx * dx + dy * dy <= r * r;
    if (px > x + w - r && py > y + h - r) return dx * dx + dy * dy <= r * r;
    return px >= x && px <= x + w && py >= y && py <= y + h;
}

function segDist(px, py, x1, y1, x2, y2) {
    const dx = x2 - x1;
    const dy = y2 - y1;
    const len2 = dx * dx + dy * dy;
    let t = len2 === 0 ? 0 : ((px - x1) * dx + (py - y1) * dy) / len2;
    t = Math.max(0, Math.min(1, t));
    const cx = x1 + t * dx;
    const cy = y1 + t * dy;
    return Math.hypot(px - cx, py - cy);
}

function makeIcon(size, { maskable = false } = {}) {
    const buf = new Uint8ClampedArray(size * size * 4);
    const set = (x, y, [r, g, b], a = 255) => {
        if (x < 0 || y < 0 || x >= size || y >= size) return;
        const i = (y * size + x) * 4;
        buf[i] = r; buf[i + 1] = g; buf[i + 2] = b; buf[i + 3] = a;
    };

    // Background: full bleed for maskable icons (safe-zone padding handles
    // the mask), gently rounded for standalone "any" icons.
    const bgRadius = maskable ? 0 : size * 0.22;
    for (let y = 0; y < size; y++) {
        for (let x = 0; x < size; x++) {
            if (maskable || roundedRectMask(0, 0, size, size, bgRadius, x + 0.5, y + 0.5)) {
                set(x, y, BLUE);
            }
        }
    }

    // Glyph sits inside the maskable "safe zone" (inner ~80% circle) and is
    // otherwise centered with generous padding.
    const scale = maskable ? 0.62 : 0.72;
    const gw = size * scale * 0.62;
    const gh = size * scale;
    const gx = (size - gw) / 2;
    const gy = (size - gh) / 2;
    const gr = gw * 0.14;

    // Receipt paper.
    for (let y = Math.floor(gy); y < Math.ceil(gy + gh); y++) {
        for (let x = Math.floor(gx); x < Math.ceil(gx + gw); x++) {
            if (roundedRectMask(gx, gy, gw, gh, gr, x + 0.5, y + 0.5)) {
                set(x, y, WHITE);
            }
        }
    }

    // Three text bars.
    const barH = Math.max(1, gh * 0.07);
    const barInsetX = gw * 0.18;
    const barW = gw - barInsetX * 2;
    [0.22, 0.38, 0.54].forEach((t) => {
        const by = gy + gh * t;
        for (let y = Math.floor(by); y < Math.ceil(by + barH); y++) {
            for (let x = Math.floor(gx + barInsetX); x < Math.ceil(gx + barInsetX + barW); x++) {
                set(x, y, BLUE, 200);
            }
        }
    });

    // Orange "paid" badge with a white checkmark, bottom-right of the paper.
    const badgeR = gw * 0.34;
    const bcx = gx + gw * 0.86;
    const bcy = gy + gh * 0.86;
    for (let y = Math.floor(bcy - badgeR - 2); y < Math.ceil(bcy + badgeR + 2); y++) {
        for (let x = Math.floor(bcx - badgeR - 2); x < Math.ceil(bcx + badgeR + 2); x++) {
            if (Math.hypot(x + 0.5 - bcx, y + 0.5 - bcy) <= badgeR) {
                set(x, y, ORANGE);
            }
        }
    }
    const checkR = badgeR * 0.16;
    const p1 = [bcx - badgeR * 0.42, bcy];
    const p2 = [bcx - badgeR * 0.1, bcy + badgeR * 0.32];
    const p3 = [bcx + badgeR * 0.45, bcy - badgeR * 0.35];
    for (let y = Math.floor(bcy - badgeR); y < Math.ceil(bcy + badgeR); y++) {
        for (let x = Math.floor(bcx - badgeR); x < Math.ceil(bcx + badgeR); x++) {
            const d = Math.min(
                segDist(x + 0.5, y + 0.5, p1[0], p1[1], p2[0], p2[1]),
                segDist(x + 0.5, y + 0.5, p2[0], p2[1], p3[0], p3[1]),
            );
            if (d <= checkR) set(x, y, WHITE);
        }
    }

    return buf;
}

function chunk(type, data) {
    const typeBuf = Buffer.from(type, 'ascii');
    const lenBuf = Buffer.alloc(4);
    lenBuf.writeUInt32BE(data.length, 0);
    const crcInput = Buffer.concat([typeBuf, data]);
    const crcBuf = Buffer.alloc(4);
    crcBuf.writeUInt32BE(zCrc32(crcInput) >>> 0, 0);
    return Buffer.concat([lenBuf, typeBuf, data, crcBuf]);
}

function encodePNG(size, rgba) {
    const sig = Buffer.from([137, 80, 78, 71, 13, 10, 26, 10]);
    const ihdr = Buffer.alloc(13);
    ihdr.writeUInt32BE(size, 0);
    ihdr.writeUInt32BE(size, 4);
    ihdr[8] = 8; // bit depth
    ihdr[9] = 6; // color type RGBA
    ihdr[10] = 0; ihdr[11] = 0; ihdr[12] = 0;

    const raw = Buffer.alloc(size * (size * 4 + 1));
    for (let y = 0; y < size; y++) {
        raw[y * (size * 4 + 1)] = 0; // no filter
        Buffer.from(rgba.buffer, y * size * 4, size * 4).copy(raw, y * (size * 4 + 1) + 1);
    }
    const idat = deflateSync(raw);

    return Buffer.concat([
        sig,
        chunk('IHDR', ihdr),
        chunk('IDAT', idat),
        chunk('IEND', Buffer.alloc(0)),
    ]);
}

function build(size, opts, filename) {
    const rgba = makeIcon(size, opts);
    const png = encodePNG(size, rgba);
    writeFileSync(new URL(filename, OUT_DIR), png);
    console.log(`wrote ${filename} (${png.length} bytes)`);
}

build(192, {}, 'icon-192.png');
build(512, {}, 'icon-512.png');
build(512, { maskable: true }, 'icon-512-maskable.png');
build(180, {}, 'apple-touch-icon.png');
