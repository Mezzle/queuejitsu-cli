<?php
/**
 * Copyright (c) 2017 Martin Meredith
 * Copyright (c) 2017 Stickee Technology Limited
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\PhpFileProvider;

$cacheConfig = [
    'config_cache_path' => 'data/config-cache.php',
];

if (class_exists(DotEnv::class)) {
    try {
        (new Dotenv(__DIR__ . '/../'))->load();
    } catch (InvalidPathException $e) {
        // Pass - no .env file
    }
}

$aggregator = new \Zend\ConfigAggregator\ConfigAggregator(
    [
        // Include cache configuration
        new ArrayProvider($cacheConfig),

        // Default App module config
        QueueJitsu\ConfigProvider::class,
        QueueJitsu\Cli\ConfigProvider::class,

        // Load application config in a pre-defined order in such a way that local settings
        // overwrite global settings. (Loaded as first to last):
        //   - `global.php`
        //   - `*.global.php`
        //   - `local.php`
        //   - `*.local.php`
        new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),

        // Load development config if it exists
        new PhpFileProvider('config/development.config.php'),
    ], $cacheConfig['config_cache_path']
);

return $aggregator->getMergedConfig();
