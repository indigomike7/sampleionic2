# svn up
grunt requirejs:iphone
grunt sass:iphone

cp -rf app/settings/site.js dist.iphone/js/site.js
# cp -rf dist.iphone/* ../www/
cp -rf dist.iphone/* ../platforms/ios/www
