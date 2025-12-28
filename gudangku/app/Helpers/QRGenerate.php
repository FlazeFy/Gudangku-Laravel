<?php
namespace App\Helpers;
use Endroid\QrCode\Builder\Builder;
use Illuminate\Support\Str;

// Helper
use App\Helpers\Generator;

class QRGenerate
{
    public static function generateQR($keyword = null)
    {
        $id = Generator::getUUID();

        $keyword = $keyword ?? ('qr:' . Str::uuid());
        $filename = "qr_".time()."_$id.png";
        $path = storage_path("app/tmp/{$filename}");

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $result = Builder::create()
            ->data($keyword)
            ->size(300)
            ->margin(10)
            ->build();

        file_put_contents($path, $result->getString());

        return $path;
    }
}
