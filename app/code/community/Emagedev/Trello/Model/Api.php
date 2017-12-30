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
 * the Emagedev Trello module to newer versions in the future.
 *
 * @copyright  Copyright (C) Effdocs, LLC
 * @license    http://www.binpress.com/license/view/l/45d152a594cd48488fda1a62931432e7
 */

/**
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Model_Api
 *
 * API model - map different actions to requests
 */
class Emagedev_Trello_Model_Api
{
    /**
     * Available card params to validate request
     *
     * @see https://developers.trello.com/v1.0/reference#cardsid-1
     *
     * @var array
     */
    protected $cardParams
        = array(
            'name', 'desc', 'closed',
            'idMembers', 'idAttachmentCover',
            'idList', 'idLabels',
            'idBoard', 'pos', 'due',
            'dueComplete', 'subscribed'
        );

    /**
     * @var Emagedev_Trello_Model_Api_Adapter
     */
    protected $adapter;

    /**
     * Create trello card
     *
     * @param array $params
     *
     * @see https://developers.trello.com/v1.0/reference#cards-2
     *
     * @return array|string
     */
    public function createCard($params)
    {
        $this->checkParams($params);

        $cardResponse = $this->getAdapter()
            ->run(
                array('cards'),
                Zend_Http_Client::POST,
                $params
            );

        return $this->decodeResponse($cardResponse);
    }

    /**
     * Get Trello card params
     *
     * @param $cardId
     *
     * @return array|string
     */
    public function getCard($cardId)
    {
        $cardResponse = $this->getAdapter()
            ->run(
                array('cards' => $cardId),
                Zend_Http_Client::GET
            );

        return $this->decodeResponse($cardResponse);
    }

    /**
     * Update some card by id, set new parameters
     *
     * @param string $cardId
     * @param array  $params
     *
     * @return array|string
     */
    public function updateCard($cardId, $params)
    {
        $this->checkParams($params);

        $cardResponse = $this->getAdapter()
            ->run(
                array('cards' => $cardId),
                Zend_Http_Client::PUT,
                $params
            );

        return $this->decodeResponse($cardResponse);
    }

    /**
     * Fast method to archive card: update with closed param
     *
     * @param      $cardId
     * @param bool $archive
     *
     * @return array|string
     */
    public function archiveCard($cardId, $archive = true)
    {
        return $this->updateCard($cardId, array('closed' => $archive));
    }

    /**
     * Delete some Trello card permanently
     *
     * @param $cardId
     */
    public function deleteCard($cardId)
    {
        $this->getAdapter()
            ->run(
                array('cards' => $cardId),
                Zend_Http_Client::DELETE
            );
    }

    /**
     * Create Trello list with provided data
     *
     * @param array $params
     *
     * @return array|string
     */
    public function createList($params)
    {
        if (
            !array_key_exists('name', $params) || $params['name'] == ''
            || !array_key_exists('idBoard', $params) || $params['idBoard'] == ''
        ) {
            Mage::throwException('To create list in trello, you should set name and idBoard params');
        }

        $listResponse = $this->getAdapter()
            ->run(
                array('lists'),
                Zend_Http_Client::POST,
                $params
            );

        return $this->decodeResponse($listResponse);
    }

    /**
     * Update Trello list with provided data
     *
     * @param string $listId
     * @param array  $params
     *
     * @return array|string
     */
    public function updateList($listId, $params = array())
    {
        $listResponse = $this->getAdapter()
            ->run(
                array('lists' => $listId),
                Zend_Http_Client::PUT,
                $params
            );

        return $this->decodeResponse($listResponse);
    }

    /**
     * Fast method to archive list: update with closed param
     *
     * @param string $listId
     * @param bool   $archive
     *
     * @return array|string
     */
    public function archiveList($listId, $archive = true)
    {
        return $this->updateList($listId, array('value' => $archive));
    }

    /**
     * Check is sent params available in API
     *
     * @param array $params
     *
     * @return $this
     */
    public function checkParams($params)
    {
        $diff = array_diff(array_keys($params), $this->cardParams);

        if (!empty($diff)) {
            Mage::throwException('Params: ' . implode(' ', $diff) . ' not available for cards');
        }

        return $this;
    }

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
    protected function decodeResponse($response)
    {
        if (!is_array($response) || !array_key_exists('code', $response) || $response['code'] != 200) {
            if (!is_array($response) || !array_key_exists('body', $response) || $response['body'] == '') {
                Mage::throwException('No answer from API');
            }

            Mage::throwException('Failed to update Trello card: ' . $response);
        }

        return Mage::helper('core')->jsonDecode($response['body']);
    }
}