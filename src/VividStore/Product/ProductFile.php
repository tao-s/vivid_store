<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Product;

use Database;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="VividStoreDigitalFiles")
 */
class ProductFile
{
    /** 
     * @Id @Column(type="integer") 
     * @GeneratedValue 
     */
    protected $dfID;
    
    /**
     * @Column(type="integer")
     */
    protected $pID;
    
    /**
     * @Column(type="integer")
     */
    protected $dffID;
    
    private function setProductID($pID)
    {
        $this->pID = $pID;
    }
    private function setFileID($fID)
    {
        $this->dffID = $fID;
    }
    
    public function getID()
    {
        return $this->dfID;
    }
    public function getProductID()
    {
        return $this->pID;
    }
    public function getFileID()
    {
        return $this->dffID;
    }
    
    public static function getByID($id)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Product\ProductFile', $id);
    }
    
    public static function getFilesForProduct(StoreProduct $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository('Concrete\Package\VividStore\Src\VividStore\Product\ProductFile')->findBy(array('pID' => $product->getProductID()));
    }
    
    public static function getFileObjectsForProduct(StoreProduct $product)
    {
        $results = self::getFilesForProduct($product);
        $fileObjects = array();
        foreach ($results as $result) {
            $fileObjects[] = \File::getByID($result->getFileID());
        }
        return $fileObjects;
    }
    
    public static function addFilesForProduct(array $files, StoreProduct $product)
    {
        self::removeFilesForProduct($product);
        //add new ones.
        if(!empty($files['dffID'])) {
            foreach ($files['dffID'] as $fileID) {
                if (!empty($fileID) && $fileID > 0) {
                    self::add($product->getProductID(), $fileID);
                    $fileObj = \File::getByID($fileID);
                    $fs = \FileSet::getByName("Digital Downloads");
                    $fs->addFileToSet($fileObj);
                    $fileObj->resetPermissions(1);
                    $pk = \Concrete\Core\Permission\Key\FileKey::getByHandle('view_file');
                    $pk->setPermissionObject($fileObj);
                    $pao = $pk->getPermissionAssignmentObject();
                    $groupEntity = \Concrete\Core\Permission\Access\Entity\GroupEntity::getOrCreate(\Group::getByID(GUEST_GROUP_ID));
                    $pa = $pk->getPermissionAccessObject();
                    if ($pa) {
                        $pa->removeListItem($groupEntity);
                        $pao->assignPermissionAccess($pa);
                    }
                }
            }
        }
    }

    public static function removeFilesForProduct(StoreProduct $product)
    {
        $existingFiles = self::getFilesForProduct($product);
        foreach ($existingFiles as $file) {
            $file->delete();
        }
    }
    
    public static function add($pID, $fID)
    {
        $productFile = new self();
        $productFile->setProductID($pID);
        $productFile->setFileID($fID);
        $productFile->save();
        return $productFile;
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
