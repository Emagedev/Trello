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
 * Class Emagedev_Trello_Model_List
 *
 * Trello List - Order Status connection
 *
 * @method $this setStatus(string $status)
 * @method string getStatus()
 * @method $this setListId(string $listId)
 * @method string getListId()
 * @method $this setBoardId(string $boardId)
 * @method string getBoardId()
 * @method $this setName(string $name)
 * @method string getName()
 */
class Emagedev_Trello_Model_List extends Emagedev_Trello_Model_Trello_Entity_Abstract
{
    protected $status;

    /**
     * @var array
     */
    protected $apiDataMap = array(
        'name'    => 'name',
        'idBoard' => 'board_id'
    );

    /**
     * Init the resource
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('trello/list');

        $this->setBoardId($this->getDataHelper()->getBoardId());
    }

    public function loadFromTrello($trelloId)
    {
        return $this->load($trelloId, 'list_id');
    }

    /**
     * Save data to Trello
     *
     * @todo: Make universal
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        if ($this->doSync) {
            if ($this->getCardId()) {
                $this->sync();
            } else {
                $this->export();
            }
        }

        return parent::_beforeSave();
    }

    /**
     * Create Trello list with provided data
     *
     * @return array|string
     */
    public function export()
    {
        $params = $this->prepareParams();

        $listResponse = $this->getAdapter()
            ->run(
                array('lists'),
                Zend_Http_Client::POST,
                $params
            );

        $response = $this->getAdapter()->decodeResponse($listResponse);
        return $this->processResponse($response);
    }

    /**
     * Update Trello list with provided data
     *
     * @return array|string
     */
    public function sync()
    {
        $params = $this->prepareParams();

        $listResponse = $this->getAdapter()
            ->run(
                array('lists' => $this->getListId()),
                Zend_Http_Client::PUT,
                $params
            );

        $response = $this->getAdapter()->decodeResponse($listResponse);
        return $this->processResponse($response);
    }

    protected function processResponse($response)
    {
        $this->setListId($response['id']);

        return $this;
    }

    /**
     * Fast method to archive list: update with closed param
     *
     * @param string $listId
     * @param bool   $archive
     *
     * @return array|string
     */
    public function archive($listId, $archive = true)
    {
        return $this->update($listId, array('value' => $archive));
    }
}
