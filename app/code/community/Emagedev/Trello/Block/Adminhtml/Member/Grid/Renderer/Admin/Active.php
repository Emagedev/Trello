<?php


class Emagedev_Trello_Block_Adminhtml_Member_Grid_Renderer_Admin_Active extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $status = $value ? 'Active' : 'Disabled';
        $class = $value ? 'grid-severity-notice' : 'grid-severity-critical';

        return '<span class="' . $class . '"><span>' . $status . '</span></span>';
    }
}