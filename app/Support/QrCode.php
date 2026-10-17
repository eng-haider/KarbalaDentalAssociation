<?php

namespace App\Support;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode as Generator;
use chillerlan\QRCode\QROptions;

/** QR codes drawn on the server, for pages people print or share. */
class QrCode
{
    /** Inline SVG markup that scales to any size, on screen and in print. */
    public static function svg(string $data): string
    {
        return (new Generator(new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'outputBase64' => false,
            'eccLevel' => EccLevel::M,
            'quietzoneSize' => 2,
            'svgAddXmlHeader' => false,
            'drawLightModules' => false,
            'connectPaths' => true,
            'svgUseFillAttributes' => true,
        ])))->render($data);
    }

    /** A large PNG (about 1,000px) for sharing or for designers' print files. */
    public static function png(string $data): string
    {
        return (new Generator(new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'outputBase64' => false,
            'eccLevel' => EccLevel::M,
            'scale' => 24,
            'quietzoneSize' => 3,
        ])))->render($data);
    }
}
