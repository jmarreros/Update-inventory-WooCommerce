<?php
require __DIR__ . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;

// Conexión WooCommerce API destino
// ================================
$url_API_woo = 'https://tuempresa.site/';
$ck_API_woo = 'ck_5fde0679616fe2363980edf8a2061e815682e2ff';
$cs_API_woo = 'cs_992d905e88b2a119fc8e786d181e309d0685a4c0';

$woocommerce = new Client(
    $url_API_woo,
    $ck_API_woo,
    $cs_API_woo,
    ['version' => 'wc/v3']
);
// ================================


// Conexión API origen
// ===================
$url_API="http://localhost:3000/inventory/";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url_API);


$items_origin = curl_exec($ch);
curl_close($ch);

if ( ! $items_origin ) {
    exit('Error en API origen');
}
// ===================


// Recorremos datos devueltos del origen
$items_origin = json_decode($items_origin);

foreach ($items_origin as $item) {
    // Recuperarmos los valores de sku, cantidad y precio de la API de origen
    $sku = $item->sku;
    $quantity = $item->qty;
    $price = $item->regular_price;

    echo "Producto ".$sku." ... ";

    // Obtenemos el producto basado en el SKU
    $product = $woocommerce->get('products/?sku='.$sku);

    if ( $product ){

        // Obtenemos el id del producto
        $id_product = $product[0]->id;

        //Formamos los datos a actualizar
        $data_update = [
            'stock_quantity' => $quantity,
            'price' => $price,
        ];

        // Enviamos producto y datos
        $result = $woocommerce->put('products/'.$id_product, $data_update);

        if (! $result) {
            echo("❗Error al actualizar producto \n");
        } else {
            print("✔ Producto actualizado \n");
        }
    }

}

