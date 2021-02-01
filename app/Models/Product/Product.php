<?php

namespace App\Models\Product;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'price',
        'image',
        'upload_token'
    ];

    protected $primaryKey = 'id';
    protected $table = 'products';
    protected static $logName = 'product';
    protected static $logFillable = true;
    protected static $logOnlyDirty = true;
    protected static $ignoreChangedAttributes = ['updated_at'];
   
    public function scopeFilterById($q, $id)
    {
        if (! $id) {
            return $q;
        }

        return $q->where('id', '=', $id);
    }

    public function scopeFilterByUuid($q, $uuid)
    {
        if (! $uuid) {
            return $q;
        }

        return $q->where('uuid', '=', $uuid);
    }


   
    public function scopeFilterByTitle($q, $title, $s = 0)
    {
        if (! $title) {
            return $q;
        }

        return ($s) ? $q->where('title', '=', $title) : $q->where('title', 'like', '%'.$title.'%');
    }

    public function scopeFilterByKeyword($q, $keyword)
    {
        if (! $keyword) {
            return $q;
        }

        return $q->where(function ($q1) use ($keyword) {
            $q1->where('title', 'like', '%'.$keyword.'%')->where('description', 'like', '%'.$keyword.'%');
        });
    }

    public function scopeDateOfProductBetween($q, $dates)
    {
        if ((! $dates['start_date'] || ! $dates['end_date']) && $dates['start_date'] <= $dates['end_date']) {
            return $q;
        }

        return $q->where('created_at', '>=', getStartOfDate($dates['start_date']))->where('created_at', '<=', getEndOfDate($dates['end_date']));
    }
}
