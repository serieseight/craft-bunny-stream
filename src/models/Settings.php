<?php

namespace serieseight\bunnystream\models;

use Craft;
use craft\base\Model;

/**
 * Bunny Stream settings
 */
class Settings extends Model
{
  public $libraryId = '';
  public $streamUrl = '';
  public $pullZone = '';
  public $streamKey = '';

  public function defineRules(): array
  {
    return [
      [['libraryId', 'streamUrl', 'pullZone', 'streamKey'], 'required'],
      // ...
    ];
  }
}
