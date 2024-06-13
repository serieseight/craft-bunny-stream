<?php

namespace serieseight\bunnystream\controllers;

use craft\web\Controller;
use serieseight\bunnystream\Plugin;
use yii\web\Response;

class SettingsController extends Controller
{
    public function actionIndex()
    {
        return $this->renderTemplate(
            "bunny-stream/_settings",
            [
                "settings" => Plugin::getInstance()->settings,
            ],
        );
    }

    public function actionSave(): Response
    {
        $settings = Plugin::getInstance()->settings;

        $settings->libraryId = $this->request->getBodyParam('libraryId');
        $settings->streamUrl = $this->request->getBodyParam('streamUrl');
        $settings->pullZone = $this->request->getBodyParam('pullZone');
        $settings->streamKey = $this->request->getBodyParam('streamKey');

        $path = "plugins.bunny-stream.settings";

        \Craft::$app->getProjectConfig()->set($path, [
            'libraryId' => $settings->libraryId,
            'streamUrl' => $settings->streamUrl,
            'pullZone' => $settings->pullZone,
            'streamKey' => $settings->streamKey,
        ]);

        return $this->redirectToPostedUrl();
    }
}