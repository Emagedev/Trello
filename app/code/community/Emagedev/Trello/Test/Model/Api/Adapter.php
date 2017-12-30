<?php
/**
 * Emagedev extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * Copyright (C) Effdocs, LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 *
 * This source file is proprietary and confidential
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Emagedev Trello module to newer versions in the future.
 *
 * @copyright  Copyright (C) Effdocs, LLC
 * @license    http://www.binpress.com/license/view/l/45d152a594cd48488fda1a62931432e7
 */

/**
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Test_Model_Api_Adapter
 */
class Emagedev_Trello_Test_Model_Api_Adapter extends EcomDev_PHPUnit_Test_Case
{
    protected $alias = 'trello/api_adapter';

    /**
     * Check if API request URL created correctly
     *
     * @param array $actions
     * @param array $params
     *
     * @dataProvider dataProvider
     * @test
     */
    public function checkCombineUrl($actions, $params)
    {
        /** @var Emagedev_Trello_Model_Api_Adapter $adapter */
        $adapter = Mage::getModel($this->alias);

        $url = $adapter->combineUrl($actions, $params);

        $this->assertEquals($this->expected('auto')->getUrl(), $url);
    }
}