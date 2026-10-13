"""Tai moi anh /uploads/... cua cabinbaove.com.vn ve tham-khao/media (giu nguyen duong dan)."""
import json, os, re, urllib.request, urllib.parse
from concurrent.futures import ThreadPoolExecutor
from bs4 import BeautifulSoup
ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
c = json.load(open(os.path.join(ROOT, 'tham-khao/data/crawl.json')))
urls = set()
for v in c.values():
    if not v['file']: continue
    h = open(os.path.join(ROOT, 'tham-khao/html', v['file']), errors='ignore').read()
    for m in re.findall(r'''(?:src|data-src|href|content)=["']((?:https?://(?:www\.)?cabinbaove\.com\.vn)?/uploads/[^"'?#]+)''', h):
        urls.add(re.sub(r'^https?://(?:www\.)?cabinbaove\.com\.vn', '', m))
    for m in re.findall(r'url\((?:&quot;|["\'])?((?:https?://cabinbaove\.com\.vn)?/uploads/[^)"\'&]+)', h):
        urls.add(re.sub(r'^https?://cabinbaove\.com\.vn', '', m))
OUT = os.path.join(ROOT, 'tham-khao/media')
def get(p):
    dst = os.path.join(OUT, urllib.parse.unquote(p).lstrip('/'))
    if os.path.exists(dst) and os.path.getsize(dst) > 0: return p, 'skip'
    os.makedirs(os.path.dirname(dst), exist_ok=True)
    try:
        q = urllib.parse.quote(urllib.parse.unquote(p))
        d = urllib.request.urlopen(urllib.request.Request('https://cabinbaove.com.vn' + q, headers={'User-Agent': 'Mozilla/5.0'}), timeout=60).read()
        open(dst, 'wb').write(d); return p, 'ok'
    except Exception as e: return p, 'ERR ' + str(e)[:60]
with ThreadPoolExecutor(6) as ex: res = list(ex.map(get, sorted(urls)))
json.dump(sorted(urls), open(os.path.join(ROOT, 'tham-khao/data/media-urls.json'), 'w'), indent=0)
from collections import Counter
print(len(urls), Counter(r[1][:3] for r in res)); print([r for r in res if r[1].startswith('ERR')][:10])
