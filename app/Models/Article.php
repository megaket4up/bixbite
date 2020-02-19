<?php

namespace BBCMS\Models;

// Сторонние зависимости.
use BBCMS\Models\Setting;
use BBCMS\Models\User;
use BBCMS\Models\Observers\ArticleObserver;

class Article extends BaseModel
{
    use Mutators\ArticleMutators,
        Relations\Categoryable,
        Relations\Commentable,
        Relations\Extensible,
        Relations\Fileable,
        Relations\Taggable,
        Scopes\ArticleScopes,
        Traits\Dataviewer,
        Traits\FullTextSearch;

    protected $primaryKey = 'id';

    protected $table = 'articles';

    public $timestamps = false;

    protected $casts = [
        'title' => 'string',
        'img' => 'array',
        'image_id' => 'integer',
        'on_mainpage' => 'boolean',
        'is_favorite' => 'boolean',
        'is_pinned' => 'boolean',
        'is_catpinned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'url',
        'created',
        'updated',
        // 'image',
    ];

    protected $fillable = [
        'user_id',
        'image_id',
        'title',
        'slug',
        'teaser',
        'content',
        'description',
        'keywords',
        // Flags
        'allow_com',
        'state',
        'robots',
        'on_mainpage',
        'is_favorite',
        'is_pinned',
        'is_catpinned',
        // Extension
        'views',
        'votes',
        'rating',
        // Dates
        'created_at',
        'updated_at',
    ];

    protected $allowedFilters = [
        'id',
        'title',
        'content',
        'state',
        'views',
        'created_at',
        // 'votes',
        // 'rating',

        // Nested filters.
        'comments.content',
        'comments.is_approved',
        'comments.count',
        'comments.created_at',
        'files.count',
        'categories.id',
    ];

    protected $orderableColumns = [
        'id',
        'title',
        'views',
        'state',
        'created_at',
    ];

    /**
     * The columns of the full text index.
     */
    protected $searchable = [
        'title',
        'content',
    ];

    /**
     * The relations to eager load on every query.
     * При выводе списка комментариев нужно получить ссылку на комментарий,
     * а ссылка на комментарий к записи формируется с использованием категорий.
     */
    protected $with = [
        'categories:categories.id,categories.title,categories.slug',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::observe(ArticleObserver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id', 'user');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class, 'module_name');
    }
}
