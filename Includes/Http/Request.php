<?php

namespace HTTP;

use CurlMultiHandle;
use Exception;
use Generator;
use SplObjectStorage;

class Request {

    private CurlMultiHandle $mh;
    private SplObjectStorage $curls;
    private bool $executed = false;

    public function __construct(?int $multiHandleOption = null, mixed $value = null) {
        $this->curls = new SplObjectStorage;
        $this->mh = curl_multi_init();
        if($multiHandleOption !== null) {
            curl_multi_setopt($this->mh, $multiHandleOption, $value);
        }
    }

    public function __destruct() {
        curl_multi_close($this->mh);
    }

    public function bind(string $url, ?array $setopt = null) : self {
        $ch = curl_init($url);
        if($setopt) {
            curl_setopt_array($ch, $setopt);
        }

        $this->curls->attach($ch);

        curl_multi_add_handle($this->mh, $ch);
        return $this;
    }

    public function execute() : void {
        $requests = count($this->curls);
        if($requests < 1) {
            throw new Exception('No url bound to this Request');
        }
        elseif($requests == 1) {
            curl_exec($this->curls[0]);
            curl_close($this->curls[0]);
            $this->curls->detach($this->curls[0]);
        }
        else {
            $running = null;
            do {
                curl_multi_exec($this->mh, $running);
            } while($running);
        }

        $this->executed = true;
    }

    public function fetch() : Generator|string|bool {
        if($this->executed == false) throw new Exception('Cannot fetch before executing requests');

        if(count($this->curls) == 1) {
            $data = curl_exec($this->curls[0]);
            curl_close($this->curls[0]);
            $this->curls->detach($this->curls[0]);
            return $data;
        } 

        foreach($this->curls as $ch) {
            yield curl_multi_getcontent($ch);
            $this->curls->detach($ch);
            curl_close($ch);
        }
    }
}