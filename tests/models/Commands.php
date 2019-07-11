<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commands extends Model
{
    protected $connection = 'testbench';

    protected $table = 'commands';

    protected $dates = ['created_at'];

    protected $fillable = ['guid','command','queue_name','binding_key','payload','header','created_at'];

    public $timestamps = false;
}