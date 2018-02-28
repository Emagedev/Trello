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
 * @subpackage Controller
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Api_BoardController
 *
 * Mass action controller methods
 */
class Emagedev_Trello_Api_BoardController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $jsonBody = $this->getRequest()->getRawBody();

        try {
            $this->getDataHelper()->log('Received webhook request', Zend_Log::DEBUG);
            $this->getDataHelper()->log('Data: ' . PHP_EOL . $jsonBody, Zend_Log::DEBUG);

            // First request is empty. Mark status as "OK" in this case
            if ($this->getRequest()->isHead()) {
                /** @var Emagedev_Trello_Helper_Data $moduleHelper */
                $moduleHelper = Mage::helper('trello');
                $moduleHelper->updateWebhookCheck();

                Mage::app()->getCacheInstance()->cleanType('config');
            } else {
                /** @var Mage_Core_Helper_Data $helper */
                $helper = Mage::helper('core');

                $actionPayload = $helper->jsonDecode($jsonBody);

                if (!$this->checkBoard($actionPayload)) {
                    $this->getResponse()->setHttpResponseCode(400);
                    $this->getResponse()->setBody('Wrong Model ID');

                    return;
                }

                /** @var Emagedev_Trello_Model_Webhook_Action $actionModel */
                $actionModel = Mage::getModel('trello/webhook_action');
                $actionModel->setJsonData($actionPayload['action']);

                $actionModel->dispatch();
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->getDataHelper()->log('Webhook processing failed due to following error:', Zend_Log::ERR);
            $this->getDataHelper()->log($e->getMessage(), Zend_Log::ERR);

            $this->getResponse()->setHttpResponseCode(500);
            $this->getResponse()->setBody('Internal Server Error');
        }
    }

    protected function checkBoard($actionPayload)
    {
        /** @var Emagedev_Trello_Helper_Data $moduleHelper */
        $moduleHelper = Mage::helper('trello');
        $boardId = $moduleHelper->getBoardId();

        return $actionPayload['model']['id'] == $boardId;
    }

    /**
     * @return Emagedev_Trello_Helper_Data
     */
    protected function getDataHelper()
    {
        return Mage::helper('trello');
    }
}
