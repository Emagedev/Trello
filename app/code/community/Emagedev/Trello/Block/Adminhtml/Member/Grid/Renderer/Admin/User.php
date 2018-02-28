<?php


class Emagedev_Trello_Block_Adminhtml_Member_Grid_Renderer_Admin_User extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        /** @var Mage_Admin_Model_Resource_User_Collection $users */
        $users = Mage::registry('current_trello_members_admin_ids');
        $value = $row->getData($this->getColumn()->getIndex());

        /** @var Mage_Admin_Model_User $user */
        $user = $users->getItemById($value);

        if ($user) {
            return implode(' ', array($user->getFirstname(), $user->getLastname())) . ' (' . $value . ')';
        }

        return 'Not Connected';
    }
}