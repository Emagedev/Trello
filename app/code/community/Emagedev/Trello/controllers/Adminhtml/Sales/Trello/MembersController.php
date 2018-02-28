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
 * @subpackage controllers
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */
class Emagedev_Trello_Adminhtml_Sales_Trello_MembersController extends Mage_Adminhtml_Controller_Action
{
    protected $_model = 'trello/member';

    /**
     * Init layout
     *
     * @return Emagedev_Trello_Adminhtml_Sales_Trello_MembersController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            //->_setActiveMenu('custom_modules/scheduled_content')
            ->_addBreadcrumb($this->__('Trello Members'), $this->__('Trello Members'));
        $this->_title($this->__('Trello Members'));

        return $this;
    }

    /**
     * Show grid
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    /**
     * Edit item action
     *
     * @return void
     */
    public function editAction()
    {
        $modelId = intval($this->getRequest()->getParam('id', 0));
        $error = false;
        if ($modelId) {
            $model = Mage::getModel($this->_model)->load($modelId);
            if ($model->getId()) {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($modelId);
                }
                Mage::register('current_trello_member', $model);
            } else {
                $this->_getSession()->addError($this->__('Item doesn\'t exist'));
                $error = true;
            }
        }

        if ($error) {
            $this->_redirectError($this->_getRefererUrl());
        } else {
            $this->_initAction();
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->renderLayout();
        }
    }

    public function fetchMembersAction()
    {
        /** @var Emagedev_Trello_Model_Resource_Member_Collection $collection */
        $collection = Mage::getModel('trello/member')->getCollection();

        try {
            $collection->fetchTrelloMembers();
            $collection->save();
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable To Fetch Members'));
        }

        $this->_getSession()->addSuccess($this->__('Members Fetched Successfully'));

        $this->_redirect('*/*/');
    }

    /**
     * save menu item action
     *
     * @return void
     */
    public function saveAction()
    {
        $error = false;

        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel($this->_model);

            $modelId = intval($this->getRequest()->getParam('id', 0));

            if ($modelId) {
                $model->load($modelId);
            } else {
                $this->_getSession()->addError($this->__('Cannot Implicit Create Users, Only Fetch'));
                $this->_redirectReferer();
                return;
            }

            $this->_getSession()->setFormData($data);

            try {
                $model->setData($data);

                if ($modelId) {
                    $model->setId($modelId);
                }

                $model->save();

                if (!$model->getId()) {
                    Mage::throwException($this->__('Error saving item'));
                }

                $this->_getSession()->addSuccess($this->__('Item was successfully saved.'));
                $this->_getSession()->setFormData(false);

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $error = true;
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Error while saving item'));
                Mage::logException($e);
                $error = true;
            }
        } else {
            $this->_getSession()->addError($this->__('No data found to save'));
        }

        if (!$error && isset($model) && $model->getId()) {
            // The following line decides if it is a "save" or "save and continue"
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
            } else {
                $this->_redirect('*/*/');
            }
        } else {
            $this->_redirectReferer();
        }
    }

    /**
     * Delete item action
     *
     * @return mixed
     */
    public function deleteAction()
    {
        if ($modelId = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel($this->_model);
                $model->setId($modelId);
                $model->delete();
                $this->_getSession()->addSuccess($this->__('Item has been deleted.'));
                $this->_redirect('*/*/');

                return;
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

                return;
            }
        }
        $this->_getSession()->addError($this->__('Unable to find the item to delete.'));
        $this->_redirect('*/*/');
    }

    /**
     * Load grid for ajax action
     *
     * @return void
     */
    public function gridAction()
    {
        $this->loadLayout()
            ->renderLayout();
    }

    /**
     * Mass delete items action
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $modelIds = $this->getRequest()->getParam('item');
        if (!is_array($modelIds)) {
            $this->_getSession()->addError($this->__('Please select item(s).'));
        } else {
            try {
                foreach ($modelIds as $modelId) {
                    Mage::getSingleton($this->_model)
                        ->load($modelId)
                        ->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were deleted.', count($modelIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/');
    }
}
