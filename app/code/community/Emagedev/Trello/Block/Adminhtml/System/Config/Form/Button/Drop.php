<?php


class Emagedev_Trello_Block_Adminhtml_System_Config_Form_Button_Drop
    extends Emagedev_Trello_Block_Adminhtml_System_Config_Form_Button_Abstract
{
    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/sales_trello/drop');
    }

    public function getLabel()
    {
        return $this->__('Drop');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'id'       => $this->getElement()->getHtmlId(),
                    'disabled' => !$this->webhookConnected(),
                    'label'    => $this->getLabel(),
                    'onclick'  => 'javascript:void'
                )
            );

        return $button->toHtml();
    }
}