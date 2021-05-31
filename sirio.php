<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Chiron, www.chiron.ai <support@chiron.ai>
 * @copyright Copyright (c) permanent, Chiron
 * @license   Addons PrestaShop license limitation
 * @see       /LICENSE
 *
 *  International Registered Trademark & Property of Chiron
 */

class Sirio extends Module
{
    /**
     * If false, then SirioContainer is in immutable state
     */
    const DISABLE_CACHE = true;

    /**
     * @var SirioContainer
     */
    private $moduleContainer;

    public function __construct()
    {
        $this->name = 'sirio';
        $this->tab = 'analytics_stats';
        $this->version = '0.0.1';
        $this->author = 'Chiron';

        parent::__construct();
        $this->displayName = $this->l('Sirio');
        $this->description = $this->l('Sirio is an advanced monitoring system ideal for E-Commerce.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->module_key = '1d1be07cf291473029caea0c12939961';
    }


    public function install()
    {
        return parent::install() &&
        $this->registerHook('actionFrontControllerSetMedia') &&
        $this->registerHook('header');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }


    /**
     * Gets container with loaded classes defined in src folder
     *
     * @return SirioContainer
     */
    public function getContainer()
    {
        return $this->moduleContainer;
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerJavascript('remote-sirio', 'https://api.sirio.chiron.ai/api/v1/profiling', ['server' => 'remote', 'position' => 'bottom', 'priority' => 20]);
		
		
		if($this->context->controller->php_self == 'index' ) {
			return $this->appendHomeJS();
		}
		else if($this->context->controller->php_self == 'product' ) {
			return $this->appendProductJS();
		}
		else if($this->context->controller->php_self == 'category' ) {
			return $this->appendProductCategoryJS();
		}
		else if ($this->context->controller->php_self == 'search') {
			return $this->appendProductSearchJS();
		}
		else if ($this->context->controller->php_self == 'order') {
			return $this->appendCheckoutJS();
		}
		else if ($this->context->controller->php_self == 'order-confirmation') {
			return $this->appendCheckoutSuccessJS();
		}
		
    }
	
	private function appendHomeJS() {
		global $cookie;
		$locale = Language::getgetIsoById( (int)$cookie->id_lang );
		$currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
		return
			'<script type="text/javascript">
                     //<![CDATA[
                     var sirioCustomObject = {};
                     sirioCustomObject.pageType = "home";
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
		
	}
	
	private function appendProductJS() {
		global $cookie;
		$locale = Language::getgetIsoById( (int)$cookie->id_lang );
		$current_product = Mage::registry('current_product');
		$currency = new CurrencyCore($cookie->id_currency);
		$currency_code = $currency->iso_code;
		return
			'<script type="text/javascript">
                     //<![CDATA[
                     var sirioCustomObject = {};
                     sirioCustomObject.productDetails = {"sku:"'.$current_product->getSku().'"name":"'.$current_product->getName().'","image":"'.$current_product->getImageUrl().'","description":"'.$current_product->getDescription().'","price":"'.$current_product->getPrice().'","special_price":"'.$current_product->getSpecialPrice().'"};
                     sirioCustomObject.pageType = "product";
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
		
	}
	
	private function appendProductCategoryJS() {
		global $cookie;
		$locale = Language::getgetIsoById( (int)$cookie->id_lang );
		/*$limit = $this->getLimit();
		$page = Mage::app()->getRequest()->getParam('p')?Mage::app()->getRequest()->getParam('p'):1;
		$current_category = Mage::registry('current_category');
		$products_count = $limit;
		$max_product_count = $current_category->getProductCount();*/
		$currency = new CurrencyCore($cookie->id_currency);
		$currency_code = $currency->iso_code;
		
		if($max_product_count % $limit > 0){
			$pages = (int)($max_product_count / $limit) + 1 ;
		}
		else{
			$pages = $max_product_count / $limit ;
		}
		if($page == $pages){
			$products_count = $max_product_count % $limit;
		}
		
		return
			'<script type="text/javascript">
                     //<![CDATA[
                     var sirioCustomObject = {};
                     sirioCustomObject.categoryDetails = {"name":"'.$current_category->getName().'","image":"'.$current_category->getImageUrl().'","description":"'.$current_category->getDescription().'"};
                     sirioCustomObject.pageType = "category";
                     sirioCustomObject.numProducts = '.$products_count.';
                     sirioCustomObject.pages = '.$pages.';
                     sirioCustomObject.currentPage = '.$page.';
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
	}
	
	private function appendProductSearchJS() {
		global $cookie;
		$locale = Language::getgetIsoById( (int)$cookie->id_lang );
		/*$limit = $this->getLimit();
		$page = Mage::app()->getRequest()->getParam('p')?Mage::app()->getRequest()->getParam('p'):1;
		$current_category = Mage::registry('current_category');
		$products_count = $limit;
		$max_product_count = Mage::app()->getLayout()->getBlock('search.result')->getResultCount();*/
		$currency = new CurrencyCore($cookie->id_currency);
		$currency_code = $currency->iso_code;
		if($max_product_count % $limit > 0){
			$pages = (int)($max_product_count / $limit) + 1 ;
		}
		else{
			$pages = $max_product_count / $limit ;
		}
		if($page == $pages){
			$products_count = $max_product_count % $limit;
		}
		return
			'<script type="text/javascript">
                     //<![CDATA[
                     var sirioCustomObject = {};
                     sirioCustomObject.pageType = "search";
                     sirioCustomObject.numProducts = '.$products_count.';
                     sirioCustomObject.pages = '.$pages.';
                     sirioCustomObject.currentPage = '.$page.';
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
	}
	
	
	private function appendCartJS() {
		global $cookie;
		$locale = Language::getgetIsoById( (int)$cookie->id_lang );
		$currency = new CurrencyCore($cookie->id_currency);
		$currency_code = $currency->iso_code;
		return
			'<script type="text/javascript">
                     //<![CDATA[
                     //var sirioCustomObject = {};
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
	}
	
	
	
	private function appendCheckoutJS() {
		global $cookie;
		$iso_code = Language::getgetIsoById( (int)$cookie->id_lang );
		$currency = new CurrencyCore($cookie->id_currency);
		$currency_code = $currency->iso_code;
		return
			'<script type="text/javascript">
                     //<![CDATA[
                     var sirioCustomObject = {};
                     sirioCustomObject.pageType = "checkout";
                     sirioCustomObject.locale = "'.$iso_code.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
	}
	
	private function appendCheckoutSuccessJS() {
		global $cookie;
		$locale = Language::getgetIsoById( (int)$cookie->id_lang );
		$currency = new CurrencyCore($cookie->id_currency);
		$currency_code = $currency->iso_code;
		
		if(isset($_COOKIE['cart_new'])){
			unset($_COOKIE['cart_new']);
		}
		
		return
			'<script type="text/javascript">
                     //<![CDATA[
                     var sirioCustomObject = {};
                     sirioCustomObject.pageType = "checkout_success";
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
		
		
		
	}
	
	public function cartTrack($observer) {
		/*$quote = Mage::getSingleton('checkout/session')->getQuote();
		$coupon = $quote->getCouponCode();
		$cart = $quote->getAllVisibleItems();
		$shipping = $quote->getShippingAddress()->getBaseShippingInclTax();
		$subtotal = $quote->getBaseSubtotal();
		$total = $quote->getBaseGrandTotal();
		$discount = $subtotal - $quote->getBaseSubtotalWithDiscount();*/
		
		/*
				quando questa funzione viene chiamata:
				metto in cart_new il carrello attuale
		*/
		$products = array();
		//echo json_encode($cart_product);
		foreach($cart as $item){
			
			$products[] = array(
				"sku"=>$item->getSku(),
				"price"=>round($item->getBaseRowTotalInclTax()/$item->getQty(),2),
				"qty"=>round($item->getQty()),
				"name"=>$item->getName(),
				"discount_amount"=>$item->getBaseDiscountAmount()
			);
		}
		$cart_full = '{"cart_total":'.$total.', "cart_subtotal":'.$subtotal.', "shipping":'.$shipping.', "coupon_code":'.$coupon.', "discount_amount":'.$discount.', "cart_products":'.json_encode($products).'}';
		
		if(isset($_COOKIE['cart_new'])){
			setcookie('cart_new', "", 1);
		}
		setcookie('cart_new', base64_encode($cart_full), time() + (86400 * 30), "/");
	}
	


    public function getContent()
    {
        /* Empty the Shop domain cache */
        if (method_exists('ShopUrl', 'resetMainDomainCache')) {
            ShopUrl::resetMainDomainCache();
        }
        return;
        //return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }
}
