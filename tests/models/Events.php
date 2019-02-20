<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    protected $connection = 'testbench';

    protected $table = 'events';

    protected $dates = ['occurred_at'];

    protected $fillable = ['guid','event_type','occurred_at','payload'];

    public $timestamps = false;
}
