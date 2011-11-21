# Konscript Prosty

New late-2011 revision of Prosty, built on CakePHP. Early alpha state.

Requirements:  
- PHP5x  
- MySQL or equivalent  
- Apache/webserver w/ htaccess and mod_rewrite support  
- a kick-ass webbrowser! (you know which)  
- phpMyAdmin or equivalent can be useful  

## Quick start

1. git clone git@github.com:konscript/prosty-cake.git
2. download newest CakePHP 2.x core files and place in folder
3. mysqladmin -u root -p create prosty-cake
4. cp app/config/database.php.default app/config/database.php
5. EDIT AND UPDATE database.php
6. lib/Cake/Console/cake schema create [-s NUMBER]

## Get started from scratch (detailed)

1. make a local dir 'prosty-cake' for the project (apache/webserver needs access to here)
2. git clone the project to your local project dir ('git clone git@github.com:konscript/prosty-cake.git')
3. download newest CakePHP 2.x core files and place in folder together with app/
4. create an empty MySQL DB called 'prosty-cake' (no tables, just the DB)
5. copy app/config/database.php.default to app/config/database.php
6. edit database.php with your own local DB info
7. from your project dir, run 'lib/Cake/Console/cake schema create -s NUMBER' (replace with number of the newest schema_NUMBER.php file, e.g. 4, select y & y when asked)
8. now you have a DB with all the tables needed including a little data
9. kick up your browser and you're baking baby!

mysqladmin path in MAMP (OSX):  
/Applications/MAMP/Library/bin/mysqladmin

add this to database for MAMP (OSX) to work:  
'port' => '/Applications/MAMP/tmp/mysql/mysql.sock',

default login:  
user: admin  
pass: admin