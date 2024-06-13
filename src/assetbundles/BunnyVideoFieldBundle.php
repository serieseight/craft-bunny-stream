<?php

namespace serieseight\bunnystream\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class BunnyVideoFieldBundle extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@serieseight/bunnystream/assetbundles/';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'dist/css/field.min.css',
        ];

        $this->js = [
            'dist/js/field.js',
        ];

        parent::init();
    }
}