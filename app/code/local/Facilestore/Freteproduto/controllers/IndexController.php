<?php

class Facilestore_Freteproduto_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction(){
        $zipcode = $this->getRequest()->getParam('zipcode');
        $product_id = $this->getRequest()->getParam('product_id');

        $return = array();
        if(empty($zipcode) || !is_numeric($zipcode) || strlen($zipcode) != 8){
            $return["erro"] = $this->__("Fill in the zip");
        } elseif(empty($product_id)){
            $return["erro"] = $this->__("Product not exist");
        } else {
            $_product = Mage::getModel('catalog/product')->load($product_id);
            if($_product->getId() && $_product->isSaleable())
            {
                $quote = Mage::getModel('sales/quote');
                $quote->addProduct($_product);
                $quote->getShippingAddress()->setCountryId('BR');
                $quote->getShippingAddress()->setPostcode($zipcode);

                $quote->getShippingAddress()->collectTotals();
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->getShippingAddress()->collectShippingRates();
                $rates = $quote->getShippingAddress()->getShippingRatesCollection();


                foreach ($rates as $rate)
                {
                    $return["rates"][] = array(
                        "title" => $rate->getMethodTitle(),
                        "price" => Mage::helper('core')->currency($rate->getPrice())
                    );
                }
            } else {
                $return["erro"] = $this->__("Product not exist");
            }
        }

        $jsonData = json_encode($return);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
}
