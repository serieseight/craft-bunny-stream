<?php

namespace serieseight\bunnystream\controllers;

use craft\helpers\App;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use craft\web\View;
use Illuminate\Support\Collection;
use serieseight\bunnystream\models\BunnyStreamVideo;
use serieseight\bunnystream\Plugin;

class VideosController extends Controller
{
    public function actionIndex()
    {
        $settings = Plugin::getInstance()->settings;

        $libraryId = App::parseEnv($settings->libraryId);
        $streamUrl = App::parseEnv($settings->streamUrl);

        return $this->renderTemplate("bunny-stream/_videos", [
            "libraryId" => $libraryId,
            "streamUrl" => $streamUrl,
        ]);
    }

    public function actionGetVideos($page = 1, bool $forceRefresh = false) {
        $this->requireCpRequest();
        $this->requireAcceptsJson();

        $page = (int) $page;

        $videos = Plugin::getInstance()->video->listVideos($page, 1000, $forceRefresh);

        return $this->asJson([
            "page" => $page,
            "totalPages" => $videos->totalPages,
            "totalItems" => $videos->totalItems,
            "videos" => $videos->items,
        ]);
    }

    public function actionGeneratePresignedSignature() {
        $this->requireCpRequest();
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $guid = \Craft::$app->request->getBodyParam("guid");

        if(!$guid) {
            $this->asFailure("GUID missing");
        }

        $settings = Plugin::getInstance()->settings;

        $libraryId = App::parseEnv($settings->libraryId);
        $apiKey = App::parseEnv($settings->streamKey);
        $expirationTime = date('U', strtotime('30 minutes'));
        $videoId = $guid;

        $presignedSignature = hash("sha256", $libraryId . $apiKey . $expirationTime . $videoId);

        return $this->asJson([
            "presigned_signature" => $presignedSignature,
            "expiration_time" => $expirationTime,
            "video_guid" => $guid,
            "library_id" => $libraryId,
        ]);
    }

    public function actionVideo($videoId) {
        $this->requireCpRequest();

        $videos = Plugin::getInstance()->video->listVideos()->items ?? [];
        $videos = Collection::make($videos);

        $video = $videos->first(function($video) use ($videoId) {
            return $video->guid === $videoId;
        });

        if(!$video) {
            $this->setFailFlash("Video not found");

            return $this->redirect("bunny-stream/videos");
        }

        $settings = Plugin::getInstance()->settings;

        $libraryId = App::parseEnv($settings->libraryId);
        $streamUrl = App::parseEnv($settings->streamUrl);

        return $this->renderTemplate("bunny-stream/_video", [
            "video" => $video,
            "libraryId" => $libraryId,
            "streamUrl" => $streamUrl,
        ]);
    }

    public function actionSaveVideo() {
        $this->requireCpRequest();
        $this->requirePostRequest();

        $videoId = \Craft::$app->request->getBodyParam("videoId") ?? null;
        $title = \Craft::$app->request->getBodyParam("title") ?? null;

        if(!$videoId || !$title) {
            return $this->asFailure("Missing params");
        }

        try {
            Plugin::getInstance()->video->updateVideo($videoId, $title);

            return $this->asSuccess("Video updated");
        } catch (\Exception $e) {
            return $this->asFailure($e->getMessage());
        }
    }

    public function actionDeleteVideo() {
        $this->requireCpRequest();
        $this->requirePostRequest();

        $videoId = \Craft::$app->request->getBodyParam("videoId") ?? null;

        if(!$videoId) {
            return $this->asFailure("Missing params");
        }

        try {
            Plugin::getInstance()->video->deleteVideo($videoId);

            return $this->asSuccess("Video deleted", [], UrlHelper::cpUrl("bunny-stream/videos"));
        } catch (\Exception $e) {
            return $this->asFailure($e->getMessage());
        }
    }

    public function actionCreateVideo() {
        $this->requireCpRequest();
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $title = \Craft::$app->request->getBodyParam("title");

        if(!$title) {
            $this->asFailure("Title missing");
        }

        try {
            $video = Plugin::getInstance()->video->createVideo($title);

            return $this->asJson([
                "guid" => $video->guid,
            ]);
        } catch(\Exception $e) {
            return $this->asFailure($e->getMessage());
        }
    }

    public function actionGetPreview($videoId) {
        $this->requireCpRequest();

        $videos = Plugin::getInstance()->video->listVideos()->items ?? [];
        $videos = Collection::make($videos);

        $video = $videos->first(function($video) use ($videoId) {
            return $video->guid === $videoId;
        });

        if(!$video) {
            return $this->asFailure("Video not found");
        }

        $video = new BunnyStreamVideo($video);

        $oldMode = \Craft::$app->view->getTemplateMode();
        \Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $html = \Craft::$app->view->renderTemplate("bunny-stream/_preview", [
            "video" => $video,
        ]);
        \Craft::$app->view->setTemplateMode($oldMode);

        return $this->asJson([
            "html" => $html,
        ]);
    }
}