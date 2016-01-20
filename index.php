<?php

/*

Plugin Name: WP MLang

Plugin URI: https://github.com/EaseCloud/WP-MLang

Description: 支持自定义的多语言显示功能。

Version: 0.1

Author: Alfred Huang (呆滞的慢板)

Author URI: https://www.huangwenchao.com.cn

License: GPLv2

Copyright 2013 mlang (email : alfred.h@163.com)
 
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

*/

/**********************
 * Installation &
 * Deactiveation
 *********************/

register_activation_hook( __FILE__, 'mlang_install');

function mlang_install() {
}

// 禁用事件
register_deactivation_hook( __FILE__, 'mlang_deactive' );

function mlang_deactive() {
}

function mlang_languages() {
    $languages = get_available_languages();
    if(!in_array('en', $languages)) array_unshift($languages, 'en');
    return $languages;
}

/**********************
 * Custom Functions   *
 **********************/

add_action('init', function() {

    // 获取当前安装的语言列表
    $languages = mlang_languages();

    // 针对语言列表注册 shortcode
    foreach($languages as $lang) {
        add_shortcode($lang, function($attr, $content) use ($lang) {
            return get_locale() == $lang ? do_shortcode($content) : '';
        });
    }

});

/************************************
 * Load Current Language settings.
 ***********************************/

// load textdomain and .mo file if "lang" is set
load_theme_textdomain('theme-domain', get_template_directory() . '/lang');


add_filter('locale', function($locale) {

    @session_start();

    $languages = mlang_languages();

    // 通过子域名指定的语言域
    $lang = explode('.', $_SERVER['HTTP_HOST'])[0];
    if(in_array($lang, $languages)) return $lang;

    // 通过设置 $_SESSION 指定语言域，SESSION 可以由 $_GET 进行设置
    $lang = @$_SESSION['mlang_language'];
    if(in_array($lang, $languages)) return $lang;

    return $locale;

});


/**********************************************
 * Buffer Current Language settings to cookie.
 **********************************************/

// anyway, if session language settings exists, write it to the cookie.
add_action('init', function() {


    if(isset($_GET['mlang_language'])) {

        $lang = $_GET['mlang_language'];
        if(in_array($lang, mlang_languages())) {
            @session_start();
            $_SESSION['mlang_language'] = $lang;
            // 移除 url 上面的 mlang_language 选项
            $redirect = preg_replace(
                '/([\\?&])mlang_language=[^\\?&]+&?/',
                '\\1',
                $_SERVER['REQUEST_URI']
            );
            // 移除末尾或有的多余 ?
            $redirect = preg_replace('/\\?$/', '', $redirect);
            wp_redirect($redirect); exit;
        }

    }

    global $lang;
    $lang = get_locale();

});


/**************************************
 * register the shortcode to display. *
 **************************************/
add_action('plugins_loaded', function() {

    // Actions
    //add_action('admin_menu', 'mlang_setting_page' );

    // Filters
    add_filter('admin_title', 'do_shortcode');
    add_filter('bloginfo', 'do_shortcode');
    add_filter('bloginfo_url', 'do_shortcode');
    add_filter('the_title', 'do_shortcode');
    add_filter('the_content', 'do_shortcode');
    add_filter('the_excerpt', 'do_shortcode');
    // add_filter('the_editor_content', 'do_shortcode');
    add_filter('the_category', 'do_shortcode');
    add_filter('single_cat_title', 'do_shortcode');
    add_filter('category_description', 'do_shortcode');
    add_filter('single_tag_title', 'do_shortcode');
    add_filter('get_archives_link', 'do_shortcode');
    add_filter('get_comment_text', 'do_shortcode');
    add_filter('get_comment_excerpt', 'do_shortcode');
    add_filter('get_the_excerpt', 'do_shortcode');
    add_filter('wp_title', 'do_shortcode');
    add_filter('wp_nav_menu_items', 'do_shortcode');
    add_filter('wp_dropdown_cats', 'do_shortcode');
    add_filter('wp_dropdown_pages', 'do_shortcode');
    add_filter('wp_list_categories', 'do_shortcode');
    add_filter('wp_list_bookmarks', 'do_shortcode');
    add_filter('wp_list_pages', 'do_shortcode');
    add_filter('wp_tag_cloud', 'do_shortcode');
//	add_filter('wp_get_attachment_metadata', 'do_shortcode');
    add_filter('the_tags', 'do_shortcode');
    add_filter('list_cats', 'do_shortcode');
    add_filter('admin_title', 'do_shortcode');
    add_filter('term_name', 'do_shortcode');
//    add_filter('get_the_terms', 'trans_terms');

    // 对 breadcrumb 插件的支持
    add_filter('bcn_breadcrumb_title', 'do_shortcode');

});


///*********************
// * UNFINISH
// */
//
//function trans_terms($obj) {
//    foreach(array_keys($obj) as $key) {
//        $obj[$key]->name = do_shortcode($obj[$key]->name);
//    }
//    return $obj;
//}