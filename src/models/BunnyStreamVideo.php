<?php

namespace serieseight\bunnystream\models;

use Craft;
use craft\base\Model;
use craft\helpers\App;
use craft\helpers\Template;
use craft\web\View;
use Illuminate\Support\Collection;
use serieseight\bunnystream\Plugin;

/**
 * Bunny Stream Video model
 */
class BunnyStreamVideo extends Model
{
    public $guid = "";
    private $video = null;

    public function __construct($data)
    {
        if(is_object($data)) {
            $this->guid = $data->guid;
        } else {
            $this->guid = $data;
        }

        $this->video = $this->getVideo();
    }

    public function __toString()
    {
        return $this->getUrl();
    }

    public function __get($name)
    {
        if($name === "url") {
            return $this->getUrl();
        }

        if($name === "embedUrl") {
            return $this->getEmbedUrl();
        }

        if($name === "thumbnailUrl") {
            return $this->getThumbnailUrl();
        }

        if($name === "previewUrl") {
            return $this->getPreviewUrl();
        }

        if (property_exists($this , $name)) {
            return $this->$name ?? null;
        }

        if($this->video === null) {
            $video = $this->getVideo();

            if(!$video) {
                $this->video = null;
            } else {
                $this->video = $video;
            }

        }

        return $this->video->$name ?? null;
    }

    public function __isset($name)
    {
        if($name === "url") {
            return $this->getUrl();
        }

        if($name === "embedUrl") {
            return $this->getEmbedUrl();
        }

        if($name === "thumbnailUrl") {
            return $this->getThumbnailUrl();
        }

        if($name === "previewUrl") {
            return $this->getPreviewUrl();
        }

        if (property_exists($this , $name)) {
            return $this->$name ?? null;
        }

        if($this->video === null) {
            $video = $this->getVideo();

            if(!$video) {
                $this->video = null;
            } else {
                $this->video = $video;
            }

        }

        return $this->video->$name ?? null;
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            ['guid', 'text'],
            ['guid', 'default', 'value' => ''],
        ]);
    }

    public function embed(array $options = [])
    {
        if(empty($this->guid)) {
            return null;
        }

        $video = $this->getVideo();

        if(!$video) {
            return null;
        }

        $settings = Plugin::getInstance()->settings;

        $libraryId = App::parseEnv($settings->libraryId);
        $streamUrl = App::parseEnv($settings->streamUrl);

        $html = \Craft::$app->view->renderTemplate("bunny-stream/_embed", [
            "video" => $this,
            "libraryId" => $libraryId,
            "streamUrl" => $streamUrl,
            "options" => $options,
        ], View::TEMPLATE_MODE_CP);

        return Template::raw($html);
    }

    private function getUrl() {
        $settings = Plugin::getInstance()->settings;

        $libraryId = App::parseEnv($settings->libraryId);
        
        if(!$this->guid) {
            return null;
        }

        return "https://video.bunnycdn.com/play/$libraryId/$this->guid";
    }

    private function getEmbedUrl() {
        $settings = Plugin::getInstance()->settings;

        $libraryId = App::parseEnv($settings->libraryId);

        if(!$this->guid) {
            return null;
        }

        return "https://iframe.mediadelivery.net/embed/$libraryId/$this->guid";
    }

    private function getThumbnailUrl() {
        $settings = Plugin::getInstance()->settings;

        $streamUrl = App::parseEnv($settings->streamUrl);

        $video = $this->getVideo();

        if(!$video) {
            return null;
        }

        return "https://$streamUrl/$video->guid/$video->thumbnailFileName";
    }

    private function getPreviewUrl() {
        $settings = Plugin::getInstance()->settings;

        $streamUrl = App::parseEnv($settings->streamUrl);

        $video = $this->getVideo();

        if(!$video) {
            return null;
        }

        return "https://$streamUrl/$video->guid/preview.webp";
    }

    private function getVideo() {
        if($this->video === null) {
            $video = Plugin::getInstance()->video->getVideo($this->guid);

            if(!$video) {
                $this->video = null;
            } else {
                $this->video = $video;
            }
        }

        return $this->video;
    }
}
