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
 * @subpackage Helper
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Omedrec_Trello_Helper_Order
 *
 * Control order cards
 */
class Omedrec_Trello_Helper_Order extends Mage_Core_Helper_Abstract
{
    /**
     * Get trello card connected to order
     *
     * @param Mage_Sales_Model_Order $order
     * @param bool                   $create
     *
     * @return bool|Omedrec_Trello_Model_Order
     */
    public function getOrderCard(Mage_Sales_Model_Order $order, $create = false)
    {
        /** @var Omedrec_Trello_Model_Order $orderCardLink */
        $orderCardLink = Mage::getModel('trello/order')->load($order->getId(), 'order_id');

        if (!$orderCardLink || !$orderCardLink->getId()) {
            if (!$create) {
                return false;
            }

            $orderCardLink = $this->createOrderCard($order);
        }

        return $orderCardLink;
    }

    /**
     * Create new Trello card and fill it with order info,
     * connect this card and order
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Omedrec_Trello_Model_Order
     */
    public function createOrderCard(Mage_Sales_Model_Order $order)
    {
        $statusList = $this->getDataHelper()->getStatusListId($order->getStatus());

        if (!$statusList) {
            $statusList = $this->createStatusList($order->getStatus());
        }

        $dateTime = new DateTime($order->getCreatedAt(), new DateTimeZone('UTC'));

        /** @var Omedrec_Trello_Model_Api $api */
        $api = Mage::getModel('trello/api');
        $trelloCard = $api->createCard(
            array(
                'idList'      => $statusList->getListId(),
                'name'        => $this->__('Order #%d', $order->getIncrementId()),
                'desc'        => $this->getOrderCardDescription($order),
                'due'         => $dateTime->format(DateTime::W3C),
                'dueComplete' => 'true'
            )
        );

        $trelloCard = new Varien_Object($trelloCard);

        /** @var Omedrec_Trello_Model_Order $orderCardLink */
        $orderCardLink = Mage::getModel('trello/order');

        $orderCardLink
            ->setOrderId($order->getId())
            ->setCardId($trelloCard->getId());

        $orderCardLink
            ->save();

        return $orderCardLink;
    }

    /**
     * Update order card and set new params
     *
     * @param Mage_Sales_Model_Order $order
     * @param array                  $params
     *
     * @return Varien_Object
     */
    public function updateOrderCard(Mage_Sales_Model_Order $order, $params)
    {
        $card = $this->getOrderCard($order);

        if (!$card) {
            return false;
        }

        $trelloCard = $this->getApi()
            ->updateCard(
                $card->getCardId(),
                $params
            );

        return new Varien_Object($trelloCard);
    }

    /**
     * Mark order card as archived (update with archived param)
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Varien_Object
     */
    public function archiveOrder(Mage_Sales_Model_Order $order)
    {
        $card = $this->getOrderCard($order);

        $trelloCard = $this->getApi()
            ->archiveCard(
                $card->getCardId()
            );

        $card
            ->setArchived('1')
            ->save();

        return new Varien_Object($trelloCard);
    }

    /**
     * Get order card and put it in some list
     *
     * @param Mage_Sales_Model_Order $order
     * @param bool                   $create
     *
     * @return bool|Varien_Object
     */
    public function updateOrderStatusList($order, $create = false)
    {
        $orderCard = $this->getOrderCard($order, $create);

        if (!$orderCard) {
            return false;
        }

        $statusList = $this->getDataHelper()->getStatusListId($order->getStatus());

        if (!$statusList) {
            $statusList = $this->createStatusList($order->getStatus());
        }

        $trelloCard = $this->getApi()->updateCard(
            $orderCard->getCardId(),
            array(
                'idList' => $statusList->getListId(),
            )
        );

        return new Varien_Object($trelloCard);
    }

    /**
     * Create Trello list for status
     *
     * @param $status
     *
     * @return Omedrec_Trello_Model_List
     */
    public function createStatusList($status)
    {
        if (!($status instanceof Mage_Sales_Model_Order_Status)) {
            /** @var Mage_Sales_Model_Order_Status $status */
            $status = Mage::getModel('sales/order_status')->load($status, 'status');
        }

        $trelloList = $this->getApi()->createList(
            array(
                'name'    => $status->getStoreLabel(Mage::app()->getStore()),
                'idBoard' => $this->getDataHelper()->getBoardId()
            )
        );

        $trelloList = new Varien_Object($trelloList);

        /** @var Omedrec_Trello_Model_List $statusListLink */
        $statusListLink = Mage::getModel('trello/list');

        $statusListLink
            ->setStatus($status->getStatus())
            ->setListId($trelloList->getId())
            ->save();

        $this->getDataHelper()->dropListCache();

        return $statusListLink;
    }

    /**
     * Set uncompeded due date on order card in Trello
     * so it marks with red-labeled due tag
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Varien_Object
     */
    public function markOrderOutdated($order)
    {
        return $this->updateOrderCard($order, array('dueComplete' => false));
    }

    /**
     * Create description for order card
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return string
     */
    public function getOrderCardDescription(Mage_Sales_Model_Order $order)
    {
        $name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();

        try {
            /** @var VES_Vendors_Model_Vendor $vendor */
            $vendor = Mage::getModel('vendors/vendor')->load($order->getVendorId());
            $vendorTitle = $vendor->getTitle();
        } catch (Exception $e) {
            $vendorTitle = $this->__('Unknown');
        }


        $data = array(
            $this->__('Created by: %s', $name),
            $this->__('Vendor: %s', $vendorTitle),
            $this->__('Grand Total: %s', $order->getGrandTotal()),
            $this->__('Created at: %s', $order->getCreatedAt()),
        );

        return implode(PHP_EOL, $data);
    }

    /**
     * Get general helper
     *
     * @return Omedrec_Trello_Helper_Data
     */
    protected function getDataHelper()
    {
        return Mage::helper('trello');
    }

    /**
     * Get API model
     *
     * @return Omedrec_Trello_Model_Api
     */
    protected function getApi()
    {
        return Mage::getSingleton('trello/api');
    }
}