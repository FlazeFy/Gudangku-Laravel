<?php

namespace App\Service;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Storage\RemoteFile;
use Kreait\Firebase\Storage\StorageClient;

class FirebaseStorage
{
    protected $storage;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
        $this->storage = $factory->createStorage();
    }

    public function uploadFile($localFilePath, $subfolder, $name)
    {
        $bucket = $this->storage->getBucket();
        $file = fopen($localFilePath, 'r');
        $fullPath = rtrim($subfolder, '/') . '/' . $name;
        
        $object = $bucket->upload($file, [
            'name' => $fullPath
        ]);

        return $object->info()['mediaLink'];
    }

    public function getFileList($path)
    {
        $bucket = $this->storage->getBucket();
        $files = $bucket->objects(['prefix' => $path]);

        $fileList = [];

        /** @var RemoteFile $file */
        foreach ($files as $file) {
            $fileList[] = [
                'name' => $file->name(),
                'size' => $file->info()['size'], 
                'content_type' => $file->info()['contentType'],
                'download_url' => $file->info()['mediaLink'],
                'created_at' => $file->info()['timeCreated'],
                'updated_at' => $file->info()['updated'],
            ];
        }

        return $fileList;
    }
}