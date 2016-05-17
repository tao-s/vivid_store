<?php
namespace Concrete\Package\VividStore\Src\VividStore\Cart;

use Session;
use Database;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethod as StoreShippingMethod;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Calculator as StoreCalculator;

class Cart
{
    protected static $cart = null;

    public static function getCart()
    {

        // this acts as a singleton, in that it wil only fetch the cart from the session and check it for validity once per request
        if (!isset(self::$cart)) {
            $cart = Session::get('vividstore.cart');
            if (!is_array($cart)) {
                Session::set('vividstore.cart', array());
                $cart = array();
            }

            $db = Database::get();

            $checkeditems = array();
            $update = false;
            // loop through and check if product hasn't been deleted. Remove from cart session if not found.
            foreach ($cart as $cartitem) {
                $product = StoreProduct::getByID((int)$cartitem['product']['pID']);

                if ($product) {
                    // check that we dont have a non-quantity product in cart with a quantity > 1
                    if (!$product->allowQuantity() && $cartitem['product']['qty'] > 0) {
                        $cartitem['product']['qty'] = 1;
                        $update = true;
                    }

                    $include = true;

                    if ($cartitem['product']['variation']) {
                        if (!StoreProductVariation::getByID($cartitem['product']['variation'])) {
                            $include = false;
                        }
                    }

                    if ($include) {
                        $checkeditems[] = $cartitem;
                    }
                } else {
                    $update = true;
                }
            }

            if ($update) {
                Session::set('vividstore.cart', $checkeditems);
            }

            self::$cart = $checkeditems;
        }

        return self::$cart;
    }

    public function add($data)
    {
        $product = StoreProduct::getByID((int)$data['pID']);

        if (!$product) {
            return false;
        }

        if ($product->isExclusive()) {
            self::clear();
        }

        //now, build a nicer "cart item"
        $cartItem = array();
        $cartItem['product'] = array(
            "pID"=>(int) $data['pID'],
            "qty"=>(int) $data['quantity']
        );
        unset($data['pID']);
        unset($data['quantity']);

        //since we removed the ID/qty, we're left with just the attributes
        $cartItem['productAttributes'] = $data;

        $removeexistingexclusive  = false;

        foreach (self::getCart() as $k=>$cart) {
            $cartproduct = StoreProduct::getByID((int)$cart['product']['pID']);

            if ($cartproduct && $cartproduct->isExclusive()) {
                self::remove($k);
                $removeexistingexclusive = true;
            }
        }

        $optionItemIds = array();

        // search for product options, if found, collect the id
        foreach ($cartItem['productAttributes'] as $name=>$value) {
            if (substr($name, 0, 3) == 'pog') {
                $optionItemIds[] = $value;
            }
        }

        if (!empty($optionItemIds)) {
            // find the variation via the ids of the options
            $variation = StoreProductVariation::getByOptionItemIDs($optionItemIds);

            // association the variation with the product
            if ($variation) {
                $options = $variation->getOptions();
                if (count($options) == count($optionItemIds)) {  // check if we've matched to a variation with the correct number of options
                    $product->setVariation($variation);
                    $cartItem['product']['variation'] = $variation->getID();
                } else {
                    return false;
                }
            } else {
                return false; // variation not matched
            }
        } elseif ($product->hasVariations()) {
            return false;  // if we have a product with variations, but no variation data was submitted, it's a broken add-to-cart form
        }


        $cart = self::getCart();

        $exists = self::checkForExistingCartItem($cartItem);

        if ($exists['exists'] === true) {
            $existingproductcount = $cart[$exists['cartItemKey']]['product']['qty'];

            //we have a match, update the qty
            if ($product->allowQuantity()) {
                $newquantity = $cart[$exists['cartItemKey']]['product']['qty'] + $cartItem['product']['qty'];

                if (!$product->isUnlimited() &&  !$product->allowBackOrders() && $product->getProductQty() < max($newquantity, $existingproductcount)) {
                    $newquantity = $product->getProductQty();
                }

                $added = $newquantity - $existingproductcount;
            } else {
                $added = 1;
                $newquantity = 1;
            }

            $cart[$exists['cartItemKey']]['product']['qty'] = $newquantity;
        } else {
            $newquantity = $cartItem['product']['qty'];


            if (!$product->isUnlimited() && !$product->allowBackOrders() && $product->getProductQty() < $newquantity) {
                $newquantity = $product->getProductQty();
            }

            $cartItem['product']['qty'] = $newquantity;

            if ($product->isExclusive()) {
                $cart = array($cartItem);
            } else {
                $cart[] = $cartItem;
            }

            $added = $newquantity;
        }

        Session::set('vividstore.cart', $cart);
        return array('added' => $added, 'exclusive'=>$product->isExclusive(), 'removeexistingexclusive'=> $removeexistingexclusive);
    }

    public function checkForExistingCartItem($cartItem)
    {
        /*
         * We need to add the item to the cart, however, first we need to do some comparisons.
         * If we're adding a product that already exists, but the attributes are different,
         * we need to create a new instance of that item. 
         * If the attibutes are the same, we just need to update the quantity.
         * If it doesn't exist, we're free to just add it. 
         * 
         * phew.
         * 
         */


        foreach (self::getCart() as $k=>$cart) {
            //  check if product is the same id first.
            if ($cart['product']['pID'] == $cartItem['product']['pID']) {

                // check if the number of attributes is the same
                if (count($cart['productAttributes']) == count($cartItem['productAttributes'])) {
                    if (empty($cartItem['productAttributes'])) {
                        // if we have no attributes, it's a direct match
                      return array('exists'=>true,'cartItemKey'=>$k);
                    } else {
                        // otherwise loop through attributes
                        $attsmatch = true;

                        foreach ($cartItem['productAttributes'] as $key=>$value) {
                            if (array_key_exists($key, $cart['productAttributes']) && $cart['productAttributes'][$key] == $value) {
                                // attributes match, keep checking
                            } else {
                                //different attributes means different "product".
                                $attsmatch = false;
                                break;
                            }
                        }

                        if ($attsmatch) {
                            return array('exists'=>true,'cartItemKey'=>$k);
                        }
                    }
                }
            }
        }

        return array('exists'=>false,'cartItemKey'=>null);
    }

    public static function update($data)
    {
        $instanceID = $data['instance'];
        $qty = (int)$data['pQty'];
        $cart = self::getCart();

        $product = StoreProduct::getByID((int)$cart[$instanceID]['product']['pID']);

        if ($qty > 0 && $product) {
            $newquantity = $qty;

            if ($cart[$instanceID]['product']['variation']) {
                $product->setVariation($cart[$instanceID]['product']['variation']);
            }

            if (!$product->isUnlimited() && !$product->allowBackOrders() && $product->getProductQty() < $newquantity) {
                $newquantity = $product->getProductQty();
            }

            $cart[$instanceID]['product']['qty'] = $newquantity;
            $added = $newquantity;
        } else {
            self::remove($instanceID);
        }

        Session::set('vividstore.cart', $cart);
        self::$cart = null;

        return array('added' => $added);
    }

    public static function remove($instanceID)
    {
        $cart = self::getCart();
        unset($cart[$instanceID]);
        Session::set('vividstore.cart', $cart);
        self::$cart = null;
    }

    public static function clear()
    {
        $cart = self::getCart();
        unset($cart);
        Session::set('vividstore.cart', null);
        self::$cart = null;
    }
    
    public static function getTotalItemsInCart()
    {
        $total = 0;
        if (self::getCart()) {
            foreach (self::getCart() as $item) {
                $subtotal = $item['product']['qty'];
                $total = $total + $subtotal;
            }
        }
        return $total;
    }

    public function isShippable()
    {
        $shippableItems = self::getShippableItems();
        $shippingMethods = StoreShippingMethod::getAvailableMethods();
        if (count($shippingMethods) > 0) {
            if (count($shippableItems) > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getShippableItems()
    {
        $shippableItems = array();
        //go through items
        if (self::getCart()) {
            foreach (self::getCart() as $item) {
                //check if items are shippable
                $product = StoreProduct::getByID($item['product']['pID']);
                if ($product->isShippable()) {
                    $shippableItems[] = $item;
                }
            }
        }

        return $shippableItems;
    }
    
    public function getCartWeight()
    {
        $totalWeight = 0;
        if (self::getCart()) {
            foreach (self::getCart() as $item) {
                $product = StoreProduct::getByID($item['product']['pID']);
                if ($product->isShippable()) {
                    $totalProductWeight = $product->getProductWeight() * $item['product']['qty'];
                    $totalWeight = $totalWeight + $totalProductWeight;
                }
            }
        }
        //only returns weight of shippable items.
        return $totalWeight;
    }


    // determines if a cart requires a customer to be logged in
    public static function requiresLogin()
    {
        if (self::getCart()) {
            foreach (self::getCart() as $item) {
                $product = StoreProduct::getByID($item['product']['pID']);
                if ($product) {
                    if (($product->hasUserGroups() || $product->hasDigitalDownload()) && !$product->createsLogin()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    // determines if the cart contains a product that will auto-create a user account
    public function createsAccount()
    {
        if (self::getCart()) {
            foreach (self::getCart() as $item) {
                $product = StoreProduct::getByID($item['product']['pID']);
                if ($product) {
                    if ($product->createsLogin()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
    //deprecated
    public function getTotal()
    {
        return StoreCalculator::getGrandTotal();
    }
}
