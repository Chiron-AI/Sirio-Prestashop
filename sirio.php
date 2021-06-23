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

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}


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
        $this->description = $this->l('Sirio is an advanced monitoring system ideal for E-Commerce, with plans that can be adapted according to the number of visitors to the site. Sirio receives a large amount of data in real-time and analyzes customers\' behavior within the website, tracing all the interactions to find any possible anomaly that it might encounter in the funnel.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->module_key = '1d1be07cf291473029caea0c12939961';
    }


    public function install()
    {
        if (_PS_VERSION_ < '1.7') {
            return parent::install()
                && $this->setDefaultValues() &&
                $this->registerHook('actionFrontControllerSetMedia') &&
                $this->registerHook('displayFooter') &&
                $this->registerHook('actionCartSave') &&
                $this->registerHook('actionSearch') &&
				$this->registerHook('actionProductListModifier');

        }
        else{
            return parent::install() &&
                $this->setDefaultValues() &&
                $this->registerHook('actionFrontControllerSetMedia') &&
                $this->registerHook('displayFooter') &&
                $this->registerHook('actionCartSave') &&
                $this->registerHook('actionProductSearchAfter');
        }
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->deleteConfigKeys();
    }

    /**
     * Set default values while installing the module
     */
    public function setDefaultValues()
    {
        Configuration::updateValue('SIRIO_MODULE_ENABLE', 1);
        return true;
    }

    /**
     * Delete module config keys
     */
    public function deleteConfigKeys()
    {
        $var = array(
            'SIRIO_MODULE_ENABLE'
        );

        foreach ($var as $key) {
            if (!Configuration::deleteByName($key)) {
                return false;
            }
        }

        return true;
    }

    private static function updateParam($param)
    {
        if (\Validate::isString(\Tools::getValue($param))) {
            Configuration::updateValue($param, \Tools::getValue($param));
        }
    }


    public function getContent()
    {
        /* Empty the Shop domain cache */
        if (method_exists('ShopUrl', 'resetMainDomainCache')) {
            ShopUrl::resetMainDomainCache();
        }

        $output = "";
        if (\Tools::isSubmit('btnSubmit')) {
            self::updateParam('SIRIO_MODULE_ENABLE');
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        if (\Validate::isString(\Tools::getValue(
            'SIRIO_MODULE_ENABLE'
        ))) {
            $this->smarty->assign(
                'SIRIO_MODULE_ENABLE',
                \Tools::getValue(
                    'SIRIO_MODULE_ENABLE',
                    Configuration::get('SIRIO_MODULE_ENABLE')
                )
            );

        } else {
            $this->smarty->assign(
                'SIRIO_MODULE_ENABLE',
                Configuration::get('SIRIO_MODULE_ENABLE')
            );
        }

        $this->context->controller->addJS('https://cdnjs.cloudflare.com/ajax/libs/riot/2.6.1/riot.min.js');
        $this->context->controller->addJS('https://cdnjs.cloudflare.com/ajax/libs/riot/2.6.1/riot+compiler.min.js');

        $this->smarty->assign('ps_version', _PS_VERSION_);

        $html = $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
        return $html .
            $this->display(__FILE__, 'views/templates/admin/prestui-0.6.0/ps-alert.tpl') .
            $this->display(__FILE__, 'views/templates/admin/prestui-0.6.0/ps-form.tpl') .
            $this->display(__FILE__, 'views/templates/admin/prestui-0.6.0/ps-panel.tpl') .
            $this->display(__FILE__, 'views/templates/admin/prestui-0.6.0/ps-table.tpl') .
            $this->display(__FILE__, 'views/templates/admin/prestui-0.6.0/ps-tabs.tpl') .
            $this->display(__FILE__, 'views/templates/admin/prestui-0.6.0/ps-tags.tpl');
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
        if (Configuration::get('SIRIO_MODULE_ENABLE') == 0) {
            return;
        }
        if (_PS_VERSION_ < '1.7') {
            $this->context->controller->addJS('https://api.sirio.chiron.ai/api/v1/profiling');
        }else{
            $this->context->controller->registerJavascript('remote-sirio', 'https://api.sirio.chiron.ai/api/v1/profiling', ['server' => 'remote', 'position' => 'top', 'priority' => 1]);
        }
    }

    ## used for PS 1.7 - CATEGORY / SEARCH
    public function hookActionProductSearchAfter($params) {
        if(Configuration::get('SIRIO_MODULE_ENABLE')==0){
            return;
        }
        return $this->populateProductListingJS($params);
    }
	
	## used for PS 1.6 - CATEGORY
	public function hookActionProductListModifier($params) {
    	if(Configuration::get('SIRIO_MODULE_ENABLE')==0){
			return;
		}
		return $this->populateProductListingJS($params);
		
	}

    ## used for PS 1.6 - SEARCH
    public function hookActionSearch($params) {
        if(Configuration::get('SIRIO_MODULE_ENABLE')==0){
            return;
        }
        return $this->populateProductListingJS($params);

    }


    /**
     * @return string
     * @throws Exception
     */
    public function hookDisplayFooter()
    {
        if(Configuration::get('SIRIO_MODULE_ENABLE')==0){
            return;
        }

        $this->script = $this->getHeaders();

        if($this->context->controller->php_self == 'index' ) {
            return $this->appendHomeJS();
        }
        else if($this->context->controller->php_self == 'product') {
            return $this->appendProductJS();
        }
        else if($this->context->controller->php_self == 'category' ) {
            return $this->appendProductListingJS();
        }
        else if ($this->context->controller->php_self == 'search') {
            return $this->appendProductListingJS();
        }
        else if ($this->context->controller->php_self == 'order') {
            return $this->appendCheckoutJS();
        }
        else if ($this->context->controller->php_self == 'order-confirmation') {
            return $this->appendCheckoutSuccessJS();
        }
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public function hookActionCartSave() {
        if(Configuration::get('SIRIO_MODULE_ENABLE')==0){
            return;
        }
        global $cookie;

        if(_PS_VERSION_ < '1.7'){
            if (!$this->context->cart) {
                return;
            }
            $cartExtra= $this->context->cart->getSummaryDetails();
            $shipping= $cartExtra['total_shipping'];
            $productsCart = $this->context->cart->getProducts(true);
            $objCart = new Cart($this->context->cart->id, (int)$cookie->id_lang);
            $coupon = array();
        }else{
            if (!$this->context->cart) {
                return;
            }
            $presenter = new \PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter($this->context);
            $presented_cart = $presenter->present($this->context->cart, $shouldSeparateGifts = true);
            $objCart = new Cart($this->context->cart->id, (int)$cookie->id_lang);
            $shipping = $objCart->getPackageShippingCost();
            $cart_rules = $this->context->cart->getCartRules();
            $productsCart = $presented_cart['products'];
        }
        $cart_rules = $this->context->cart->getCartRules();
        $coupon="";
        if(!empty($cart_rules)){
            foreach ($cart_rules as $cart_rule_item) {
                $couponObj = new CartRule($cart_rule_item['id_cart_rule']);
                $coupon[] = $couponObj->code;
            }
            $coupon = implode(",", $coupon);
        }

        $total = $objCart->getOrderTotal(true, Cart::BOTH);
        $total_discounts = $objCart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
        $discount = $total_discounts;
        $subtotal=0;
        $products = array();
        if (isset($productsCart) && !empty($productsCart)) {
            foreach ($productsCart as $item) {
                $product = array(
                    "sku" => $item['reference']?$item['reference']:$item['ean13'],
                    "price" => number_format(Product::getPriceStatic((int) $item['id_product'], true, null, 2, null, false, true),2),
                    "qty" => round($item['quantity']),
                    "name" => $item['name'],
                    "discount_amount" => number_format(Product::getPriceStatic((int) $item['id_product'], true, null, 2, null, true, true),2)
                );
                $products[]=$product;
                $subtotal+=$product["price"]*$product['qty'];
            }
        }
        $cart_full = '{"cart_total":'.$total.',"cart_subtotal":'.$subtotal.',"shipping":'.$shipping.',"coupon_code":"'.$coupon.'","discount_amount":'.$discount.',"cart_products":'.json_encode($products).'}';
        if(isset($_COOKIE['cart_sirio'])){
            setcookie('cart_sirio', "", 1);
        }
        setcookie('cart_sirio', base64_encode($cart_full), time() + (86400 * 30), "/");
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
        $product_selected = $current_product->reference?$current_product->reference:$current_product->ean13;
        $description = $current_product->description;
        if ($current_product->description_short != ''){
            $description = $current_product->description_short;
        }

        return '<script type="text/javascript">
                     //<![CDATA[
                     '.$this->script.'
                     sirioCustomObject.pageType = "product";
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     sirioCustomObject.productDetails = {
                        "sku:": "' . $product_selected . '",
                        "name":"' . array_pop($current_product->name) . '",
                        "image":"' . $image_url . '",
                        "description":"' . $this->cleanTextProduct($description) . '",
                        "price":"' . Product::getPriceStatic((int)$current_product->id, true, null, 2, null, false, false) . '",
                        "special_price":"' . Product::getPriceStatic((int)$current_product->id, true, null, 2, null, false, true) . '"
                        }
                     //]]>
                 </script>';
    }
	
    # DEPRECATED
    /*private function appendProductCategoryJS() {
        global $cookie;
        $locale = Language::getIsoById( (int)$cookie->id_lang );
        $id_category = (int) Tools::getValue('id_category');
        if (_PS_VERSION_ < '1.7') {
			$page = Tools::getValue('p') ? (int)Tools::getValue('p') : 1;
		}
		else{
			$page = Tools::getValue('page') ? (int)Tools::getValue('page') : 1;
		}
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

		if($page == $pages && $products_count % $limit > 0){
			$products_count_page = $products_count % $limit;
		}
		else{
			$products_count_page = $limit;
		}

        return '<script type="text/javascript">
                     //<![CDATA[
                     '.$this->script.'
                     sirioCustomObject.categoryDetails = {"name":"'.$current_category->name.'","image":"'.$this->context->link->getCategoryLink($current_category).'","description":"'.$this->cleanTextCategory($current_category->description).'"};
                     sirioCustomObject.pageType = "category";
                     sirioCustomObject.numProducts = '.$products_count_page.';
                     sirioCustomObject.pages = '.$pages.';
                     sirioCustomObject.currentPage = '.$page.';
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     //]]>
                 </script>';
    }*/

  	private function populateProductListingJS($params) {
		global $cookie;
    	if (_PS_VERSION_ < '1.7' && isset($params['nb_products'])) {
    		$products_count = $params['nb_products'];
		}
		else{
			$products_count = $params['total'];
			$cookie = $params['cookie'];
		}
       
    	
       if($products_count) {

            if (_PS_VERSION_ < '1.7') {
                $page = Tools::getValue('page') ? (int)Tools::getValue('page') : 1;
            }
            else{
                $page = Tools::getValue('p') ? (int)Tools::getValue('p') : 1;
            }

            $currency = new CurrencyCore($cookie->id_currency);
            $currency_code = $currency->iso_code;
            $locale = Language::getIsoById((int)$cookie->id_lang);

            $limit = (int)Tools::getValue('resultsPerPage');
            if ($limit <= 0) {
                $limit = Configuration::get('PS_PRODUCTS_PER_PAGE');
            }
            $pages = (int)($products_count / $limit);
            if ($products_count % $limit > 0) {
                $pages += 1;
            }
            if ($page == $pages && $products_count % $limit > 0) {
                $products_count_page = $products_count % $limit;
            } else {
                $products_count_page = $limit;
            }
	
		   if (isset($params["expr"])) {
			   $page_type_script = 'sirioCustomObject.pageType = "search";
                 sirioCustomObject.query = "' . $params["expr"] . '";';
		   }
		   else if(Tools::getValue('id_category')){
			   $id_category = (int) Tools::getValue('id_category');
			   $current_category = new Category(
				   $id_category,
				   (int)$cookie->id_lang
			   );
			   $page_type_script = 'sirioCustomObject.categoryDetails = {"name":"'.$current_category->name.'","image":"'.$this->context->link->getCategoryLink($current_category).'","description":"'.$this->cleanTextCategory($current_category->description).'"};
                     sirioCustomObject.pageType = "category";';
           }
			
            $snippet = '<script type="text/javascript">
                 //<![CDATA[
                 [[SCRIPT]]
                 '.$page_type_script.'
                 sirioCustomObject.numProducts = ' . $products_count_page . ';
                 sirioCustomObject.locale = "' . $locale . '";
                 sirioCustomObject.pages = ' . $pages . ';
                 sirioCustomObject.currentPage = ' . $page . ';
                 sirioCustomObject.currency = "' . $currency_code . '"
                 //]]>
             </script>';

           if (_PS_VERSION_ < '1.7') {
               $_SESSION['snippet'] = $snippet;
           }
           else{
               $this->context->cookie->snippet = $snippet;
           }
           

        }

	}

    private function appendProductListingJS() {

        if (_PS_VERSION_ < '1.7' && isset($_SESSION['snippet'])) {
            $snippet = $_SESSION['snippet'];
            unset($_SESSION['snippet']);
        }
        else if(_PS_VERSION_ >= '1.7' && isset($this->context->cookie->snippet)){
            $snippet = $this->context->cookie->snippet;
            unset($this->context->cookie->snippet);
        }
		if(isset($snippet)) {
            return str_replace("[[SCRIPT]]",$this->script, $snippet);
		}
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

        if(isset($_COOKIE['cart_sirio'])){
            unset($_COOKIE['cart_sirio']);
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
	
	
	private function cleanTextProduct($string){
		return  preg_replace('/\R/', '',
			str_replace("<br/>","",
				addslashes(
					str_replace("'\n''","",
						str_replace("'\r''","",
							str_replace("'\t''","",
								strip_tags(
									trim(array_pop($string)))))))));
	}
	private function cleanTextCategory($string){
		return  preg_replace('/\R/', '',
			str_replace("<br/>","",
				addslashes(
					str_replace("'\n''","",
						str_replace("'\r''","",
							str_replace("'\t''","",
								strip_tags(
									trim(($string)))))))));
	}

}
