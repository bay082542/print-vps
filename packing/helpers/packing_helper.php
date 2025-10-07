<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('format_order')) {
    function format_order($input)
    {
        $first = substr($input, 0, 1);
        $second = substr($input, 1, 2);
        $third = substr($input, 3, 5);
        $last = substr($input, 8, 1);
        return $first . '-' . $second . ' ' . $third . '-' . $last;
    }
}

if (!function_exists('format_packingNo')) {
    function format_packingNo($packing)
    {
        $first = substr($packing, 0, 3);
        $second = substr($packing, 3, 5);
        return $first . '-' . $second;
    }
}

if (!function_exists('format_qrcode')) {
    function format_qrcode($order, $item)
    {
        return substr($order, 1, 7) . substr($item, 0, 5);
    }
}

if (!function_exists('format_order_no')) {
    function format_order_no($order_no)
    {
        return str_replace(['-', ' '], '', $order_no);
    }
}

if (!function_exists('format_packing')) {
    function format_packing($packing)
    {
        return str_replace('-', '', $packing);
    }
}