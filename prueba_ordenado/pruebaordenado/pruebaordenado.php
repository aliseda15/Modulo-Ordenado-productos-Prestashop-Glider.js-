<?php
/** /modules/pruebaordenado/pruebaordenado.php


 * pruebaordenado - A Prestashop Module
 *
 * Modulo creado para ordenar los productos en función del scoring en orden ascendente y descendente
 *
 * @author We Are Clickers <info@weareclickers.com>
 * @copyright 2021-2022 We Are Clickers
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @version 0.0.1
 */
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/models/DatabaseOperations.php';
class pruebaordenado extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'pruebaordenado';
        $this->tab = 'front_office_features';
        $this->version = '0.0.1';
        $this->author = 'We Are Clickers';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Modulo para Ordenado');
        $this->description = $this->l('Modulo creado para ordenar los productos en función del scoring en orden ascendente y descendente');
        $this->confirmUninstall = $this->l('Estas seguro que deseas desinstalar el módulo?');
    }

    public function install()
    {
        return
            parent::install()
            && $this->installTab()
            && $this->registerHook('displayFooterCategory')
            && $this->registerHook('displayHome')
            && $this->registerHook('DisplayShoppingCartFooter')
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('displayHeader')
            && DatabaseOperationsModel::CrearBaseDatos1()
        ;
    }

    public function uninstall()
    {
        return
            parent::uninstall()
            && $this->uninstallTab()
            && $this->unregisterHook('displayFooterCategory')
            && $this->unregisterHook('displayHome')
            && $this->unregisterHook('displayFooterProduct')
            && $this->unregisterHook('displayHeader')
            && $this->unregisterHook('DisplayShoppingCartFooter')
            && DatabaseOperationsModel::BorrarBaseDatos1();
    }

    public function hookDisplayHeader()
    {
        if ($this->context->customer->isLogged()) {
            $customerId = $this->context->customer->id;
        } else {
            $customerId = 1;
        }

        if ($this->context->controller->php_self == 'product') {
            $actualPageProduct = Tools::getValue('id_product');
        } else {
            $actualPageProduct = '(negativo, en esta página no hay un id de producto concreto)';
        }

        $product_ids_arrays = [];
        $products = Context::getContext()->cart->getProducts();                 // Esto sirve para saber que ID de producto hay en el carrito ahora mismo
        foreach ($products as $product) {
            $product_ids_arrays[] = $product['id_product'];
        }
        // You can add your module stylesheets from here
        $this->context->controller->registerStylesheet('modulo-mycss', $this->_path . 'views/css/pruebaordenado.css', ['media' => 'all', 'priority' => 150]);
        // Don't forget to create /modules/pruebaordenado/views/css/pruebaordenado.css
        $idproduct = implode(', ', $product_ids_arrays);
        $this->context->smarty->assign([
            'id_cliente' => $customerId,
            'productos_carrito' => $idproduct,
            'id_producto_pagina_Actual' => $actualPageProduct,
        ]);

        return $this->display(__FILE__, 'header.tpl');
    }

    public function conseguirProductos()
    {
        if ($this->context->customer->isLogged()) {
            $customerId = $this->context->customer->id;										// Esto es para pillar la sesion logeada en la variable CustomerId, o sino default 1
            $sqlVer = Db::getInstance()->executeS('SELECT id_cliente FROM relacion WHERE id_cliente=' . $customerId . '');
            if ($sqlVer = 'empty') {
                $customerId = 1;
            } else {
                $customerId = $customerId;
            }
        } else {
            $customerId = 1;
        }

        $sql = 'SELECT ordenScoring FROM relacion WHERE id_cliente=' . $customerId;   // Aqui pillamos la id de la tabla nuestra con la de la sesion logeada

        $productos_raw = Db::getInstance()->executeS($sql);									// ejecutamos el sql

        $SepararComas = explode(',', $productos_raw[0]['ordenScoring']);					// Con esto lo que hacemos es pillar los valore del scoring que vienen separasdos por ","
        $productfs = [];															// declaramos el array que luego utilizaremos para almacenar la información
        foreach ($SepararComas as &$mostrado) {											// Aqui hacemos un for each para escoger de uno en uno los valores del scoring
            $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'product WHERE id_product=' . $mostrado;			// Sentencia que, igualando los IDs, pilla toda la info de la tabla de los productos
            $products = Db::getInstance()->executeS($sql);									// ejecutamos y guardamos en $products. Al usar executeS, me devuelve el resultado en un array
            $producto = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);  				// Esto nos valdria para pillar a modo de string los datos
            $productfs[] = $products;														// ¡importante! Esto ayuda a que los valores que vamos sacando de cada foreach, se guarde en
        }                                                                                  // esa variable/array declarada anteriormente. Se perdio mucho tiempo hasta entender esto.
        $products = $productfs;															    // asignamos la variable products como lo que hemos sacado.

        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever($this->context->link),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        $products_for_template = [];

        foreach ($products as $rawProduct) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct[0]),								// El assembler es tikismikis. Solo quiere arrays bidimensionales, nada de tridimiensionales
                $this->context->language													// Por lo que tenemos que especificarle que dentro del array [0], haga su movida.
            );
        }
        return $products_for_template;
    }

    public function HookdisplayFooterProduct()
    {
        if ($this->context->customer->isLogged()) {
            $customerId = $this->context->customer->id;
        } else {
            $customerId = 1;
        }
        $products = $this->conseguirProductos();

        $this->context->smarty->assign([
         'id_cliente' => $customerId,
         'products' => $products,
        ]);

        return $this->display(__FILE__, 'footerProduct.tpl');
    }

    public function HookDisplayShoppingCartFooter()
    {
        if ($this->context->customer->isLogged()) {
            $customerId = $this->context->customer->id;
        } else {
            $customerId = 1;
        }
        $products = $this->conseguirProductos();

        $this->context->smarty->assign([
         'id_cliente' => $customerId,
         'products' => $products,
        ]);

        return $this->display(__FILE__, 'footerProductCarrito.tpl');
    }

    public function HookDisplayProductsReviews()
    {
        if ($this->context->customer->isLogged()) {
            $customerId = $this->context->customer->id;
        } else {
            $customerId = 1;
        }
        $products = $this->conseguirProductos();

        $this->context->smarty->assign([
         'id_cliente' => $customerId,
         'products' => $products,
        ]);

        return $this->display(__FILE__, 'footerProduct.tpl');
    }

    public function HookDisplayHome()
    {
        if ($this->context->customer->isLogged()) {
            $customerId = $this->context->customer->id;
        } else {
            $customerId = 1;
        }
        $products = $this->conseguirProductos();

        $this->context->smarty->assign([
         'id_cliente' => $customerId,
         'products' => $products,
        ]);

        return $this->display(__FILE__, 'footerProduct.tpl');
    }

    public function HookdisplayFooterCategory()
    {
        if ($this->context->customer->isLogged()) {
            $customerId = $this->context->customer->id;
        } else {
            $customerId = 1;
        }
        $products = $this->conseguirProductos();

        $this->context->smarty->assign([
         'id_cliente' => $customerId,
         'products' => $products,
        ]);

        return $this->display(__FILE__, 'footerProductCategorias.tpl');
    }

    /** Example of an action hook, it's triggered when a customer signup successfully */
    public function hookActionCustomerAccountAdd($params)
    {
        // You can now retrieve the customer submitted informations with $params['_POST']
        // Or even get the new customer id with $params['newCustomer']->id
    }

    /** Module configuration page */
    public function getContent()
    {
        return 'No es necesaria una página de configuración para este modulo';
    }
    /** Install module tab, to your admin controller */
    private function installTab()
    {
        $languages = Language::getLanguages();

        $tab = new Tab();
        $tab->class_name = 'AdminMiAdminEjemplo';
        $tab->module = $this->name;
        $tab->id_parent = Context::getContext()->language->id;

        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = 'pruebaordenado';
        }

        try {
            $tab->save();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /** Uninstall module tab */
    private function uninstallTab()
    {
        $tab = Context::getContext()->language->id;
        if ($tab) {
            $mainTab = new Tab($tab);

            try {
                $mainTab->delete();
            } catch (Exception $e) {
                echo $e->getMessage();

                return false;
            }
        }

        return true;
    }
    public function renderWidget($hookName = null, array $configuration = [])
    {
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $products = $this->conseguirProductos();

        if (!empty($products)) {
            return [
                'products' => $products,
            ];
        }

        return false;
    }
}
