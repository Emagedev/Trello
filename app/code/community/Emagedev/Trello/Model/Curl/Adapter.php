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
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Model_Curl_Adapter
 *
 * cURL adapter - fix Varien adapter to add PUT and DELETE HTTP methods
 */
class Emagedev_Trello_Model_Curl_Adapter extends Varien_Http_Adapter_Curl
{
    /**
     * Send request to the remote server
     *
     * @param string        $method
     * @param string|Zend_Uri_Http $url
     * @param string        $http_ver
     * @param array         $headers
     * @param string        $body
     * @return string Request as text
     */
    public function write($method, $url, $http_ver = '1.1', $headers = array(), $body = '')
    {
        if ($url instanceof Zend_Uri_Http) {
            $url = $url->getUri();
        }
        $this->_applyConfig();

        $header = isset($this->_config['header']) ? $this->_config['header'] : true;
        $options = array(
            CURLOPT_URL                     => $url,
            CURLOPT_RETURNTRANSFER          => true,
            CURLOPT_HEADER                  => $header
        );
        if ($method == Zend_Http_Client::POST) {
            $options[CURLOPT_POST]          = true;
            $options[CURLOPT_POSTFIELDS]    = $body;
        } elseif ($method == Zend_Http_Client::GET) {
            $options[CURLOPT_HTTPGET]       = true;
        } elseif ($method == Zend_Http_Client::PUT) {
            $options[CURLOPT_PUT]           = true;
        } elseif ($method == Zend_Http_Client::DELETE) {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        }

        if (is_array($headers)) {
            $options[CURLOPT_HTTPHEADER]    = $headers;
        }

        curl_setopt_array($this->_getResource(), $options);

        return $body;
    }
}