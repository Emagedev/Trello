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
 * Class Emagedev_Trello_Test_Helper_Order
 */
class Emagedev_Trello_Test_Helper_Order extends EcomDev_PHPUnit_Test_Case
{
    protected $alias = 'trello/card';

    /** @var EcomDev_PHPUnit_Mock_Proxy */
    protected $apiMock;

    public function setUp()
    {
        $this->apiMock = $this->mockModel('trello/api');
    }

    /**
     * Check if order card update request created in right way
     *
     * @loadFixture
     * @test
     */
    public function checkUpdateOrderCard()
    {
        /** @var Emagedev_Trello_Helper_Order $helper */
        $helper = Mage::helper($this->alias);

        $params = array('param1' => 'value1');

        /** @var Emagedev_Trello_Model_Card $card */
        $card = Mage::getModel('trello/card')->load(1, 'order_id');

        $this->apiMock
            ->expects($this->once())
            ->method('updateCard')
            ->withConsecutive(array(
                $this->equalTo($card->getCardId()),
                $this->equalTo($params)
            ));

        $this->apiMock->replaceByMock('model');

        $helper->updateOrderCard(Mage::getModel('sales/order')->load(1), $params);
    }

    /**
     * Check if order creation request created in right way
     *
     * @loadFixture
     * @test
     */
    public function checkCreateOrderCard()
    {
        /** @var Emagedev_Trello_Helper_Order $helper */
        $helper = Mage::helper($this->alias);

        $statusListId = 'abc';

        $this->apiMock->replaceByMock('model');

        $order = Mage::getModel('sales/order')->load(1);
        $dateTime = new DateTime($order->getCreatedAt(), new DateTimeZone('UTC'));

        $params = array(
            'idList'      => $statusListId,
            'name'        => $helper->__('Order #%d', $order->getIncrementId()),
            'desc'        => $helper->getOrderCardDescription($order),
            'due'         => $dateTime->format(DateTime::W3C),
            'dueComplete' => 'true'
        );

        $coreHelperMock = $this->mockHelper('trello', array('getStatusListId'));

        $coreHelperMock
            ->expects($this->any())
            ->method('getStatusListId')
            ->willReturn(new Varien_Object(array('list_id' => $statusListId)));

        $coreHelperMock->replaceByMock('helper');

        $this->apiMock
            ->expects($this->once())
            ->method('createCard')
            ->with(
                $this->equalTo($params)
            );

        $helper->createOrderCard($order);
    }

    /**
     * Check if order archivation request created correctly
     *
     * @loadFixture
     * @test
     */
    public function checkArchiveOrderCard()
    {
        /** @var Emagedev_Trello_Model_Card $card */
        $card = Mage::getModel('trello/card')->load(1, 'order_id');

        $helper = $this->mockHelper($this->alias, array('getOrderCard'));
        $helper
            ->expects($this->any())
            ->method('getOrderCard')
            ->willReturn($card);

        $helper->replaceByMock('helper');

        $this->apiMock
            ->expects($this->once())
            ->method('archiveCard')
            ->withConsecutive(array(
                $this->equalTo($card->getCardId())
            ));

        $this->apiMock->replaceByMock('model');

        $order = Mage::getModel('sales/order')->load(1);

        /** @var Emagedev_Trello_Helper_Order $helper */
        $helper->archiveOrder($order);

        $this->assertEquals('1', $card->getData('archived'));
    }

    /**
     * Check update request for creation of outdated label on card
     *
     * @loadFixture
     * @test
     */
    public function checkMarkOrderOutdated()
    {
        $this->apiMock->replaceByMock('model');

        /** @var Emagedev_Trello_Model_Card $card */
        $card = Mage::getModel('trello/card')->load(1, 'order_id');

        $params = array(
            'dueComplete' => false
        );

        $this->apiMock
            ->expects($this->once())
            ->method('updateCard')
            ->withConsecutive(array(
                $this->equalTo($card->getCardId()),
                $this->equalTo($params)
            ));

        /** @var Emagedev_Trello_Helper_Order $helper */
        $helper = Mage::helper($this->alias);
        $helper->markOrderOutdated(Mage::getModel('sales/order')->load(1));
    }
}