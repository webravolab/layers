<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobsLayerTest extends Model
{
    protected $connection = 'testbench';

    protected $table = 'jobs';

    protected $dates = ['created_at','delivered_at'];

    protected $fillable = ['guid','name','channel','routing_key','status','created_at','delivered_at','delivered_token','payload','header'];

    public $timestamps = false;
}
