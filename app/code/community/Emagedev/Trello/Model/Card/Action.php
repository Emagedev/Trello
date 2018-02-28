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
 * Class Emagedev_Trello_Model_Api
 *
 * API model - map different actions to requests
 */
class Emagedev_Trello_Model_Card_Action
{
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
    public function create($params)
    {
        $cardResponse = $this->getAdapter()
            ->run(
                array('cards' => $params['card_id'], 'actions' => 'comments'),
                Zend_Http_Client::POST,
                $params
            );

        return $this->getAdapter()->decodeResponse($cardResponse);
    }

    /**
     * Get Trello card params
     *
     * @param $cardId
     *
     * @return array|string
     */
    public function get($cardId)
    {
        $cardResponse = $this->getAdapter()
            ->run(
                array('cards' => $cardId),
                Zend_Http_Client::GET
            );

        return $this->getAdapter()->decodeResponse($cardResponse);
    }

    /**
     * Update some card by id, set new parameters
     *
     * @param string $cardId
     * @param array  $params
     *
     * @return array|string
     */
    public function update($cardId, $params)
    {
        $this->checkParams($params);

        $cardResponse = $this->getAdapter()
            ->run(
                array('cards' => $cardId),
                Zend_Http_Client::PUT,
                $params
            );

        return $this->getAdapter()->decodeResponse($cardResponse);
    }

    /**
     * Fast method to archive card: update with closed param
     *
     * @param      $cardId
     * @param bool $archive
     *
     * @return array|string
     */
    public function archive($cardId, $archive = true)
    {
        return $this->update($cardId, array('closed' => $archive));
    }

    /**
     * Delete some Trello card permanently
     *
     * @param $cardId
     */
    public function delete($cardId)
    {
        $this->getAdapter()
            ->run(
                array('cards' => $cardId),
                Zend_Http_Client::DELETE
            );
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
}