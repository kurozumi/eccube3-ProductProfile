<?php

/*
 * This file is part of the ProductProfile
 *
 * Copyright (C) 2017 kurozumi
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductProfile;

use Eccube\Application;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Plugin\ProductProfile\Entity\ProductProfile;
use Symfony\Component\Validator\Constraints as Assert;

class ProductProfileEvent
{

    /** @var  \Eccube\Application $app */
    private $app;

    /**
     * ProductProfileEvent constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * フォームに項目追加
     * 
     * @param EventArgs $event
     */
    public function onAdminProductEditIndexInitialize(EventArgs $event)
    {
        $Product = $event->getArgument('Product');
        $id = $Product->getId();

        // DBからデータ取得
        $ProductProfile = $this->app['eccube.plugin.product_profile.repository.product_profile']->findOneBy(array('product_id' => $Product->getId()));

        // データがなれけばエンティティインスタンス生成
        if (!$ProductProfile) {
            $ProductProfile = new ProductProfile();
        }

        $builder = $event->getArgument('builder');
        // ここからフォーム項目追加
        $builder
                ->add('plg_headline', 'text', array(
                    'required' => false, // 必須かどうか
                    'label' => 'ヘッドライン', // 項目名
                    'mapped' => false,
                    'data' => $ProductProfile->getHeadline() //データ
        ));
    }

    /**
     * DBに登録
     * 
     * @param EventArgs $event
     */
    public function onAdminProductEditIndexComplete(EventArgs $event)
    {
        $Product = $event->getArgument('Product');
        $id = $Product->getId();

        // DBからデータ取得
        $ProductProfile = $this->app['eccube.plugin.product_profile.repository.product_profile']->findOneBy(array("product_id" => $id));

        // データがなれけばエンティティインスタンス生成
        if (!$ProductProfile) {
            $ProductProfile = new ProductProfile();
        }

        // エンティティを更新
        $form = $event->getArgument('form');
        $ProductProfile
                ->setProduct($Product)
                // ここからDBに保存したい項目を追加
                ->setHeadline($form['plg_headline']->getData());

        // DB更新
        $this->app['orm.em']->persist($ProductProfile);
        $this->app['orm.em']->flush($ProductProfile);
    }

    /**
     * 商品詳細ページにヘッドラインを追加
     * 
     * @param TemplateEvent $event
     */
    public function onProductDetailRender(TemplateEvent $event)
    {
        // 検索するタグ
        $search = '<h3 id="detail_description_box__name" class="item_name">{{ Product.name }}</h3>';

        // DBかデータを取得
        $id = $this->app['request']->get('id');
        $ProductProfile = $this->app['eccube.plugin.product_profile.repository.product_profile']->findOneBy(array("product_id" => $id));

        // ヘッドラインがあれば表示
        if ($ProductProfile) {
            $tag = sprintf('<h4>%s</h4>', $ProductProfile->getHeadline());
            $source = str_replace($search, $tag . $search, $event->getSource());
            $event->setSource($source);
        }
    }

}
