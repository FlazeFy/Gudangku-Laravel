<?php
namespace App\Helpers;
use App\Helpers\Generator;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Storage;

class Firebase
{
    private static $factory;

    public static function init()
    {
        if (!self::$factory) {
            self::$factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
        }
    }

    public static function uploadFile($ctx, $user_id, $username, $file, $file_ext){
        self::init();
        // Firebase Storage instance
        $storage = self::$factory->createStorage();
        $bucket = $storage->getBucket();
        $uploadedFile = fopen($file->getRealPath(), 'r');
        $id = Generator::getUUID();

        // Upload file to Firebase Storage
        $object = $bucket->upload($uploadedFile, [
            'name' => $ctx.'/' . $user_id . '_' . $username . '/' . $id . '.' . $file_ext,
            'predefinedAcl' => 'publicRead',
            'contentType' => $file_ext,
        ]);

        // Uploaded link
        $object->update([
            'acl' => [],
        ]);                
        $fileUrl = $object->info()['mediaLink']; 

        return $fileUrl;
    }
}