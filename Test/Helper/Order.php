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
 * the Omedrec Startpage module to newer versions in the future.
 *
 * @copyright  Copyright (C) Effdocs, LLC
 * @license    http://www.binpress.com/license/view/l/45d152a594cd48488fda1a62931432e7
 */

/**
 * @category   Omedrec
 * @package    Omedrec_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Omedrec_Trello_Test_Helper_Order
 *
 *
 */
class Omedrec_Trello_Test_Helper_Order extends EcomDev_PHPUnit_Test_Case
{
    protected $alias = 'trello/order';

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
        /** @var Omedrec_Trello_Helper_Order $helper */
        $helper = Mage::helper($this->alias);

        $params = array('param1' => 'value1');

        /** @var Omedrec_Trello_Model_Order $card */
        $card = Mage::getModel('trello/order')->load(1, 'order_id');

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
        /** @var Omedrec_Trello_Helper_Order $helper */
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
        /** @var Omedrec_Trello_Model_Order $card */
        $card = Mage::getModel('trello/order')->load(1, 'order_id');

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

        /** @var Omedrec_Trello_Helper_Order $helper */
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

        /** @var Omedrec_Trello_Model_Order $card */
        $card = Mage::getModel('trello/order')->load(1, 'order_id');

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

        /** @var Omedrec_Trello_Helper_Order $helper */
        $helper = Mage::helper($this->alias);
        $helper->markOrderOutdated(Mage::getModel('sales/order')->load(1));
    }
}