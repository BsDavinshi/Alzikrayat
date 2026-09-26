<?php

$router->get('/', ['HomeController', 'index']);
$router->get('/about', ['HomeController', 'about']);

$router->get('/login', ['AuthController', 'showLogin']);
$router->post('/login', ['AuthController', 'login']);
$router->get('/register', ['AuthController', 'showRegister']);
$router->post('/register', ['AuthController', 'register']);
$router->post('/logout', ['AuthController', 'logout']);

$router->get('/photos', ['PhotoController', 'index']);
$router->get('/photo/create', ['PhotoController', 'create']);
$router->post('/photo/store', ['PhotoController', 'store']);
$router->get('/photo/{id:\d+}', ['PhotoController', 'show']);
$router->post('/photo/{id:\d+}/delete', ['PhotoController', 'delete']);
$router->post('/photo/{id:\d+}/like', ['PhotoController', 'toggleLike']);
$router->post('/photo/{id:\d+}/untag/{userId:\d+}', ['PhotoController', 'untag']);

$router->post('/photo/{id:\d+}/comments', ['CommentController', 'store']);
$router->post('/comment/{id:\d+}/delete', ['CommentController', 'delete']);

$router->get('/albums', ['AlbumController', 'index']);
$router->get('/album/create', ['AlbumController', 'create']);
$router->post('/album/store', ['AlbumController', 'store']);
$router->get('/album/{id:\d+}', ['AlbumController', 'show']);
$router->post('/album/{id:\d+}/delete', ['AlbumController', 'delete']);

$router->get('/user/{id:\d+}', ['UserController', 'profile']);
$router->get('/profile/edit', ['UserController', 'edit']);
$router->post('/profile/update', ['UserController', 'update']);
$router->get('/api/users/search', ['UserController', 'search']);
