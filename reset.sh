#!/bin/bash
# reset_db.sh
rm -f writable/mobileMoney.db
sqlite3 writable/mobileMoney.db < base.sql
echo "Base réinitialisée."