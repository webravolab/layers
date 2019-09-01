<?php
namespace tests\TestProject\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

class EventSourceTestTransaction extends Model
{
    protected $connection = 'testbench';

    protected $table = 'eventsource_test_transaction';

    protected $dates = ['occurred_at'];

    protected $fillable = ['guid','aggregate_type','aggregate_id','event','occurred_at','version','payload'];

    public $timestamps = false;
}
