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
 * Class Omedrec_Trello_Model_Observer
 *
 * API model - map different actions to requests
 */
class Omedrec_Trello_Model_Observer
{
    /**
     * Add card to Trello for new order
     *
     * @param Varien_Event_Observer $observer
     */
    public function addOrderCard(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $order = $event->getOrder();

        if (!$order || !$order->getId()) {
            return;
        }

        try {
            Mage::helper('trello/order')->createOrderCard($order);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Update order card on Trello when order updated (saved)
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateOrderCard(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $order = $event->getOrder();

        if (!$order || !$order->getId()) {
            return;
        }

        try {
            Mage::helper('trello/order')->updateOrderStatusList($order, true);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Find orders that not changed last 3 days, mark them as
     * outdated to check their real status
     * If order was completed 3 or more days ago, archive card
     */
    public function markOrArchiveOutdatedOrders()
    {
        /** @var Mage_Sales_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = Mage::getModel('sales/order')->getCollection();

        /** @var Omedrec_Trello_Model_Resource_Order $cardResource */
        $cardResource = Mage::getModel('trello/order')->getResource();
        $cardResource->filterOrdersWithActiveCards($orderCollection);

        $nowDate = new DateTime('now');

        /** @var Omedrec_Trello_Helper_Order $orderHelper */
        $orderHelper = Mage::helper('trello/order');

        /** @var Mage_Sales_Model_Order $order */
        foreach ($orderCollection as $order) {
            $orderDate = new DateTime($order->getUpdatedAt());

            $days = $orderDate->diff($nowDate)->days;

            if ($days > 3) {
                if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
                    $orderHelper->archiveOrder($order);
                } elseif ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
                    // Do nothing with orders on hold
                    continue;
                } else {
                    $orderHelper->markOrderOutdated($order);
                }
            }
        }
    }

    /**
     * Add mass trello update option for adminhtml orders grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function addMassTrelloUpdate(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();

        /** @var Omedrec_Sales_Block_Adminhtml_Sales_Order_Grid $grid */
        $grid = $event->getGrid();

        $grid->getMassactionBlock()->addItem('update_trello_status', array(
            'label'=> Mage::helper('sales')->__('Update Trello Status'),
            'url'  => $grid->getUrl('*/sales_trello/massUpdate'),
        ));
    }
}