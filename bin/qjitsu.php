#!/usr/bin/env php
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
$autoload_files = [
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../autoload.php',
    __DIR__ . '/../vendor/autoload.php',

];

foreach ($autoload_files as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
call_user_func(
    function () {
        $container_files = [
            __DIR__ . '/../../../../config/container.php',
            __DIR__ . '/../../config/container.php',
        ];

        foreach ($container_files as $file) {
            if (file_exists($file)) {
                /** @var \Psr\Container\ContainerInterface $container */
                $container = require $file;
                break;
            }
        }

        if (!isset($container)) {
            $container = require __DIR__ . '/../config/container.php';
        }

        /** @var \Symfony\Component\Console\Application $app */
        $app = $container->get(\Symfony\Component\Console\Application::class);
        $app->run();
    }
);
