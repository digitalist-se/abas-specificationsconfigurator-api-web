<?php

namespace App\Http\Controllers;

class IllustrationController extends Controller
{
    private function assetUrl($filename)
    {
        return asset('/images/'.$filename);
    }

    public function get()
    {
        return view('business-illustration',
            [
                'production'                   => $this->assetUrl('01-production.png'),
                'warehouse'                    => $this->assetUrl('02-warehouse.png'),
                'reception'                    => $this->assetUrl('03-reception.png'),
                'sale'                         => $this->assetUrl('04-sale.png'),
                'financialBillingDepartment'   => $this->assetUrl('05-financial-billing-department.png'),
                'purchase'                     => $this->assetUrl('06-purchase.png'),
                'productDevelopment'           => $this->assetUrl('07-product-development.png'),
                'projectManagement'            => $this->assetUrl('08-project-management.png'),
                'service'                      => $this->assetUrl('09-service.png'),
                'humanResources'               => $this->assetUrl('10-human-resources.png'),
                'management'                   => $this->assetUrl('11-management.png'),
            ]);
    }
}
