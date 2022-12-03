<?php
$collate = new Collate(dirname(__DIR__));
$collate();

class Collate
{
    protected $dir;

    protected $scripts = [];

    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    public function __invoke()
    {
        $vendors = scandir($this->dir . '/vendors/');

        foreach ($vendors as $vendor) {
            $this->loadVendor($vendor);
        }

        echo json_encode($this->scripts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function loadVendor(string $vendor)
    {
        if (substr($vendor, 0, 1) === '.') {
            return;
        }

        $vendorDir = $this->dir . "/vendors/{$vendor}";
        $files = glob("$vendorDir/*.composer.json");
        foreach ($files as $file) {
            echo $file . PHP_EOL;
            $this->loadComposer($file);
        }
    }

    protected function loadComposer(string $file)
    {
        $text = file_get_contents($file);
        $composer = json_decode($text);

        if (empty($composer->scripts)) {
            return;
        }

        $scripts = [];

        foreach ($composer->scripts as $name => $command) {
            $scripts[$name] = implode(' ;; ', (array) $command);
        }

        ksort($scripts);
        $this->scripts[$composer->name] = $scripts;
    }
}
