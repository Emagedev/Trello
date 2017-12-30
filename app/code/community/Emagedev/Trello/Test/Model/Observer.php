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
 * @subpackage Test
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Test_Model_Observer
 */
class Emagedev_Trello_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
{
    protected $alias = 'trello/observer';

    /**
     * Check is correct orders will be marked as outdated or archived
     *
     * @loadFixture
     * @test
     */
    public function checkObsolescenceOfOrderStatus()
    {
        /** @var Emagedev_Trello_Model_Observer $observer */
        $observer = Mage::getModel($this->alias);

        $helperMock = $this->mockHelper('trello/order', array('archiveOrder', 'markOrderOutdated'));

        $helperMock
            ->expects($this->once())
            ->method('markOrderOutdated')
            ->with($this->callback(function($order){
                if ($this->expected()->getOutdated() == $order->getId()) {
                    return $order;
                }

                return false;
            }));

        $helperMock
            ->expects($this->once())
            ->method('archiveOrder')
            ->with($this->callback(function($order){
                if ($this->expected()->getArchived() == $order->getId()) {
                    return $order;
                }

                return false;
            }));

        $helperMock->replaceByMock('helper');

        $orderMock = $this->mockModel('sales/order', array('getUpdatedAt'));

        $orderMock
            ->expects($this->any())
            ->method('getUpdatedAt')
            ->willReturn($this->callback(function($order) {
                if (
                    $order->getId() == $this->expected()->getOutdated() ||
                    $order->getId() == $this->expected()->getArchived()
                ) {
                    $datetime = new DateTime('now');
                    $datetime->sub(new DateInterval('D5'));

                    return $datetime->format(DateTime::W3C);
                }
            }));

        $orderMock->replaceByMock('model');

        $observer->markOrArchiveOutdatedOrders(new Varien_Event_Observer());
    }
}