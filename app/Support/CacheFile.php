<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Cache\FileStore;

class CacheFile extends FileStore
{
    protected $cacheMap = [
        // getCachedCategories()
        // Not simple: orderByRaw('ISNULL(`position`), `position` ASC')
        'categories' => \App\Models\XField::class,

        // roles(), not simple
        'roles' => \App\Models\Privilege::class,

        // getPrivileges(), not simple
        'privileges' => \App\Models\User::class,

        // fields(), simple ->get()
        'x_fields' => \App\Models\XField::class,
    ];

    /**
     * Get datetime of created cache file by key.
     * @param  string $key
     * @return Carbon|null
     */
    public function created(string $key): ?Carbon
    {
        $path = $this->path($key);

        if ($this->files->exists($path)) {
            return Carbon::createFromTimestamp(
                $this->files->lastModified($path)
            );
        }

        return null;
    }

    /**
     * Get the expiration time from cache file by key.
     * @param  string $key
     * @return Carbon|null
     */
    public function expired(string $key): ?Carbon
    {
        $path = $this->path($key);

        if ($this->files->exists($path)) {
            return Carbon::createFromTimestamp(
                substr($this->files->get($path), 0, 10)
            );
        }

        return null;
    }
}
