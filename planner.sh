cron 55 12 30 4,6,9,11          php -f send_stats.php;
cron 55 12 31 1,3,5,7,8,10,12   php -f send_stats.php;
cron 55 12 28 2                 php -f send_stats.php;
