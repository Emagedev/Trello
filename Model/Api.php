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
 * @category   Omedrec
 * @package    Omedrec_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Omedrec_Trello_Model_Api
 *
 * API model - map different actions to requests
 */
class Omedrec_Trello_Model_Api
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
     * Create trello card
     *
     * @param array $params
     *
     * @return array|string
     */
    public function createCard($params)
    {
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
                Zend_Http_Client::POST
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
        if (!array_key_exists('name', $params) || !array_key_exists('idBoard', $params)) {
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
     * @param array $params
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
     * Get API adapter model
     *
     * @return Omedrec_Trello_Model_Api_Adapter
     */
    public function getAdapter()
    {
        return Mage::getModel('trello/api_adapter');
    }

    /**
     * Decode response: throw error if there is,
     * or parse JSON otherwise
     *
     * @param array $response
     *
     * @return array|string
     */
    protected function decodeResponse($response)
    {
        if ($response['code'] != 200) {
            Mage::throwException($response['body']);
        }

        return Mage::helper('core')->jsonDecode($response['body']);
    }
}