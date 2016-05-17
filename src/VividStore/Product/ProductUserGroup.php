<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Product;

use Database;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="VividStoreProductUserGroups")
 */
class ProductUserGroup
{
    /** 
     * @Id @Column(type="integer") 
     * @GeneratedValue 
     */
    protected $pugID;
    
    /**
     * @Column(type="integer")
     */
    protected $pID;
    
    /**
     * @Column(type="integer")
     */
    protected $gID;
    
    private function setProductID($pID)
    {
        $this->pID = $pID;
    }
    private function setUserGroupID($gID)
    {
        $this->gID = $gID;
    }
    
    public function getProductID()
    {
        return $this->pID;
    }
    public function getUserGroupID()
    {
        return $this->gID;
    }
    
    public static function getByID($pgID)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Product\ProductGroup', $pgID);
    }
    
    public static function getUserGroupsForProduct(StoreProduct $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository('Concrete\Package\VividStore\Src\VividStore\Product\ProductUserGroup')->findBy(array('pID' => $product->getProductID()));
    }
    
    public static function getUserGroupIDsForProduct($product)
    {
        $userGroups = self::getUserGroupsForProduct($product);
        $groupIDs = array();
        foreach ($userGroups as $userGroup) {
            $groupIDs[] = $userGroup->getUserGroupID();
        }
        return $groupIDs;
    }
    
    public static function addUserGroupsForProduct(array $data, StoreProduct $product)
    {
        //clear out existing groups
        self::removeUserGroupsForProduct($product);

        //add new ones.
        if (!empty($data['pUserGroups'])) {
            foreach ($data['pUserGroups'] as $gID) {
                self::add($product->getProductID(), $gID);
            }
        }
    }

    public static function removeUserGroupsForProduct(StoreProduct $product)
    {
        $existingUserGroups = self::getUserGroupsForProduct($product);
        foreach ($existingUserGroups as $group) {
            $group->delete();
        }
    }

    public static function add($pID, $gID)
    {
        $productUserGroup = new self();
        $productUserGroup->setProductID($pID);
        $productUserGroup->setUserGroupID($gID);
        $productUserGroup->save();
        return $productUserGroup;
    }
    
    public function save()
    {
        $em = Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
    
    public function delete()
    {
        $em = Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
}
