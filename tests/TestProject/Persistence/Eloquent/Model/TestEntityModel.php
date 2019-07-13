<?php
namespace tests\TestProject\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

class TestEntityModel extends Model
{
    protected $connection = 'testbench';

    protected $table = 'test_entity';

    protected $dates = ['created_at'];

    protected $fillable = ['guid','fk_id','created_at', 'name'];

    public $timestamps = false;
}
