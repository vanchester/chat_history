chat_history
============

Roundcube plugin for work with ejabberd history. Deep deep alpha :)

Tested at Roundcube Webmail 1.0-beta

Installation
------------

1. Clone this repository to folder **[rouncube]/plugins**
```sh
cd [roundcube]/plugins
git clone https://github.com/vanchester/chat_history.git 
```
2. Copy **config.inc.php.dist** to **config.inc.php** and change params for ejabberd DB connection in **config.inc.php**
3. Enable plugin in **[roundcube]/config/config.inc.php**
```php
$config['plugins'] = array(
        ...
        'chat_history'
);
```
4. (optional) Import timezones into MySQL (roundcube db) for correct time conversion
```sh
mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root -p mysql
```

TODO
----
* Search in messages
* Search in contactlist
* Sortable columns in history table
* Export to file
* Delete messages (by one or all)
