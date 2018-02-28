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
 *
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Model_Api_Adapter
 *
 * API adapter - run and dispatch queries with params
 */
class Emagedev_Trello_Model_Api_Adapter
{
    const API_BASE_URI = 'https://api.trello.com/1';

    /**
     * Trello API key
     * @see https://trello.com/app-key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Trello API token (paired with key for auth)
     *
     * @var string
     */
    protected $apiToken;

    /**
     * Occurred errors
     *
     * @var array
     */
    protected $errors = array();

    /**
     * HTTP CURL adapter
     *
     * @var
     */
    protected $adapter;

    /**
     * Is response code = 200
     *
     * @var bool
     */
    protected $successful = false;

    /**
     * Is message sent
     *
     * @var bool
     */
    protected $sent = false;

    /**
     * Set up key-token pair
     *
     * Emagedev_Trello_Model_Api_Adapter constructor.
     */
    public function __construct()
    {
        $this->apiKey = Mage::getStoreConfig('trello_api/general/key');
        $this->apiToken = Mage::getStoreConfig('trello_api/general/token');
    }

    /**
     * Run a query to process some action
     *
     * @param array  $actions [action => value] to action/:value
     * @param string $method HTTP method
     * @param array  $params Query params
     * @param array  $headers HTTP headers
     * @param string $body Query body
     *
     * @return bool|array
     */
    public function run($actions, $method = Zend_Http_Client::GET, $params = array(), $headers = array(), $body = '')
    {
        $params['key'] = $this->apiKey;
        $params['token'] = $this->apiToken;

        $defaultHeaders = array('Accept' => 'application/json', 'Content-Type' => 'application/json');
        $headers = array_merge($defaultHeaders, $headers);

        try {
            $this->getAdapter()
                ->write(
                    $method,
                    $this->combineUrl($actions, $params),
                    '1.1',
                    $headers,
                    $body
                );

            $response = $this->getAdapter()->read();

            $this->sent = true;
        } catch (Exception $e) {
            Mage::logException($e);
            return $this->dispatchFailure($e->getMessage());
        }

        if ($this->getAdapter()->getErrno()) {
            return $this->dispatchFailure($this->getAdapter()->getError());
        }

        /** @var Emagedev_Utils_Helper_Curl $curlHelper */
        $curlHelper = Mage::helper('emagedev_utils/curl');
        $responseObject = $curlHelper->dispatchResponse($response);

        $code = $responseObject->getCode();
        $responseHead = $responseObject->getHeadersAsString();
        $responseBody = $responseObject->getBody();

        $this->getAdapter()->close();

        if ($code === 200) {
            $this->successful = true;
        }

        return array(
            'code' => $code,
            'head' => $responseHead,
            'body' => $responseBody,
        );
    }

    /**
     * Set flags if request failed
     *
     * @param $message
     *
     * @return bool
     */
    protected function dispatchFailure($message)
    {
        $this->successful = false;
        $this->errors[] = $message;
        return false;
    }

    /**
     * Dispatch actions and params to ...action/:value?param1=a&param2=b
     *
     * @param $actions [action => value]
     * @param $params
     *
     * @return string
     */
    public function combineUrl($actions, $params)
    {
        $nestedAction = array();

        foreach ($actions as $action => $key)
        {
            if (is_string($action)) {
                $nestedAction[] = $action . '/' . $key;
            } else {
                $nestedAction[] = $key;
            }
        }

        $nestedAction = implode('/', $nestedAction);
        $path =  self::API_BASE_URI . '/' . $nestedAction;

        /** @var Zend_Uri_Http $url */
        $url = Zend_Uri_Http::fromString($path);
        $url->setQuery($params);

        return $url->__toString();
    }

    /**
     * Get HTTP adapter
     *
     * @return Varien_Http_Adapter_Curl
     */
    protected function getAdapter()
    {
        if (is_null($this->adapter)) {
            $adapter = Mage::getModel('trello/curl_adapter');

            $adapter
                ->addOptions(
                    array(
                        CURLOPT_CONNECTTIMEOUT => 1,
                        CURLOPT_TIMEOUT        => 5
                    )
                );

            $this->adapter = $adapter;
        }

        return $this->adapter;
    }

    /**
     * Decode response: throw error if there is,
     * or parse JSON otherwise
     *
     * @throws Mage_Core_Exception
     *
     * @param array $response
     *
     * @return array|string
     */
    public function decodeResponse($response)
    {
        if (!is_array($response) || !array_key_exists('code', $response) || $response['code'] != 200) {
            if (!is_array($response) || !array_key_exists('body', $response) || $response['body'] == '') {
                Mage::throwException('No answer from API.');
                $this->getDataHelper()->log('API request failed (no content): ' . serialize($response), Zend_Log::ERR);
            }

            Mage::throwException('Failed to process Trello API request: ' . $response['body']);
            $this->getDataHelper()->log('API request failed: ' . $response['body'], Zend_Log::ERR);
        }

        return Mage::helper('core')->jsonDecode($response['body']);
    }

    /**
     * @return Emagedev_Trello_Helper_Data
     */
    protected function getDataHelper()
    {
        return Mage::helper('trello');
    }
}