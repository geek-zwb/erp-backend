<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 手动配置迁移生成的默认字符串长度 why? https://d.laravel-china.org/docs/5.5/migrations#索引长度--MySQL--MariaDB
        Schema::defaultStringLength(191);

        DB::listen(function ($query) {
            // $query->sql
            // $query->bindings
            // $query->time
            Log::info("\r\n sql: ".$query->sql."\r\n query_time: ".$query->time);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
