#!/bin/bash

###########
# clone project: zip project and mysqldump
###########

# set variables
source "/srv/www/services/prosty/bash/config.sh"
project_id="${1}"
folderToZip=$(basename ${2})
cwdToFolder=$(dirname ${2})
dbname=${3}
error=0

pathTemp="/srv/www/services/prosty/temp/"
tarFile="${pathTemp}${project_id}.tar"
sqlFile="${pathTemp}export.sql"

rm ${sqlFile}
rm ${tarFile}

# create mysqldump
mysqldump -u $SQLUser -p$SQLPass $dbname > $sqlFile		

# any problems making sql dump ?
if [ $? != 0 ]; then {
	error=1	
} fi
	
# create tar archive with web files and add mysqldump
tar --create --file=${tarFile} -C ${cwdToFolder} ${folderToZip} -C ${pathTemp} export.sql

# any problems making tar?
if [ $? != 0 ]; then {
	error=1
} fi
	
# everything went well
if [ $error == 0 ]; then {
	echo "success"
} fi
