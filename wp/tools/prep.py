"""Chuan bi import.json: gan ngay dang, anh dai dien, danh muc cho tung bai/SP."""
import json, os, re
from bs4 import BeautifulSoup
ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
c = json.load(open(os.path.join(ROOT, 'tham-khao/data/crawl.json')))
it = json.load(open(os.path.join(ROOT, 'tham-khao/data/site.json')))
dates, thumbs = {}, {}
for u, v in c.items():
    if not v['file']: continue
    s = BeautifulSoup(open(os.path.join(ROOT, 'tham-khao/html', v['file']), errors='ignore').read(), 'lxml')
    for a in s.select('article'):
        t = a.select_one('.title a'); tm = a.select_one('.time')
        if not t: continue
        slug = re.sub(r'\.html$', '', t.get('href', '').split('/')[-1])
        if tm:
            m = re.search(r'(\d{2})/(\d{2})/(\d{4})', tm.get_text())
            if m: dates[slug] = f'{m.group(3)}-{m.group(2)}-{m.group(1)} 09:00:00'
        im = a.select_one('img')
        if im: thumbs.setdefault(slug, (im.get('data-src') or im.get('src') or '').replace('/.thumbs/', '/'))
seen=set(); it=[x for x in sorted(it,key=lambda x: not x['url'].endswith('.html')) if not ((x['type'],x['slug']) in seen or seen.add((x['type'],x['slug'])))]
S = {x['slug']: x for x in it}
pcat = {}; acat = {}
for x in it:
    if x['type'] == 'product_cat' and x['slug'] != 'san-pham':
        for s in x['products']: pcat.setdefault(s, []).append(x['slug'])
    if x['type'] == 'category':
        for s in x['posts']: acat.setdefault(s, []).append(x['slug'])
for x in it:
    if x['type'] == 'product':
        x['cats'] = pcat.get(x['slug'], [])
        x['featured'] = (x['gallery'] or x['content_imgs'] or [''])[0]
    if x['type'] == 'post':
        x['cats'] = acat.get(x['slug'], [])
        x['date'] = dates.get(x['slug'], '')
        og = x['og_image'].replace('https://cabinbaove.com.vn', '')
        x['featured'] = thumbs.get(x['slug']) or (og if 'icd-cabin-technology' not in og else '') or (x['content_imgs'] or [''])[0]
    if x['type'] == 'product_cat':
        x['parent'] = '' if x['slug'] in ('san-pham',) else (re.sub(r'\.html$', '', x['crumbs'][-2][1]) if len(x['crumbs']) > 2 else '')
# Thu tu bai khong co ngay: dat truoc bai cu nhat
json.dump(it, open(os.path.join(ROOT, 'tham-khao/data/import.json'), 'w'), ensure_ascii=False, indent=1)
posts = [x for x in it if x['type'] == 'post']
print('posts', len(posts), 'co ngay', sum(1 for p in posts if p['date']), 'co anh', sum(1 for p in posts if p['featured']))
print('khong ngay', [p['slug'] for p in posts if not p['date']])
print('khong cat', [p['slug'] for p in posts if not p['cats']], [p['slug'] for p in it if p['type']=='product' and not p['cats']])
print([(x['slug'], x['parent']) for x in it if x['type'] == 'product_cat'])
