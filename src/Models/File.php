<?php

namespace Sihq\Reactive\Models;

// Illuminate
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use GuzzleHttp\Psr7\MimeType;

// Traits
use Sihq\Reactive\Traits\UuidTrait;

class File extends Model
{
    use UuidTrait, SoftDeletes;

    protected $fillable = [
        "id", 
        "name", 
        "mime", 
        "size", 
        "meta"
    ];

    protected $casts = [
        "meta" => "json",
    ];

    protected static function booted()
    {
        parent::boot();

        static::creating(function ($model) {
            if($this->persist()){
                $this->setFileMetaData();
                return true;
            }
            return false;
        });
        static::deleting(function ($model) {
            if ($model->isForceDeleting()) {
                $model->purge();
            } else {
              $model->archive();
            }
        });
    }

    public function persist($unmetered = false)
    {
        if (!$unmetered) {
            if (
                Storage::size(config('files.staging')."/$this->id") >
                (optional(optional(auth()->user())->storage())["available"] ?? 0)
            ) {
                return false;
            }
        }
        if (Storage::copy(config('files.staging')."/$this->id",config('files.persisted')."/$this->id")) {
            // $original = $this->original;
            $this->save();
            // if (!empty(optional($original)["id"]) && $original["id"] !== $this->id) {
            //     // Keep a copy of the original. But mark as deleted.
            //     File::create($original)->delete();
            // }
            return true;
        } else {
            abort(500);
        }
        return false;
    }

    public function archive(){
        try {
            if (Storage::copy(config('files.persisted')."/$this->id",config('files.archived')."/$this->id")) {
                $this->deleted_at = now();
                $this->save();
                return true;
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    public function unarchive(){
        try {
            if (Storage::copy(config('files.archived')."/$this->id", config('files.persisted')."/$this->id")) {
                $this->deleted_at = null;
                $this->save();
                return true;
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    public function purge(){
        try {
            if (Storage::delete(config('files.archived')."/$this->id")) {
                return true;
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    protected function setFileMetaData(){
        $meta = [];
        $size = Storage::size("public/" . $this->id);
        $meta['last_modified'] = Storage::lastModified("public/" . $this->id);
        $mime = (new MimeType())->fromFilename($this->name);
        switch ($mime) {
            case "image/gif":
            case "image/jpeg":
            case "image/png":
                $meta['dimensions'] = ['height'=> 0, 'width'=> 0];
                $meta['orientation'] = 'portrait';
                break;
        }

        $this->meta = object($meta);
        $this->size = $size;
        $this->mime = $mime;
    }

 
}
