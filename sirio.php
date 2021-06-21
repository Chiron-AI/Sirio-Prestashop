<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}
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
/*
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Adapter\Search\SearchProductSearchProvider;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;
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
        return parent::install() &&
            $this->setDefaultValues() &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('actionCartSave') &&
            $this->registerHook('actionSearch');

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
    public function hookActionSearch($params) {
        if(Configuration::get('SIRIO_MODULE_ENABLE')==0){
            return;
        }
        return $this->appendProductSearchJS($params);

    }
    public function hookActionCartSave() {
        if(Configuration::get('SIRIO_MODULE_ENABLE')==0){
            return;
        }

        global $cookie;
        $presenter = new CartPresenter($this->context);
        $presented_cart = $presenter->present($this->context->cart, $shouldSeparateGifts = true);
        $objCart = new Cart($this->context->cart->id, (int)$cookie->id_lang);
        $total = $objCart->getOrderTotal(true, Cart::BOTH);
        $shipping = $objCart->getPackageShippingCost();
        $cart_rules = $this->context->cart->getCartRules();
        //TODO debug
        $coupon=array();
        foreach ($cart_rules as $cart_rule_item) {
            $couponObj = new CartRule($cart_rule_item['id_cart_rule']);
            $coupon[] = $couponObj->code;
        }
        $coupon = implode(",", $coupon);
        $total_discounts = $objCart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
        //TODO debug
        $discount = $total_discounts;

        /*
                quando questa funzione viene chiamata:
                metto in cart_new il carrello attuale
        */
        $subtotal=0;
        $products = array();
        if (isset($presented_cart['products']) && !empty($presented_cart['products'])) {
            foreach ($presented_cart['products'] as $item) {
                $product = array(
                    "sku" => $item['reference']?$item['reference']:$item['ean13'],
                    "price" => Product::getPriceStatic((int) $item['id_product'], true, null, 2, null, false, true),
                    "qty" => round($item['quantity']),
                    "name" => $item['name'],
                    "discount_amount" => Product::getPriceStatic((int) $item['id_product'], true, null, 2, null, true, true)
                );
                $products[]=$product;
                $subtotal+=$product["price"]*$product['qty'];
            }
        }
        $cart_full = '{"cart_total":'.$total.', "cart_subtotal":'.$subtotal.', "shipping":'.$shipping.', "coupon_code":"'.$coupon.'", "discount_amount":'.$discount.', "cart_products":'.json_encode($products).'}';
        if(isset($_COOKIE['cart_new'])){
            setcookie('cart_new', "", 1);
        }
        setcookie('cart_new', base64_encode($cart_full), time() + (86400 * 30), "/");
    }

    /**
     * @return string
     * @throws Exception
     */
    public function hookDisplayHeader()
    {
        if(Configuration::get('SIRIO_MODULE_ENABLE')==0){
            return;
        }

        $this->script = $this->getHeaders();
        print_r($this->context->controller->php_self);
        if($this->context->controller->php_self == 'index' ) {
            return $this->appendHomeJS();
        }
        else if($this->context->controller->php_self == 'product') {
            return $this->appendProductJS();
        }
        else if($this->context->controller->php_self == 'category' ) {
            return $this->appendProductCategoryJS();
        }/*
        else if ($this->context->controller->php_self == 'search') {
            return $this->appendProductSearchJS();
        }*/
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
        $product_selected = $current_product->reference?$current_product->reference:$current_product->ean13;
        $description='EMPTY';
        if($current_product->description[1] != ''){
            $description = $current_product->description;
        }elseif ($current_product->description_short != ''){
            $description = $current_product->description_short;
        }
        return '<script type="text/javascript">
                     //<![CDATA[
                     '.$this->script.'
                     sirioCustomObject.pageType = "product";
                     sirioCustomObject.locale = "'.$locale.'";
                     sirioCustomObject.currency = "'.$currency_code.'";
                     
                     sirioCustomObject.productDetails = {
                        "sku:""'.$product_selected.'",
                        "name":"'.array_pop($current_product->name).'",
                        "image":"'.$image_url.'",
                        "description":"'.addslashes(str_replace("'\n''","", str_replace("'\r''","", str_replace("'\t''","", array_pop($description))))).'",
                        "price":"'.Product::getPriceStatic((int) $current_product->id, true, null, 2, null, false, false).'",
                        "special_price":"'.Product::getPriceStatic((int) $current_product->id, true, null, 2, null, false, true).'"
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
        print_r($max_product_count);
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
                     sirioCustomObject.categoryDetails = {"name":"'.$current_category->name.'","image":"'.$this->context->link->getCategoryLink($current_category).'","description":"'.addslashes(str_replace("\n","", str_replace("\r","", str_replace("\t","",$current_category->description)))).'"};
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

    private function appendProductSearchJS($params) {
        $products_count = $params['total'];
        $cookie = $params['cookie'];
        $locale = Language::getIsoById((int)$cookie->id_lang); // prende due volte
        $page = Tools::getValue('page')?(int) Tools::getValue('page'):1;
        $limit = (int) Tools::getValue('resultsPerPage');
        if ($limit <= 0) {
            $limit = Configuration::get('PS_PRODUCTS_PER_PAGE');
        }
        $products_count = $limit;
        return '<script type="text/javascript">
                     //<![CDATA[
                     '.$this->script.'
                     sirioCustomObject.pageType = "search";
                     sirioCustomObject.numProducts = '.$products_count.';                
                     sirioCustomObject.locale = "'.$locale.'";                   
                     //]]>
                 </script>';
        //print_r($params);

        /*print_r($params["expr"]["printed"]["total"]);
        global $cookie;




        $currency_code = $currency->iso_code;
        $query = new ProductSearchQuery();
        print_r($query);
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
        //sirioCustomObject.pages = '.$pages.';
          sirioCustomObject.currentPage = '.$page.';
        sirioCustomObject.currency = "'.$currency_code.'";
        );
*/

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


}
