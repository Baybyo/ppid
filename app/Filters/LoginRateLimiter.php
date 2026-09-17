<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class LoginRateLimiter implements FilterInterface
{
    private int $maxAttempts = 5;
    private int $decayMinutes = 5;
    private int $lockoutMinutes = 15;

    public function before(RequestInterface $request, $arguments = null)
    {
        $ip = $request->getIPAddress();
        $key = 'login_attempts_' . md5($ip);
        $cache = cache();

        $attempts = $cache->get($key) ?? ['count' => 0, 'last_attempt' => 0];

        if ($attempts['count'] >= $this->maxAttempts) {
            $timeSinceLastAttempt = time() - $attempts['last_attempt'];
            $lockoutSeconds = $this->lockoutMinutes * 60;

            if ($timeSinceLastAttempt < $lockoutSeconds) {
                $remainingMinutes = ceil(($lockoutSeconds - $timeSinceLastAttempt) / 60);
                return redirect()->back()->withInput()->with('error', 
                    "Terlalu banyak percobaan login. Silakan coba lagi dalam {$remainingMinutes} menit.");
            } else {
                $attempts = ['count' => 0, 'last_attempt' => 0];
                $cache->delete($key);
            }
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }

    public function recordFailedAttempt(string $ip): void
    {
        $key = 'login_attempts_' . md5($ip);
        $cache = cache();

        $attempts = $cache->get($key) ?? ['count' => 0, 'last_attempt' => 0];
        $attempts['count']++;
        $attempts['last_attempt'] = time();

        $cache->save($key, $attempts, $this->decayMinutes * 60);
    }

    public function clearAttempts(string $ip): void
    {
        $key = 'login_attempts_' . md5($ip);
        cache()->delete($key);
    }
}
