<?php


class Omedrec_Trello_Model_Curl_Adapter extends Varien_Http_Adapter_Curl
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