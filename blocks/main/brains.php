<?php 

// 'brains' is a silly moniker for the contents of the head

echo o()->__(
    l('script')->_t('text/javascript')->__('
        var BASE_URL = "'.BASE_URL.'";
        var PUBLISHED_POSTS_DIR = "'.PUBLISHED_POSTS_DIR.'";
    '),
    css_link('blueprint/screen.min.css'),
    html_if('lt IE 8', css_link('blueprint/ie.css')),
    html_if('lt IE 9', script_src('html5shiv.min.js'))
);

?>