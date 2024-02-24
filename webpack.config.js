require('dotenv-defaults').config({
    path: './assets/.env'
})

var Encore = require('@symfony/webpack-encore');

Encore
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()

    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .enableSingleRuntimeChunk()

    .autoProvidejQuery()
    .enableSassLoader()

    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('app', [
        './assets/js/app.js',
    ])

    .addEntry('theme', [
        './assets/themes/' + process.env.theme + '/index.js',
        './assets/themes/' + process.env.theme + '/images/logo.png',
        './assets/themes/' + process.env.theme + '/images/logo-login.png',
        './assets/themes/' + process.env.theme + '/images/logo-menu.png',
        './assets/themes/' + process.env.theme + '/images/logo-small.png'
    ])

    .autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery',
    })
;

module.exports = Encore.getWebpackConfig();
