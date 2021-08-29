<?php

namespace Jawabapp\Community\Models;

use Jawabapp\Community\Services\DeepLinkBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Jawabapp\Community\Traits\HasDynamicRelation;

class PostTag extends Model
{
    use HasDynamicRelation;

    
}
