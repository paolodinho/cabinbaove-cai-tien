"""Tai noi dung + anh cac bai chi co tren cabinbaove.vn -> vn-move-import.json"""
import json, re, os, urllib.request, urllib.parse, html
ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
D = os.path.join(ROOT, 'tham-khao/data')
UA = {'User-Agent': 'Mozilla/5.0'}
out = []
for p in json.load(open(f'{D}/vn-move-posts.json')):
    c = re.sub(r'\s(srcset|sizes)="[^"]*"', '', p['content']['rendered'])
    c = re.sub(r'(https?://cabinbaove\.vn/wp-content/uploads/[^"\s]+?)-\d+x\d+(\.(?:jpe?g|png|gif|webp))', r'\1\2', c)
    imgs = sorted(set(re.findall(r'https?://cabinbaove\.vn/wp-content/uploads/[^"\s\)]+\.(?:jpe?g|png|gif|webp)', c, re.I)))
    feat = ''
    if p.get('featured_media'):
        try: feat = json.load(urllib.request.urlopen(urllib.request.Request(f"https://cabinbaove.vn/wp-json/wp/v2/media/{p['featured_media']}", headers=UA), timeout=30))['source_url']
        except Exception: pass
    for u in imgs + ([feat] if feat else []):
        dst = os.path.join(ROOT, 'tham-khao/media-vn', urllib.parse.unquote(u.split('/wp-content/uploads/')[1]))
        if os.path.exists(dst): continue
        os.makedirs(os.path.dirname(dst), exist_ok=True)
        try: open(dst, 'wb').write(urllib.request.urlopen(urllib.request.Request(urllib.parse.quote(u, safe=':/%'), headers=UA), timeout=60).read())
        except Exception as e: print('ERR', u, e)
    title = html.unescape(p['title']['rendered']).replace('–', '-').replace('—', '-')
    news = bool(re.search(r'FLC|57 dự án|Sa Pa|Quảng Ninh|Khai thác tạm|năng lượng mặt trời|Pallet', title))
    out.append({'slug': p['slug'], 'title': title, 'content': c.replace('–', '-').replace('—', '-'),
                'excerpt': html.unescape(re.sub('<[^>]+>', '', p['excerpt']['rendered'])).strip().replace('[&hellip;]', '').replace('[…]', ''),
                'date': p['date'].replace('T', ' '), 'imgs': imgs, 'featured': feat, 'cat': 'tin-tuc' if news else 'hoat-dong', 'vn_url': p['link']})
json.dump(out, open(f'{D}/vn-move-import.json', 'w'), ensure_ascii=False)
print(len(out), 'anh', sum(len(x['imgs']) for x in out), 'co anh dai dien', sum(1 for x in out if x['featured']))
