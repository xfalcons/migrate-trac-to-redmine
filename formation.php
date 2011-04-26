#!/usr/bin/php

<?php

/// Shared Function
/**
 * Remove '/' in wiki link
 */
function format_wiki_link($s) {
    $str = str_replace("/", "", $s);
    return $str;
}


/// Text Substitution Function
/**
 * Main function to do substitution
 * @param data the wiki content
 * @param title current wiki page name(wikidoc)
 */
function modify_content($data, $title) {
    replace_string($data);
    trans_toc($data);
    trans_image($data);
    trans_table($data);
    trans_list($data);
    trans_color($data);
    trans_include_user_link($data);
    trans_email_http_link($data);
    trans_single_square_bracket($data);
    trans_wiki_page_link($data);
    add_prefix_to_wikipage($data, $title);
    trans_project_wiki_link($data);
    normalize_wiki_link($data);
    return $data;
}


/**
 * Translate single square bracket to double square bracket
 */
function trans_single_square_bracket(&$data) {
    $pattern = '#\[\[\s*(.*?)\]\]#';
    $replacement = '===$1===';
    $data = preg_replace($pattern, $replacement, $data);
    $pattern = '#\[+(.*?)\]+#';
    $replacement = '[[$1]]';
    $data = preg_replace($pattern, $replacement, $data);
    $pattern = '#===(.*?)===#';
    $replacement = '[[$1]]';
    $data = preg_replace($pattern, $replacement, $data);
}

/** 
 * Replace [[\w]] temporary
 * Add '|' Between wiki_page and subject name if is presented.
 * Remove [[br.*]]
 * [[./teatime  下午茶名單]]
 * [[./library muchiiilla library]]
 * [[ Admin|Admin]]
 * [[ ProductManagement |PM Team]]
 */
function trans_wiki_page_link(&$data) {
    $pattern = '#\[\[br.*?\]\]#i';
    $replacement = '';
    $data = preg_replace($pattern, $replacement, $data);

    $pattern = '#\[\[(\w+)\]\]#';
    $replacement = '====$1====';
    $data = preg_replace($pattern, $replacement, $data);

    $pattern = '/(\[)([^\s|\]]+)\|?/';
    //$pattern = '/(\[)([^\s]+)\|?/';
    $replacement = '$1$2|';
    $data = preg_replace($pattern, $replacement, $data);

    $pattern = '#====(\w+)====#';
    $replacement = '[[$1]]';
    $data = preg_replace($pattern, $replacement, $data);
}

/** 
 * Add current page title to the link which start by './'
 * [[./teatime| 下午茶名單]]
 * [[./library| muchiiilla library]]
 * [./2010/02/02A 蘋果芒刺在背　亞馬遜對決出版商前倨後恭]
 */
function add_prefix_to_wikipage(&$data, $prefix) {
    $pattern = '#(\[\[)(\./)([^\|]*)#';
    $replacement = "$1${prefix}$3";
    $data = preg_replace($pattern, $replacement, $data);
}

/** 
 * Translate '||' to '|' and eliminate useless space 
 */
function trans_table(&$data) {
    $pattern = '#[ \t]*\|\|[ \t]*#';
    $replacement = '|';
    $data = preg_replace($pattern, $replacement, $data);
}

/**
 * Replace TOC 
 */
function trans_toc(&$data) {
    $pattern = '#\[\[TOC.*?\]\]#';
    $replacement = '{{>toc}}';
    $data = preg_replace($pattern, $replacement, $data);
}

/** 
 * Replace IMAGE
 */
function trans_image(&$data) {
    $pattern = '#\[\[Image\((.*?)\)\]\]#i';
    $replacement = '!$1!';
    $data = preg_replace($pattern, $replacement, $data);
}

/** 
 * Replace email link
 */
function trans_email_http_link(&$data) {
    $pattern = '#\[mailto:([^\s]*)\s*(.*)\]#i';
    $replacement = '"$2":$1';
    $data = preg_replace($pattern, $replacement, $data);
}

/** 
 * Transform user's wiki page link
 * [[Include(wiki:u/kevin_luo/Weekly20110309)]]
 */
function trans_include_user_link(&$data) {
    $pattern = '#\[\[Include\((.*?):u/(.*?)\)\]\]#i';
    # we need the second matched string
    $data = preg_replace_callback(
        $pattern,
        "cb_trans_include_user_link",
        $data
    );
}

/**
 * remove '/' in wiki link
 */
function cb_trans_include_user_link($s) {
    $str =  format_wiki_link($s[2]);
    $str = "{{include(U$str)}}";
    return $str;
}

/**
 * remove '//projects/xxxxx/wiki' & '/' in wiki link
 * [[//projects/muchiii/wiki/ProductManagement/muchiiiTeamMembers| 全 Team 通訊錄]]
 */
function trans_project_wiki_link(&$data) {
    $pattern = '#(//projects/\w+/wiki/)(.*)#';
    $data = preg_replace_callback(
        $pattern,
        "cb_trans_project_wiki_link",
        $data
    );
}

/**
 * remove '/' in wiki link
 */
function cb_trans_project_wiki_link($s) {
    return format_wiki_link($s[2]);
}

/**
 * Remove '/' in wiki link
 */
function normalize_wiki_link(&$data) {
    $pattern = '#(\[\[)([^|]*)(.*)#';
    $data = preg_replace_callback(
        $pattern,
        "cb_normalize_wiki_link",
        $data
    );
}

/**
 * remove '/' in wiki link
 */
function cb_normalize_wiki_link($s) {
    $str = format_wiki_link($s[2]);
    return $s[1].$str.$s[3];
}

/**
 * Replace specific string
 */
function replace_string(&$data) {
    $search = array(
            "\r",
            "http://muchiiilla.dlink.com.tw",
            "@dlink.com.tw",
            "muchiii.com",
            "git-muchiii.dlink.com.tw",
            "http://muchiiilla.corp.miiicasa.com/produce/",
            "devm1-muchiii.dlink.com.tw"
            );
    $replacement = array(
            "",
            "",
            "@miiicasa.com",
            "miiicasa.com",
            "git.corp.miiicasa.com",
            "http://produce.corp.miiicasa.com/",
            "devm1.corp.miiicasa.com"
            );
    $data = str_replace($search, $replacement, $data);
}

/**
 * Transform list format
 */
function trans_list(&$data) {
    $pattern = '#^\s+(1\.) (.*)$#m';
    $replacement = '# $2';
    $data = preg_replace($pattern, $replacement, $data);
}

/**
 * Transform color
 * 輸入[[Color(none,red,%windir%\system32\mstsc.exe v:172.17.8.201)]]，
 * 點選下一步[[br]]!04.JPG!| 1. 輸入捷徑名稱 [[Color(none,red,Terminal Server)]]，點選完成[[br|  ]]!05.JPG!
 * [[Color(none,red,NEW)]]
 */
function trans_color(&$data) {
    $pattern = '#\[\[Color\((.*?,){1,2}(.*?)\)\]\]#';
    $replacement = '*$2*';
    $data = preg_replace($pattern, $replacement, $data);
    $data = str_replace("^*NEW*^", '', $data);
}

?>
