const plugin = require('tailwindcss/plugin')

module.exports = {
    corePlugins: {
        preflight: false,
    },
    content: [
        "./src/templates/**/*.{twig,html}",
        "./src/assetbundles/src/**/*.{css,scss,js}",
    ],
    safelist: [],
    theme: {
        colors: {
            current: "currentColor",
            transparent: "transparent",
            inherit: "inherit",
            black: "black",
            white: "white",
        },
    },
    plugins: [
        plugin(function ({ matchUtilities, theme }) {
            matchUtilities(
                {
                    'auto-fill': (value) => ({
                        gridTemplateColumns: `repeat(auto-fill, minmax(min(${value}, 100%), 1fr))`,
                    }),
                    'auto-fit': (value) => ({
                        gridTemplateColumns: `repeat(auto-fit, minmax(min(${value}, 100%), 1fr))`,
                    }),
                },
                {
                    values: theme('width', {}),
                }
            )
        })]
};