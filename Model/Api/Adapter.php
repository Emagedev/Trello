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
 *
 * @category   Omedrec
 * @package    Omedrec_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Omedrec_Trello_Model_Api_Adapter
 *
 * API adapter - run and dispatch queries with params
 */
class Omedrec_Trello_Model_Api_Adapter
{
    const API_BASE_URI = 'https://api.trello.com/1';

    /**
     * Trello API key
     * @see https://trello.com/app-key
     *
     * @var string
     */
    protected $apiKey = 'a86595aa6acb6b29e0496836f54b53bf';

    /**
     * Trello API token (paired with key for auth)
     *
     * @var string
     */
    protected $apiToken = '2a700a2c2b6095e728b9abe9af01d2604cb4f72068d98a27483ce0dc51ecdc7e';

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
     * Omedrec_Trello_Model_Api_Adapter constructor.
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
     * @return array
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

        /** @var Omedrec_Welcome_Helper_Curl $curlHelper */
        $curlHelper = Mage::helper('omedrec_welcome/curl');
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
                        CURLOPT_CONNECTTIMEOUT => 15,
                        CURLOPT_TIMEOUT        => 30
                    )
                );

            $this->adapter = $adapter;
        }

        return $this->adapter;
    }
}