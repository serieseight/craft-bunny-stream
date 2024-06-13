<?php

namespace serieseight\bunnystream\services;

use Craft;
use craft\helpers\App;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Mpdf\Mpdf;
use serieseight\bunnystream\Plugin;
use serieseight\bunnystream\records\VideoRecord;
use yii\base\Component;
use serieseight\bunnystream\elements\Video as VideoElement;

/**
 * Video service
 */
class Video extends Component
{
    private $accessKey;
    private $libraryId;
    private $client;
    private $apiUrl;

    public function __construct($config = [])
    {
        $this->accessKey = App::parseEnv(Plugin::getInstance()->getSettings()->streamKey);
        $this->libraryId = App::parseEnv(Plugin::getInstance()->getSettings()->libraryId);

        $this->apiUrl = 'https://video.bunnycdn.com/library/' . $this->libraryId . '/';

        $this->client = Craft::createGuzzleClient([
            'base_uri' => $this->apiUrl,
            'headers' => [
                'AccessKey' => $this->accessKey,
                'accept' => 'application/json',
                'content-type' => 'application/*+json',
            ],
        ]);

        parent::__construct($config);
    }

    public function listVideos(int $page = 1, int $limit = 1000, bool $forceRefresh = false, string $search = null) {
        $cacheKey = "bunny-videos-$this->libraryId-$page-$limit";

        if(!$forceRefresh && Craft::$app->cache->exists($cacheKey)) {
            return Craft::$app->cache->get($cacheKey);
        }

        $query = [
            "page" => $page,
            "itemsPerPage" => $limit,
        ];

        if(strlen($search)) {
            $query["search"] = $search;
        }

        try {
            $request = $this->client->get("videos", [
                "query" => $query,
            ]);

            $response = $request->getBody()->getContents();
            $response = json_decode($response);

            $items = $response;

            $items->totalPages = ceil($items->totalItems / $limit);

            // 1 hour
            Craft::$app->cache->set($cacheKey, $items, 60 * 60);

            return $items;
        } catch (GuzzleException $e) {
            return [];
        }
    }

    public function createVideo($title) {
        try {
            $request = $this->client->post('videos', [
                "body" => json_encode([
                    "title" => $title,
                ]),
            ]);

            $response = $request->getBody()->getContents();
            $response = json_decode($response);

            return $response;
        } catch (GuzzleException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function updateVideo($videoId, $title) {
        try {
            $request = $this->client->post("videos/$videoId", [
                "body" => json_encode([
                    "title" => $title,
                ]),
            ]);

            $response = $request->getBody()->getContents();
            $response = json_decode($response);

            $this->listVideos(1, 1000, true);

            return $response;
        } catch (GuzzleException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function deleteVideo($videoId) {
        try {
            $request = $this->client->delete("videos/$videoId");

            $response = $request->getBody()->getContents();
            $response = json_decode($response);

            $this->listVideos(1, 1000, true);

            return $response;
        } catch (GuzzleException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getVideo($videoId) {
        $videos = Plugin::getInstance()->video->listVideos()->items ?? [];
        $videos = Collection::make($videos);

        return $videos->first(function($video) use ($videoId) {
            return $video->guid === $videoId;
        });
    }
}
