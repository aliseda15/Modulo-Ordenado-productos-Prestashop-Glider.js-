<?php
/** /modules/pruebaordenado/models/MiModeloEjemplo.php


 * pruebaordenado - A Prestashop Module
 *
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

class DatabaseOperationsModel extends ObjectModel
{
    public static function CrearBaseDatos1()
    {
        $sql = '
                DROP TABLE IF EXISTS relacion;
                CREATE TABLE relacion (
                    id_cliente int(5) PRIMARY KEY not null,
                    ordenScoring varchar(500)
                );
                INSERT INTO relacion(id_cliente) SELECT id_customer FROM " . _DB_PREFIX_ . "customer;
                UPDATE relacion SET ordenScoring = "21,15,16,12,8,4,1,10" WHERE id_cliente = 1;
                UPDATE relacion SET ordenScoring = "16,2,5,4,1,7,19,10" WHERE id_cliente = 2;
                UPDATE relacion SET ordenScoring = "20,7,16,18,1,5,8,11" WHERE id_cliente = 3;
                UPDATE relacion SET ordenScoring = "16,2,5,4,1,7,19,10" WHERE id_cliente = 4;'
        ;

        return Db::getInstance()->execute($sql);
    }

    public static function BorrarBaseDatos1()
    {
        $sql = 'DROP TABLE IF EXISTS relacion;';

        return Db::getInstance()->execute($sql);
    }
}
