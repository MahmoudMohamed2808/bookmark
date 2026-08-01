<?php

it('redirects guests away from the admin panel', function () {
    $this->get('/admin')->assertRedirect('/admin/login');

    $this->get('/admin/bookmarks')->assertRedirect('/admin/login');
});

it('serves the admin login page', function () {
    $this->get('/admin/login')->assertSuccessful();
});
