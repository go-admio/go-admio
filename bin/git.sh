#!/bin/bash

echo "$(date +"[%b %d %H:%M:%S]#") $(basename "$0") $@" | sudo tee -a "/var/log/go/$(basename "$0").log" >/dev/null

rm -rf /goadmio
mkdir -p /goadmio/home

cf /usr/local/goadmio /goadmio
rm -rf /goadmio/bin/*
cp /root/*.sh /goadmio/bin
find /goadmio -maxdepth 1 -type f -delete
rm -rf /goadmio/bin/encoder.sh
rm -rf /goadmio/bin/decoder.sh

cp /usr/local/goadmio/bin/README.md /goadmio/
cp /usr/local/goadmio/bin/LICENSE.md /goadmio/

cp /home/goadmio.com/www/user.png /goadmio/
cp /home/goadmio.com/www/admin.png /goadmio/

cd /goadmio
rm -rf cache
rm -rf includes
rm -rf db
rm -rf tmp
rm -rf backup  
rm -rf latest
rm -rf lock
rm -rf shell
rm -rf sqlite
rm -rf include  
rm -rf linux
rm -rf script
rm -rf template
rm -rf install
rm -rf vps

git config --global --add safe.directory /goadmio
git config --global user.name "goadmio"
git config --global user.email "info@goadmio.com"
git init
git add .
git status
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/goadmio/goadmio.git
# git push -u origin main
git push -f origin main

# 1- Log in to GitHub, click avatar → Settings → Developer settings → Personal access tokens → Tokens (classic).
# 2- Click Generate new token (classic).
# 3- Check the repo permission (complete read/write access to repositories).
# 4- Push Again and Use Token as Password







#!/bin/bash
set -euo pipefail

# ============================================================
# goadmio deployment script
# Goal: Build a clean staging tree at /goadmio from /usr/local/goadmio
#       WITHOUT destroying the existing 13.0.0 history on GitHub.
#       Then commit and push as 13.2.0 while preserving 13.0.0.
#       NOTE: bin/ scripts from the LIVE 13.0.0 repo are downloaded
#             and kept, then /root/*.sh are added on top.
# ============================================================

echo ">>> [1/10] Cleaning workspace /goadmio"
rm -rf /goadmio
mkdir -p /goadmio/home

echo ">>> [2/10] Copying base tree from /usr/local/goadmio to /goadmio"
cp -a /usr/local/goadmio/. /goadmio/

echo ">>> [3/10] Downloading live 13.0.0 repo to extract its bin/ scripts"
TMP_CLONE=$(mktemp -d)
git clone --depth 1 https://github.com/goadmio/goadmio.git "$TMP_CLONE"

echo ">>> [4/10] Merging 13.0.0 bin/ scripts into /goadmio/bin (existing files kept)"
mkdir -p /goadmio/bin
if [ -d "$TMP_CLONE/bin" ]; then
    cp -an "$TMP_CLONE"/bin/. /goadmio/bin/ 2>/dev/null || true
fi

echo ">>> [7/10] Re-adding required docs"
cp /usr/local/goadmio/bin/README.md  /goadmio/
cp /usr/local/goadmio/bin/LICENSE.md /goadmio/

cp /home/goadmio.com/www/user.png /goadmio/
cp /home/goadmio.com/www/admin.png /goadmio/

echo ">>> [8/10] Removing unwanted directories + moving .git to preserve history"
cd /goadmio
for d in cache includes db tmp backup latest lock shell sqlite include linux script template install vps; do
    rm -rf "./$d"
done

# Reuse the clone's .git so 13.0.0 history is preserved
mv "$TMP_CLONE/.git" /goadmio/.git
rm -rf "$TMP_CLONE"

echo ">>> [9/10] Staging changes over the existing repo working tree"
git config --global --add safe.directory /goadmio
git config user.name  "goadmio"
git config user.email "info@goadmio.com"

git add -A
git status

echo ">>> [10/10] Commit + tag + push (no force)"
if git diff --cached --quiet; then
    echo "No changes to commit."
else
    git commit -m "Release 13.2.0"
fi

if git rev-parse "v13.2.0" >/dev/null 2>&1; then
    echo "Tag v13.2.0 already exists locally."
else
    git tag -a "v13.2.0" -m "Release 13.2.0"
fi

git branch -M main
git push origin main
git push origin "v13.2.0"

GH_TOKEN=ghp_your_token_here gh release create v13.2.0 \
  --title "GoAdmio v13.2.0" \
  --notes "Release 13.2.0" \
  --latest \
  --repo goadmio/goadmio


echo ">>> Done. Version 13.2.0 pushed; 13.0.0 history and bin/ scripts preserved."