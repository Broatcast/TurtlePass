var Encore = require('@symfony/webpack-encore');

Encore
// directory where compiled assets will be stored
    .setOutputPath('web/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    .addEntry('login', './assets/js/login.js')
    .addEntry('app', './assets/js/app.js')
    .addEntry('install', './assets/js/install.js')

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(false)
    .enableSassLoader()
    .enablePostCssLoader()

    .disableSingleRuntimeChunk()
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
