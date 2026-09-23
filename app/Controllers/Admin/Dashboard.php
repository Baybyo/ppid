<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PermohonanModel;
use App\Models\MasyarakatModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $permohonanModel = new PermohonanModel();
        $masyarakatModel = new MasyarakatModel();

        $stats  = $permohonanModel->statusCounts();
        $recent = $permohonanModel->recentForAdmin(10);

        $stats['totalMasyarakat'] = $masyarakatModel->countAllResults();

        return view('admin/dashboard', [
            'title'      => 'Dashboard Admin',
            'active'     => 'dashboard',
            'pageTitle'  => 'Dashboard',
            'pageSubtitle' => 'Ringkasan permohonan masuk',
            'stats'      => $stats,
            'recent'     => $recent,
        ]);
    }
}
