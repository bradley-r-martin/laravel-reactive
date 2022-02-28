<?php
namespace Sihq\Reactive\Http\Controllers\Reactive;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Laravel\Vapor\Contracts\SignedStorageUrlController as SignedStorageUrlControllerContract;

class Transfer extends Controller implements SignedStorageUrlControllerContract
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
                "file" => [
                    "status"=> "staged",
                    "id" => $uuid,
                    "store" => url('storage')."/"
                ],
                "headers" => [
                    "uuid" => $uuid,
                ],
            ];
        } elseif ($driver === "s3") {


            $this->ensureEnvironmentVariablesAreAvailable(request());

            $client = $this->storageClient();
    
            $uuid = (string) Str::uuid();
    
            $key = request()->input('key') ?: 'tmp/'.$uuid;
    
            $expiresAfter = config('vapor.signed_storage_url_expires_after', 5);
    
            $signedRequest = $client->createPresignedRequest(
                $this->createCommand($request, $client, $bucket, $key),
                sprintf('+%s minutes', $expiresAfter)
            );
    
            $uri = $signedRequest->getUri();
    
            return [
                "uuid" => $uuid,
                'bucket' => $bucket,
                'key' => $key,
                'url' => $uri->getScheme().'://'.$uri->getAuthority().$uri->getPath().'?'.$uri->getQuery(),
                'headers' => $this->headers($request, $signedRequest),

                "file" => [
                    "status"=> "staged",
                    "id" => $uuid,
                    "store" => "https://$bucket.s3.ap-southeast-2.amazonaws.com/"
                ],

            ];
        }
   }

   public function stage(){
        if (config("filesystems.default") === "local") {
            $file = file_get_contents("php://input");
            Storage::disk("local")->put("public/tmp/" . request()->header("uuid"), $file);
            return true;
        } else {
            return abort("404");
        }
    }

    /**
     * Create a command for the PUT operation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Aws\S3\S3Client  $client
     * @param  string  $bucket
     * @param  string  $key
     * @return \Aws\Command
     */
    protected function createCommand(Request $request, S3Client $client, $bucket, $key)
    {
        return $client->getCommand('putObject', array_filter([
            'Bucket' => $bucket,
            'Key' => $key,
            'ACL' => $request->input('visibility') ?: $this->defaultVisibility(),
            'ContentType' => $request->input('content_type') ?: 'application/octet-stream',
            'CacheControl' => $request->input('cache_control') ?: null,
            'Expires' => $request->input('expires') ?: null,
        ]));
    }

    /**
     * Get the headers that should be used when making the signed request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \GuzzleHttp\Psr7\Request
     * @return array
     */
    protected function headers(Request $request, $signedRequest)
    {
        return array_merge(
            $signedRequest->getHeaders(),
            [
                'Content-Type' => $request->input('content_type') ?: 'application/octet-stream',
            ]
        );
    }

    /**
     * Ensure the required environment variables are available.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function ensureEnvironmentVariablesAreAvailable(Request $request)
    {
        $missing = array_diff_key(array_flip(array_filter([
            $request->input('bucket') ? null : 'AWS_BUCKET',
            'AWS_DEFAULT_REGION',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
        ])), $_ENV);

        if (empty($missing)) {
            return;
        }

        throw new InvalidArgumentException(
            'Unable to issue signed URL. Missing environment variables: '.implode(', ', array_keys($missing))
        );
    }

    /**
     * Get the S3 storage client instance.
     *
     * @return \Aws\S3\S3Client
     */
    protected function storageClient()
    {
        $config = [
            'region' => config('filesystems.disks.s3.region', $_ENV['AWS_DEFAULT_REGION']),
            'version' => 'latest',
            'signature_version' => 'v4',
            'use_path_style_endpoint' => config('filesystems.disks.s3.use_path_style_endpoint', false),
        ];

        if (! isset($_ENV['AWS_LAMBDA_FUNCTION_VERSION'])) {
            $config['credentials'] = array_filter([
                'key' => $_ENV['AWS_ACCESS_KEY_ID'] ?? null,
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'] ?? null,
                'token' => $_ENV['AWS_SESSION_TOKEN'] ?? null,
            ]);

            if (array_key_exists('AWS_URL', $_ENV) && ! is_null($_ENV['AWS_URL'])) {
                $config['url'] = $_ENV['AWS_URL'];
                $config['endpoint'] = $_ENV['AWS_URL'];
            }
        }

        return S3Client::factory($config);
    }

    /**
     * Get the default visibility for uploads.
     *
     * @return string
     */
    protected function defaultVisibility()
    {
        return 'private';
    }
}