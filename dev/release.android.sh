# svn up
grunt requirejs:android
 grunt sass:android

cp -rf app/settings/site.js dist.android/js/site.js
cp -rf dist.android/* ../platforms/android/assets/www
