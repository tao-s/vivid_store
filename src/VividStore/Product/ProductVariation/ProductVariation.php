<?php
namespace Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation;

use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariationOptionItem as StoreProductVariationOptionItem;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as StorePrice;
use Doctrine\Common\Collections\ArrayCollection;
use Database;
use File;

/**
 * @Entity
 * @Table(name="VividStoreProductVariations")
 */
class ProductVariation
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $pvID;

    /**
     * @Column(type="integer")
     */
    protected $pID;

    /**
     * @Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pvPrice;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $pvSKU;

    /**
    * @Column(type="decimal", precision=10, scale=2, nullable=true)
    */
    protected $pvSalePrice;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $pvfID;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $pvQty;

    /**
     * @Column(type="boolean",nullable=true)
     */
    protected $pvQtyUnlim;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $pvWidth;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $pvHeight;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $pvLength;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $pvWeight;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $pvNumberItems;

    /**
     * @OneToMany(targetEntity="Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariationOptionItem", mappedBy="variation"))
     */
    protected $options;

    /**
     * @return mixed
     */
    public function getVariationFID()
    {
        return $this->pvfID;
    }

    /**
     * @param mixed $pvfID
     */
    public function setVariationFID($pvfID)
    {
        $this->pvfID = $pvfID;
    }


    public function getVariationImageID()
    {
        return $this->pvfID;
    }
    public function getVariationImageObj()
    {
        if ($this->pvfID) {
            $fileObj = File::getByID($this->pvfID);
            return $fileObj;
        }
    }

    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOptionItemIDs()
    {
        $options = $this->getOptions();

        $optionids = array();

        foreach ($options as $opt) {
            $optionids[] = $opt->getOption()->getID();
        }

        sort($optionids);
        return $optionids;
    }


    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->pvID;
    }

    /**
     * @return mixed
     */
    public function getProductID()
    {
        return $this->pID;
    }

    /**
     * @return mixed
     */
    public function getVariationSKU()
    {
        return $this->pvSKU;
    }

    /**
     * @param mixed $pvSKU
     */
    public function setVariationSKU($pvSKU)
    {
        $this->pvSKU = $pvSKU;
    }


    /**
     * @param mixed $pID
     */
    public function setProductID($pID)
    {
        $this->pID = $pID;
    }

    /**
     * @return mixed
     */
    public function getVariationPrice()
    {
        return $this->pvPrice;
    }

    public function getFormattedVariationPrice()
    {
        return StorePrice::format($this->pvPrice);
    }

    /**
     * @param mixed $pvPrice
     */
    public function setVariationPrice($pvPrice)
    {
        if ($pvPrice != '') {
            $this->pvPrice = $pvPrice;
        } else {
            $this->pvPrice = null;
        }
    }

    /**
     * @return mixed
     */
    public function getVariationSalePrice()
    {
        return $this->pvSalePrice;
    }

    /**
     * @param mixed $pvSalePrice
     */
    public function setVariationSalePrice($pvSalePrice)
    {
        if ($pvSalePrice != '') {
            $this->pvSalePrice = $pvSalePrice;
        } else {
            $this->pvSalePrice = null;
        }
    }

    /**
     * @return mixed
     */
    public function getVariationQty()
    {
        return $this->pvQty;
    }

    /**
     * @param mixed $pvQty
     */
    public function setVariationQty($pvQty)
    {
        $this->pvQty = $pvQty;
    }

    /**
     * @return mixed
     */
    public function getVariationQtyUnlim()
    {
        return $this->pvQtyUnlim;
    }

    /**
     * @param mixed $pvQtyUnlim
     */
    public function setVariationQtyUnlim($pvQtyUnlim)
    {
        $this->pvQtyUnlim = $pvQtyUnlim;
    }

    /**
     * @return mixed
     */
    public function getVariationWidth()
    {
        return $this->pvWidth;
    }

    /**
     * @param mixed $pWidth
     */
    public function setVariationWidth($pvWidth)
    {
        if ($pvWidth != '') {
            $this->pvWidth = $pvWidth;
        } else {
            $this->pvWidth = null;
        }
    }

    /**
     * @return mixed
     */
    public function getVariationHeight()
    {
        return $this->pvHeight;
    }

    /**
     * @param mixed $pvHeight
     */
    public function setVariationHeight($pvHeight)
    {
        if ($pvHeight != '') {
            $this->pvHeight = $pvHeight;
        } else {
            $this->pvHeight = null;
        }
    }

    /**
     * @return mixed
     */
    public function getVariationLength()
    {
        return $this->pvLength;
    }

    /**
     * @param mixed $pvLength
     */
    public function setVariationLength($pvLength)
    {
        if ($pvLength != '') {
            $this->pvLength = $pvLength;
        } else {
            $this->pvLength = null;
        }
    }

    /**
     * @return mixed
     */
    public function getVariationWeight()
    {
        return $this->pvWeight;
    }

    /**
     * @param mixed $pvWeight
     */
    public function setVariationWeight($pvWeight)
    {
        if ($pvWeight != '') {
            $this->pvWeight = $pvWeight;
        } else {
            $this->pvWeight = null;
        }
    }

    /**
     * @return mixed
     */
    public function getVariationNumberItems()
    {
        return $this->pvNumberItems;
    }

    /**
     * @param mixed $pvNumberItems
     */
    public function setVariationNumberItems($pvNumberItems)
    {
        if ($pvNumberItems != '') {
            $this->pvNumberItems = $pvNumberItems;
        } else {
            $this->pvNumberItems = null;
        }
    }


    public function isUnlimited()
    {
        return $this->getVariationQtyUnlim();
    }

    public function isSellable()
    {
        if ($this->isUnlimited() || $this->getVariationQty()> 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function addVariations(array $data, StoreProduct $product)
    {
        $optItems = $product->getProductOptionItems();

        $optionArrays = array();

        foreach ($optItems as $optItem) {
            $optionArrays[$optItem->getProductOptionGroupID()][] = $optItem->getID();
        }

        $comboOptions = self::combinations(array_values($optionArrays));

        $variationIDs = array();

        if (!empty($comboOptions)) {
            foreach ($comboOptions as $key=>$optioncombo) {
                if (!is_array($optioncombo)) {
                    $optioncomboarray =  array();
                    $optioncomboarray[] = $optioncombo;
                    $optioncombo = $optioncomboarray;
                }

                $variation = self::getByOptionItemIDs($optioncombo);

                if (!$variation) {
                    $variation = self::add(
                        $product->getProductID(),
                        array(
                        'pvSKU' => '',
                        'pvPrice' => '',
                        'pvSalePrice'=>'',
                        'pvQty'=>'',
                        'pvQtyUnlim'=>'',
                        'pvfID'=>'',
                        'pvWeight'=>'',
                        'pvNumberItems'=>'',
                        'pvWidth'=>'',
                        'pvHeight'=>'',
                        'pvLength'=>'')
                    );

                    foreach ($optioncombo as $optionvalue) {
                        $option = StoreProductOptionItem::getByID($optionvalue);

                        if ($option) {
                            $variationoption = new StoreProductVariationOptionItem();
                            $variationoption->setOption($option);
                            $variationoption->setVariation($variation);
                            $variationoption->save();
                        }
                    }
                } else {
                    $key = $variation->getID();

                    $variation->setVariationSKU($data['pvSKU'][$key]);
                    $variation->setVariationPrice($data['pvPrice'][$key]);
                    $variation->setVariationSalePrice($data['pvSalePrice'][$key]);
                    $variation->setVariationQty($data['pvQty'][$key]);
                    $variation->setVariationQtyUnlim($data['pvQtyUnlim'][$key]);
                    $variation->setVariationFID($data['pvfID'][$key]);
                    $variation->setVariationWidth($data['pvWeight'][$key]);
                    $variation->setVariationNumberItems($data['pvNumberItems'][$key]);
                    $variation->setVariationWeight($data['pvWidth'][$key]);
                    $variation->setVariationHeight($data['pvHeight'][$key]);
                    $variation->setVariationLength($data['pvLength'][$key]);
                    $variation->save();
                }

                $variationIDs[] = $variation->getID();
            }
        }

        $db = Database::connection();

        if (!empty($variationIDs)) {
            $options = implode(',', $variationIDs);
            $pvIDstoDelete = $db->getAll("SELECT pvID FROM VividStoreProductVariations WHERE pID = ? and pvID not in ($options)", array($product->getProductID()));
        } else {
            $pvIDstoDelete = $db->getAll("SELECT pvID FROM VividStoreProductVariations WHERE pID = ?", array($product->getProductID()));
        }

        if (!empty($pvIDstoDelete)) {
            foreach ($pvIDstoDelete as $pvID) {
                $variation = self::getByID($pvID);
                if ($variation) {
                    $variation->delete();
                }
            }
        }
    }

    public static function getByID($pvID)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariation', $pvID);
    }

    public static function add($productID, $data)
    {
        $variation = new self();
        $variation->setProductID($productID);
        $variation->setVariationSKU($data['pvSKU']);
        $variation->setVariationPrice($data['pvPrice']);
        $variation->setVariationSalePrice($data['pvSalePrice']);
        $variation->setVariationQty($data['pvQty']);
        $variation->setVariationQtyUnlim($data['pvQtyUnlim']);
        $variation->setVariationFID($data['pvfID']);
        $variation->setVariationWeight($data['pvWidth']);
        $variation->setVariationNumberItems($data['pvNumberItems']);
        $variation->setVariationHeight($data['pvHeight']);
        $variation->setVariationLength($data['pvLength']);
        $variation->setVariationWidth($data['pvWeight']);
        $variation->save();
        return $variation;
    }

    public static function getByOptionItemIDs(array $optionids)
    {
        $db = \Database::connection();

        if (is_array($optionids) && !empty($optionids)) {
            $options = implode(',', $optionids);
            $pvID = $db->fetchColumn("SELECT pvID FROM VividStoreProductVariationOptionItems WHERE poiID in ($options)
                                 group by pvID having count(*) = ?", array(count($optionids)));
            return self::getByID($pvID);
        }

        return false;
    }

    public function save()
    {
        $em = Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public static function getVariationsForProduct(StoreProduct $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository('Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariation')->findBy(array('pID' => $product->getProductID()));
    }

    public function delete()
    {
        $em = Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }

    public static function removeVariationsForProduct(StoreProduct $product, $excluding = array())
    {
        if (!is_array($excluding)) {
            $excluding = array();
        }

        //clear out existing product option groups
        $existingVariations = self::getVariationsForProduct($product);
        foreach ($existingVariations as $variation) {
            if (!in_array($variation->getID(), $excluding)) {
                $variation->delete();
            }
        }
    }

    public static function combinations($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        // get combinations from subsequent arrays
        $tmp = self::combinations($arrays, $i + 1);

        $result = array();

        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge(array($v), $t) :
                    array($v, $t);
            }
        }

        return $result;
    }
}
