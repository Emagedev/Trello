<?php
/**
 * J.R. Dunn Jewelers. extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Emagedev Trello module to newer versions in the future.
 * If you wish to customize the Emagedev Trello module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @copyright  Copyright (C) 2018 J.R. Dunn Jewelers.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
 * Class Emagedev_Trello_Model_Label
 *
 * @method $this setTrelloLabelId(string $labelId)
 * @method string getTrelloLabelId()
 * @method $this setName(string $name)
 * @method string getName()
 * @method $this setColor(string $name)
 * @method string getColor()
 * @method $this setActive(bool $active)
 * @method bool getActive()
 */
class Emagedev_Trello_Model_Label extends Emagedev_Trello_Model_Trello_Entity_Abstract
{
    protected $_eventPrefix = 'trello_label';

    protected $items;

    protected $card;

    /**
     * @var array
     */
    protected $apiDataMap = array(
        'id'          => 'trello_label_id',
        'idBoard'     => 'board_id',
        'name'        => 'name',
        'color'       => 'color'
    );

    /**
     * Init the resource
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('trello/label');

        $this->setPos('bottom');
    }

    public function loadFromTrello($trelloId)
    {
        return $this->load($trelloId, 'trello_label_id');
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
        parent::_beforeSave();

        /*if ($this->doSync) {
            $this->export();
        }*/

        return $this;
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
                array('labels'),
                Zend_Http_Client::POST,
                $params
            );

        $response = $this->getAdapter()->decodeResponse($listResponse);
        $this->processResponse($response);

        return $this;
    }

    /**
     * Update Trello list with provided data
     *
     * @deprecated
     *
     * @return array|string
     */
    public function sync()
    {
        $params = $this->prepareParams();

        $listResponse = $this->getAdapter()
            ->run(
                array('checklists' => $this->getListId()),
                Zend_Http_Client::PUT,
                $params
            );

        $response = $this->getAdapter()->decodeResponse($listResponse);
        return $this->processResponse($response);
    }

    protected function processResponse($response)
    {
        $this->setTrelloLabelId($response['id']);

        return $this;
    }
}