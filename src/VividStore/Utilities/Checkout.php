<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use Controller;
use Core;
use Database;
use Session;
use Illuminate\Filesystem\Filesystem;
use View;
use User;
use UserInfo;
use UserAttributeKey;
use Concrete\Attribute\Address\Value as AttributeValue;
use \Concrete\Package\VividStore\Src\VividStore\Customer\Customer as StoreCustomer;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart as StoreCart;

class Checkout extends Controller
{
    //public $error;
    public function updater()
    {
        if (isset($_POST)) {
            $data = $_POST;
            $billing = false;
            if ($data['adrType']=='billing') {
                $billing=true;

                $u = new User();
                $guest = !$u->isLoggedIn();

                $requiresLoginOrDifferentEmail = false;

                if ($guest) {
                    $emailexists = $this->validateAccountEmail($data['email']);
                }

                $orderRequiresLogin = StoreCart::requiresLogin();

                if ($orderRequiresLogin && $emailexists) {
                    $requiresLoginOrDifferentEmail = true;
                }
            }

            $e = $this->validateAddress($data, $billing);

            if ($requiresLoginOrDifferentEmail) {
                $e->add(t('The email address you have entered has already been used to create an account. Please login first or enter a different email address.'));
            }

            if ($e->has()) {
                echo $e->outputJSON();
            } else {
                $customer = new StoreCustomer();
                $address = new AttributeValue();



                if ($data['adrType']=='billing') {
                    $this->updateBilling($data);
                    $addressraw = $customer->getValue('billing_address');
                    $phone = $customer->getValue('billing_phone');
                    $first_name = $customer->getValue('billing_first_name');
                    $last_name = $customer->getValue('billing_last_name');
                    $company_name = $customer->getValue('billing_company_name');
                    $email = $customer->getEmail();
                }

                if ($data['adrType']=='shipping') {
                    $this->updateShipping($data);
                    $addressraw = $customer->getValue('shipping_address');
                    $phone = '';
                    $email = '';
                    $first_name = $customer->getValue('shipping_first_name');
                    $last_name = $customer->getValue('shipping_last_name');
                    $company_name = $customer->getValue('shipping_company_name');
                }

                // use concrete5's built in address class for formatting
                $address->address1 = $addressraw->address1;
                $address->address2 = $addressraw->address2;
                $address->city = $addressraw->city;
                $address->state_province = $addressraw->state_province;
                $address->postal_code = $addressraw->postal_code;
                $address->city  = $addressraw->city;
                $address->country = $addressraw->country;

                $address = nl2br($address . '');  // force to string

                echo json_encode(array('first_name'=>$first_name, 'last_name'=>$last_name, 'company_name'=>$company_name, 'phone'=>$phone, 'email'=>$email, 'address'=>$address, "error"=>false));
            }
        } else {
            echo "An error occured";
        }
    }

    private function validateAccountEmail($email)
    {
        $user = UserInfo::getByEmail($email);

        if ($user) {
            return true;
        } else {
            return false;
        }
    }
    
    private function updateBilling($data)
    {
        //update the users billing address
        $customer = new StoreCustomer();

        if ($customer->isGuest()) {
            $customer->setEmail(trim($data['email']));
        }

        $customer->setValue("billing_first_name", trim($data['fName']));
        Session::set('billing_first_name', trim($data['fName']));
        $customer->setValue("billing_last_name", trim($data['lName']));
        Session::set('billing_last_name', trim($data['lName']));
        $customer->setValue("billing_company_name", trim($data['cName']));
        Session::set('billing_company_name', trim($data['cName']));
        $customer->setValue("billing_phone", trim($data['phone']));
        Session::set('billing_phone', trim($data['phone']));
        $address = array(
            "address1"=>trim($data['addr1']),
            "address2"=>trim($data['addr2']),
            "city"=>trim($data['city']),
            "state_province"=>trim($data['state']),
            "postal_code"=>trim($data['postal']),
            "country"=>trim($data['count']),
        );
        $customer->setValue("billing_address", $address);
        Session::set('billing_address', $address);
    }

    public function updateShipping($data)
    {
        //update the users shipping address
        $this->validateAddress($data);
        $customer = new StoreCustomer();
        $customer->setValue("shipping_first_name", trim($data['fName']));
        Session::set('shipping_first_name', trim($data['fName']));
        $customer->setValue("shipping_last_name", trim($data['lName']));
        Session::set('shipping_last_name', trim($data['lName']));
        $customer->setValue("shipping_company_name", trim($data['cName']));
        Session::set('shipping_company_name', trim($data['cName']));
        $address = array(
            "address1"=>trim($data['addr1']),
            "address2"=>trim($data['addr2']),
            "city"=>trim($data['city']),
            "state_province"=>trim($data['state']),
            "postal_code"=>trim($data['postal']),
            "country"=>trim($data['count']),
        );
        $customer->setValue("shipping_address", $address);
        Session::set('shipping_address', $address);
    }
    
    public function validateAddress($data, $billing=null)
    {
        $e = Core::make('helper/validation/error');
        $vals = Core::make('helper/validation/strings');
        $customer = new StoreCustomer();

        if ($billing) {
            if ($customer->isGuest()) {
                if (!$vals->email($data['email'])) {
                    $e->add(t('You must enter a valid email address'));
                }
            }
        }

        if (strlen($data['fName']) < 1) {
            $e->add(t('You must enter a first name'));
        }
        if (strlen($data['fName']) > 255) {
            $e->add(t('Please enter a first name under 255 characters'));
        }
        if (strlen($data['lName']) < 1) {
            $e->add(t('You must enter a Last Name'));
        }
        if (strlen($data['lName']) > 255) {
            $e->add(t('Please enter a last name under 255 characters'));
        }
        if (strlen($data['lName']) > 255) {
            $e->add(t('Please enter a company name under 255 characters'));
        }
        if (strlen($data['addr1']) < 3) {
            $e->add(t('You must enter an address'));
        }
        if (strlen($data['addr1']) > 255) {
            $e->add(t('Please enter a street name under 255 characters'));
        }
        if (strlen($data['count']) < 2) {
            $e->add(t('You must enter a Country'));
        }
        if (strlen($data['count']) > 30) {
            $e->add(t('You did not select a Country from the list'));
        }
        if (strlen($data['city']) < 2) {
            $e->add(t('You must enter a City'));
        }
        if (strlen($data['city']) > 30) {
            $e->add(t('You must enter a valid City'));
        }
        if (strlen($data['postal']) > 10) {
            $e->add(t('You must enter a valid Postal Code'));
        }
        if (strlen($data['postal']) < 2) {
            $e->add(t('You must enter a valid Postal Code'));
        }
        
        return $e;
    }

    public static function getCountryOptions($addressType='billing')
    {
        $allcountries = Core::make('helper/lists/countries')->getCountries();
        $countries =  $allcountries;
        $db = Database::connection();
        if ($addressType=='shipping') {
            $ak = UserAttributeKey::getByHandle('shipping_address');
        } else {
            $ak = UserAttributeKey::getByHandle('billing_address');
        }

        $row = $db->GetRow(
            'select akHasCustomCountries, akDefaultCountry from atAddressSettings where akID = ?',
            array($ak->getAttributeKeyID())
        );

        $defaultCountry = $row['akDefaultCountry'];
        if (!$defaultCountry) {
            $defaultCountry = "US"; // 'mericah
        }

        if ($row['akHasCustomCountries'] == 1) {
            $availableCountries = $db->GetCol(
                'select country from atAddressCustomCountries where akID = ?',
                array($ak->getAttributeKeyID())
            );
            unset($countries);
            $countries = array();
            foreach ($availableCountries as $countrycode) {
                $countries[$countrycode] = $allcountries[$countrycode];
            }
        }
        return array('countries'=>$countries,'defaultCountry'=>$defaultCountry);
    }

    public function getShippingMethods()
    {
        if (Filesystem::exists(DIR_BASE."/application/elements/checkout/shipping_methods.php")) {
            View::element("checkout/shipping_methods");
        } else {
            View::element("checkout/shipping_methods", null, "vivid_store");
        }
    }
}
