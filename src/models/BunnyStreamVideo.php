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
            $this->video = $data;
            $this->guid = $data->guid;
        } else {
            $this->guid = $data;
        }
    }

    public function __toString()
    {
        return $this->getUrl();
    }

    public function __get($name)
    {
        if (property_exists($this , $name)) {
            return $this->$name ?? null;
        }

        if($name === "embedUrl") {
            return $this->getEmbedUrl();
        }

        if($this->video === null) {
            $video = Plugin::getInstance()->video->getVideo($this->guid);

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
        if (property_exists($this , $name)) {
            return $this->$name ?? null;
        }

        if($name === "embedUrl") {
            return $this->getEmbedUrl();
        }

        if($this->video === null) {
            $video = Plugin::getInstance()->video->getVideo($this->guid);

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

        $videoId = $this->guid;

        $videos = Plugin::getInstance()->video->listVideos()->items ?? [];
        $videos = Collection::make($videos);

        $video = $videos->first(function($video) use ($videoId) {
            return $video->guid === $videoId;
        });

        if(!$video) {
            return null;
        }

        $settings = Plugin::getInstance()->settings;

        $libraryId = App::parseEnv($settings->libraryId);
        $streamUrl = App::parseEnv($settings->streamUrl);

        $html = \Craft::$app->view->renderTemplate("bunny-stream/_embed", [
            "video" => $video,
            "libraryId" => $libraryId,
            "streamUrl" => $streamUrl,
            "options" => $options,
        ], View::TEMPLATE_MODE_CP);

        return Template::raw($html);
    }

    private function getUrl() {
        $settings = Plugin::getInstance()->settings;

        $libraryId = App::parseEnv($settings->libraryId);

        return "https://video.bunnycdn.com/play/$libraryId/$this->guid";
    }

    private function getEmbedUrl() {
        $settings = Plugin::getInstance()->settings;

        $libraryId = App::parseEnv($settings->libraryId);

        return "https://iframe.mediadelivery.net/embed/$libraryId/$this->guid";
    }
}
