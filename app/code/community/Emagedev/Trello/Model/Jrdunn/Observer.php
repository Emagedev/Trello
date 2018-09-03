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
 * Class Emagedev_Trello_Model_Jrdunn_Observer
 *
 * API model - map different actions to requests
 */
class Emagedev_Trello_Model_Jrdunn_Observer
{
    public function addHistoryCommentAuthor(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();

        /** @var Mage_Sales_Model_Order_Status_History $comment */
        $comment = $event->getStatusHistory();

        /** @var Emagedev_Trello_Model_Member $member */
        $member = $event->getMember();

        if (!$member) {
            return;
        }

        $adminUser = $member->getAdminUser();

        if ($adminUser && $adminUser->getId()) {
            /** @var MageWorx_OrdersEdit_Model_Order_Status_History $extendedHistory */
            $extendedHistory = Mage::getModel('mageworx_ordersedit/order_status_history');

            $extendedHistory
                ->setData('history_id', $comment->getEntityId())
                ->setData('creator_admin_user_id', $adminUser->getId())
                ->setData('creator_firstname', $adminUser->getFirstname())
                ->setData('creator_lastname', $adminUser->getLastname())
                ->setData('creator_username', $adminUser->getUsername());

            $extendedHistory->save();
        }

        return;
    }

    public function addActionAuthor(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();

        /** @var Mage_Sales_Model_Order_Status_History $comment */
        $comment = $event->getStatusHistory();

        /** @var Emagedev_Trello_Model_Action $comment */
        $action = $event->getAction();

        /** @var MageWorx_OrdersEdit_Model_Order_Status_History $extendedHistory */
        $extendedHistory = Mage::getModel('mageworx_ordersedit/order_status_history');
        $extendedHistory->load($comment->getId(), 'history_id');

        $author = join(' ', array($extendedHistory->getCreatorFirstname(), $extendedHistory->getCreatorLastname()));

        if (trim($author) != '') {
            $author = '**' . trim($author) . ':**' . PHP_EOL;
        } else {
            $author = '';
        }

        $action->setText($author . $comment->getComment());
    }

    public function addCustomerNameToCard(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();

        /** @var Mage_Sales_Model_Order $order */
        $order = $event->getOrder();

        /** @var Emagedev_Trello_Model_Card $card */
        $card = $event->getCard();

        $billingAddress = $order->getBillingAddress();
        $name = array($billingAddress->getPrefix(), $billingAddress->getFirstname(), $billingAddress->getMiddlename(), $billingAddress->getLastname());
        $name = implode(' ', array_filter($name));

        $order->getCustomerName();

        $card->setName($card->getName() . ': ' . $name);
    }

    public function fetchStatusFromAmasty(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();

        $code = $event->getCode();

        /** @var Mage_Sales_Model_Order_Status|null $status */
        $status = $event->getStatus();

        if (!$status->getStatus() || !$status->getLabel()) {
            $amCodes = explode('_', $code);

            if (count($amCodes) == 2) {
                /** @var Amasty_Orderstatus_Model_Mysql4_Status_Collection $amStatuses */
                $amStatuses = Mage::getModel('amorderstatus/status')->getCollection();
                $amStatuses
                    ->addFieldToFilter('parent_state', $amCodes[0])
                    ->addFieldToFilter('alias', $amCodes[1]);

                if ($amStatuses->count() == 1) {
                    $amStatus = $amStatuses->getFirstItem();

                    $labels = array();
                    $states = $this->getStatesForAm();

                    if ($states[$amStatus->getParentState()]) {
                        $labels[] = $states[$amStatus->getParentState()];
                    }

                    $labels[] = $amStatus->getStatus();

                    $status->setData(array(
                        'status' => $code,
                        'label'  => implode(': ', $labels)
                    ));
                }
            }
        }
    }

    protected function getStatesForAm()
    {
        $states = array();

        $config = Mage::getConfig();
        foreach ($config->getNode('global/sales/order/states')->children() as $state => $node)
        {
            $label = Mage::helper('sales')->__(trim( (string) $node->label ) );
            $states[$state] = $label;
        }

        return $states;
    }
}