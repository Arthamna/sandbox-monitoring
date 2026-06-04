<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// if u want to use scheduler :
// Schedule::command('proxmox:nodes-sync')
// ->everyMinute()
// ->withoutOverlapping();

// example to put in server :
// * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1 

    
    



