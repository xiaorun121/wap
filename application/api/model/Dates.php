<?php
namespace app\api\model;

use think\Model;

class Dates extends Model
{
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = true;
}