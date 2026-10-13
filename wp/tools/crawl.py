"""Crawl toan bo cabinbaove.com.vn (CMS PHP cu) -> tham-khao/html + data/crawl.json"""
import re, json, os, sys, time, hashlib, urllib.request, urllib.parse
from html.parser import HTMLParser
BASE = 'https://cabinbaove.com.vn/'
ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
OUT = os.path.join(ROOT, 'tham-khao', 'html'); os.makedirs(OUT, exist_ok=True)
UA = {'User-Agent': 'Mozilla/5.0 (Macintosh) ICD-audit'}
SKIP = re.compile(r'\.(jpg|jpeg|png|gif|webp|svg|pdf|zip|rar|doc|docx|xls|xlsx|mp4|css|js|ico)(\?|$)', re.I)
class L(HTMLParser):
    def __init__(s): super().__init__(); s.links = []
    def handle_starttag(s, t, a):
        if t == 'a':
            h = dict(a).get('href')
            if h: s.links.append(h)
def norm(u):
    u = urllib.parse.urljoin(BASE, u.strip()).split('#')[0]
    p = urllib.parse.urlparse(u)
    if p.netloc.replace('www.', '') != 'cabinbaove.com.vn': return None
    if SKIP.search(p.path): return None
    return 'https://cabinbaove.com.vn' + (p.path or '/') + (('?' + p.query) if p.query else '')
seen, queue, res = set(), [BASE], {}
for x in re.findall(r'<loc>([^<]+)</loc>', urllib.request.urlopen(urllib.request.Request(BASE + 'sitemap.xml', headers=UA), timeout=30).read().decode('utf8', 'ignore')):
    n = norm(x); n and queue.append(n)
while queue and len(seen) < 3000:
    u = queue.pop(0)
    if u in seen: continue
    seen.add(u)
    try:
        r = urllib.request.urlopen(urllib.request.Request(u, headers=UA), timeout=30)
        code, final, html = r.status, r.geturl(), r.read().decode('utf8', 'ignore')
    except urllib.error.HTTPError as e:
        code, final, html = e.code, u, ''
    except Exception as e:
        code, final, html = 0, u, ''
    fn = hashlib.md5(u.encode()).hexdigest()[:12] + '.html'
    if html: open(os.path.join(OUT, fn), 'w').write(html)
    t = re.search(r'<title>(.*?)</title>', html, re.S | re.I)
    res[u] = {'code': code, 'final': final, 'file': fn if html else None, 'title': (t.group(1).strip() if t else '')}
    if code == 200 and html:
        l = L(); l.feed(html)
        for h in l.links:
            n = norm(h)
            if n and n not in seen: queue.append(n)
    print(len(seen), code, u, flush=True); time.sleep(0.3)
json.dump(res, open(os.path.join(ROOT, 'tham-khao', 'data', 'crawl.json'), 'w'), ensure_ascii=False, indent=1)
print('DONE', len(res))
