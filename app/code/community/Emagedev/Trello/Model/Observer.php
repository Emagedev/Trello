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
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Model_Observer
 *
 * API model - map different actions to requests
 */
class Emagedev_Trello_Model_Observer
{
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

        /** @var Emagedev_Trello_Model_Resource_Order $cardResource */
        $cardResource = Mage::getModel('trello/order')->getResource();
        $cardResource->filterOrdersWithActiveCards($orderCollection);

        $nowDate = new DateTime('now');

        /** @var Emagedev_Trello_Helper_Order $orderHelper */
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

        /** @var Emagedev_Sales_Block_Adminhtml_Sales_Order_Grid $grid */
        $grid = $event->getGrid();

        $grid->getMassactionBlock()->addItem('update_trello_status', array(
            'label'=> Mage::helper('sales')->__('Update Trello Status'),
            'url'  => $grid->getUrl('*/sales_trello/massUpdate'),
        ));
    }
}