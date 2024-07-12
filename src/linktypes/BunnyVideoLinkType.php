<?php

namespace serieseight\bunnystream\linktypes;

use lenz\linkfield\fields\LinkField;
use lenz\linkfield\models\Link;
use lenz\linkfield\models\LinkType;
use serieseight\bunnystream\Plugin;

class BunnyVideoLinkType extends LinkType
{
    public string $displayName = "Bunny Video";

    const MODEL_CLASS = BunnyVideoLink::class;

    public function getDisplayName(): string {
        return \Craft::t("app", $this->displayName);
    }

    public function getInputHtml(Link $value, bool $disabled): string {
        return \Craft::$app->view->renderTemplate(
            'bunny-stream/_input-select',
            [
                'linkType'    => $this,
                'selectField' => $this->getSelectField($value, $disabled),
            ]
        );
    }

    public function getSettingsHtml(LinkField $field): string {
        return false;
    }

    protected function getSelectField(Link $value, bool $disabled): array {
        return [
            'disabled' => $disabled,
            'id'       => "guid",
            'name'     => "guid",
            'options'  => $this->getVideoOptions(),
            'value'    => $value->guid ?? null,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function prepareLegacyData(mixed $data): ?array {
        if (!is_numeric($data)) {
            return null;
        }

        return [
            "guid" => $data
        ];
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