<?php

namespace Gamba\Tools;

use Discord\Builders\Components\Button;

final class ButtonCollectionManager {
    private array $buttons;

    public function __construct(private int $removeAfter) {}

    public function add(Button $button, string $id, array $assocData) {
        $this->buttons[$id] = [
            'button' => $button,
            'data' => $assocData
        ];
    }

    public function updateDataKey(string $buttonId, mixed $value, string $key) {
        $this->buttons[$buttonId]['data'][$key] = $value;
    }
    
}