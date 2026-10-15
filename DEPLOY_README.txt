EdSchool COMPLETE package (server upload)
========================================

Excluded (use your server copies / recreate):
- application/config/database.php
- application/config/config.php
- application/config/purchase_key.php
- application/config/subjective_ai.php
- .htaccess (all)
- node_modules/
- backups/
- *.sql dumps, *.zip archives
- application/logs/log-*.php
- application/cache session files
- mpdf tmp files
- uploads/db_backup, uploads/db_temp
- .git / IDE / OS junk

Deploy
1. Backup live files + database first.
2. Extract into the edschool web root (merge/overwrite).
3. Keep existing server config files and .htaccess (do not replace with local ones).
4. Ensure writable: application/logs/, application/cache/, uploads/
5. Confirm PHP/MySQL and base_url in server config.php.
