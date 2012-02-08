<?php

echo l('header')->_c('span-24')->__(
    l('div')->_c('span-4 first')->__(
        l('h1')->_c('text-center')->__('afeique'),
        l('h2')->_c('text-center')->__('posts here')
    ),
    l('nav')->_i('main-nav')->_c('span-14 prepend-6 last')->__(
        ul(
            li(l_link('', 'home')),
            li(l_link('browse'))
        )
    )
);

?>