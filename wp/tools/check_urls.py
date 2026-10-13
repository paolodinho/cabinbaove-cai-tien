"""Kiem tra moi URL cu cabinbaove.com.vn + cabinbaove.vn tren ban moi (mac dinh Local). 0 URL 404, 0 chuoi redirect."""
import json, os, sys, re, urllib.request, urllib.error, http.client, urllib.parse
ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
BASE = sys.argv[1] if len(sys.argv) > 1 else 'http://cabinbaove.local'
host = urllib.parse.urlparse(BASE).netloc
VNHOST = sys.argv[2] if len(sys.argv) > 2 else '127.0.0.1:10129'  # cong nginx rieng cua site Local, de gia lap Host cabinbaove.vn
def req(path, h=None):
    c = http.client.HTTPConnection(VNHOST if h else host, timeout=30)
    c.request('GET', urllib.parse.quote(path, safe="/?=&%.-_~"), headers={'Host': h or host, 'User-Agent': 'check'})
    r = c.getresponse(); r.read(); return r.status, r.getheader('Location') or ''
crawl = json.load(open(f'{ROOT}/tham-khao/data/crawl.json'))
bad = []; n = 0
for u, v in crawl.items():
    if v['code'] != 200: continue
    path = u.replace('https://cabinbaove.com.vn', '') or '/'
    st, loc = req(path); n += 1
    if st == 301:
        st2, _ = req(urllib.parse.urlparse(loc).path or '/')
        if st2 != 200: bad.append((path, st, loc, st2))
    elif st != 200: bad.append((path, st, loc))
print('com.vn:', n, 'URL, loi:', len(bad)); [print(' ', b) for b in bad[:30]]
m = json.load(open(f'{ROOT}/wp/mu-plugins/cbv-redirects.json'))['vn']
bad2 = []
for p, to in list(m.items()):
    st, loc = req(p or '/', 'cabinbaove.vn')
    exp = to if to.startswith('http') else 'https://cabinbaove.com.vn' + to
    if st != 301 or loc != exp: bad2.append((p, st, loc, exp)); continue
    if not to.startswith('http'):
        st2, _ = req(to)
        if st2 != 200: bad2.append((p, 'dich', to, st2))
print('cabinbaove.vn:', len(m), 'URL, loi:', len(bad2)); [print(' ', b) for b in bad2[:30]]
