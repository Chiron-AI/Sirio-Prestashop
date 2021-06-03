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
	
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Adapter\Search\SearchProductSearchProvider;

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
    
    private $script;

    public function __construct()
    {
        $this->name = 'sirio';
        $this->tab = 'analytics_stats';
        $this->version = '0.1.0';
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
    
    protected function getHeaders(){
		$header_request = getallheaders();
		$header_response = headers_list();
		$header_response_status_code = http_response_code();
	
	
		$header_response_filtered = array();
	
	
	
		foreach ($header_response as $response) {
			$explode_pos = strpos($response,':');
			$key = substr($response, 0, $explode_pos);
			if($key !== 'Link'){
				$value = substr($response, $explode_pos);
				$header_response_filtered[] = array($key, $value);
			}
		}
	
		$headers = array(
			'request'=>array(
				'Accept-Encoding'=>$header_request['Accept-Encoding'],
				'Accept-Language'=>$header_request['Accept-Language'],
				'Cookie'=>$header_request['Cookie']
			),
			'response'=>array(
				$header_response_filtered,
				'status_code'=>$header_response_status_code
			)
		);
	
		$script = 'var sirioCustomObject = {};
	 				 sirioCustomObject.headers = '.json_encode($headers).';';
	
		return $script;
	}
	
	protected function getImage($object, $id_image)
	{
		$retriever = new ImageRetriever(
			$this->context->link
		);
		
		return $retriever->getImage($object, $id_image);
	}

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerJavascript('remote-sirio', 'https://api.sirio.chiron.ai/api/v1/profiling', ['server' => 'remote', 'position' => 'top', 'priority' => 1]);
	}
	
	/**
	 * @return string
	 * @throws Exception
	 */
	public function hookDisplayHeader()
	{
		$this->script = $this->getHeaders();
		
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
		$locale = Language::getIsoById( (int)$cookie->id_lang );
		$currency = new CurrencyCore($cookie->id_currency);
		$currency_code = $currency->iso_code;
		return '<script type="text/javascript">
                     //<![CDATA[
                     '.$this->script.'
                     sirioCustomObject.pageType = "home";
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
	}
	
	private function appendProductJS() {
		global $cookie;
		$locale = Language::getIsoById( (int)$cookie->id_lang );
		$id_product = (int) Tools::getValue('id_product');
		$current_product = new Product(
			$id_product,
			(int)$cookie->id_lang
		);
		$currency = new CurrencyCore($cookie->id_currency);
		$currency_code = $currency->iso_code;
		$images = Product::getCover($current_product->id);
		$image_url = $this->context->link->getImageLink(array_pop($current_product->link_rewrite), $images['id_image']);
		
		return '<script type="text/javascript">
                     //<![CDATA[
                     '.$this->script.'
                     sirioCustomObject.productDetails = {"sku:"'.$current_product->reference?$current_product->reference:$current_product->ean13.', "name":"'.array_pop($current_product->name).'","image":"'.$image_url.'","description":"'.addslashes(str_replace("\n","", str_replace("\r","", str_replace("\t","", array_pop($current_product->description))))).'","price":"'.Product::getPriceStatic((int) $current_product->id, true, null, 2, null, false, true).'","special_price":"'.Product::getPriceStatic((int) $current_product->id, true, null, 2, null, false, false).'"};
                     sirioCustomObject.pageType = "product";
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
		
	}
	
	private function appendProductCategoryJS() {
		global $cookie;
		$locale = Language::getIsoById( (int)$cookie->id_lang );
		$id_category = (int) Tools::getValue('id_category');
		$page = Tools::getValue('page')?(int) Tools::getValue('page'):1;
		$current_category = new Category(
			$id_category,
			(int)$cookie->id_lang
		);
		
		$max_product_count = $current_category->getProducts(1, 1, 10000, null, null, true);
		$limit = (int) Tools::getValue('resultsPerPage');
		if ($limit <= 0) {
			$limit = Configuration::get('PS_PRODUCTS_PER_PAGE');
		}
		$products_count = $limit;
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
		
		return '<script type="text/javascript">
                     //<![CDATA[
                     '.$this->script.'
                     sirioCustomObject.categoryDetails = {"name":"'.$current_category->name.'","image":"'.$this->getImage($current_category,$current_category->id_image)['medium']['url'].'","description":"'.addslashes(str_replace("\n","", str_replace("\r","", str_replace("\t","",$current_category->description)))).'"};
                     sirioCustomObject.pageType = "category";
                     sirioCustomObject.numProducts = '.$products_count.';
                     sirioCustomObject.pages = '.$pages.';
                     sirioCustomObject.currentPage = '.$page.';
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
	}
	
	protected function getDefaultProductSearchProvider()
	{
		return new SearchProductSearchProvider(
			$this->getTranslator()
		);
	}
	
	private function getProductSearchProviderFromModules($query)
	{
		$providers = Hook::exec(
			'productSearchProvider',
			array('query' => $query),
			null,
			true
		);
		
		if (!is_array($providers)) {
			$providers = array();
		}
		
		foreach ($providers as $provider) {
			if ($provider instanceof ProductSearchProviderInterface) {
				return $provider;
			}
		}
	}
	
	protected function getProductSearchContext()
	{
		return (new ProductSearchContext())
			->setIdShop($this->context->shop->id)
			->setIdLang($this->context->language->id)
			->setIdCurrency($this->context->currency->id)
			->setIdCustomer(
				$this->context->customer ?
					$this->context->customer->id :
					null
			);
	}
	
	private function appendProductSearchJS() {
		global $cookie;
		$locale = Language::getIsoById( (int)$cookie->id_lang );
		$page = Tools::getValue('page')?(int) Tools::getValue('page'):1;
		$limit = (int) Tools::getValue('resultsPerPage');
		if ($limit <= 0) {
			$limit = Configuration::get('PS_PRODUCTS_PER_PAGE');
		}
		$products_count = $limit;
		
		$currency = new CurrencyCore($cookie->id_currency);
		$currency_code = $currency->iso_code;
		$query = new ProductSearchQuery();
		$search_string = Tools::getValue('s');
		$query->setSortOrder(new SortOrder('product', 'position', 'desc'))
			->setSearchString($search_string);
		$provider = $this->getProductSearchProviderFromModules($query);
		
		// if no module wants to do the query, then the core feature is used
		if (null === $provider) {
			$provider = $this->getDefaultProductSearchProvider();
		}
		// the search provider will need a context (language, shop...) to do its job
		$context = $this->getProductSearchContext();
		$result = $provider->runQuery(
			$context,
			$query
		);
		$max_product_count = $result->getTotalProductsCount();
		if($max_product_count % $limit > 0){
			$pages = (int)($max_product_count / $limit) + 1 ;
		}
		else{
			$pages = $max_product_count / $limit ;
		}
		if($page == $pages){
			$products_count = $max_product_count % $limit;
		}
		return '<script type="text/javascript">
                     //<![CDATA[
                     '.$this->script.'
                     sirioCustomObject.pageType = "search";
                     sirioCustomObject.numProducts = '.$products_count.';
                     sirioCustomObject.pages = '.$pages.';
                     sirioCustomObject.currentPage = '.$page.';
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
	}
	
	private function appendCheckoutJS() {
		global $cookie;
		$iso_code = Language::getIsoById( (int)$cookie->id_lang );
		$currency = new CurrencyCore($cookie->id_currency);
		$currency_code = $currency->iso_code;
		return '<script type="text/javascript">
                     //<![CDATA[
                     '.$this->script.'
                     sirioCustomObject.pageType = "checkout";
                     sirioCustomObject.locale = "'.$iso_code.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
	}
	
	private function appendCheckoutSuccessJS() {
		global $cookie;
		$locale = Language::getIsoById( (int)$cookie->id_lang );
		$currency = new CurrencyCore($cookie->id_currency);
		$currency_code = $currency->iso_code;
		
		if(isset($_COOKIE['cart_new'])){
			unset($_COOKIE['cart_new']);
		}
		
		return '<script type="text/javascript">
                     //<![CDATA[
                     '.$this->script.'
                     sirioCustomObject.pageType = "checkout_success";
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
	}
	
	public function hookActionCartSave() {
		global $cookie;
		$presenter = new CartPresenter();
		$presented_cart = $presenter->present($this->context->cart, $shouldSeparateGifts = true);
		$objCart = new Cart($this->context->cart, (int)$cookie->id_lang);
		$total = $objCart->getOrderTotal(true, Cart::BOTH);
		$shipping = $objCart->getPackageShippingCost();
		$cart_rules = $this->context->cart->getCartRules();
		//TODO debug
		foreach ($cart_rules as $cart_rule_item) {
			$couponObj = new CartRule($cart_rule_item['id_cart_rule']);
			$coupon[] = $couponObj->code;
		}
		$coupon = implode(",", $coupon);
		$total_discounts = $this->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
		//TODO debug
		$discount = $total_discounts;
		
		/*
				quando questa funzione viene chiamata:
				metto in cart_new il carrello attuale
		*/
		$subtotal=0;
		$products = array();
		//echo json_encode($cart_product);
		if (isset($presented_cart['products']) && !empty($presented_cart['products'])) {
			foreach ($presented_cart['products'] as $item) {
				
				$product = array(
					"sku" => $item->product_sku,
					"price" => round($item->getBaseRowTotalInclTax() / $item->quantity, 2),
					"qty" => round($item->quantity),
					"name" => $item->product_name,
					"discount_amount" => $item->getBaseDiscountAmount()
				);
				$products[]=$product;
				$subtotal+=$product["price"];
			}
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
