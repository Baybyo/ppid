<?php
use CodeIgniter\Router\RouteCollection;
/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);
$routes->get('/', 'Permohonan::create');
$routes->group('permohonan', static function (RouteCollection $routes) {
    $routes->get('create', 'Permohonan::create');
    $routes->post('store-draft', 'Permohonan::storeDraft');
    $routes->post('submit', 'Permohonan::submit');
    $routes->get('success/(:segment)', 'Permohonan::success/$1');
    $routes->get('riwayat', 'Permohonan::riwayat');
    $routes->get('detail/(:num)', 'Permohonan::detail/$1');
    $routes->get('cetak-bukti/(:num)', 'Permohonan::cetakBukti/$1');
    $routes->get('download-bukti/(:num)', 'Permohonan::downloadBukti/$1');
    $routes->post('delete-draft/(:num)', 'Permohonan::deleteDraft/$1');
    $routes->get('tracking', 'Permohonan::tracking');
    $routes->post('check-status', 'Permohonan::checkStatus');
});
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::doRegister');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::doLogin');
$routes->get('logout', 'Auth::logout');
$routes->get('files/(:num)', 'FileServe::serve/$1');

$routes->get('profil', 'Profil::index');
$routes->post('profil/update', 'Profil::update');
$routes->post('profil/change-password', 'Profil::changePassword');
$routes->get('admin/login', 'Admin\Auth::login');
$routes->post('admin/login', 'Admin\Auth::attempt');
$routes->get('admin/logout', 'Admin\Auth::logout');
$routes->group('admin', ['filter' => 'adminAuth'], static function (RouteCollection $routes) {
    $routes->get('/', 'Admin\Dashboard::index');
    $routes->group('permohonan', static function (RouteCollection $routes) {
        $routes->get('/', 'Admin\Permohonan::index');
        $routes->get('export', 'Admin\Permohonan::export');
        $routes->get('detail/(:num)', 'Admin\Permohonan::detail/$1');
        $routes->post('update-status/(:num)', 'Admin\Permohonan::updateStatus/$1');
        $routes->post('upload-jawaban/(:num)', 'Admin\Permohonan::uploadJawaban/$1');
        $routes->post('delete/(:num)', 'Admin\Permohonan::delete/$1');
    });
    $routes->group('masyarakat', static function (RouteCollection $routes) {
        $routes->get('/', 'Admin\Masyarakat::index');
        $routes->get('detail/(:num)', 'Admin\Masyarakat::detail/$1');
        $routes->post('toggle-status/(:num)', 'Admin\Masyarakat::toggleStatus/$1');
    });
    $routes->get('profil', 'Admin\Profil::index');
    $routes->post('profil/update', 'Admin\Profil::update');
    $routes->post('profil/change-password', 'Admin\Profil::changePassword');
});
