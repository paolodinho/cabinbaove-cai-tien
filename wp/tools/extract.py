"""Tach du lieu tu HTML da crawl (cabinbaove.com.vn) -> tham-khao/data/site.json
Phan loai: product, post, product_cat, category, home, contact. Giu nguyen slug (ke ca chu hoa)."""
import json, os, re
from bs4 import BeautifulSoup

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
DATA = os.path.join(ROOT, 'tham-khao', 'data')
HTML = os.path.join(ROOT, 'tham-khao', 'html')
crawl = json.load(open(os.path.join(DATA, 'crawl.json')))


def slug_of(url):
    p = url.replace('https://cabinbaove.com.vn', '').strip('/')
    return re.sub(r'\.html$', '', p)


def clean_html(node):
    """Bo font/size inline cua CKEditor cu, giu noi dung, anh, bang, link."""
    if node is None:
        return ''
    for t in node.find_all(['script', 'style', 'iframe']):
        if t.name == 'iframe' and 'youtube' in (t.get('src') or ''):
            continue
        t.decompose()
    for t in node.find_all(True):
        st = t.get('style')
        if st:
            keep = [d for d in st.split(';') if d.strip().split(':')[0].strip().lower() in ('text-align', 'width', 'height') and t.name in ('p', 'img', 'td', 'th', 'table', 'div')]
            if t.name == 'img':
                keep = []
            if keep:
                t['style'] = ';'.join(x.strip() for x in keep)
            else:
                del t['style']
        for a in ('class', 'id', 'face', 'size', 'color'):
            if a in t.attrs and t.name != 'iframe':
                del t[a]
    # bo span/font rong, giu chu
    for t in node.find_all(['span', 'font']):
        if not t.attrs:
            t.unwrap()
    out = node.decode_contents().strip()
    out = re.sub(r'(&nbsp;|\xa0)+', ' ', out)
    out = re.sub(r'<p>\s*</p>', '', out)
    out = out.replace('—', '-').replace('–', '-')
    return out


def imgs(node):
    return [i.get('data-src') or i.get('src') for i in (node.find_all('img') if node else []) if (i.get('data-src') or i.get('src'))]


def meta(soup, name, attr='name'):
    m = soup.find('meta', attrs={attr: name})
    return (m.get('content') or '').strip() if m else ''


def crumbs(soup):
    b = soup.select('.breadcrumb li a')
    return [(a.get_text(strip=True), a.get('href') or '') for a in b][1:]


items = []
for url, v in crawl.items():
    if v['code'] != 200 or not v['file'] or re.search(r'/trang-\d+\.html$', url):
        continue
    soup = BeautifulSoup(open(os.path.join(HTML, v['file']), encoding='utf8', errors='ignore').read(), 'lxml')
    canon = soup.find('link', rel='canonical')
    it = {'url': url, 'slug': slug_of(url), 'title_tag': v['title'], 'meta_desc': meta(soup, 'description'),
          'og_image': meta(soup, 'og:image', 'property'), 'canonical': canon.get('href') if canon else '', 'crumbs': crumbs(soup)}
    if soup.select_one('.prd-detail'):
        d = soup.select_one('.prd-detail')
        attrs = {}
        for li in d.select('.list_item_prd li'):
            b = li.find('b')
            if b:
                k = b.get_text(strip=True).rstrip(':')
                attrs[k] = li.get_text(' ', strip=True)[len(b.get_text(strip=True)):].strip()
        art = d.select_one('.detail-content')
        it.update(type='product', h1=d.select_one('.prd-title').get_text(strip=True), attrs=attrs,
                  gallery=imgs(d.select_one('.prd-gallerys')), excerpt=clean_html(d.select_one('.prd-excerpt')),
                  content=clean_html(art), content_imgs=imgs(art),
                  price=(d.select_one('.price-saleoff span').get_text(strip=True) if d.select_one('.price-saleoff span') else ''))
    elif soup.select_one('.art-detail'):
        d = soup.select_one('.art-detail')
        art = d.select_one('.detail-content')
        it.update(type='post', h1=d.select_one('.main-title').get_text(strip=True), excerpt=clean_html(d.select_one('.description')),
                  content=clean_html(art), content_imgs=imgs(art))
        tm = soup.select_one('.art-detail .meta, .art-detail .time, .art-detail .date')
        it['date_text'] = tm.get_text(' ', strip=True) if tm else ''
    elif soup.select_one('.prdcatalogue') and it['slug'] not in ('',):
        c = soup.select_one('section.prdcatalogue')
        h = c.select_one('.heading-1, .heading-2, h1, h2')
        desc = c.select_one('.description, .cat-description, .catalogue-description')
        it.update(type='product_cat', name=h.get_text(strip=True) if h else v['title'],
                  description=clean_html(desc), products=[slug_of('https://cabinbaove.com.vn/' + a.get('href')) for a in c.select('.product-1 .title a')])
    elif soup.select_one('.artcatalogue'):
        c = soup.select_one('section.artcatalogue')
        h = c.select_one('.heading-1, .heading-2, h1, h2')
        it.update(type='category', name=h.get_text(strip=True) if h else v['title'],
                  posts=[slug_of('https://cabinbaove.com.vn/' + a.get('href')) for a in c.select('article .title a, .article .title a, h3 a')])
    elif soup.select_one('.main-slide'):
        it.update(type='home')
    else:
        m = soup.select_one('.main-content')
        it.update(type='contact' if 'Liên hệ' in v['title'] else 'other', content=clean_html(m))
    items.append(it)

# gom danh muc theo phan trang (trang-2..n)
for url, v in crawl.items():
    m = re.search(r'^(.*)/trang-\d+\.html$', url)
    if not m or v['code'] != 200:
        continue
    base = slug_of(m.group(1) + '.html')
    parent = next((x for x in items if x['slug'] == base), None)
    if not parent:
        continue
    soup = BeautifulSoup(open(os.path.join(HTML, v['file']), encoding='utf8', errors='ignore').read(), 'lxml')
    if parent['type'] == 'product_cat':
        parent['products'] += [slug_of('https://cabinbaove.com.vn/' + a.get('href')) for a in soup.select('section.prdcatalogue .product-1 .title a')]
    elif parent['type'] == 'category':
        parent['posts'] += [slug_of('https://cabinbaove.com.vn/' + a.get('href')) for a in soup.select('section.artcatalogue article .title a, section.artcatalogue h3 a')]
for x in items:
    for k in ('products', 'posts'):
        if k in x:
            x[k] = list(dict.fromkeys(x[k]))

json.dump(items, open(os.path.join(DATA, 'site.json'), 'w'), ensure_ascii=False, indent=1)
from collections import Counter
print(Counter(x['type'] for x in items))
for x in items:
    if x['type'] in ('product_cat', 'category'):
        print(x['type'], x['slug'], '|', x['name'], '|', len(x.get('products', x.get('posts', []))), [c[0] for c in x['crumbs']])
