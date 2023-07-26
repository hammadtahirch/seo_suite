<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Store extends Model
{
    /**
     * db connection
     */
    protected $connection = 'mongodb';

    /**
     * db collection
     */
    protected $collection = 'stores';

    /**
     * 
     */
    protected $fillable = [
        'name',
        'access_token',
        'myshopify_domain',
        'phone',
        'address1',
        'address2',
        'zip',
    ];
}
