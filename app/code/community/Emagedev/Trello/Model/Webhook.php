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
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Model_Webhook
 *
 * @method $this setModelId(string $modelId)
 * @method string getModelId()
 * @method $this setWebhookId(string $modelId)
 * @method string getWebhookId()
 * @method $this setDescription(string $description)
 * @method string getDescription()
 * @method $this setMonitoringObjectId(string $orderId)
 * @method string getMonitoringObjectId()
 * @method $this setActive(string $active)
 * @method string getActive()
 * @method $this setCallbackUrl(string $url)
 */
class Emagedev_Trello_Model_Webhook extends Varien_Object
{
    /**
     * @var Emagedev_Trello_Model_Api_Adapter
     */
    protected $adapter;

    /**
     * Get API adapter model
     *
     * @return Emagedev_Trello_Model_Api_Adapter
     */
    protected function getAdapter()
    {
        if (is_null($this->adapter)) {
            $this->adapter = Mage::getModel('trello/api_adapter');
        }

        return $this->adapter;
    }

    public function connect()
    {
        $hookResponse = $this->getAdapter()
            ->run(
                array('webhooks'),
                Zend_Http_Client::POST,
                array(
                    'callbackURL' => $this->getCallbackUrl(),
                    'idModel'     => $this->getModelId(),
                    'description' => $this->getDescription(),
                    'active'      => $this->getActive() ? 'true' : 'false'
                )
            );

        $response = $this->getAdapter()->decodeResponse($hookResponse);

        return $this->processResponse($response);
    }

    public function drop()
    {
        $this->getAdapter()
            ->run(
                array('webhooks' => $this->getId()),
                Zend_Http_Client::DELETE
            );

        $this->setData(array());

        return true;
    }

    public function processResponse($response)
    {
        if (!$response['id']) {
            return false;
        }

        $this
            ->setWebhookId($response['id'])
            ->setDescription($response['description'])
            ->setModelId($response['idModel'])
            ->setCallbackUrl($response['callbackURL'])
            ->setActive($response['active']);

        return true;
    }

    public function getCallbackUrl()
    {
        $url = Mage::getUrl('trello_secret_callback/api_board/index');

        if (Mage::getStoreConfigFlag('web/seo/use_rewrites')) {
            $url = str_replace('index.php/', '', $url);
        }

        return $url;
    }
}
