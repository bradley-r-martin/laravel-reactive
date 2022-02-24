<?php

namespace Sihq\Reactive\Http\Controllers\Reactive;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class Transfer extends Controller
{

   public function request(){
        $uuid = Str::uuid();
        $location = "tmp";
        $expires = "+20 minutes";
        $driver = config("filesystems.default");
        if ($driver === "local") {
            return [
                "uuid" => $uuid,
                "bucket" => "",
                "key" => "$location/$uuid",
                "url" => url("/reactive/signed-transfer"),
                "headers" => [
                    "uuid" => $uuid,
                ],
            ];
        } elseif ($driver === "s3") {
            $adapter = Storage::disk("s3")->getAdapter();
            $client = $adapter->getClient();
            $bucket = $adapter->getBucket();

            $cmd = $client->getCommand("PutObject", [
                "Bucket" => $bucket,
                "Key" => "$location/$uuid",
                "ACL" => "public-read",
            ]);
            // Get the presigned request
            $request = $client->createPresignedRequest($cmd, $expires);

            return [
                "uuid" => $uuid,
                "bucket" => $bucket,
                "key" => "$location/$uuid",
                "url" => (string) $request->getUri(),
                "headers" => [
                    "uuid" => $uuid,
                ],
            ];
        }
   }

   public function stage(){
        if (config("filesystems.default") === "local") {
            $file = file_get_contents("php://input");
            Storage::disk("local")->put("tmp/" . request()->header("uuid"), $file);
            return true;
        } else {
            return abort("404");
        }
   }
}