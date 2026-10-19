<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait GeneratesSlug
{
    /**
     * Boot the GeneratesSlug trait.
     */
    protected static function bootGeneratesSlug(): void
    {
        static::creating(function (Model $model) {
            $sourceField = method_exists($model, 'slugSourceField') ? $model->slugSourceField() : 'name';

            if (empty($model->slug) && ! empty($model->{$sourceField})) {
                $model->slug = str()->slug($model->{$sourceField});
            }
        });
    }

    /**
     * The model attribute used to generate the slug.
     */
    public function slugSourceField(): string
    {
        return 'name';
    }
}
