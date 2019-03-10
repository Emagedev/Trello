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
 * the Emagedev Trello module to newer versions in the future.
 * If you wish to customize the Emagedev Trello module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (C) Emagedev, LLC (https://www.emagedev.com/)
 * @license    https://opensource.org/licenses/BSD-3-Clause     New BSD License
 */

/**
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Controller
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Helper_Data
 *
 * Mass action controller methods
 */
class Emagedev_Trello_Adminhtml_Sales_TrelloController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Update or create order cards from orders grid
     */
    public function massUpdateAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());

        /** @var Emagedev_Trello_Helper_Order $helper */
        $helper = Mage::helper('trello/order');
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);

            $helper->updateOrderCard($order, true);
        }

        $this->_getSession()->addSuccess($this->__('%s order(s) updated in Trello board.', count($orderIds)));

        $this->_redirect('*/sales_order/');
    }

    /**
     * Connect and save webhook
     */
    public function connectAction()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $params = $this->getRequest()->getParams();

                $boardId = $params['groups']['order_status']['fields']['board_id']['value'];

                if (!$boardId) {
                    $result = array(
                        'error' => true,
                        'message' => 'Board ID not specified.'
                    );
                } else {
                    /** @var Emagedev_Trello_Model_Webhook $webhook */
                    $webhook = Mage::getModel('trello/webhook');

                    $webhook
                        ->setDescription('Emagedev Magento Trello Connection')
                        ->setModelId($boardId)
                        ->setActive(true);

                    $success = $webhook->connect();

                    if ($success) {
                        Mage::getConfig()->saveConfig('trello_api/webhook/id', $webhook->getWebhookId(), 'default', 0);

                        $result = array(
                            'error' => false,
                            'message' => 'Webhook connected succesfully! Webhook status should be updated to "OK" in few minutes.',
                            'webhook_id' => $webhook->getWebhookId()
                        );
                    } else {
                        $result = array(
                            'error' => true,
                            'message' => 'Unable to connect'
                        );
                    }

                    Mage::app()->getCacheInstance()->cleanType('config');
                }
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->getDataHelper()->log('AWebhook connect failed', Zend_Log::ERR);
                $this->getDataHelper()->log($e->getMessage(), Zend_Log::ERR);

                $result = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $this->getDataHelper()->log('Webhook connect failed', Zend_Log::ERR);
                $this->getDataHelper()->log($e->getMessage(), Zend_Log::ERR);

                $result = array(
                    'error' => true,
                    'message' => 'Failed to connect: Internal server error'
                );
            }

            /** @var Mage_Core_Helper_Data $coreHelper */
            $coreHelper = Mage::helper('core');

            $this->getResponse()->setBody($coreHelper->jsonEncode($result));
        } else {
            $this->getResponse()
                ->setHttpResponseCode(400)
                ->setBody('Bad request');
        }
    }

    /**
     * Drop save webhook
     */
    public function dropAction()
    {
        if ($this->getRequest()->isPost()) {
            try {
                /** @var Emagedev_Trello_Helper_Data $dataHelper */
                $dataHelper = Mage::helper('trello');

                /** @var Emagedev_Trello_Model_Webhook $webhook */
                $webhook = Mage::getModel('trello/webhook');

                $webhook
                    ->setDescription('Emagedev Magento Trello Connection')
                    ->setModelId($dataHelper->getBoardId())
                    ->setId($dataHelper->getWebhookId())
                    ->setActive(true);

                $success = $webhook->drop();

                if ($success) {
                    Mage::getConfig()->saveConfig('trello_api/webhook/id', '', 'default', 0);

                    $result = array(
                        'error' => false,
                        'message' => 'Webhook deleted succesfully! Webhook status should be updated to "Not Working" in few minutes.',
                        'webhook_id' => ''
                    );
                } else {
                    $result = array(
                        'error' => true,
                        'message' => 'Unable to connect'
                    );
                }

                $dataHelper->dropWebhookCheck();

                Mage::app()->getCacheInstance()->cleanType('config');
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->getDataHelper()->log('Webhook drop failed', Zend_Log::ERR);
                $this->getDataHelper()->log($e->getMessage(), Zend_Log::ERR);

                $result = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $this->getDataHelper()->log('Webhook drop failed', Zend_Log::ERR);
                $this->getDataHelper()->log($e->getMessage(), Zend_Log::ERR);

                $result = array(
                    'error' => true,
                    'message' => 'Failed to connect: Internal server error'
                );
            }

            /** @var Mage_Core_Helper_Data $coreHelper */
            $coreHelper = Mage::helper('core');

            $this->getResponse()->setBody($coreHelper->jsonEncode($result));
        } else {
            $this->getResponse()
                ->setHttpResponseCode(400)
                ->setBody('Bad request');
        }
    }

    /**
     * @return Emagedev_Trello_Helper_Data
     */
    protected function getDataHelper()
    {
        return Mage::helper('trello');
    }
}
