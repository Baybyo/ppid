<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PermohonanModel;
use App\Models\PermohonanLampiranModel;

class FileServe extends BaseController
{
    protected PermohonanModel $permohonanModel;
    protected PermohonanLampiranModel $lampiranModel;

    public function __construct()
    {
        $this->permohonanModel = new PermohonanModel();
        $this->lampiranModel   = new PermohonanLampiranModel();
    }

    public function serve(int $lampiranId)
    {
        $lampiran = $this->lampiranModel->find($lampiranId);
        if (!$lampiran) {
            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan.');
        }

        $permohonan = $this->permohonanModel->find($lampiran['permohonan_id']);
        if (!$permohonan) {
            return $this->response->setStatusCode(404)->setBody('Data tidak ditemukan.');
        }

        $isOwner = session()->get('isMasyarakatLoggedIn')
            && (int) $permohonan['masyarakat_id'] === (int) session()->get('masyarakatId');
        $isAdmin = session()->get('isAdminLoggedIn');

        if (!$isOwner && !$isAdmin) {
            return $this->response->setStatusCode(403)->setBody('Akses ditolak.');
        }

        $filePath = FCPATH . $lampiran['path_file'];
        $realPath = realpath($filePath);
        if ($realPath === false || strpos($realPath, realpath(FCPATH)) !== 0) {
            return $this->response->setStatusCode(403)->setBody('Akses ditolak.');
        }
        if (!file_exists($realPath)) {
            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan di server.');
        }

        $mime = $lampiran['mime_type'] ?: mime_content_type($filePath);
        $name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $lampiran['nama_file'] ?? basename($filePath));

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $name . '"')
            ->setHeader('Content-Length', filesize($filePath))
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($filePath));
    }
}
