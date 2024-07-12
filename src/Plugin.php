<?php

namespace serieseight\bunnystream;

use Craft;
use craft\base\Event;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\web\UrlManager;
use craft\web\View;
use craft\web\twig\variables\Cp;
use lenz\linkfield\events\LinkTypeEvent;
use serieseight\bunnystream\fields\BunnyStreamVideo;
use serieseight\bunnystream\linktypes\BunnyVideoLinkType;
use serieseight\bunnystream\linktypes\hyper\BunnyVideo;
use serieseight\bunnystream\models\Settings;
use serieseight\bunnystream\services\Video as VideoAlias;
use lenz\linkfield\Plugin as LinkPlugin;
use verbb\hyper\services\Links;

/**
 * Bunny Stream plugin
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @author Series Eight <info@serieseight.com>
 * @copyright Series Eight
 * @license https://craftcms.github.io/license/ Craft License
 * @property-read VideoAlias $video
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.1';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => ['video' => VideoAlias::class],
        ];
    }

    public function init(): void
    {
        // Define a custom alias named after the namespace
        Craft::setAlias('@bunny-stream', __DIR__);

        // Set the controllerNamespace based on whether this is a console or web request
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'serieseight\\bunnystream\\console\\controllers';
        } else {
            $this->controllerNamespace = 'serieseight\\bunnystream\\controllers';
        }

        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
        });

        $this->setComponents([
            "video" => \serieseight\bunnystream\services\Video::class,
        ]);
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('bunny-stream/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots[$this->id] = __DIR__ . '/templates';
            }
        );

        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function(RegisterCpNavItemsEvent $event) {
                $event->navItems[] = [
                    'label' => 'Bunny Stream',
                    'url' => 'bunny-stream',
                    'icon' => '@bunny-stream/icon-mask.svg',
                    'subnav' => [
                        'videos' => [
                            'label' => 'Videos',
                            'url' => 'bunny-stream',
                        ],
                        'settings' => [
                            'label' => 'Settings',
                            'url' => 'bunny-stream/settings',
                        ]
                    ]
                ];
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['bunny-stream'] = 'bunny-stream/videos';
                $event->rules['bunny-stream/videos'] = 'bunny-stream/videos';
                $event->rules['bunny-stream/settings'] = 'bunny-stream/settings';
                $event->rules['bunny-stream/<videoId>'] = 'bunny-stream/videos/video';
            }
        );

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
            $event->types[] = BunnyStreamVideo::class;
        });

        if(class_exists(LinkPlugin::class)) {
            Event::on(
                LinkPlugin::class,
                LinkPlugin::EVENT_REGISTER_LINK_TYPES,
                function(LinkTypeEvent $event) {
                    $event->linkTypes["bunny"] = new BunnyVideoLinkType();
                }
            );
        }

        if(class_exists(Links::class)) {
            Event::on(
                Links::class,
                Links::EVENT_REGISTER_LINK_TYPES,
                function(RegisterComponentTypesEvent $event) {
                    $event->types["bunny"] = BunnyVideo::class;
                }
            );
        }
    }

    public function getSettingsResponse(): mixed
    {
        $url = \craft\helpers\UrlHelper::cpUrl('bunny-stream/settings');

        return \Craft::$app->controller->redirect($url);
    }
}
