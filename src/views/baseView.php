<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/settings.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';

class BaseView {
<<<<<<< HEAD
    private Latte\Engine $latteEngine;

    public string $requestPath;
    public string $path;
    public string $fileName;
    public array $data;

    public function __construct(string $requestPath, string $path, string $fileName) {
        $this->latteEngine = new Latte\Engine;
        $this->latteEngine->setTempDirectory('/tmp/latte');

        $this->requestPath = $requestPath;
        $this->path = $path;
        $this->fileName = $fileName;
        $this->data = array(
            "server" => array(
                "requestPath" => $requestPath,
                "path" => $path,
                "fileName" => $fileName,
            ),
        );
    }

    protected function redirect(string $path) {
        header("Location: " . $path);
    }

    protected function render() {
        // $this->latteEngine->render($this->path, $this->data);
        $this->latteEngine->render(
            $_SERVER["DOCUMENT_ROOT"] . "/src/templates/"
            . $this->path . $this->fileName . ".latte",
            $this->data
        );
    }

    public function run() {
        throw new Exception("Not implemented!");
    }
=======
	private Latte\Engine $latteEngine;

	public string $requestPath;
	public string $path;
	public string $fileName;
	public array $data;

	public function __construct(string $requestPath, string $path, string $fileName) {
		$this->latteEngine = new Latte\Engine;
		$this->latteEngine->setTempDirectory( SETTINGS["latte"]["tempDirectory"] );

		$this->requestPath = $requestPath;
		$this->path = $path;
		$this->fileName = $fileName;
		$this->data = array(
			"server" => array(
				"requestPath" => $requestPath,
				"path" => $path,
				"fileName" => $fileName,
			),
		);
	}

	protected function redirect(string $path) {
		header("Location: " . $path);
	}

	protected function render() {
		// $this->latteEngine->render($this->path, $this->data);
		$this->latteEngine->render(
			$_SERVER["DOCUMENT_ROOT"] . "/src/templates/"
				. $this->path . $this->fileName . ".latte",
			$this->data
		);
	}

	public function run() {
		throw new Exception("Not implemented!");
	}
>>>>>>> Moved temp directory to settings
}

