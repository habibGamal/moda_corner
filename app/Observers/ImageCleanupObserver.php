<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Storage;

class ImageCleanupObserver
{
    public function saved(Model $model): void
    {
        $imageAttributeName = method_exists($model, 'getImageAttributeName') ? $model->getImageAttributeName() : 'image';
        if ($model->isDirty($imageAttributeName) && $model->getOriginal($imageAttributeName) !== null) {
            Storage::disk('public')->delete(paths: $model->getOriginal($imageAttributeName));
        }
    }

    public function deleted(Model $model): void
    {
        $imageAttributeName = method_exists($model, 'getImageAttributeName') ? $model->getImageAttributeName() : 'image';

        if (!is_null($model->getOriginal($imageAttributeName))) {
            Storage::disk('public')->delete($model->getOriginal($imageAttributeName));
        }
    }
}
