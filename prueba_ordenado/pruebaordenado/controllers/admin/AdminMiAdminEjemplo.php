<?php
/** /modules/pruebaordenado/controllers/admin/AdminMiAdminEjemplo.php
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
// You can now access this controller from /your_admin_directory/index.php?controller=AdminMiAdminEjemplo
class AdminMiAdminEjemploController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        // Do your stuff here
    }

    public function renderList()
    {
        $html = '<h1>Hello Admin </h1>';

        $list = parent::renderList();

        // You can create your custom HTML with smarty or whatever then concatenate your list to it and serve it however you want !
        return $html . $list;
    }
}
