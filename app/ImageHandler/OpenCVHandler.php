<?php

namespace App\ImageHandler;

use CV\Scalar, CV\Size;

class OpenCVHandler implements InterfaceHandler
{    
    private $net;
    private $src;
    private $faces = [];
    private $scale_w;
    private $scale_h;
    private $name;
    private $isRandom;
    private $isColorfull;

    function __construct($img, $isRandom, $isColorfull) {
        $this->net = \CV\DNN\readNetFromONNX('models/centerface/centerface.onnx');
        $this->src = \CV\imread(__DIR__ . '/../../images/'.$img);
        $this->name = $img;
        $this->isRandom = $isRandom;
        $this->isColorfull = $isColorfull;
    }

    private function landmarks($landmarksMat, $l, $i, $j, $x1, $y1, $s0, $s1) {
        $landmarks = [];
        for ($k = 0; $k < $l/2; $k++) {
            $landmarks[] = [$landmarksMat->atIdx([0,$k*2+1,$i,$j])*$s1+$x1, $landmarksMat->atIdx([0,$k*2+0,$i,$j])*$s0+$y1];
        }
    
        return $landmarks;
    }

    private function setFaces()
    {
        $img_w_new = ceil($this->src->cols / 32) * 32;
        $img_h_new = ceil($this->src->rows / 32) * 32;

        $this->scale_w = $img_w_new / $this->src->cols;
        $this->scale_h = $img_h_new / $this->src->rows;

        $blob = \CV\DNN\blobFromImage($this->src, 1,  new Size($img_w_new, $img_h_new), new Scalar(), true, false);
        $this->net->setInput($blob);

        [$heatmapMat, $scaleMat, $offsetMat, $landmarksMat] = $this->net->forwardMulti(['537', '538', '539', '540']);

        $thresh = 0.7;

        $h = $heatmapMat->shape[2];
        $w = $heatmapMat->shape[3];

        $l = $landmarksMat->shape[1];

        for ($i = 0; $i < $h; $i++) {
            for ($j = 0; $j < $w; $j++) {
                $confidence = $heatmapMat->atIdx([0,0,$i,$j]);
                if ($confidence > $thresh) {

                    $s0 = exp($scaleMat->atIdx([0,0,$i,$j])) * 4;
                    $s1 = exp($scaleMat->atIdx([0,1,$i,$j])) * 4;
                    $o0 = $offsetMat->atIdx([0,0,$i,$j]);
                    $o1 = $offsetMat->atIdx([0,1,$i,$j]);
                    $x1 = max(0, ($j + $o1 + 0.5) * 4 - $s1 / 2);
                    $y1 = max(0, ($i + $o0 + 0.5) * 4 - $s0 / 2);
                    $x1 = min($x1, $this->src->cols);
                    $y1 = min($y1, $this->src->rows);
                    $x2 = $x1 + $s1;
                    $y2 = $y1 + $s0;

                    if ($this->faces) { 
                        foreach ($this->faces as $id => [$existX1, $existY1, $existX2, $existY2, $existConf]) {
                            if ($existX1 < $x2 && $existY1 < $y2 && $existX2 > $x1 && $existY2 > $y1) {
                                if ($confidence > $existConf) {
                                    $this->faces[$id] = [$x1, $y1, $x2, $y2, $confidence, $this->landmarks($landmarksMat, $l, $i, $j, $x1, $y1, $s0, $s1)];
                                }
                                continue 2;
                            }
                        }
                    }

                    $this->faces[]= [$x1, $y1, $x2, $y2, $confidence, $this->landmarks($landmarksMat, $l, $i, $j, $x1, $y1, $s0, $s1)];
                }
            }
        }
    }

    public function process()
    {
        $this->setFaces();
        $facePoints = [];
        foreach ($this->faces as [$x1, $y1, $x2, $y2, $conf, $landmarks]) {
            $points = [];
            foreach ($landmarks as $index => $point) {
                if ($index < 2) {
                    $points[] = $point[0];
                    $points[] = $point[1];
                }   
            }
            $facePoints[] = $points;
        }
        
        foreach ($facePoints as $point) {
            $random = random_int(0, 1);
            $values = [0, 255];
            $colorRandom = $this->isRandom ? $values[$random]: 0;
            $color = new Scalar($colorRandom, $colorRandom, $colorRandom);
            if ($this->isColorfull && !$this->isRandom) {
                $color = new Scalar(random_int(0, 255), random_int(0, 255), random_int(0, 255));
            } 

            $startX = $point[0] - 80;
            $startY = $point[1] + 5;
            $endX = $point[2] + 80;
            $endY = $point[3] - 10;
            \CV\rectangle(
                $this->src,
                $startX / $this->scale_w,
                $startY / $this->scale_h,
                $endX / $this->scale_w,
                $endY / $this->scale_h,
                $color,
                -1);
        }

        \CV\imwrite("results/$this->name", $this->src);
    }
}
