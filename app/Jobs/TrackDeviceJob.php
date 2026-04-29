<?php

namespace App\Jobs;

use App\Models\Device;
use App\Models\DeviceLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class TrackDeviceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $deviceIdentifier,
        protected string $platform,
        protected ?string $ip,
        protected ?string $ua,
        protected ?int $bundleId = null,
        protected ?int $applicationId = null,
        protected string $type = 'check'
    ) {}

    public function handle(): void
    {
        $data = [
            'platform' => $this->platform,
            'bundle_id' => $this->bundleId,
            'ip_address' => $this->ip,
            'user_agent' => $this->ua,
            'last_active_at' => now(),
        ];

        // Fetch location with caching and rate limiting
        if ($this->ip && ! in_array($this->ip, ['127.0.0.1', '::1'])) {
            $location = Cache::get("ip_location_{$this->ip}");

            if (! $location) {
                $executed = RateLimiter::attempt(
                    'ip-api-lookup',
                    40, // Max 40 requests
                    function () use (&$location) {
                        try {
                            $response = Http::timeout(5)->get("http://ip-api.com/json/{$this->ip}?fields=status,country,city");
                            if ($response->successful() && $response->json('status') === 'success') {
                                $location = [
                                    'country' => $response->json('country'),
                                    'city' => $response->json('city'),
                                ];
                                Cache::put("ip_location_{$this->ip}", $location, 86400);
                            }
                        } catch (\Exception $e) {
                            Log::warning("Failed to fetch location for IP: {$this->ip}. Error: ".$e->getMessage());
                        }
                    },
                    60 // Per minute
                );

                if (! $executed) {
                    // Release the job back to the queue to try again in a minute
                    $this->release(60);

                    return;
                }
            }

            if ($location) {
                $data['country'] = $location['country'];
                $data['city'] = $location['city'];
            }
        }

        // Basic OS/Model parsing from UA
        if ($this->ua) {
            if (preg_match('/\((.*?)\)/', $this->ua, $matches)) {
                $parts = array_map('trim', explode(';', $matches[1]));
                foreach ($parts as $part) {
                    if (stripos($part, 'Android') !== false) {
                        $data['os_version'] = $part;
                    } elseif (stripos($part, 'Build/') !== false) {
                        $model = trim(explode('Build/', $part)[0]);
                        if (str_contains($model, ' ')) {
                            $model = explode(' ', $model)[0];
                        }
                        $data['device_model'] = $model;
                    }
                }
            }
        }

        $device = Device::updateOrCreate(
            ['device_identifier' => $this->deviceIdentifier],
            [
                'platform' => $this->platform,
                'bundle_id' => $this->bundleId,
                'last_active_at' => now(),
            ]
        );

        DeviceLog::create([
            'device_id' => $device->id,
            'application_id' => $this->applicationId,
            'bundle_id' => $this->bundleId,
            'ip_address' => $this->ip,
            'user_agent' => $this->ua,
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'os_version' => $data['os_version'] ?? null,
            'device_model' => $data['device_model'] ?? null,
            'type' => $this->type,
        ]);
    }
}
