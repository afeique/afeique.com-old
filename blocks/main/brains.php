<?php 

// 'brains' is a silly moniker for the contents of the head

$content = o()->__(
    css_link('jquery-ui-lightness/jquery-ui-1.8.17.min.css'),
    css_link('blueprint/screen.min.css'),
    html_if('lt IE 8', css_link('blueprint/ie.css')),
    css_link('main.css'),
    html_if('lt IE 9', script_src('html5shiv.js')),
    l('script')->_t('text/javascript')->__('
        var BASE_URL = "'.BASE_URL.'";
        var PUBLISHED_POSTS_DIR = "'.PUBLISHED_POSTS_DIR.'";
    ')
    //script_src('jquery-1.7.1.min.js'),
    //script_src('jquery-ui.min.js'),
    //script_src('main.js')
);

?>