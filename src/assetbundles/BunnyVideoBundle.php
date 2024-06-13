<?php

namespace serieseight\bunnystream\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class BunnyVideoBundle extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@serieseight/bunnystream/assetbundles/';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'dist/css/all.min.css',
        ];

        $this->js = [
            'dist/js/all.js',
        ];

        parent::init();
    }
}