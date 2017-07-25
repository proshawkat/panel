<?php
/*
 * Project: Double-P Framework
 * Copyright: 2011-2012, Moin Uddin (pay2moin@gmail.com)
 * Version: 1.0
 * Author: Moin Uddin
 */
function heading()
{
    module_include("header");
}

function footing()
{
    $base=BASE;
    module_include("footer");
}

function set_flash_message($message, $flag)
{
    $_SESSION['flash']['message']=$message;
    $_SESSION['flash']['type']=$flag;
}

function get_flash_message()
{
    if(isset($_SESSION['flash']))
    {
        $message=array('message'=>$_SESSION['flash']['message'], 'type'=>$_SESSION['flash']['type']);
        unset($_SESSION['flash']);
        return $message;
    }
    else return 0;
}

function logged_in()
{
    if( isset( $_SESSION['shop'] ) ) {
		
		return true;
		
	} else return false;
}

//following function returns the id of current user
function current_user_info($parameter)
{
    if(isset($_SESSION['auth_user'][$parameter])) return $_SESSION['auth_user'][$parameter];
    else return false;
}

function db_connect()
{
	$link=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die('<h1>Could not connect to database</h1>');
	mysql_select_db(DB_NAME,$link) or die('<h1>Could not connect to database</h1>');
	return $link;
}

function module_include($module)
{
    global $option, $mysqli;
	if(file_exists("modules/".$module."/".$module.".php")) include("modules/".$module."/".$module.".php");
}

function form_processor()
{
	if(isset($_REQUEST['process']))
	{
		$func="process_".$_REQUEST['process'];
		$func();
		die();
	}
}

//following function creates a pagination
function paginate($total, $current_page, $total_every_page, $url)
{

    $total_pages=$total/$total_every_page;
    if($total_page>round($total_page)) $total_pages=round($total_pages)+1;

    if($current_page>1) echo "<a href='".$url."/page/".($current_page-1)."'><input type='submit' value='<<<Previous'></a>";
    if($current_page<($total_pages)) echo "<a href='".$url."/page/".($current_page+1)."'><input type='submit' value='Next>>>'></a>";
}

function upload_an_image($max_size, $prefix, $valid_exts) {        
    
    $path = FILEUPLOAD; // upload directory
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if( ! empty($_FILES['image']) ) {
            // get uploaded file extension
            $ext = strtolower(pathinfo($_FILES['image']['name'][0], PATHINFO_EXTENSION));
            // looking for format and size validity
            if (in_array($ext, $valid_exts) AND $_FILES['image']['size'][0] < $max_size*50) {
                $path = $path . uniqid(). $prefix.rand(0,100).'.' .$ext;
                
                
                // move uploaded file from temp to uploads directory
                if (move_uploaded_file($_FILES['image']['tmp_name'][0], $path)) {   
                    return $path;
                } //else echo $_FILES['image']['tmp_name'][0];
            } else {
                //echo 'Invalid file!';
            }
        } else {
            //echo 'File not uploaded!';
        }
    } else {
        //echo 'Bad request!';
    }
}

function add_shop_meta( $shop_id, $meta_name, $value ) {
	
	global $mysqli;
	$res = $mysqli->query("SELECT meta_id FROM shops_meta WHERE meta_name='$meta_name' AND shop_id='$shop_id'");
	if( $res->num_rows > 0 ) {
		
		$arr = $res->fetch_array( MYSQLI_ASSOC );
		$mysqli->query("UPDATE shops_meta SET meta_value='" . $mysqli->real_escape_string( $value ) . "' WHERE meta_id='" . $arr['meta_id'] . "'");
	} else $mysqli->query("INSERT INTO shops_meta (shop_id, meta_name, meta_value) VALUES ('" . $shop_id . "', '" . $mysqli->real_escape_string( $meta_name ) . "', '" . $mysqli->real_escape_string( $value ) . "')");
	
	return true;
}

function delete_shop_meta( $shop_id, $meta_name ) {
	
	global $mysqli;
	$res = $mysqli->query("DELETE FROM shops_meta WHERE shop_id='" . $_SESSION['shop_id'] . "' AND meta_name='" . $mysqli->real_escape_string( $meta_name ) . "'");
	return true;
}

function get_shop_meta( $shop_id, $meta_name ) {
	
	global $mysqli;
	$res = $mysqli->query("SELECT meta_value FROM shops_meta WHERE meta_name='$meta_name' AND shop_id='$shop_id'");
	if( $res->num_rows > 0 ) {
		
		$arr = $res->fetch_array( MYSQLI_ASSOC );
		return $arr['meta_value'];
	} else return false;
}

function google_pagespeed_insight( $url ) {

    $url = 'https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url=' . urlencode( $url );
    $useragent="Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:24.0) Gecko/20100101 Firefox/24.0";
    $ch = curl_init ();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_USERAGENT, $useragent); // set user agent
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    $output = curl_exec ($ch);
    curl_close($ch);
    $output = json_decode( $output );
    return $output->ruleGroups->SPEED->score;
}

function diff_with_current_time( $time ) {

    $current_time = date("Y-m-d H:i:s");
    return $diff = ( strtotime( $current_time ) - strtotime( $time ) );
}

function shopify_reload_products() {
    $session_id = $_REQUEST['mysession'];
    require_once 'includes/shopify.php';
    $sc = new ShopifyClient($_SESSION[ $session_id ]['shop'], $_SESSION[ $session_id ]['token'], SHOPIFY_API_KEY, SHOPIFY_SECRET);
    //load the list of products and save in session variable
    $all_loaded = false;
    $page = 1;
    while( !$all_loaded ) {
        $limit = 250;
        $products = $sc->call('GET', '/admin/products.json?fields=id,title,handle,product_type,', array('published_status'=>'published', 'limit'=>$limit, 'page'=>$page));
        
        foreach( $products as $product ) {
            $product_list[] = $product;
        }
        
        if( count( $products ) < $limit ) $all_loaded = true;
        $page++;
    }
    $_SESSION[$session_id]['products'] = $product_list;
}

function shopify_reload_stats( $shop_id ) {
    global $mysqli;
    $session_id = $_REQUEST['mysession'];
    require_once 'includes/shopify.php';

    $res = $mysqli->query("SELECT shop, token FROM shops WHERE id='$shop_id'");
    if( $res->num_rows > 0 ) {
        $arr = $res->fetch_array( MYSQLI_ASSOC );
        $sc = new ShopifyClient($arr['shop'], $arr['token'], SHOPIFY_API_KEY, SHOPIFY_SECRET);
        //load list of order and save for stats
        $order_last_sync = get_shop_meta( $shop_id, 'order_last_sync' );
        
        $all_loaded = false;
        $total_orders = get_shop_meta( $shop_id, 'total_orders' );
        $total_orders_revenue = get_shop_meta( $shop_id, 'total_orders_revenue' );
        $page = 1;
        while( !$all_loaded ) {
            $limit = 250;
            $params = array('financial_status'=>'authorized', 'limit'=>$limit, 'page'=>$page);
            if( $order_last_sync != '' ) $params['created_at_min'] = date("Y-m-dTH:i:s-00:00", strtotime( $order_last_sync ) );
            $orders = $sc->call('GET', '/admin/orders.json?fields=created_at,line_items,', $params);
            
            foreach( $orders as $order ) {
                $total_orders++;
                if( !isset( $first_order_created_at ) ) $first_order_created_at = date("Y-m-d H:i:s", strtotime( $order['created_at'] ) );
                foreach( $order['line_items'] as $line_item ) {
                    $total_orders_revenue += ( $line_item['quantity'] * $line_item['price'] );
                    if( !isset( $order_products[ $line_item['product_id'] ] ) ) $order_products[ $line_item['product_id'] ] = array( 'orders' => $line_item['quantity'], 'abandons' => 0 );
                    else $order_products[ $line_item['product_id'] ]['orders'] += $line_item['quantity'];
                }
            }

            if( count( $orders ) < $limit ) $all_loaded = true;
            $page++;
        }

        //load list of abandoned checkouts and save for stats
        $total_abandoned_checkouts = get_shop_meta( $shop_id, 'total_abandoned_checkouts' );
        $total_abandoned_revenue = get_shop_meta( $shop_id, 'total_abandoned_revenue' );
        $all_loaded = false;
        $page = 1;
        while( !$all_loaded ) {
            $limit = 250;
            $params = array('limit'=>$limit, 'page'=>$page);
            if( $order_last_sync != '' ) $params['created_at_min'] = date("Y-m-dTH:i:s-00:00", strtotime( $order_last_sync ) );
            $abandons = $sc->call('GET', '/admin/checkouts.json', $params);
            foreach( $abandons as $order ) {
                $total_abandoned_checkouts++;
                foreach( $order['line_items'] as $line_item ) {
                    $total_abandoned_revenue += ( $line_item['quantity'] * $line_item['price'] );
                    if( !isset( $order_products[ $line_item['product_id'] ] ) ) $order_products[ $line_item['product_id'] ] = array( 'orders' => 0, 'abandons' => $line_item['quantity'] );
                    else $order_products[ $line_item['product_id'] ]['abandons'] += $line_item['quantity'];

                }
            }
            if( count( $abandons ) < $limit ) $all_loaded = true;
            $page++;
        }
        
        foreach( array_keys( $order_products ) as $product_id ) {
            $res = $mysqli->query("SELECT id, sold_total, abandoned_total FROM shop_products WHERE shop_id='$shop_id' AND product_id='$product_id'");
            if( $res->num_rows > 0 ) {
                $arr = $res->fetch_array( MYSQLI_ASSOC );
                $mysqli->query("UPDATE shop_products SET sold_total='" . ( $arr['sold_total'] + $order_products[ $product_id ]['orders'] ) . "', abandoned_total='" . ( $arr['abandoned_total'] + $order_products[ $product_id ]['abandons'] ) . "' WHERE shop_id='$shop_id' AND product_id='$product_id'");
            } else {
                $mysqli->query("INSERT INTO shop_products ( shop_id, product_id, sold_init, abandoned_init, sold_total, abandoned_total ) VALUES ( '$shop_id', '$product_id', '" . $order_products[ $product_id ]['orders'] . "', '" . $order_products[ $product_id ]['abandons'] . "', '" . $order_products[ $product_id ]['orders'] . "', '" . $order_products[ $product_id ]['abandons'] . "')");
            }
        }

        //get last one month and one week sales
        $all_loaded = false;
        $month_products_sold = 0;
        $month_revenue = 0;
        $week_products_sold = 0;
        $week_revenue = 0;
        $last_month = mktime( 0, 0, 0, ( date('n') - 1 ), date('j'), date('Y') );
        $last_week = mktime( 0, 0, 0, date('n'), ( date('j') - 7 ), date('Y') );
        $page = 1;
        while( !$all_loaded ) {
            $limit = 250;
            $params = array('financial_status'=>'authorized', 'limit'=>$limit, 'page'=>$page);
            $params['created_at_min'] = date("Y-m-dTH:i:s-00:00", $last_month );
            $orders = $sc->call('GET', '/admin/orders.json?fields=created_at,line_items,', $params);
            
            foreach( $orders as $order ) {
                foreach( $order['line_items'] as $line_item ) {
                    $month_products_sold += $line_item['quantity'];
                    $month_revenue += ( $line_item['quantity'] * $line_item['price'] );

                    if( strtotime( $order['created_at'] ) >= $last_week ) {
                        $week_products_sold += $line_item['quantity'];
                        $week_revenue += ( $line_item['quantity'] * $line_item['price'] );
                    }
                }
            }

            if( count( $orders ) < $limit ) $all_loaded = true;
            $page++;
        }


        //some statistics global variables
        add_shop_meta( $shop_id, 'month_products_sold', $month_products_sold );
        add_shop_meta( $shop_id, 'month_revenue', $month_revenue );
        add_shop_meta( $shop_id, 'week_products_sold', $week_products_sold );
        add_shop_meta( $shop_id, 'week_revenue', $week_revenue );

        if( get_shop_meta( $shop_id, 'total_orders_init' ) == '' ) add_shop_meta( $shop_id, 'total_orders_init', $total_orders );
        add_shop_meta( $shop_id, 'total_orders', $total_orders );
        if( get_shop_meta( $shop_id, 'total_revenue_init' ) == '' ) add_shop_meta( $shop_id, 'total_revenue_init', $total_orders_revenue );
        add_shop_meta( $shop_id, 'total_revenue', $total_orders_revenue );
        if( get_shop_meta( $shop_id, 'abandoned_checkouts_init' ) == '' ) add_shop_meta( $shop_id, 'abandoned_checkouts_init', $total_abandoned_checkouts );
        add_shop_meta( $shop_id, 'total_abandoned_checkouts', $total_abandoned_checkouts );
        if( get_shop_meta( $shop_id, 'total_abandoned_revenue_init' ) == '' ) add_shop_meta( $shop_id, 'total_abandoned_revenue_init', $total_abandoned_revenue );
        add_shop_meta( $shop_id, 'total_abandoned_revenue', $total_abandoned_revenue );
        if( get_shop_meta( $shop_id, 'first_order_created_at' ) == '' ) add_shop_meta( $shop_id, 'first_order_created_at', $first_order_created_at );
        if( get_shop_meta( $shop_id, 'app_installed_date' ) == '' ) add_shop_meta( $shop_id, 'app_installed_date', date("Y-m-d H:i:s") );
        add_shop_meta( $shop_id, 'order_last_sync', date("Y-m-d H:i:s") );
        $_SESSION[ $session_id ]['order_last_sync'] = date("Y-m-d H:i:s");
    }
}

function shopify_ninety_days_stats( $shop_id ) {
    global $mysqli;
    $session_id = $_REQUEST['mysession'];
    require_once 'includes/shopify.php';

    $app_installed_date = get_shop_meta( $shop_id, 'app_installed_date' );
    $today = date("Y-m-d H:i:s");
    $days_between_today = days_between( $app_installed_date, $today );

    $created_at_min_days = ( $days_between_today < 90 ? $days_between_today : 90 ); 

    $created_at_min = date( "Y-m-dTH:i:s-00:00", mktime( 0, 0, 0, date('n'), ( date('j') - $created_at_min_days ), date('Y') ) );

    $stats['seven'] = array( 'total' => 0, 'revenue' => 0, 'item_count' => 0, 'abandons' => 0, 'abandoned_revenue' => 0 );
    $stats['thirty'] = array( 'total' => 0, 'revenue' => 0, 'item_count' => 0, 'abandons' => 0, 'abandoned_revenue' => 0 );
    $stats['ninety'] = array( 'total' => 0, 'revenue' => 0, 'item_count' => 0, 'abandons' => 0, 'abandoned_revenue' => 0 );

    $res = $mysqli->query("SELECT shop, token FROM shops WHERE id='$shop_id'");
    if( $res->num_rows > 0 ) {
        $arr = $res->fetch_array( MYSQLI_ASSOC );
        $sc = new ShopifyClient($arr['shop'], $arr['token'], SHOPIFY_API_KEY, SHOPIFY_SECRET);
        
        $all_loaded = false;
        $page = 1;
        while( !$all_loaded ) {
            $limit = 250;
            $params = array('financial_status'=>'authorized', 'limit'=>$limit, 'page'=>$page, 'created_at_min' => $created_at_min);
            $orders = $sc->call('GET', '/admin/orders.json?fields=created_at,line_items,', $params);
            
            foreach( $orders as $order ) {
                $order_revenue = 0;
                $order_products = 0;
                foreach( $order['line_items'] as $line_item ) {
                    $order_revenue += ( $line_item['quantity'] * $line_item['price'] );
                    $order_products += $line_item['quantity'];
                }

                //if within 7 days
                if( days_between( $order['created_at'], $today ) <= 7 ) {
                    $stats['seven']['revenue'] += $order_revenue;
                    $stats['seven']['item_count'] += $order_products;
                    $stats['seven']['total'] += 1;
                }

                //if within 30 days
                if( days_between( $order['created_at'], $today ) <= 30 ) {
                    $stats['thirty']['revenue'] += $order_revenue;
                    $stats['thirty']['item_count'] += $order_products;
                    $stats['thirty']['total'] += 1;
                }

                //if within 90 days
                if( days_between( $order['created_at'], $today ) <= 90 ) {
                    $stats['ninety']['revenue'] += $order_revenue;
                    $stats['ninety']['item_count'] += $order_products;
                    $stats['ninety']['total'] += 1;
                }
            }

            if( count( $orders ) < $limit ) $all_loaded = true;
            $page++;
        }

        //load list of abandoned checkouts and save for stats
        $all_loaded = false;
        $page = 1;
        while( !$all_loaded ) {
            $limit = 250;
            $params = array('limit'=>$limit, 'page'=>$page, 'created_at_min' => $created_at_min);
            $abandons = $sc->call('GET', '/admin/checkouts.json', $params);
            foreach( $abandons as $order ) {
                $order_abandoned_revenue = 0;
                foreach( $order['line_items'] as $line_item ) {
                    $order_abandoned_revenue += ( $line_item['quantity'] * $line_item['price'] );
                }

                //if within 7 days
                if( days_between( $order['created_at'], $today ) <= 7 ) {
                    $stats['seven']['abandoned_revenue'] += $order_abandoned_revenue;
                    $stats['seven']['abandons'] += 1;
                }

                //if within 30 days
                if( days_between( $order['created_at'], $today ) <= 30 ) {
                    $stats['thirty']['abandoned_revenue'] += $order_abandoned_revenue;
                    $stats['thirty']['abandons'] += 1;
                }

                //if within 90 days
                if( days_between( $order['created_at'], $today ) <= 90 ) {
                    $stats['ninety']['abandoned_revenue'] += $order_abandoned_revenue;
                    $stats['ninety']['abandons'] += 1;
                }
            }
            if( count( $abandons ) < $limit ) $all_loaded = true;
            $page++;
        }
    }

    return $stats;
}

function country_list(){
    $countries = array
                (
                'AF' => 'Afghanistan',
                'AX' => 'Aland Islands',
                'AL' => 'Albania',
                'DZ' => 'Algeria',
                'AS' => 'American Samoa',
                'AD' => 'Andorra',
                'AO' => 'Angola',
                'AI' => 'Anguilla',
                'AQ' => 'Antarctica',
                'AG' => 'Antigua And Barbuda',
                'AR' => 'Argentina',
                'AM' => 'Armenia',
                'AW' => 'Aruba',
                'AU' => 'Australia',
                'AT' => 'Austria',
                'AZ' => 'Azerbaijan',
                'BS' => 'Bahamas',
                'BH' => 'Bahrain',
                'BD' => 'Bangladesh',
                'BB' => 'Barbados',
                'BY' => 'Belarus',
                'BE' => 'Belgium',
                'BZ' => 'Belize',
                'BJ' => 'Benin',
                'BM' => 'Bermuda',
                'BT' => 'Bhutan',
                'BO' => 'Bolivia',
                'BA' => 'Bosnia And Herzegovina',
                'BW' => 'Botswana',
                'BV' => 'Bouvet Island',
                'BR' => 'Brazil',
                'IO' => 'British Indian Ocean Territory',
                'BN' => 'Brunei Darussalam',
                'BG' => 'Bulgaria',
                'BF' => 'Burkina Faso',
                'BI' => 'Burundi',
                'KH' => 'Cambodia',
                'CM' => 'Cameroon',
                'CA' => 'Canada',
                'CV' => 'Cape Verde',
                'KY' => 'Cayman Islands',
                'CF' => 'Central African Republic',
                'TD' => 'Chad',
                'CL' => 'Chile',
                'CN' => 'China',
                'CX' => 'Christmas Island',
                'CC' => 'Cocos (Keeling) Islands',
                'CO' => 'Colombia',
                'KM' => 'Comoros',
                'CG' => 'Congo',
                'CD' => 'Congo, Democratic Republic',
                'CK' => 'Cook Islands',
                'CR' => 'Costa Rica',
                'CI' => 'Cote D\'Ivoire',
                'HR' => 'Croatia',
                'CU' => 'Cuba',
                'CY' => 'Cyprus',
                'CZ' => 'Czech Republic',
                'DK' => 'Denmark',
                'DJ' => 'Djibouti',
                'DM' => 'Dominica',
                'DO' => 'Dominican Republic',
                'EC' => 'Ecuador',
                'EG' => 'Egypt',
                'SV' => 'El Salvador',
                'GQ' => 'Equatorial Guinea',
                'ER' => 'Eritrea',
                'EE' => 'Estonia',
                'ET' => 'Ethiopia',
                'FK' => 'Falkland Islands (Malvinas)',
                'FO' => 'Faroe Islands',
                'FJ' => 'Fiji',
                'FI' => 'Finland',
                'FR' => 'France',
                'GF' => 'French Guiana',
                'PF' => 'French Polynesia',
                'TF' => 'French Southern Territories',
                'GA' => 'Gabon',
                'GM' => 'Gambia',
                'GE' => 'Georgia',
                'DE' => 'Germany',
                'GH' => 'Ghana',
                'GI' => 'Gibraltar',
                'GR' => 'Greece',
                'GL' => 'Greenland',
                'GD' => 'Grenada',
                'GP' => 'Guadeloupe',
                'GU' => 'Guam',
                'GT' => 'Guatemala',
                'GG' => 'Guernsey',
                'GN' => 'Guinea',
                'GW' => 'Guinea-Bissau',
                'GY' => 'Guyana',
                'HT' => 'Haiti',
                'HM' => 'Heard Island & Mcdonald Islands',
                'VA' => 'Holy See (Vatican City State)',
                'HN' => 'Honduras',
                'HK' => 'Hong Kong',
                'HU' => 'Hungary',
                'IS' => 'Iceland',
                'IN' => 'India',
                'ID' => 'Indonesia',
                'IR' => 'Iran, Islamic Republic Of',
                'IQ' => 'Iraq',
                'IE' => 'Ireland',
                'IM' => 'Isle Of Man',
                'IL' => 'Israel',
                'IT' => 'Italy',
                'JM' => 'Jamaica',
                'JP' => 'Japan',
                'JE' => 'Jersey',
                'JO' => 'Jordan',
                'KZ' => 'Kazakhstan',
                'KE' => 'Kenya',
                'KI' => 'Kiribati',
                'KR' => 'Korea',
                'KW' => 'Kuwait',
                'KG' => 'Kyrgyzstan',
                'LA' => 'Lao People\'s Democratic Republic',
                'LV' => 'Latvia',
                'LB' => 'Lebanon',
                'LS' => 'Lesotho',
                'LR' => 'Liberia',
                'LY' => 'Libyan Arab Jamahiriya',
                'LI' => 'Liechtenstein',
                'LT' => 'Lithuania',
                'LU' => 'Luxembourg',
                'MO' => 'Macao',
                'MK' => 'Macedonia',
                'MG' => 'Madagascar',
                'MW' => 'Malawi',
                'MY' => 'Malaysia',
                'MV' => 'Maldives',
                'ML' => 'Mali',
                'MT' => 'Malta',
                'MH' => 'Marshall Islands',
                'MQ' => 'Martinique',
                'MR' => 'Mauritania',
                'MU' => 'Mauritius',
                'YT' => 'Mayotte',
                'MX' => 'Mexico',
                'FM' => 'Micronesia, Federated States Of',
                'MD' => 'Moldova',
                'MC' => 'Monaco',
                'MN' => 'Mongolia',
                'ME' => 'Montenegro',
                'MS' => 'Montserrat',
                'MA' => 'Morocco',
                'MZ' => 'Mozambique',
                'MM' => 'Myanmar',
                'NA' => 'Namibia',
                'NR' => 'Nauru',
                'NP' => 'Nepal',
                'NL' => 'Netherlands',
                'AN' => 'Netherlands Antilles',
                'NC' => 'New Caledonia',
                'NZ' => 'New Zealand',
                'NI' => 'Nicaragua',
                'NE' => 'Niger',
                'NG' => 'Nigeria',
                'NU' => 'Niue',
                'NF' => 'Norfolk Island',
                'MP' => 'Northern Mariana Islands',
                'NO' => 'Norway',
                'OM' => 'Oman',
                'PK' => 'Pakistan',
                'PW' => 'Palau',
                'PS' => 'Palestinian Territory, Occupied',
                'PA' => 'Panama',
                'PG' => 'Papua New Guinea',
                'PY' => 'Paraguay',
                'PE' => 'Peru',
                'PH' => 'Philippines',
                'PN' => 'Pitcairn',
                'PL' => 'Poland',
                'PT' => 'Portugal',
                'PR' => 'Puerto Rico',
                'QA' => 'Qatar',
                'RE' => 'Reunion',
                'RO' => 'Romania',
                'RU' => 'Russian Federation',
                'RW' => 'Rwanda',
                'BL' => 'Saint Barthelemy',
                'SH' => 'Saint Helena',
                'KN' => 'Saint Kitts And Nevis',
                'LC' => 'Saint Lucia',
                'MF' => 'Saint Martin',
                'PM' => 'Saint Pierre And Miquelon',
                'VC' => 'Saint Vincent And Grenadines',
                'WS' => 'Samoa',
                'SM' => 'San Marino',
                'ST' => 'Sao Tome And Principe',
                'SA' => 'Saudi Arabia',
                'SN' => 'Senegal',
                'RS' => 'Serbia',
                'SC' => 'Seychelles',
                'SL' => 'Sierra Leone',
                'SG' => 'Singapore',
                'SK' => 'Slovakia',
                'SI' => 'Slovenia',
                'SB' => 'Solomon Islands',
                'SO' => 'Somalia',
                'ZA' => 'South Africa',
                'GS' => 'South Georgia And Sandwich Isl.',
                'ES' => 'Spain',
                'LK' => 'Sri Lanka',
                'SD' => 'Sudan',
                'SR' => 'Suriname',
                'SJ' => 'Svalbard And Jan Mayen',
                'SZ' => 'Swaziland',
                'SE' => 'Sweden',
                'CH' => 'Switzerland',
                'SY' => 'Syrian Arab Republic',
                'TW' => 'Taiwan',
                'TJ' => 'Tajikistan',
                'TZ' => 'Tanzania',
                'TH' => 'Thailand',
                'TL' => 'Timor-Leste',
                'TG' => 'Togo',
                'TK' => 'Tokelau',
                'TO' => 'Tonga',
                'TT' => 'Trinidad And Tobago',
                'TN' => 'Tunisia',
                'TR' => 'Turkey',
                'TM' => 'Turkmenistan',
                'TC' => 'Turks And Caicos Islands',
                'TV' => 'Tuvalu',
                'UG' => 'Uganda',
                'UA' => 'Ukraine',
                'AE' => 'United Arab Emirates',
                'GB' => 'United Kingdom',
                'US' => 'United States',
                'UM' => 'United States Outlying Islands',
                'UY' => 'Uruguay',
                'UZ' => 'Uzbekistan',
                'VU' => 'Vanuatu',
                'VE' => 'Venezuela',
                'VN' => 'Viet Nam',
                'VG' => 'Virgin Islands, British',
                'VI' => 'Virgin Islands, U.S.',
                'WF' => 'Wallis And Futuna',
                'EH' => 'Western Sahara',
                'YE' => 'Yemen',
                'ZM' => 'Zambia',
                'ZW' => 'Zimbabwe',
                );
  return $countries;
}

function persuation_badges() {
    $badges = array(
                array( 'icon' => 'flaticon-atm', 'label' => 'Discount' ),
                array( 'icon' => 'flaticon-badge', 'label' => 'Unique' ),
                array( 'icon' => 'flaticon-barcode', 'label' => 'Brand' ),
                array( 'icon' => 'flaticon-cash-register', 'label' => 'Service' ),
                array( 'icon' => 'flaticon-coupon', 'label' => 'Coupon' ),
                array( 'icon' => 'flaticon-coupon-1', 'label' => 'Coupon' ),
                array( 'icon' => 'flaticon-credit-card-1', 'label' => 'Credit&nbsp;Card' ),
                array( 'icon' => 'flaticon-customer-service', 'label' => 'Support' ),
                array( 'icon' => 'flaticon-delivery-truck', 'label' => 'Delivery' ),
                array( 'icon' => 'flaticon-faq', 'label' => 'Support' ),
                array( 'icon' => 'flaticon-giftbox', 'label' => 'Bonus' ),
                array( 'icon' => 'flaticon-online-shop', 'label' => 'Global' ),
                array( 'icon' => 'flaticon-paypal', 'label' => 'Paypal' ),
                array( 'icon' => 'flaticon-piggy-bank', 'label' => 'Discount' ),
                array( 'icon' => 'flaticon-price-tag', 'label' => 'Price' ),
                array( 'icon' => 'flaticon-shield', 'label' => 'Secured' ),
                array( 'icon' => 'flaticon-shop', 'label' => 'Ethical' ),
                array( 'icon' => 'flaticon-shopping-cart-1', 'label' => 'Home&nbsp;Delivery' ),
                array( 'icon' => 'flaticon-stroller', 'label' => 'Free&nbsp;Shipping' ),
                array( 'icon' => 'flaticon-tag', 'label' => 'Charity' )
        );
    return $badges;
}

function cart_badges() {
    $badges = array(
                    array( 'icon' => 'flaticon-2-checkout-pay-logo', 'label' => '2Checkout' ),
                    array( 'icon' => 'flaticon-alipay-logo', 'label' => 'Alipay' ),
                    array( 'icon' => 'flaticon-american-express-logo', 'label' => 'American&nbsp;Express' ),
                    array( 'icon' => 'flaticon-cirrus-pay-logo', 'label' => 'Cirrus' ),
                    array( 'icon' => 'flaticon-citibank-logo', 'label' => 'Citibank' ),
                    array( 'icon' => 'flaticon-credit-card', 'label' => 'Credit&nbsp;Card' ),
                    array( 'icon' => 'flaticon-discover-logo-of-pay-system', 'label' => 'Discover' ),
                    array( 'icon' => 'flaticon-dwolla-logo', 'label' => 'Dwolla' ),
                    array( 'icon' => 'flaticon-euronet-pay-logo', 'label' => 'Euronet' ),
                    array( 'icon' => 'flaticon-gift-card-pay-logo', 'label' => 'Gift Card' ),
                    array( 'icon' => 'flaticon-google-wallet-pay-logo', 'label' => 'Google&nbsp;Wallet' ),
                    array( 'icon' => 'flaticon-hsbc-logo', 'label' => 'HSBC' ),
                    array( 'icon' => 'flaticon-jcb-pay-logo-symbol', 'label' => 'JCB' ),
                    array( 'icon' => 'flaticon-maestro-pay-logo', 'label' => 'Maestro' ),
                    array( 'icon' => 'flaticon-master-card-logo', 'label' => 'Mastercard' ),
                    array( 'icon' => 'flaticon-paypal-logo', 'label' => 'Paypal' ),
                    array( 'icon' => 'flaticon-recurly-pay-logo', 'label' => 'Recurly' ),
                    array( 'icon' => 'flaticon-square-pay-logo', 'label' => 'Square Pay' ),
                    array( 'icon' => 'flaticon-stripe-logo', 'label' => 'Stripe' ),
                    array( 'icon' => 'flaticon-ukash-logo', 'label' => 'Ukash' ),
                    array( 'icon' => 'flaticon-verisign-logo', 'label' => 'Verisign' ),
                    array( 'icon' => 'flaticon-visa-pay-logo', 'label' => 'Visa' ),
                    array( 'icon' => 'flaticon-webmoney-paying-logo', 'label' => 'Webmoney' ),
                    array( 'icon' => 'flaticon-wepay-pay-logo', 'label' => 'Wepay' ),
                    array( 'icon' => 'flaticon-western-union-money-transfer-logo', 'label' => 'Western Union' ),
                    array( 'icon' => 'flaticon-wire-transfer-logo', 'label' => 'Wire Transfer' ),
                    array( 'icon' => 'flaticon-worldpay-logo', 'label' => 'Worldpay' )
                );
    return $badges;
}

function days_between( $from, $to ) {
    $from = strtotime( $from ); // or your date as well
    $to = strtotime( $to );
    $datediff = $to - $from;
    return floor($datediff / (60 * 60 * 24));
}
?>
