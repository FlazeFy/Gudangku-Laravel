<?php

namespace App\Service;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirebaseRealtime
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
        $firebase = $factory->withDatabaseUri('https://gudangku-94edc-default-rtdb.firebaseio.com/');

        $this->database = $firebase->createDatabase();
    }

    public function insert_command($path, $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $reference = $this->database->getReference($path);
        $reference->set($data);
    }
}
