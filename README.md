# Bunny Stream



## Requirements

This plugin requires Craft CMS 5.0.0 or later, and PHP 8.2.11 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Bunny Stream”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

composer config repositories.craft-bunny-stream git git@github.com:serieseight/craft-bunny-stream.git

# tell Composer to load the plugin
composer require serieseight/craft-bunny-stream:v5.x-dev@dev

# tell Craft to install the plugin
./craft plugin/install bunny-stream
```

## Usage

By default, the bunny stream video field will output the video's direct play URL.
```
# Outputs https://video.bunnycdn.com/play/**libraryId**/**videoGuid**

{{ entry.bunnyField }}
```

You can also get the embed URL using `.embedUrl`
```
# Outputs https://iframe.mediadelivery.net/embed/**libraryId**/**videoGuid**

{{ entry.bunnyField.embedUrl }}
```

You can also get pre-made embed markup using `.embed(options)`
```
# Outputs iframe markup
# Options are optional

{{ entry.bunnyField.embed({
  width: "",
  height: "",
  class: "",
  style: "",
  responsive: false,
  autoplay: false,
  preload: false,
  loop: false,
  muted: false,
  controls: false,
}) }}
```

The full video object returned [from the API](https://docs.bunny.net/reference/video_getvideo) can also be accessed
```
{{ entry.bunnyField.dateUploaded }}
{{ entry.bunnyField.views }}
{{ entry.bunnyField.width }}
{{ entry.bunnyField.height }}

# For a full list, see the video object returned from the API in the link above
```