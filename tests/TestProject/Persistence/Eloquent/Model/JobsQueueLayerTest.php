<?php
namespace tests\TestProject\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

class JobsQueueLayerTest extends Model
{
    protected $connection = 'testbench';

    protected $table = 'jobs_queue';

    protected $dates = ['created_at','last_run_at'];

    protected $fillable = ['guid','queue_name','channel','strategy','routing_key','status','created_at','last_run_at','messages_total'];

    public $timestamps = false;
}
