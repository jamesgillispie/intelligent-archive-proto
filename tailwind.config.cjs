const { resolve, join } = require("path");
const rootPath = resolve(__dirname, "");
const vitrinePath = resolve(__dirname, "vendor/area17/vitrine-ui");

// A17 tailwind plugins
const {
    Setup,
    ApplyColorVariables,
    ColorTokens,
    Components,
    Container,
    CssInJs,
    DevTools,
    FullBleedScroller,
    GridGap,
    GridLayout,
    GridLine,
    InteractionMediaQueries,
    Keyline,
    Layout,
    PseudoElements,
    Scrollbar,
    Spacing,
    SpacingTokens,
    StrokeFull,
    Typography,
    Underline,
} = require("@area17/a17-tailwind-plugins");

// conf
const feConfig = require(join(rootPath, "frontend.config.json"));

/** @type {import("tailwindcss").Config} */
module.exports = {
    content: [
        join(rootPath, "/resources/frontend/**/*.js"),
        join(rootPath, "/resources/frontend/vitrine-ui/**/*.{js,json}"),
        join(rootPath, "/resources/views/**/*.{blade.php,.php}"),
        join(vitrinePath, "/resources/frontend/theme/**/*.{js,json}"),
        join(vitrinePath, "/resources/frontend/**/*.js"),
        join(vitrinePath, "/resources/views/**/*.blade.php"),
        join(vitrinePath, "/app/View/**/*.php"),
    ],
    safelist: [
        ...Object.keys(feConfig.typography.typesets).map(
            (token) => `f-${token}`,
        ),
    ],
    corePlugins: {
        container: false,
    },
    plugins: [
        Setup,
        ColorTokens,
        Components,
        Container,
        CssInJs,
        DevTools,
        FullBleedScroller,
        GridGap,
        GridLayout,
        GridLine,
        InteractionMediaQueries,
        Keyline,
        Layout,
        PseudoElements,
        Scrollbar,
        Spacing,
        StrokeFull,
        Typography,
        Underline,
    ],
    theme: {
        screens: feConfig.structure.breakpoints,
        mainColWidths: feConfig.structure.container,
        innerGutters: feConfig.structure.gutters.inner,
        outerGutters: feConfig.structure.gutters.outer,
        columnCount: feConfig.structure.columns,
        fontFamilies: feConfig.typography.families,
        typesets: feConfig.typography.typesets,
        spacingGroups: feConfig.spacing.groups,
        spacing: SpacingTokens(feConfig.spacing.tokens),
        colors: feConfig.color.tokens,
        borderColor: ApplyColorVariables(
            feConfig.color.tokens,
            feConfig.color.border,
        ),
        outlineColor: ApplyColorVariables(
            feConfig.color.tokens,
            feConfig.color.border,
        ),
        textDecorationColor: ApplyColorVariables(
            feConfig.color.tokens,
            feConfig.color.border,
        ),
        divideColor: ApplyColorVariables(
            feConfig.color.tokens,
            feConfig.color.border,
        ),
        ringColor: ApplyColorVariables(
            feConfig.color.tokens,
            feConfig.color.ring,
        ),
        ringOffsetColor: ApplyColorVariables(
            feConfig.color.tokens,
            feConfig.color.border,
        ),
        textColor: ApplyColorVariables(
            feConfig.color.tokens,
            feConfig.color.text,
        ),
        backgroundColor: ApplyColorVariables(
            feConfig.color.tokens,
            feConfig.color.background,
        ),
        backgroundImage: feConfig.backgroundImage,
        underlineColor: ApplyColorVariables(
            feConfig.color.tokens,
            feConfig.color.text,
        ),
        aspectRatio: feConfig.ratios,
        components: feConfig.components,
        css: feConfig.css,
        borderRadius: feConfig.borderRadius,
        boxShadow: feConfig.boxShadow,
        extend: {
            zIndex: {
                99: 99,
                999: 999,
                9999: 9999,
                99999: 99999,
                999999: 999999,
            },
            minHeight: ({ theme }) => ({ ...theme("spacing") }),
            minWidth: ({ theme }) => ({ ...theme("spacing") }),
            maxWidth: ({ theme }) => ({
                ...theme("spacing"),
                "1/2": "50%",
            }),
            width: {
                unset: "unset",
                auto: "auto",
            },
            spacing: {
                "safe-top": "env(safe-area-inset-top)",
                "safe-bottom": "env(safe-area-inset-bottom)",
                "safe-left": "env(safe-area-inset-left)",
                "safe-right": "env(safe-area-inset-right)",
                "safe-screen": "min(100vh, 100dvh)",
                "max-screen": "calc(100lvh - var(--spacing-header))",
                gutter: "var(--inner-gutter)",
                "space-container": "var(--container-width, 100%)",
                "outer-gutter": "var(--outer-gutter, 0px)",
                "bleeding-gutter": "calc(var(--outer-gutter, 0px) / 2)",
                "dynamic-outer":
                    "calc((100vw - (var(--container-width, 100%) - (2 * var(--outer-gutter, 0)))) / 2)",
                header: "var(--spacing-header)",
            },
            transitionDuration: {
                1: "1ms",
            },
            transitionTimingFunction: {
                "out-sine": "cubic-bezier(0.61, 1, 0.88, 1)",
            },
            textDecorationColor: {
                inherit: "inherit",
                current: "currentColor",
                transparent: "transparent",
            },
            backgroundColor: {
                inherit: "inherit",
                current: "currentColor",
                transparent: "transparent",
            },
        },
    },
};
