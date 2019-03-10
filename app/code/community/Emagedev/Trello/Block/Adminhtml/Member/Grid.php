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
 * @subpackage Block
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */
class Emagedev_Trello_Block_Adminhtml_Member_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Init grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('membersGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $this->setModelPath('trello/member');
        $this->setIdFieldName(Mage::getModel($this->getModelPath())->getResource()->getIdFieldName());
    }

    /**
     * prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel($this->getModelPath())->getCollection();
        $this->setCollection($collection);

        $this->prepareAdminUsers();

        return parent::_prepareCollection();
    }

    protected function prepareAdminUsers()
    {
        /** @var Emagedev_Trello_Model_Resource_Member_Collection $trelloUserCollection */
        $trelloUserCollection = $this->getCollection();
        $adminUserIds = array();

        /** @var Emagedev_Trello_Model_Member $trelloUser */
        foreach ($trelloUserCollection as $trelloUser) {
            $adminUserIds[] = $trelloUser->getAdminUserId();
        }

        /** @var Mage_Admin_Model_Resource_User_Collection $users */
        $users = Mage::getModel('admin/user')->getCollection();
        $users->addFieldToFilter('user_id', array('in' => $adminUserIds));

        Mage::register('current_trello_members_admin_ids', $users);

        return $this;
    }

    /**
     * prepare grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn($this->getIdFieldName(), array(
            'header'    =>  $this->__('Link ID'),
            'align'     =>  'right',
            'width'     =>  '100px',
            'type'      =>  'int',
            'index'     =>  $this->getIdFieldName()
        ));

        $this->addColumn('admin_user_id', array(
            'header'    =>  $this->__('Magento Admin User'),
            'align'     =>  'center',
            'renderer'  =>  'Emagedev_Trello_Block_Adminhtml_Member_Grid_Renderer_Admin_User',
            'index'     =>  'admin_user_id'
        ));

        $this->addColumn('full_name', array(
            'header'    =>  $this->__('Trello Full Name'),
            'align'     =>  'center',
            'type'      =>  'text',
            'index'     =>  'full_name'
        ));

        $this->addColumn('trello_member_id', array(
            'header'    =>  $this->__('Trello Member ID'),
            'align'     =>  'center',
            'type'      =>  'text',
            'index'     =>  'trello_member_id'
        ));

        $this->addColumn('active', array(
            'header'    =>  $this->__('Active'),
            'align'     =>  'center',
            'renderer'  =>  'Emagedev_Trello_Block_Adminhtml_Member_Grid_Renderer_Admin_Active',
            'index'     =>  'active'
        ));

        return parent::_prepareColumns();
    }

    /**
     * get the row url for edit
     *
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * prepare mass action methods
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField($this->getIdFieldName());
        $this->getMassactionBlock()->setFormFieldName('item');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => $this->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => $this->__('Are you sure?')
        ));

        return parent::_prepareMassaction();
    }

    /**
     * get the grid url for ajax updates
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
