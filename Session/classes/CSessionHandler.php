<?php namespace EC\Session;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use SessionHandlerInterface;

class CSessionHandler implements SessionHandlerInterface {
    private $session;

    public function __construct(MSession $session) {
        $this->session = $session;
    }

    public function close(): bool {
        return $this->session->sessionHandlers_Close();
    }

    public function destroy(string $id): bool {
        return $this->session->sessionHandlers_Destroy($id);
    }

    public function gc(int $max_lifetime): int|false {
        return $this->session->sessionHandlers_GC($max_lifetime);
    }

    public function open(string $path, string $name): bool {
        return $this->session->sessionHandlers_Open($path, $name);
    }

    public function read(string $id): string|false {
        return $this->session->sessionHandlers_Read($id);
    }

    public function write(string $id, string $data): bool {
        return $this->session->sessionHandlers_Write($id, $data);
    }
}