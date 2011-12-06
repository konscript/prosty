#!/bin/bash

# set variables
project_id=${1}
path="/srv/www/${project_id}/web/"
pathTemp="/srv/www/konscript-services/prosty/temp/"
pathWPDefaults="/srv/www/konscript-services/kontemplate-wp/"
wpFile="${pathTemp}latest.tar.gz"

# remove old wordpress installations
rm ${wpFile}

# download latest wordpress
wget -P ${pathTemp} http://wordpress.org/latest.tar.gz

# untar wordpress to dev
tar -C ${path} -zxf ${wpFile} --strip 1

# add default wp files to dev
cd ${pathWPDefaults} && git pull konscript master
rsync -az ${pathWPDefaults} ${path} --exclude '.git'

# do initial commit
cd ${path} && git add -A && git commit -m 'Automatic initial commit from Caesar'
cd ${path} && git push konscript master
