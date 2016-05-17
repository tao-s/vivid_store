<?php
defined('C5_EXECUTE') or die("Access Denied.");
$dh = Core::make('helper/date');
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;
use \Concrete\Package\VividStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;

?>

<?php if ($controller->getTask() == 'order') {
    ?>
    
    <div class="ccm-dashboard-header-buttons">
        <form action="<?=URL::to('/dashboard/store/orders/details/slip')?>" method="post" target="_blank">
            <input type="hidden" name="oID" value="<?=$order->getOrderID()?>">
            <button class="btn btn-primary"><?php echo t("Print Order Slip")?></button>
        </form>
    </div>
    
    <h3><?=t("Customer Overview")?></h3>
    <hr>
    <div class="row">
        <div class="col-sm-12">
            <?php $orderemail = $order->getAttribute("email");

    if ($orderemail) {
        ?>
            <h4><?=t("Email")?></h4>
            <p><a href="mailto:<?=$order->getAttribute("email");
        ?>"><?=$order->getAttribute("email");
        ?></a></p>
            <?php 
    }
    ?>

            <?php
            $ui = UserInfo::getByID($order->getCustomerID());
    if ($ui) {
        ?>
            <h4><?=t("User")?></h4>
            <p><a href="<?= View::url('/dashboard/users/search/view/' . $ui->getUserID());
        ?>"><?= $ui->getUserName();
        ?></a></p>
            <?php 
    }
    ?>
        </div>

        <div class="col-sm-6">
            <h4><?=t("Billing Information")?></h4>
            <p>
                <?=$order->getAttribute("billing_first_name"). " " . $order->getAttribute("billing_last_name")?><br>
                <?php $billingaddress = $order->getAttributeValueObject(StoreOrderKey::getByHandle('billing_address'));
    if ($billingaddress) {
        echo $billingaddress->getValue('displaySanitized', 'display');
    }
    ?>
                <br /> <br /><?php echo t('Phone');
    ?>: <?=$order->getAttribute("billing_phone")?>
            </p>
        </div>
        <?php if ($order->isShippable()) {
    ?>
            <div class="col-sm-6">
                <?php if ($order->getAttribute("shipping_address")->address1) {
    ?>
                    <h4><?=t("Shipping Information")?></h4>
                    <p>
                        <?=$order->getAttribute("shipping_first_name"). " " . $order->getAttribute("shipping_last_name")?><br>
                        <?php $shippingaddres = $order->getAttributeValueObject(StoreOrderKey::getByHandle('shipping_address'));
    if ($shippingaddres) {
        echo $shippingaddres->getValue('displaySanitized', 'display');
    }
    ?>
                    </p>
                <?php 
}
    ?>
            </div>
        <?php 
}
    ?>
    </div>
    <h3><?=t("Order Info")?></h3>
    <hr>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><strong><?=t("Product Name")?></strong></th>
                <th><?=t("Product Options")?></th>
                <th><?=t("Price")?></th>
                <th><?=t("Quantity")?></th>
                <th><?=t("Subtotal")?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $items = $order->getOrderItems();

    if ($items) {
        foreach ($items as $item) {
            ?>
                <tr>
                    <td><?=$item->getProductName()?>
                    <?php if ($sku = $item->getSKU()) {
    echo '(' .  $sku . ')';
}
            ?>
                    </td>
                    <td>
                        <?php
                            $options = $item->getProductOptions();
            if ($options) {
                echo "<ul class='list-unstyled'>";
                foreach ($options as $option) {
                    echo "<li>";
                    echo "<strong>".$option['oioKey'].": </strong>";
                    echo $option['oioValue'];
                    echo "</li>";
                }
                echo "</ul>";
            }
            ?>
                    </td>
                    <td><?=Price::format($item->getPricePaid())?></td>
                    <td><?=$item->getQty()?></td>
                    <td><?=Price::format($item->getSubTotal())?></td>
                </tr>
              <?php

        }
    }
    ?>
        </tbody>
    </table>
    
    <?php $applieddiscounts = $order->getAppliedDiscounts();

    if (!empty($applieddiscounts)) {
        ?>
        <h3><?=t("Discounts Applied")?></h3>
        <hr />
        <table class="table table-striped">
            <thead>
            <tr>
                <th><strong><?=t("Name")?></strong></th>
                <th><?=t("Displayed")?></th>
                <th><?=t("Deducted From")?></th>
                <th><?=t("Amount")?></th>
                <th><?=t("Triggered")?></th>
            </tr>

            </thead>
            <tbody>
            <?php foreach ($applieddiscounts as $discount) {
    ?>
                <tr>
                    <td><?= h($discount['odName']);
    ?></td>
                    <td><?= h($discount['odDisplay']);
    ?></td>
                    <td><?= h($discount['odDeductFrom']);
    ?></td>
                    <td><?= ($discount['odValue'] > 0 ? $discount['odValue'] : $discount['odPercentage'] . '%');
    ?></td>
                    <td><?= ($discount['odCode'] ? t('by code'). ' ' .$discount['odCode']: t('Automatically'));
    ?></td>
                </tr>
            <?php 
}
        ?>

            </tbody>
        </table>
    
    <?php 
    }
    ?>
    
     <p>
        <strong><?=t("Subtotal")?>: </strong><?=Price::format($order->getSubTotal())?><br>
        <?php if ($order->isShippable()) {
    ?>
        <strong><?=t("Shipping")?>: </strong><?=Price::format($order->getShippingTotal())?><br>
        <?php 
}
    ?>
        <?php foreach ($order->getTaxes() as $tax) {
    ?>
            <strong><?=$tax['label']?>:</strong> <?=Price::format($tax['amount'] ? $tax['amount'] : $tax['amountIncluded'])?><br>
        <?php 
}
    ?>
        <strong><?=t("Grand Total")?>: </strong><?=Price::format($order->getTotal())?>
    </p>
    <p>
        <strong><?=t("Payment Method")?>: </strong><?=$order->getPaymentMethodName()?><br>
        <?php $transactionReference = $order->getTransactionReference();
    if ($transactionReference) {
        ?>
             <strong><?=t("Transaction Reference")?>: </strong><?=$transactionReference?><br>
        <?php 
    }
    ?>
        <?php if ($order->isShippable()) {
    ?>
        <br /><strong><?=t("Shipping Method")?>: </strong><?=$order->getShippingMethodName()?>
        <?php 
}
    ?>
    </p>

    <h3><?=t("Order Status History")?></h3>
    <hr>
    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?=t("Update Status")?></h4>
                </div>
                <div class="panel-body">

                    <form action="<?=View::url("/dashboard/store/orders/updatestatus", $order->getOrderID())?>" method="post">
                        <div class="form-group">
                            <?php echo $form->select("orderStatus", $orderStatuses, $order->getStatus());
    ?>
                        </div>
                        <input type="submit" class="btn btn-default" value="<?=t("Update")?>">
                    </form>

                </div>
            </div>
        </div>
        <div class="col-sm-8">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><strong><?=t("Status")?></strong></th>
                    <th><?=t("Date")?></th>
                    <th><?=t("User")?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $history = $order->getStatusHistory();
    if ($history) {
        foreach ($history as $status) {
            ?>
                        <tr>
                            <td><?=$status->getOrderStatusName()?></td>
                            <td><?=$status->getDate()?></td>
                            <td><?=$status->getUserName()?></td>
                        </tr>
                    <?php

        }
    }
    ?>
                </tbody>
            </table>
        </div>

    </div>

    <h3><?=t("Manage Order")?></h3>
    <hr>
    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?=t("Order Options")?></h4>
                </div>
                <div class="panel-body">
                    
                    <a id="btn-delete-order" href="<?=View::url("/dashboard/store/orders/remove", $order->getOrderID())?>" class="btn btn-danger"><?=t("Delete Order")?></a>
                    
                </div>
            </div>
        </div>
    </div>
    
    
<?php 
} else {
    ?>

    <div class="ccm-dashboard-header-buttons">
    </div>

<div class="ccm-dashboard-content-full">
    <form role="form" class="form-inline ccm-search-fields">
        <div class="ccm-search-fields-row">
            <?php if ($statuses) {
    ?>
                <ul id="group-filters" class="nav nav-pills">
                    <li><a href="<?php echo View::url('/dashboard/store/orders/')?>"><?=t('All Statuses')?></a></li>
                    <?php foreach ($statuses as $status) {
    ?>
                        <li><a href="<?php echo View::url('/dashboard/store/orders/', $status->getHandle())?>"><?=$status->getName();
    ?></a></li>
                    <?php 
}
    ?>
                </ul>
            <?php 
}
    ?>
        </div>


        <div class="ccm-search-fields-row ccm-search-fields-submit">
            <div class="form-group">
                <div class="ccm-search-main-lookup-field">
                    <i class="fa fa-search"></i>
                    <?php echo $form->search('keywords', $searchRequest['keywords'], array('placeholder' => t('Search Orders')))?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary pull-right"><?php echo t('Search')?></button>

        </div>

    </form>

    <table class="ccm-search-results-table">
        <thead>
            <th><a><?=t("Order %s", "#")?></a></th>
            <th><a><?=t("Customer Name")?></a></th>
            <th><a><?=t("Order Date")?></a></th>
            <th><a><?=t("Total")?></a></th>
            <th><a><?=t("Status")?></a></th>
            <th><a><?=t("View")?></a></th>
        </thead>
        <tbody>
            <?php
                foreach ($orderList as $order) {
                    ?>
                <tr>
                    <td><a href="<?=View::url('/dashboard/store/orders/order/', $order->getOrderID())?>"><?=$order->getOrderID()?></a></td>
                    <td><?=$order->getAttribute('billing_last_name').", ".$order->getAttribute('billing_first_name')?></td>
                    <td><?=$dh->formatDateTime($order->getOrderDate())?></td>
                <td><?=Price::format($order->getTotal())?></td>
                    <td><?=ucwords($order->getStatus())?></td>
                    <td><a class="btn btn-primary" href="<?=View::url('/dashboard/store/orders/order/', $order->getOrderID())?>"><?=t("View")?></a></td>
                </tr>
            <?php 
                }
    ?>
        </tbody>
    </table>
</div>

<?php if ($paginator->getTotalPages() > 1) {
    ?>
    <?= $pagination ?>
<?php 
}
    ?>

<?php 
} ?>

<style>
    @media (max-width: 992px) {
        div#ccm-dashboard-content div.ccm-dashboard-content-full {
            margin-left: -20px !important;
            margin-right: -20px !important;
        }
    }
</style>