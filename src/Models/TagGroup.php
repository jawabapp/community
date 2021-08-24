<?php

namespace Jawabapp\Community\Models;

use Jawabapp\Community\Scopes\TagGroup\CountryCodeScope;
use Jawabapp\Community\Scopes\TagGroup\PublishedScope;
use Jawabapp\Community\Scopes\TagGroup\ServiceScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jawabapp\Community\Traits\HasDynamicRelation;

class TagGroup extends Model
{
    use SoftDeletes, HasDynamicRelation;

    static $enableGlobalScope = true;

    protected $fillable = [
        'name',
        'image',
        'order',
        'parent_id',
        'country_code',
        'is_published',
        'hide_in_public',
        'services'
    ];

    protected $casts = [
        'services' => 'array',
        'name' => 'array',
        'is_published' => 'boolean',
        'hide_in_public' => 'boolean',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'account_following',
    ];

    public function getAccountFollowingAttribute()
    {
        return $this->isAccountFollowingBy();
    }

    public function tags()
    {
        return $this->hasMany(Tag::class, 'tag_group_id');
    }

    public function parent()
    {
        return $this->belongsTo(TagGroup::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(TagGroup::class, 'parent_id', 'id')->oldest('order');
    }

    public function getRoot()
    {
        if ($this->parent_id) {
            $parent = self::find($this->parent_id);
            if ($parent) {
                return $parent->getRoot();
            }
        }

        return $this;
    }

    public function isAccountFollowingBy()
    {
        $activeAccountId = config('community.user_class')::getActiveAccountId();

        if ($activeAccountId) {
            return TagGroupFollower::whereAccountId($activeAccountId)
                ->whereTagGroupId($this->getKey())
                ->exists();
        }

        return false;
    }

    protected static function boot()
    {
        parent::boot();

        //GlobalScope

        static::addGlobalScope(new PublishedScope());
        static::addGlobalScope(new CountryCodeScope());
        static::addGlobalScope(new ServiceScope());

        ////

        static::deleted(function (self $node) {
            if (is_null($node->parent_id)) {
                self::where('parent_id', $node->id)->delete();
            }
        });
    }
}
