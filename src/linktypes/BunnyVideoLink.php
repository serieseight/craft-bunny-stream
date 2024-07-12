<?php

namespace serieseight\bunnystream\linktypes;

use lenz\linkfield\models\Link;
use serieseight\bunnystream\models\BunnyStreamVideo;
use serieseight\bunnystream\Plugin;

class BunnyVideoLink extends Link
{
    public $guid = null;

    public function attributes(): array {
        return array_merge(parent::attributes(), [
            "guid",
        ]);
    }

    public function getIntrinsicText(): string {
        $video = $this->getVideo();
        return is_null($video) ? '' : (string)$video->title;
    }

    public function getIntrinsicUrl(): ?string {
        $video = $this->getVideo();
        return is_null($video) ? null : $video->embedUrl;
    }


    public function isEmpty(): bool {
        return empty($this->guid);
    }

    public function getVideo(): ?BunnyStreamVideo {
        if($this->isEmpty()) {
            return null;
        }

        $video = Plugin::getInstance()->video->getVideo($this->guid);

        return new BunnyStreamVideo($video);
    }
}