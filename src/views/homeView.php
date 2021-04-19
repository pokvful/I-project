<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/views/baseView.php";

class HomeView extends BaseView {
    public function __construct(string $path) {
        parent::__construct($path);
    }

    public function run() {
        $this->render();
    }
}

