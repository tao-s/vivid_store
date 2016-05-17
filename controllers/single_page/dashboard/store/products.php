<?php
namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store;

use \Concrete\Core\Page\Controller\DashboardPageController;
use Core;
use View;
use FilePermissions;
use TaskPermission;
use File;
use Loader;
use PageType;
use GroupList;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductFile as StoreProductFile;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductGroup as StoreProductGroup;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductImage as StoreProductImage;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductList as StoreProductList;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductLocation as StoreProductLocation;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductUserGroup as StoreProductUserGroup;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOption as StoreProductOption;
use \Concrete\Package\VividStore\Src\VividStore\Group\Group as StoreGroup;
use \Concrete\Package\VividStore\Src\VividStore\Group\GroupList as StoreGroupList;
use \Concrete\Package\VividStore\Src\Attribute\Key\StoreProductKey;
use \Concrete\Package\VividStore\Src\VividStore\Tax\TaxClass as StoreTaxClass;

class products extends DashboardPageController
{
    public function view($gID=null)
    {
        $products = new StoreProductList();
        $products->setItemsPerPage(10);
        $products->setGroupID($gID);
        $products->activeOnly(false);
        $products->setShowOutOfStock(true);
        $products->setSortBy('date');


        if ($this->get('keywords')) {
            $products->setSearch($this->get('keywords'));
        }

        $paginator = $products->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('products', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

        $this->getAssets();

        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist", $grouplist);
    }
    private function getAssets()
    {
        $js = \Concrete\Package\VividStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('css', 'vividStoreDashboard');
        $this->requireAsset('javascript', 'vividStoreFunctions');
    }
    public function success()
    {
        $this->set("success", t("Product Added"));
        $this->view();
    }
    
    public function updated()
    {
        $this->set("success", t("Product Updated"));
        $this->view();
    }
    public function removed()
    {
        $this->set("success", t("Product Removed"));
        $this->view();
    }
    public function add()
    {
        $this->loadFormAssets();
        $this->getAssets();
        $this->set("actionType", t("Add"));
        
        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist", $grouplist);
        foreach ($grouplist as $productgroup) {
            $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
        }
        $this->set("productgroups", $productgroups);

        $gl = new GroupList();
        $gl->setItemsPerPage(1000);
        $gl->filterByAssignable();
        $usergroups = $gl->get();

        $usergrouparray = array();

        foreach ($usergroups as $ug) {
            if ($ug->gName != 'Administrators') {
                $usergrouparray[$ug->gID] = $ug->gName;
            }
        }

        $this->set('pageTitle', t('Add Product'));
        $this->set('usergroups', $usergrouparray);
    }
    public function edit($pID, $status = '')
    {
        if ($status == 'updated') {
            $this->set("success", t("Product Updated"));
        }

        if ($status == 'added') {
            $this->set("success", t("Product Added"));
        }

        $this->loadFormAssets();
        $this->getAssets();
        $this->set("actionType", t("Update"));
        
        //get the product
        $product = StoreProduct::getByID($pID);

        if (!$product) {
            $this->redirect('/dashboard/store/products/');
        }

        $optItems = $product->getProductOptionItems();
        $groups = $product->getProductOptionGroups();

        $this->set('p', $product);
        $this->set("images", $product->getProductImages());
        $this->set("groups", $groups);
        $this->set('optItems', $optItems);
        $this->set('locationPages', $product->getProductLocationPages());
        $this->set('pgroups', $product->getProductGroupIDs());

        $variations = $product->getProductVariations();
        $variationLookup = array();

        $optionArrays = array();
        $optionItemLookup = array();

        foreach ($optItems as $optItem) {
            $optionArrays[$optItem->getProductOptionGroupID()][] = $optItem->getID();
            $optionItemLookup[ $optItem->getID()] = $optItem;
        }

        $groupLookup = array();

        foreach ($groups as $group) {
            $groupLookup[$group->getID()] = $group;
        }

        $this->set('groupLookup', $groupLookup);

        $optionArrays = array_values($optionArrays);
        $comboOptions = StoreProductVariation::combinations($optionArrays);

        $checkedOptions = array();

        foreach ($comboOptions as $option) {
            if (!is_array($option)) {
                $checkedOptions[] = array($option);
            } else {
                $checkedOptions[] =$option;
            }
        }

        $comboOptions = $checkedOptions;

        $this->set('comboOptions', $comboOptions);
        $this->set('optionItemLookup', $optionItemLookup);

        foreach ($variations as $variation) {
            $options = $variation->getOptions();
            $optionids = array();

            foreach ($options as $varoption) {
                $option = $varoption->getOption();

                if ($option) {
                    $optionids[] = $option->getID();
                }
            }

            sort($optionids);
            $variationLookup[implode('_', $optionids)] = $variation;
        }

        $this->set('variations', $variations);
        $this->set('variationLookup', $variationLookup);

        //populate "Groups" select box options
        $grouplist = StoreGroupList::getGroupList();
        foreach ($grouplist as $productgroup) {
            $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
        }
        $this->set("productgroups", $productgroups);

        $gl = new GroupList();
        $gl->setItemsPerPage(1000);
        $gl->filterByAssignable();
        $usergroups = $gl->get();

        $usergrouparray = array();

        foreach ($usergroups as $ug) {
            if ($ug->gName != 'Administrators') {
                $usergrouparray[$ug->gID] = $ug->gName;
            }
        }

        $this->set('pageTitle', t('Edit Product'));
        $this->set('usergroups', $usergrouparray);
    }


    public function generate($pID, $templateID=null)
    {
        StoreProduct::getByID($pID)->generatePage($templateID);
        $this->redirect('/dashboard/store/products/edit', $pID);
    }
    public function delete($pID)
    {
        $product = StoreProduct::getByID($pID);
        $product->remove();
        $this->redirect('/dashboard/store/products/removed');
    }
    public function loadFormAssets()
    {
        $this->requireAsset('redactor');
        $this->requireAsset('core/file-manager');
        $this->requireAsset('core/sitemap');
        
        $this->set('fp', FilePermissions::getGlobal());
        $this->set('tp', new TaskPermission());
        $this->set('al', Core::make('helper/concrete/asset_library'));
                
        $this->addHeaderItem('<style type="text/css">.redactor_editor{padding:20px}</style>');
        $this->requireAsset('css', 'vividStoreDashboard');
        $this->requireAsset('javascript', 'vividStoreFunctions');
        
        $attrList = StoreProductKey::getList();
        $this->set('attribs', $attrList);
        
        $pageType = PageType::getByHandle("store_product");
        $pageTemplates = $pageType->getPageTypePageTemplateObjects();
        $templates = array();
        foreach ($pageTemplates as $pt) {
            $templates[$pt->getPageTemplateID()] = $pt->getPageTemplateName();
        }
        $this->set('pageTemplates', $templates);
        $taxClasses = array();
        foreach (StoreTaxClass::getTaxClasses() as $taxClass) {
            $taxClasses[$taxClass->getTaxClassID()] = $taxClass->getTaxClassName();
        }
        $this->set('taxClasses', $taxClasses);
    }
    public function save()
    {
        $data = $this->post();
        if ($data['pID']) {
            $this->edit($data['pID']);
        } else {
            $this->add();
        }
        if ($this->isPost()) {
            $errors = $this->validate($data);
            $this->error = null; //clear errors
            $this->error = $errors;
            if (!$errors->has()) {
                    
                //save the product
                $product = StoreProduct::saveProduct($data);
                //save product attributes
                $aks = StoreProductKey::getList();
                foreach ($aks as $uak) {
                    $uak->saveAttributeForm($product);
                }
                //save images
                StoreProductImage::addImagesForProduct($data, $product);
                
                //save product groups
                StoreProductGroup::addGroupsForProduct($data, $product);
                
                //save product user groups
                StoreProductUserGroup::addUserGroupsForProduct($data, $product);
                
                //save product options
                StoreProductOption::addProductOptions($data, $product);
                
                //save files
                StoreProductFile::addFilesForProduct($data, $product);
                
                //save category locations
                StoreProductLocation::addLocationsForProduct($data, $product);

                // save variations
                StoreProductVariation::addVariations($data, $product);


                if ($data['pID']) {
                    $this->redirect('/dashboard/store/products/', 'updated');
                } else {
                    $this->redirect('/dashboard/store/products/', 'success');
                }
            }//if no errors
        }//if post
    }
    public function validate($args)
    {
        $e = Loader::helper('validation/error');
        
        if ($args['pName']=="") {
            $e->add(t('You must have a Product Name'));
        }
        if (strlen($args['pName']) > 255) {
            $e->add(t('Keep the Product name under 255 Characters'));
        }
        if (!is_numeric($args['pPrice'])) {
            $e->add(t('The Price must be set, and numeric'));
        }
        if (!is_numeric($args['pQty']) && !$args['pQtyUnlim']) {
            $e->add(t('The Quantity must be set, and numeric'));
        }
        if (!is_numeric($args['pWidth'])) {
            $e->add(t('The Product Width must be a number'));
        }
        if (!is_numeric($args['pHeight'])) {
            $e->add(t('The Product Height must be a number'));
        }
        if (!is_numeric($args['pLength'])) {
            $e->add(t('The Product Length must be a number'));
        }
        if (!is_numeric($args['pWeight'])) {
            $e->add(t('The Product Weight must be a number'));
        }
        
        return $e;
    }
    
    // GROUPS PAGE
    public function groups()
    {
        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist", $grouplist);
        $this->requireAsset('css', 'vividStoreDashboard');
        $this->requireAsset('javascript', 'vividStoreFunctions');
    }
    public function groupadded()
    {
        $this->set('success', "Group Successfully Added!");
        $this->groups();
    }
    public function addgroup()
    {
        $this->groups();
        $this->error = null; //clear errors
        $errors = $this->validateGroup($this->post());
        $this->error = $errors;
        if (!$errors->has()) {
            StoreGroup::add($this->post('groupName'));
            $this->redirect('/dashboard/store/products/', 'groupadded');
        }
    }
    public function editgroup($gID)
    {
        StoreGroup::getByID($gID)->update($this->post('gName'));
    }
    public function validateGroup($args)
    {
        $e = Loader::helper('validation/error');
        
        if ($args['groupName']=="") {
            $e->add(t('You did not enter anything for the Group Name'));
        }
        if (strlen($args['groupName']) > 100) {
            $e->add(t('Keep the Group Name under 100 Characters'));
        }
        return $e;
    }
    public function deletegroup($gID)
    {
        StoreGroup::getByID($gID)->remove();
    }
}
