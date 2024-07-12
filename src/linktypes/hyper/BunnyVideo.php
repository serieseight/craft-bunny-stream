<?php

namespace serieseight\bunnystream\linktypes\hyper;

use serieseight\bunnystream\models\BunnyStreamVideo;
use serieseight\bunnystream\Plugin;
use verbb\hyper\base\Link;

use Craft;
use verbb\hyper\fieldlayoutelements\LinkField;
use verbb\hyper\fields\HyperField;

class BunnyVideo extends Link
{
    public static function displayName(): string
    {
        return Craft::t("app", "Bunny Video");
    }

    public function getLinkUrl(): ?string
    {
        $video = $this->getLinkVideo();
        return is_null($video) ? null : $video->embedUrl;
    }

    public function getLinkText(): ?string
    {
        $video = $this->getLinkVideo();
        return is_null($video) ? '' : (string)$video->title;
    }

    public function getVideo(): ?BunnyStreamVideo
    {
        if(!$this->linkValue) {
            return null;
        }

        $video = Plugin::getInstance()->video->getVideo($this->linkValue);

        return new BunnyStreamVideo($video);
    }

    public function getInputHtml(LinkField $layoutField, HyperField $field): ?string
    {
        $variables = $this->getInputHtmlVariables($layoutField, $field);

        return Craft::$app->getView()->renderTemplate("bunny-stream/_hyper/input-select", $variables);
    }

    public function getSettingsHtml(): ?string
    {
        return "";
    }

    public function getVideoOptions(): array {
        $videos = Plugin::getInstance()->video->listVideos(1, 1000)->items ?? [];

        $videos = array_map(function($video) {
            return [
                "label" => $video->title,
                "value" => $video->guid,
            ];
        }, $videos);

        return $videos;
    }
}