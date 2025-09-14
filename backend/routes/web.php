<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $permissions = collect(Route::getRoutes())
            ->whereNotNull('action.as')
            ->map(function ($route) {
                $action = collect($route->action)->toArray();
                $as = str($action['as'])->lower();
                if ($as->startsWith('api') && in_array('permission', $action['middleware'])) { 
                    return [
                        'name' => $action['as'],
                    ];
                }else {
                    return null;
                }
            })
            ->filter(function ($value) {
                return !is_null($value);
            });
            return $permissions->values();
});
