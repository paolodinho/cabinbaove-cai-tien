#!/usr/bin/env python3
"""Đồng bộ bản xem giao diện tĩnh từ http://cabinbaove.local lên GitHub Pages (nhánh gh-pages).
Dùng: python3 wp/tools/sync-demo.py   -> https://paolodinho.github.io/cabinbaove-cai-tien/
Bản tĩnh chỉ để XEM giao diện: form báo giá, tìm kiếm không chạy."""
import os, re, shutil, subprocess, time
W = os.path.expanduser('~/Claude-Workspace/cabinbaove/demo-static')
REPO = 'https://github.com/paolodinho/cabinbaove-cai-tien.git'
OUT = W + '/site'; NEW = W + '/_new'
os.makedirs(W, exist_ok=True); shutil.rmtree(NEW, ignore_errors=True)
subprocess.run(['wget', '--mirror', '--convert-links', '--adjust-extension', '--page-requisites', '--no-parent', '-e', 'robots=off', '--no-host-directories',
    '--timeout=30', '--tries=2', '--reject-regex', r'(\?(s|p|page_id|post_type|bg|replytocom)=|/wp-admin|/wp-json|/feed|xmlrpc|/comments|admin-post\.php)',
    '-P', NEW, '-o', W + '/wget.log', 'http://cabinbaove.local/'])
git = OUT + '/.git'
if os.path.exists(git): shutil.move(git, W + '/_git')
shutil.rmtree(OUT, ignore_errors=True); shutil.copytree(NEW, OUT)
if os.path.exists(W + '/_git'): shutil.move(W + '/_git', git)
os.chdir(OUT)
ver = re.compile(r'(?:%3F|\?)(?:ver|v)=[^"\'\s>)]*')
stamp = str(int(time.time()))
for r, ds, fs in os.walk('.'):
    if '.git' in r: continue
    for f in fs:
        p = os.path.join(r, f)
        if '?' in f: os.replace(p, os.path.join(r, re.sub(r'\?.*$', '', f))); p = os.path.join(r, re.sub(r'\?.*$', '', f))
        if not p.endswith(('.html', '.css')): continue
        s = open(p, encoding='utf-8', errors='surrogateescape').read(); t = ver.sub('', s)
        if p.endswith('.html'):
            pre = '../' * (r.count('/'))
            t = t.replace('http://cabinbaove.local/', pre)
            t = re.sub(r'(themes/icd-cabin/assets/(?:css|js)/main\.(?:css|js))(?![\w?])', r'\1?v=' + stamp, t)
            if 'noindex' not in t: t = t.replace('<head>', '<head>\n<meta name="robots" content="noindex, nofollow">', 1)
            t = t.replace('action="http://cabinbaove.local/wp-admin/admin-post.php"', 'action="#" onsubmit="alert(\'Bản xem giao diện: form gửi thật sẽ chạy trên website chính thức.\');return false"')
        if t != s: open(p, 'w', encoding='utf-8', errors='surrogateescape').write(t)
open('.nojekyll', 'w').close()
if not os.path.exists('.git'):
    subprocess.run(['git', 'init', '-q', '-b', 'gh-pages']); subprocess.run(['git', 'remote', 'add', 'origin', REPO])
subprocess.run(['git', 'add', '-A'])
subprocess.run(['git', 'commit', '-qm', 'Cập nhật bản xem giao diện ' + time.strftime('%Y-%m-%d %H:%M')])
subprocess.run(['git', 'push', '-qf', 'origin', 'gh-pages'])
print('Demo: https://paolodinho.github.io/cabinbaove-cai-tien/')
