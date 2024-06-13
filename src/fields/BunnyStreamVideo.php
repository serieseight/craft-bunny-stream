<?php

namespace serieseight\bunnystream\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\ArrayHelper;
use craft\helpers\Cp;
use craft\helpers\Json;
use serieseight\bunnystream\assetbundles\BunnyVideoFieldBundle;
use serieseight\bunnystream\Plugin;
use yii\db\Schema;
use serieseight\bunnystream\models\BunnyStreamVideo as BunnyStreamVideoModel;

/**
 * Bunny Stream Video2 field type
 */
class BunnyStreamVideo extends Field
{
    protected $value;

    public static function displayName(): string
    {
        return Craft::t('bunny-stream', 'Bunny Stream Video');
    }

    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if ($value === null) {
            if ($this->required) {
                return null;
            }

            return new BunnyStreamVideoModel(null);
        }

        if ($value instanceof BunnyStreamVideoModel && $value->guid) {
            return $this->value = $value->guid;
        }

        $value = Json::decodeIfJson($value);

        while(is_array($value)) {
            $value = ArrayHelper::getValue($value, 'guid');
        }

        if(is_string($value)) {
            return $this->value = new BunnyStreamVideoModel($value);
        }

        return new BunnyStreamVideoModel(null);
    }

    protected function inputHtml(mixed $value, ElementInterface $element = null): string
    {
        Craft::$app->view->registerAssetBundle(BunnyVideoFieldBundle::class);

        $videos = Plugin::getInstance()->video->listVideos(1, 1000)->items ?? [];

        $videos = array_map(function($video) {
            return [
                "label" => $video->title,
                "value" => $video->guid,
            ];
        }, $videos);

        $select = Cp::selectizeHtml([
            'id' => $this->getInputId(),
            'describedBy' => $this->describedBy,
            'name' => $this->handle,
            'value' => $value->guid,
            'options' => $videos,
            'class' => 'bunny-video-field',
        ]);

        $preview = "<div class='bunny-video-preview'></div>";

        // In progress
        $preview = "";

        return $select . $preview;
    }
}
