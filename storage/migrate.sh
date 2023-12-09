#!/bin/bash

nohup /bin/sh -c "php artisan migrate --no-interaction --force; \
    php artisan view:cache; \
    php artisan config:cache; \
    php artisan route:cache; \
    php artisan event:cache; \
    php artisan up" >../nohup.out 2>&1 &
