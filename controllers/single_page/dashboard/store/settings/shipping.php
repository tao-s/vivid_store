<?php
namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store\Settings;

use \Concrete\Core\Page\Controller\DashboardPageController;
use View;
use Loader;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethodType as StoreShippingMethodType;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethod as StoreShippingMethod;

class shipping extends DashboardPageController
{
    public function view()
    {
        $this->set("methodTypes", StoreShippingMethodType::getAvailableMethodTypes());
    }
    public function add($smtID)
    {
        $this->set('pageTitle', t("Add Shipping Method"));
        $smt = StoreShippingMethodType::getByID($smtID);
        $this->set('smt', $smt);
        $this->set("task", t("Add"));
    }
    public function edit($smID)
    {
        $this->set('pageTitle', t("Edit Shipping Method"));
        $sm = StoreShippingMethod::getByID($smID);
        $smt = $sm->getShippingMethodType();
        $this->set('sm', $sm);
        $this->set('smt', $smt);
        $this->set("task", t("Update"));
    }
    public function delete($smID)
    {
        $sm = StoreShippingMethod::getByID($smID);
        $sm->delete();
        $this->redirect('/dashboard/store/settings/shipping/removed');
    }
    public function success()
    {
        $this->view();
        $this->set("message", t("Successfully added a new Shipping Method"));
    }
    public function updated()
    {
        $this->view();
        $this->set("message", t("Successfully updated"));
    }
    public function removed()
    {
        $this->view();
        $this->set("message", t("Successfully removed"));
    }
    public function add_method()
    {
        $data = $this->post();
        $errors = $this->validate($data);
        $this->error = null; //clear errors
        $this->error = $errors;
        if (!$errors->has()) {
            if ($this->post('shippingMethodID')) {
                //update
                $shippingMethod = StoreShippingMethod::getByID($this->post('shippingMethodID'));
                $shippingMethodTypeMethod = $shippingMethod->getShippingMethodTypeMethod();
                $shippingMethodTypeMethod->update($this->post());
                $shippingMethod->update($this->post('methodName'), $this->post('methodEnabled'));
                $this->redirect('/dashboard/store/settings/shipping/updated');
            } else {
                //first we send the data to the shipping method type.
                $shippingMethodType = StoreShippingMethodType::getByID($this->post('shippingMethodTypeID'));
                $shippingMethodTypeMethod = $shippingMethodType->addMethod($this->post());
                //make a shipping method that correlates with it.
                StoreShippingMethod::add($shippingMethodTypeMethod, $shippingMethodType, $this->post('methodName'), true);
                $this->redirect('/dashboard/store/settings/shipping/success');
            }
        } else {
            if ($this->post('shippingMethodID')) {
                $this->edit($this->post('shippingMethodID'));
            } else {
                $this->add($this->post('shippingMethodTypeID'));
            }
        }
    }
    public function validate($data)
    {
        $this->error = null;
        $e = Loader::helper('validation/error');
        
        //check our manditory fields
        if ($data['methodName']=="") {
            $e->add(t("Method Name must be set"));
        }
        if (!is_numeric($data['minimumAmount'])) {
            $e->add(t("Minimum Amount must be numeric"));
        }
        if (!is_numeric($data['maximumAmount'])) {
            $e->add(t("Maximum Amount must be numeric"));
        }
        
        //pass the validator to the shipping method to check for it's own errors
        $shippingMethodType = StoreShippingMethodType::getByID($data['shippingMethodTypeID']);
        $e = $shippingMethodType->getMethodTypeController()->validate($data, $e);
        
        return $e;
    }
}
