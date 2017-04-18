<?php

/*
 * This file is part of the ProductProfile
 *
 * Copyright (C) 2017 kurozumi
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductProfile\ServiceProvider;

use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Plugin\ProductProfile\Form\Type\ProductProfileConfigType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class ProductProfileServiceProvider implements ServiceProviderInterface
{

    public function register(BaseApplication $app)
    {
        // Repository
        $app['eccube.plugin.product_profile.repository.product_profile'] = $app->share(function() use ($app) {
                return $app['orm.em']->getRepository('Plugin\ProductProfile\Entity\ProductProfile');
        });

        // ログファイル設定
        $app['monolog.logger.productprofile'] = $app->share(function ($app) {

            $logger = new $app['monolog.logger.class']('productprofile');

            $filename = $app['config']['root_dir'].'/app/log/productprofile.log';
            $RotateHandler = new RotatingFileHandler($filename, $app['config']['log']['max_files'], Logger::INFO);
            $RotateHandler->setFilenameFormat(
                'productprofile_{date}',
                'Y-m-d'
            );

            $logger->pushHandler(
                new FingersCrossedHandler(
                    $RotateHandler,
                    new ErrorLevelActivationStrategy(Logger::ERROR),
                    0,
                    true,
                    true,
                    Logger::INFO
                )
            );

            return $logger;
        });

    }

    public function boot(BaseApplication $app)
    {
    }

}
