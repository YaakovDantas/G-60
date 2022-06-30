<?php

namespace App\ImageHandler;

use Intervention\Image\ImageManager;

class CloudMersiveHandler implements InterfaceHandler
{   
    private $config;
    private $src;
    private $api;
    private $isRandom;
    private $isColorfull;

    function __construct($img, $isRandom, $isColorfull) {
        $this->src = $img;
        $this->config = \Swagger\Client\Configuration::getDefaultConfiguration()->setApiKey('Apikey', $_ENV['CLOUDMERSIVE_API_KEY']);
        $this->setApi();
        $this->name = explode('/', $img)[1];
        $this->isRandom = $isRandom;
        $this->isColorfull = $isColorfull;
    }

    private function setApi()
    {
        $this->api = new \Swagger\Client\Api\FaceApi(
            new \GuzzleHttp\Client(),
            $this->config
        );
    }

    public function process()
    {
        try {
            $result = $this->api->faceLocateWithLandmarks($this->src);

            $facePoints = [];
            foreach ($result['faces'] as $landmarks) {
                $points = [];
                
                $points[] = $landmarks['left_eye'][0]['x'];
                $points[] = $landmarks['left_eye'][0]['y'];
                $points[] = $landmarks['right_eye'][0]['x'];
                $points[] = $landmarks['right_eye'][0]['y'];
                
                $facePoints[] = $points;
                continue;
            }
            $manager = new ImageManager(['driver' => 'imagick']);
            $img = $manager->make($this->src);

            foreach ($facePoints as $point) {

                $random = random_int(0, 1);
                $values = ['#000', '#fff'];
                $colorRandom = $this->isRandom ? $values[$random]: '#000';
                if ($this->isColorfull && !$this->isRandom) {
                    $colorRandom = "#" . $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
                }
                
                $startX = $point[0] - 85;
                $startY = $point[1] + 10;
                $endX = $point[2] + 85;
                $endY = $point[3] - 10;

                $img->rectangle(
                    $startX,
                    $startY,
                    $endX,
                    $endY,
                    function ($draw) use($colorRandom) {
                        $draw->background($colorRandom);
                    }
                );
                

            }

            $img->save("results/" . $this->name);

        } catch (Exception $e) {
            echo 'Exception when calling FaceApi->faceLocateWithLandmarks: ', $e->getMessage(), PHP_EOL;
        }
    }

    private function random_color_part() {
        return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
    }
}
