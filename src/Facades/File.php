<?php

namespace Sihq\Facades;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use GuzzleHttp\Psr7\MimeType;

class File{

    protected $_id;
    protected $_name;
    protected $_extension;
    protected $_mime;
    protected $_size;
    protected $_meta;
    protected $_status;

    public function __construct($data = null){
        if($data){
            $this->_id = optional($data)['id'];
            $this->_name = optional($data)['name'];
            $this->_extension = optional($data)['extension'];
            $this->_mime = optional($data)['mime'];
            $this->_size = optional($data)['size'];
            $this->_meta = optional($data)['meta'];
            $this->_status = optional($data)['status'];
        }
    }

    public function id(){
        return $this->_id;
    }
    public function name(){
        return $this->_name;
    }
    public function extension(){
        return $this->_extension;
    }
    public function mime(){
        return $this->_mime;
    }
    public function size(){
        return $this->_size;
    }
    public function meta(){
        return $this->_meta;
    }
    public function status(){
        return $this->_status;
    }

    public function __serialize(): array
    {
        return [
          'id' => $this->id(),
          'name' => $this->name(),
          'extension' => $this->extension(),
          'mime' => $this->mime(),
          'size' => $this->size(),
          'meta' => $this->meta(),
          'status' => $this->status(),
        ];
    }

    public function persist($unmetered = false)
    {
        if (!$unmetered) {
            if (
                Storage::size(config('files.staging')."/".$this->id()) >
                (optional(optional(auth()->user())->storage())["available"] ?? 0)
            ) {
                return false;
            }
        }
        if (Storage::copy(config('files.staging')."/".$this->id(),config('files.persisted')."/".$this->id())) {
            // $original = $this->original;
            $this->_status = "persisted";

            // set meta data.
            $meta = [];
            $size = Storage::size(config('files.persisted') . $this->id());
            $meta['last_modified'] = Storage::lastModified(config('files.persisted') . $this->id());
            $mime = (new MimeType())->fromFilename($this->name());
            switch ($mime) {
                case "image/gif":
                case "image/jpeg":
                case "image/png":
                    $meta['dimensions'] = ['height'=> 0, 'width'=> 0];
                    $meta['orientation'] = 'portrait';
                    break;
            }
    
            $this->_meta = object($meta);
            $this->_size = $size;
            $this->_mime = $mime;
            
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
            if (Storage::copy(config('files.persisted')."/".$this->id(),config('files.archived')."/".$this->id())) {
                $this->_status = "archived";
                return true;
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    public function unarchive(){
        try {
            if (Storage::copy(config('files.archived')."/".$this->id(), config('files.persisted')."/".$this->id())) {
                $this->_status = "persisted";
                return true;
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    public function purge(){
        try {
            if (Storage::delete(config('files.archived')."/".$this->id())) {
                $this->_id = null;
                $this->_name = null;
                $this->_extension = null;
                $this->_mime = null;
                $this->_size = null;
                $this->_meta = null;
                $this->_status = null;
            }
        } catch (\Exception $e) {
        }
        return false;
    }


}