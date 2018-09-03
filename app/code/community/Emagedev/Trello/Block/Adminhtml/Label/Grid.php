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
 * @subpackage Block
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */
class Emagedev_Trello_Block_Adminhtml_Label_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Init grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('labelGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $this->setModelPath('trello/label');
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
        return parent::_prepareCollection();
    }

    /**
     * prepare grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn($this->getIdFieldName(), array(
            'header'    =>  $this->__('ID'),
            'align'     =>  'right',
            'width'     =>  '50px',
            'type'      =>  'int',
            'index'     =>  $this->getIdFieldName()
        ));

        $this->addColumn('name', array(
            'header'    =>  $this->__('Label Name'),
            'align'     =>  'center',
            'type'      =>  'text',
            'index'     =>  'name'
        ));

        $this->addColumn('trello_label_id', array(
            'header'    =>  $this->__('Trello Label ID'),
            'align'     =>  'center',
            'type'      =>  'text',
            'index'     =>  'trello_label_id'
        ));

        $this->addColumn('color', array(
            'header'    =>  $this->__('Label Color'),
            'align'     =>  'center',
            'type'      =>  'text',
            'index'     =>  'color'
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
