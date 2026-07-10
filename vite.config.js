import eslintPlugin from '@nabla/vite-plugin-eslint'
import laravel from 'laravel-vite-plugin'
import { resolve } from 'path'
import { defineConfig, loadEnv } from 'vite'
import dynamicImport from 'vite-plugin-dynamic-import'
import environmentPlugin from 'vite-plugin-environment'
import manifestSRI from 'vite-plugin-manifest-sri'

export default ({ mode }) => {
    process.env = { ...process.env, ...loadEnv(mode, process.cwd()) }
    const host = process.env.VITE_APP_URL

    return defineConfig({
        plugins: [
            dynamicImport(),
            eslintPlugin(),
            environmentPlugin({
                MODE: mode,
                BEHAVIORS_PATH: resolve(
                    __dirname,
                    'resources/frontend/scripts/behaviors'
                ),
                BEHAVIORS_EXTENSION: 'js'
            }),
            laravel({
                input: [
                    resolve(__dirname, 'resources/frontend/styles/app.css'),
                    resolve(__dirname, 'resources/frontend/scripts/app.js')
                ],
                detectTls: host,
                refresh: true
            }),
            manifestSRI()
        ],
        resolve: {
            alias: {
                '@': resolve(__dirname, 'resources/frontend'),
                '@root': resolve(__dirname, './'),
                '@vitrineUI': resolve(__dirname, 'vendor/area17/vitrine-ui'),
                '@vitrineUIComponents': resolve(
                    __dirname,
                    'vendor/area17/vitrine-ui/resources/views/components/'
                )
            }
        }
    })
}
