<?php

namespace Sihq\Facades;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use GuzzleHttp\Psr7\MimeType;

class File implements \JsonSerializable {

    protected $_id;
    protected $_name;
    protected $_extension;
    protected $_mime;
    protected $_size;
    protected $_meta;
    protected $_status;

    protected $_store;

    protected $_region;
    protected $_bucket;

    public function __construct($data = null){
       
        if($data){
            $this->_id = optional($data)->{'id'};
            $this->_name = optional($data)->{'name'};
            $this->_extension = optional($data)->{'extension'};
            $this->_mime = optional($data)->{'mime'};
            $this->_size = optional($data)->{'size'};
            $this->_meta = optional($data)->{'meta'};
            $this->_status = optional($data)->{'status'};
            $this->_store = optional($data)->{'store'};
        }
        return $this;
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
    public function url(){
        $directory = config('sihq.directories.'.$this->status());
        return "https://foremind-prod-bucket.s3.ap-southeast-2.amazonaws.com/".$directory."/".$this->id();
    }
    


    public function toArray(): array
    {
        return [
          'id' => $this->id(),
          'name' => $this->name(),
          'extension' => $this->extension(),
          'mime' => $this->mime(),
          'size' => $this->size(),
          'meta' => $this->meta(),
          'status' => $this->status(),
          'url'=> $this->url()
        ];
    }

    public function jsonSerialize() {
        return $this->toArray();
    }

    public function __serialize() 
    {
        return $this->toArray();
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }

    public function persist($unmetered = false)
    {
        if (!$unmetered) {
            if (
                Storage::size(config('sihq.directories.staging')."/".$this->id()) >
                (optional(optional(auth()->user())->storage())["available"] ?? 0)
            ) {
                return false;
            }
        }
       

        if (Storage::move(config('sihq.directories.staging')."/".$this->id(),config('sihq.directories.persisted')."/".$this->id())) {
            // $original = $this->original;
           
            $this->_status = "persisted";

            // set meta data.
            $meta = [];
            $size = Storage::size(config('sihq.directories.persisted')."/" . $this->id());
            $meta['last_modified'] = Storage::lastModified(config('sihq.directories.persisted')."/" . $this->id());
            $mime = (new MimeType())->fromFilename($this->id());
            switch ($mime) {
                case "image/gif":
                case "image/jpeg":
                case "image/png":
                    $meta['dimensions'] = ['height'=> 0, 'width'=> 0];
                    $meta['orientation'] = 'portrait';
                    break;
            }
    
            $this->_meta = (object) $meta;
            $this->_size = $size;
            $this->_mime = $mime;
            
            return true;
        } else {
            abort(500);
        }
        return false;
    }

    public function archive(){
        try {
            if (Storage::copy(config('sihq.directories.persisted')."/".$this->id(),config('sihq.directories.archived')."/".$this->id())) {
                $this->_status = "archived";
                return true;
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    public function unarchive(){
        try {
            if (Storage::copy(config('sihq.directories.archived')."/".$this->id(), config('sihq.directories.persisted')."/".$this->id())) {
                $this->_status = "persisted";
                return true;
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    public function purge(){
        $directory = config('sihq.directories.'.$this->status());
        try {
            if (Storage::delete($directory."/".$this->id())) {
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