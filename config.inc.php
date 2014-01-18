<?php

// Database connection string (DSN) for read+write operations
// Format (compatible with PEAR MDB2): db_provider://user:password@host/database
// Currently supported db_providers: mysql, pgsql, sqlite, mssql or sqlsrv
// For examples see http://pear.php.net/manual/en/package.database.mdb2.intro-dsn.php
// NOTE: for SQLite use absolute path: 'sqlite:////full/path/to/sqlite.db?mode=0646'
$config['chathistory_db_dsnw'] = 'mysql://ejabberd:ejabberd@localhost/ejabberd';

// Database DSN for read-only operations (if empty write database will be used)
// useful for database replication
$config['chathistory_db_dsnr'] = '';

// use persistent db-connections
// beware this will not "always" work as expected
// see: http://www.php.net/manual/en/features.persistent-connections.php
$config['chathistory_db_persistent'] = false;

// Log SQL queries to <log_dir>/sql or to syslog
$config['chathistory_sql_debug'] = true;

// Timezone of data in database
$config['chathistory_timezone'] = 'Europe/Moscow';
