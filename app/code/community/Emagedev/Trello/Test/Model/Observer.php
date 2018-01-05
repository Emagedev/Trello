<?php
/**
 * Emagedev extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Omedrec Welcome module to newer versions in the future.
 * If you wish to customize the Omedrec Welcome module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (C) Emagedev, LLC (https://www.emagedev.com/)
 * @license    https://opensource.org/licenses/BSD-3-Clause     New BSD License
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