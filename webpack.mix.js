const mix = require("laravel-mix");

mix
    .options({
        processCssUrls: false,
        sourcemaps: false,
    })

    .js([
        "src/assetbundles/src/js/bunny-stream-video.js",
    ], "./src/assetbundles/dist/js/all.js")

    .js([
        "src/assetbundles/src/js/bunny-stream-video-field.js",
    ], "./src/assetbundles/dist/js/field.js")

    .postCss("src/assetbundles/src/css/main.css", "./src/assetbundles/dist/css/all.min.css", [
        require("postcss-import"),
        require("tailwindcss/nesting"),
        require("tailwindcss"),
        require("postcss-pxtorem")({
            propWhiteList: ["*"],
            rootValue: 16,
            unitPrecision: 3,
            mediaQuery: true,
            minPixelValue: 1,
        }),
        require("postcss-prefixer")({
            prefix: "bunny-stream-tw-",
        }),
    ])

    .postCss("src/assetbundles/src/css/field.css", "./src/assetbundles/dist/css/field.min.css", [
        require("tailwindcss/nesting"),
        require("tailwindcss"),
    ])

    .webpackConfig({
        module: {
            rules: [
                {
                    test: /\.css/,
                    loader: "import-glob",
                },
            ],
        },
    });