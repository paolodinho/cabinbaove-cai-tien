"""Hop nhat cabinbaove.vn -> cabinbaove.com.vn: tao bang 301 + danh sach bai can chuyen sang."""
import json, os, re, html, unicodedata, urllib.request
from concurrent.futures import ThreadPoolExecutor
ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
D = os.path.join(ROOT, 'tham-khao/data')
it = json.load(open(f'{D}/import.json'))
vn = json.load(open(f'{D}/cabinbaove-vn.json'))
rows = [l.split(' ', 1) for l in open(f'{D}/vn-sitemap.txt').read().split('\n') if l]
STOP = {'cabin','bảo','vệ','bốt','gác','cho','và','tại','dự','án','lắp','đặt','icd','các','của','nhà','chòi','chốt','mẫu','có'}
def toks(s):
    s = unicodedata.normalize('NFC', html.unescape(re.sub('<[^>]+>', '', s))).lower()
    return set(re.findall(r'\w+', s))
def sim(a, b):
    a, b = toks(a), toks(b); return len(a & b) / max(1, len(a | b))
def title(u):
    try:
        h = urllib.request.urlopen(urllib.request.Request(u, headers={'User-Agent': 'Mozilla/5.0'}), timeout=40).read().decode('utf8', 'ignore')
        m = re.search(r'<h1[^>]*>(.*?)</h1>', h, re.S) or re.search(r'<title>(.*?)</title>', h, re.S)
        return u, html.unescape(re.sub('<[^>]+>', '', m.group(1))).strip() if m else ''
    except Exception as e: return u, ''
with ThreadPoolExecutor(6) as ex:
    titles = dict(ex.map(title, [u for t, u in rows if t in ('san-pham', 'danh-muc-san-pham')]))
prods = [x for x in it if x['type'] == 'product']; posts = [x for x in it if x['type'] == 'post']
cats = [x for x in it if x['type'] == 'product_cat']
KW = [  # tu khoa -> danh muc .com.vn
    (r'thùng rác', 'https://dailythungrac.com/'), (r'thu phí|trạm|\bbot\b(?!.?gac)', 'Cabin-thu-phi-giao-thong-BOT'), (r'vệ sinh|wc|toilet', 'nha-ve-sinh-cong-cong'),
    (r'inox', 'cabin-nha-bao-ve-inox'), (r'composite|lục lăng', 'bot-gac-composite'),
    (r'thép|panel|tôn', 'choi-gac-bao-ve-thep'), (r'barrier|barie', 'Barrier-kiem-soat-ra-vao'),
    (r'bán hàng|quầy|bán vé|kiosk', 'Xe-ban-hang-luu-dong'), (r'lắp ghép|di động|container|phi thuyền', 'Nha-di-dong-nha-phi-thuyen-nha-lap-ghep'),
    (r'.', 'Cabin-bao-ve-Bot-gac-bao-ve-pc20')]
def kwcat(t):
    t = t.lower()
    for p, s in KW:
        if re.search(p, t): return s if s.startswith('http') else '/' + s + '.html'
vnmap, move, report = {}, [], []
for t, u in rows:
    path = re.sub(r'^https://cabinbaove\.vn', '', u) or '/'
    if path in ('', '/'):
        vnmap['/'] = '/'; continue
    if t == 'san-pham':
        tt = titles.get(u, '')
        if path == '/san-pham/': to = '/san-pham.html'
        else:
            best = max(prods, key=lambda x: sim(tt, x['h1'])); s = sim(tt, best['h1'])
            to = '/' + best['slug'] + '.html' if s >= 0.6 else kwcat(tt)
        report.append(('SP', path, tt, to))
    elif t == 'danh-muc-san-pham':
        to = kwcat(re.sub(r' - ICD Cabin Technology', '', titles.get(u, '')) or path.replace('-', ' ')); report.append(('DM', path, titles.get(u, ''), to))
    elif t == 'post':
        p = next((p for p in vn['posts'] if p['link'] == u), None)
        tt = html.unescape(p['title']['rendered']) if p else ''
        best = max(posts, key=lambda x: sim(tt, x['h1'])); s = sim(tt, best['h1'])
        if s >= 0.85: to = '/' + best['slug'] + '.html'
        else: to = '/' + path.strip('/') + '.html'; move.append(p)
        report.append(('BAI', path, tt, to))
    elif t == 'page':
        to = {'/': '/', '/gioi-thieu/': '/gioi-thieu-cong-ty.html', '/lien-he/': '/lien-he.html', '/danh-muc-san-pham/': '/san-pham.html'}.get(path, '/')
        report.append(('TRANG', path, '', to))
    elif t == 'category':
        to = '/hoat-dong.html' if 'du-an' in path or 'hoat' in path else '/tin-tuc.html'; report.append(('CM', path, '', to))
    else:  # tag
        to = kwcat(path.replace('-', ' ')); report.append(('TAG', path, '', to))
    vnmap[path] = to
for k in list(vnmap): vnmap[k.rstrip('/') or '/'] = vnmap[k]
com = {'/Thong-tin-pc2.html': '/', '/Chot-bao-ve-composite-mai-nhon-CAB-1515-a349.html': '/bot-gac-composite.html',
       '/Barie-tu-dong-can-gap-cho-tang-ham-DZ-701-a351.html': '/Barrier-kiem-soat-ra-vao.html', '/Hai-long-moi-khach-hang.html': '/gioi-thieu.html',
       '/index.html': '/', '/trang-chu.html': '/', '/p-detail/bot-bao-ve-composite-hinh-chu-nhat.html': '/bot-gac-composite.html'}
json.dump({'vn': vnmap, 'com': com}, open(os.path.join(ROOT, 'wp/mu-plugins/cbv-redirects.json'), 'w'), ensure_ascii=False, indent=0)
json.dump(move, open(f'{D}/vn-move-posts.json', 'w'), ensure_ascii=False)
import csv
with open(os.path.join(ROOT, 'noi-dung/301-cabinbaove-vn.csv'), 'w', newline='') as f:
    w = csv.writer(f); w.writerow(['Loại', 'URL cabinbaove.vn', 'Tiêu đề', 'Chuyển 301 tới cabinbaove.com.vn'])
    for r in report: w.writerow([r[0], 'https://cabinbaove.vn' + r[1], r[2], r[3] if r[3].startswith('http') else 'https://cabinbaove.com.vn' + r[3]])
from collections import Counter
print(Counter(r[0] for r in report), 'bai chuyen sang:', len(move))
for r in report:
    if r[0] in ('SP', 'DM'): print(r[0], r[2][:50], '->', r[3])
