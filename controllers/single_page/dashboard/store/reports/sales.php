<?php
namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store\Reports;

use \Concrete\Core\Page\Controller\DashboardPageController;
use Package;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderList as StoreOrderList;
use \Concrete\Package\VividStore\Src\VividStore\Report\SalesReport as StoreSalesReport;

class sales extends DashboardPageController
{
    public function view()
    {
        $sr = new StoreSalesReport();
        $this->set('sr', $sr);
        $pkg = Package::getByHandle('vivid_store');
        $this->requireAsset('chartist');
        $today = date('Y-m-d');
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $this->set('defaultFromDate', $thirtyDaysAgo);
        $this->set('defaultToDate', $today);
        
        $dateFrom = $this->post('dateFrom');
        $dateTo = $this->post('dateTo');
        if (!$dateFrom) {
            $dateFrom = $thirtyDaysAgo;
        }
        if (!$dateTo) {
            $dateTo = $today;
        }
        $this->set('dateFrom', $dateFrom);
        $this->set('dateTo', $dateTo);
        
        $ordersTotals = $sr::getTotalsByRange($dateFrom, $dateTo);
        $this->set('ordersTotals', $ordersTotals);
        
        $orders = new StoreOrderList();
        $orders->setFromDate($dateFrom);
        $orders->setToDate($dateTo);
        $orders->setItemsPerPage(10);

        $paginator = $orders->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('orders', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

        $js = \Concrete\Package\VividStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('css', 'vividStoreDashboard');
        $this->requireAsset('javascript', 'vividStoreFunctions');
    }
    //TODO
    public function export()
    {
        $from = $this->get('fromDate');
        $to = $this->get('toDate');
    }
}
