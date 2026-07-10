module.exports = {
    plugins: [
        require("tailwindcss/nesting"),
        require("tailwindcss"),
        require("autoprefixer"),
        ...(process.env.NODE_ENV === "production"
            ? [
                  require("postcss-variable-compress")([
                      "--breakpoint",
                      "--scrollbar-width",
                  ]),
              ]
            : []),
    ],
};
