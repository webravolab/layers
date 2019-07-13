<?php

namespace tests\TestProject\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

class CommandsLayerTest extends Model
{
    protected $connection = 'testbench';

    protected $table = 'commands';

    protected $dates = ['created_at'];

    protected $fillable = ['guid','command','queue_name','binding_key','payload','header','created_at'];

    public $timestamps = false;
}
