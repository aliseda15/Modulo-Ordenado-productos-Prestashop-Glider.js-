<?php
/** /modules/pruebaordenado/controllers/front/MiFrontEjemplo.php
 * pruebaordenado - A Prestashop Module
 * Modulo creado para ordenar los productos en función del scoring en orden ascendente y descendente
 *
 * @author We Are Clickers <info@weareclickers.com>
 * @copyright 2021-2022 We Are Clickers
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @version 0.0.1
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
// You can now access this controller from /index.php?fc=module&module=pruebaordenado&controller=MiFrontEjemplo
class pruebaordenadoMiFrontEjemploModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        // Do your stuff here
    }

    public function initContent()
    {
        $this->context->smarty->assign(
            [
                'greetingsFront' => 'Hello Front from pruebaordenado !',
            ]
        );
        $this->setTemplate('my-front-template.tpl');
        // Don't forget to create /modules/pruebaordenado/views/templates/front/my-front-template.tpl
        parent::initContent();
    }
}
