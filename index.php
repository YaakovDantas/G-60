<?php

require __DIR__.'/vendor/autoload.php';
require_once(__DIR__ . '/vendor/cloudmersive/cloudmersive_imagerecognition_api_client/vendor/autoload.php');
require 'init.php';


use App\ImageHandler\OpenCVHandler;
use App\ImageHandler\CloudMersiveHandler;


$isRandomParasite = false;
$randomOpt = getopt(null, ["parasite:"]);
if (count($randomOpt)) {
  $isRandomParasite = true;
}

$img = false;
$imgOpt = getopt(null, ["img:"]);
if (count($imgOpt)) {
  $img = $imgOpt['img'];
}

$isColorFull = false;
$colorOpt = getopt(null, ["color:"]);
if (count($colorOpt)) {
  $isColorFull = true;
}

$option = 'opencv';
$parasiteOpt = getopt(null, ["option:"]);
if (count($parasiteOpt)) {
  $option = $parasiteOpt['option'];
}


if (!$img) {
  echo '--img=<name of image.extension> is required' . PHP_EOL;
  die();
}

$parasite_options = [
  'opencv' => new OpenCVHandler($img, $isRandomParasite, $isColorFull),
  'cloudmersive' => new CloudMersiveHandler('images/' . $img, $isRandomParasite, $isColorFull),
];

$parasite = $parasite_options[$option];
$parasite->process();

echo "Image processed by '$option' and storaged in results/" . $img . PHP_EOL;
