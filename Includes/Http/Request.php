<?php

declare(strict_types=1);

namespace HTTP;

use CurlMultiHandle;
use Exception;
use Generator;
use SplObjectStorage;

final class Request
{
    private readonly CurlMultiHandle $mh;

    private readonly SplObjectStorage $curls;

    private bool $executed = false;

    public function __construct(?int $multiHandleOption = null, mixed $value = null)
    {
        $this->curls = new SplObjectStorage;
        $this->mh = curl_multi_init();
        if ($multiHandleOption !== null) {
            curl_multi_setopt($this->mh, $multiHandleOption, $value);
        }
    }

    public function __destruct()
    {
        curl_multi_close($this->mh);
    }

    public function bind(string $url, ?array $setopt = null): self
    {
        $ch = curl_init($url);
        if ($setopt) {
            curl_setopt_array($ch, $setopt);
        }

        $this->curls->attach($ch);

        curl_multi_add_handle($this->mh, $ch);
        $this->executed = false;

        return $this;
    }

    public function execute(): void
    {
        $requests = count($this->curls);
        if ($requests < 1) {
            throw new Exception('No url bound to this Request');
        }
        if ($requests === 1) {
            $ch = $this->curls->current();
            curl_exec($ch);
            curl_close($ch);
            $this->curls->detach($ch);
        }

        $running = null;
        do {
            curl_multi_exec($this->mh, $running);
        } while ($running);

        $this->executed = true;
    }

    public function fetch(): Generator|string|false
    {
        if ($this->executed === false) {
            throw new Exception('Cannot fetch before executing requests');
        }

        if (count($this->curls) === 1) {
            $ch = $this->curls->current();
            $data = curl_exec($ch);
            curl_close($ch);
            $this->curls->detach($ch);

            return $data;
        }

        while ($this->curls->valid()) {
            $ch = $this->curls->current();
            yield curl_multi_getcontent($ch);
            curl_close($ch);
            $this->curls->detach($ch);
        }

        $this->executed = false;
    }
}
