<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Group;

use Database;

/**
 * @Entity
 * @Table(name="VividStoreGroups")
 */
class Group
{
    /** 
     * @Id @Column(type="integer") 
     * @GeneratedValue 
     */
    protected $gID;
    
    /**
     * @Column(type="string")
     */
    protected $groupName;
    
    private function setGroupName($groupName)
    {
        $this->groupName = $groupName;
    }
    
    public function getGroupName()
    {
        return $this->groupName;
    }
    public function getGroupID()
    {
        return $this->gID;
    }
    
    public static function getByID($gID)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Group\Group', $gID);
    }
    
    public static function add($groupName)
    {
        $productGroup = new self();
        $productGroup->setGroupName($groupName);
        $productGroup->save();
        return $productGroup;
    }
    
    public function update($groupName)
    {
        $this->setGroupName($groupName);
        $this->save();
        return $this;
    }
    
    public function save()
    {
        $em = Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
    
    public function remove()
    {
        $em = Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
}
