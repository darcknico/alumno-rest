<?php

namespace App\Models\MP;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $table ='mp_webhooks';
    public $incrementing = false;
}
