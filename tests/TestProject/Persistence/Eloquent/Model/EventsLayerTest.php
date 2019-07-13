<?php
namespace tests\TestProject\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

class EventsLayerTest extends Model
{
    protected $connection = 'testbench';

    protected $table = 'events';

    protected $dates = ['occurred_at'];

    protected $fillable = ['guid','event_type','occurred_at','payload'];

    public $timestamps = false;
}
