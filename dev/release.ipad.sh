# svn up
grunt requirejs:ipad
grunt sass:ipad

cp -rf app/settings/site.js dist.ipad/js/site.js
# cp -rf dist.ipad/* ../www/
cp -rf dist.ipad/* ../platforms/ipad/www
