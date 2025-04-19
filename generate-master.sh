#!/bin/bash

# Cek apakah parameter diberikan
if [ -z "$1" ]; then
  echo "Usage: ./generate.sh ModelName"
  exit 1
fi

name=$1

# Jalankan perintah artisan
php artisan make:model "$name" -m
php artisan make:request "Create${name}Request"
php artisan make:request "Update${name}Request"
php artisan make:controller "API/Master/${name}Controller"

