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
    protected $actionsCache;

    /**
     * Update order card on Trello when order updated (saved)
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateOrderCard(Varien_Event_Observer $observer)
    {
        try {
            if (Mage::registry(Emagedev_Trello_Model_Webhook_Action::REGISTRY_PROCESSING_WEBHOOK_ACTION) === true) {
                return;
            }

            $this->getDataHelper()->log('Observe order update', Zend_Log::DEBUG);

            $event = $observer->getEvent();

            /** @var Mage_Sales_Model_Order $order */
            $order = $event->getOrder();

            $this->getDataHelper()->log('Process order ' . $order->getid() . ' update', Zend_Log::DEBUG);

            if (!$order || !$order->getId()) {
                $this->getDataHelper()->log('Cannot process order update', Zend_Log::DEBUG);
                return;
            }

            $this->getHelper()->updateOrderStatusList($order, true);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->getDataHelper()->log('Action update failed', Zend_Log::ERR);
            $this->getDataHelper()->log($e->getMessage(), Zend_Log::ERR);
        }
    }

    /**
     * Find orders that not changed last 3 days, mark them as
     * outdated to check their real status
     * If order was completed 3 or more days ago, archive card
     */
    public function markOrArchiveOutdatedOrders()
    {
        try {
            $this->getDataHelper()->log('Observe outdated orders', Zend_Log::NOTICE);

            /** @var Mage_Sales_Model_Resource_Order_Collection $orderCollection */
            $orderCollection = Mage::getModel('sales/order')->getCollection();

            /** @var Emagedev_Trello_Model_Resource_Card $cardResource */
            $cardResource = Mage::getModel('trello/card')->getResource();
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
                        $this->getDataHelper()->log('Archiving order ' . $order->getId(), Zend_Log::NOTICE);

                        $card = $orderHelper->getOrderCard($order);
                        $card->archive();
                    } elseif ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
                        // Do nothing with orders on hold
                        continue;
                    } else {
                        $this->getDataHelper()->log('Order ' . $order->getId() . ' is outdated.', Zend_Log::NOTICE);
                        $orderHelper->markOrderOutdated($order);
                    }
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->getDataHelper()->log('Action update failed', Zend_Log::ERR);
            $this->getDataHelper()->log($e->getMessage(), Zend_Log::ERR);
        }
    }

    /**
     * Add mass trello update option for adminhtml orders grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function addMassTrelloUpdate(Varien_Event_Observer $observer)
    {
        try {
            $event = $observer->getEvent();

            /** @var Mage_Adminhtml_Block_Sales_Order_Grid $grid */
            $grid = $event->getGrid();

            $grid->getMassactionBlock()->addItem('update_trello_status', array(
                'label'=> Mage::helper('sales')->__('Update Trello Status'),
                'url'  => $grid->getUrl('*/sales_trello/massUpdate'),
            ));
        } catch (Exception $e) {
            Mage::logException($e);
            $this->getDataHelper()->log('Add mass action failed', Zend_Log::ERR);
            $this->getDataHelper()->log($e->getMessage(), Zend_Log::ERR);
        }
    }

    /**
     * Send admin comment to Trello card comments
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendTrelloAction(Varien_Event_Observer $observer)
    {
        try {
            if (Mage::registry(Emagedev_Trello_Model_Webhook_Action::REGISTRY_PROCESSING_WEBHOOK_ACTION) === true) {
                return;
            }

            $event = $observer->getEvent();

            /** @var Mage_Sales_Model_Order_Status_History $statusHistory */
            $statusHistory = $event->getStatusHistory();

            if ($this->ignoreStatusUpdate($statusHistory)) {
                return;
            }

            $actions = $this->getTrelloCommentActions($statusHistory);

            $action = $actions->getActionByHistoryCommentId($statusHistory->getId());

            if (!$action) {
                /** @var Emagedev_Trello_Model_Action $action */
                $action = Mage::getModel('trello/action');
                $action->importFromStatusHistory($event->getStatusHistory());
            } else {
                $action
                    ->setOrder($statusHistory->getOrder())
                    ->setText($statusHistory->getComment());
            }

            $action->save();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->getDataHelper()->log('Action update failed', Zend_Log::ERR);
            $this->getDataHelper()->log($e->getMessage(), Zend_Log::ERR);
        }
    }

    /**
     * Should ibserver ignore this order comment
     *
     * @param Mage_Sales_Model_Order_Status_History $statusHistory
     *
     * @return bool
     */
    protected function ignoreStatusUpdate($statusHistory)
    {
        if (!$statusHistory->hasDataChanges()) {
            $this->getDataHelper()->log('Ignore status ' . $statusHistory->getId() . ' update: no changes', Zend_Log::DEBUG);
            return true;
        }

        if (!$statusHistory->getComment()) {
            $this->getDataHelper()->log('Ignore status ' . $statusHistory->getId() . ' update: no comment', Zend_Log::DEBUG);
            return true;
        }

        $this->getDataHelper()->log('Process status ' . $statusHistory->getId(), Zend_Log::DEBUG);
        return false;
    }

    /**
     * Get Trello comment-actions from card
     *
     * @param Mage_Sales_Model_Order_Status_History $statusHistory
     *
     * @return Emagedev_Trello_Model_Resource_Action_Collection
     */
    protected function getTrelloCommentActions($statusHistory)
    {
        $order = $statusHistory->getOrder();

        if (!$order) {
            $order = Mage::getModel('sales/order');
            $order->load($statusHistory->getParentId());

            $statusHistory->setOrder($order);
        }

        if (!array_key_exists($this->actionsCache, $statusHistory->getOrder()->getId())) {
            /** @var Emagedev_Trello_Model_Resource_Action_Collection $collection */
            $collection = Mage::getModel('trello/action')->getCollection();

            $collection->addFieldToFilter('order_id', $statusHistory->getOrder()->getId());

            $this->actionsCache[$statusHistory->getOrder()->getId()] = $collection;
        }

        return $this->actionsCache[$statusHistory->getOrder()->getId()];
    }

    /**
     * @return Emagedev_Trello_Helper_Data
     */
    protected function getDataHelper()
    {
        return Mage::helper('trello');
    }

    /**
     * @return Emagedev_Trello_Helper_Order
     */
    protected function getHelper()
    {
        return Mage::helper('trello/order');
    }
}