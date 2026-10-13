#!/bin/bash
export MYSQL_HOME=
"/Users/dohieu/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php" -d mysqli.default_socket="/Users/dohieu/Library/Application Support/Local/run/yFjoRRd90f/mysql/mysqld.sock" -d pdo_mysql.default_socket="/Users/dohieu/Library/Application Support/Local/run/e2FGxwDB0/mysql/mysqld.sock" ~/.local/bin/wp --path="/Users/dohieu/Local Sites/cabinbaove/app/public" "$@"
