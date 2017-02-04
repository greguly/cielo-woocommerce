<?php
use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Payment;

use Cielo\API30\Ecommerce\Request\CieloRequestException;

/**
 * WC Cielo Version.
 */
class WC_Cielo_Version {

    /**
     * Array Versions.
     */
    protected static $options;

    /**
     * Get json file with version and convert it to array().
     */
    protected static function getDataVersion() {

        //
        $versionlist   = file_get_contents( dirname( __FILE__ ) . '/../data/api-list/version.json');
        $versionlist = json_decode($versionlist, true);

        return $versionlist;

    }

    /**
     * Get default version.
     */
    public static function getDefaultVersion () {

        return WC_Cielo_Version::getDataVersion()['default'];

    }

    /**
     * Get full version list.
     */
    public static function getVersion( $field ) {

        foreach (WC_Cielo_Version::getDataVersion()['versions'] as $chave => $valor) {
            WC_Cielo_Version::$options[$chave] = __( $valor[$field], 'cielo-woocommerce') ;
        }

        return WC_Cielo_Version::$options;

    }

}
