<?php
$collect = new Collect(dirname(__DIR__));
$collect();

class Collect
{
    protected $dir;
    protected $jsonDir;
    protected $composerDir;
    protected $vendors = [];
    protected $attrition = [];

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function __invoke()
    {
        $this->collectVendors();
        $this->collectPackages();
        file_put_contents(
            "{$this->dir}/results/attrition.json",
            json_encode($this->attrition, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function collectVendors()
    {
        // $text = file_get_contents("https://packagist.org/packages/list.json");
        // file_put_contents("{$this->dir}/vendors/list.json", $text);

        $text = file_get_contents("{$this->dir}/vendors/list.json");
        $json = json_decode($text, true);
        foreach ($json['packageNames'] as $vendor_package) {
            list($vendor, $package) = explode('/', $vendor_package);
            $this->vendors[$vendor][] = $package;
        }
    }

    protected function collectPackages()
    {
        foreach ($this->vendors as $vendor => $packages) {
            foreach ($packages as $package) {
                $this->collectPackage($vendor, $package);
            }
        }
    }

    protected function collectPackage($vendor, $package)
    {
        $this->collectPackageJson($vendor, $package);
        $this->collectPackageComposer($vendor, $package);
    }

    protected function collectPackageJson($vendor, $package)
    {
        echo PHP_EOL;
        echo "{$vendor}/{$package} ... " . PHP_EOL;

        $dir = "{$this->dir}/vendors/{$vendor}";
        echo "    - {$dir}" . PHP_EOL;

        if (! is_dir($dir)) {
            mkdir($dir);
        }

        $file = "{$dir}/{$package}.json";

        if (file_exists($file)) {
            echo "    - package file exists" . PHP_EOL;
            return;
        }

        $text = file_get_contents("https://repo.packagist.org/p2/{$vendor}/{$package}.json");
        if (! $text) {
            $this->attrition['could not get from packagist'][] = "{$vendor}/{$package}";
            echo "    - could not get from packagist" . PHP_EOL;
            return;
        }

        file_put_contents($file, $text);
        echo "    - collected package file" . PHP_EOL;
        return json_decode($text, true);
    }

    protected function collectPackageComposer($vendor, $package)
    {
        $dir = "{$this->dir}/vendors/{$vendor}";
        $json = json_decode(file_get_contents("{$dir}/{$package}.json"), true);
        $json = $json['packages'] ?? false;

        if (! $json) {
            $this->attrition['no packages in json'][] = "{$vendor}/{$package}";
            echo "    - no 'packages' in json" . PHP_EOL;
            return;
        }

        $json = current($json); // "vendor/package"
        if (! $json) {
            $this->attrition['no vendor/package in json'][] = "{$vendor}/{$package}";
            echo "    - no '{$vendor}/{$package}' in json" . PHP_EOL;
            return;
        }

        $json = current($json); // first branch
        if (! $json) {
            $this->attrition['no branches in json'][] = "{$vendor}/{$package}";
            echo "    - no branches in json" . PHP_EOL;
            return;
        }

        if (isset($json['abandoned']) && $json['abandoned']) {
            $this->attrition['abandoned in json'][] = "{$vendor}/{$package}";
            echo "    - abandoned in json" . PHP_EOL;
            return;
        }

        $file = "{$dir}/{$package}.composer.json";

        if (file_exists($file)) {
            echo "    - composer file exists" . PHP_EOL;
            return;
        }

        $url = $json['source']['url'];

        if (strpos($url, 'github.com') === false) {
            $this->attrition['not at github'][] = "{$vendor}/{$package}";
            echo "    - not at github" . PHP_EOL;
            return;
        }

        $href = $this->getComposerHref($url);

        if (! $href) {
            $this->attrition['no composer.json href at github'][] = "{$vendor}/{$package}";
            echo "    - could not find composer.json href" . PHP_EOL;
            return;
        }

        // https://github.com               /StarsRivers/upload/blob/master/composer.json
        // https://raw.githubusercontent.com/StarsRivers/upload     /master/composer.json
        $raw = 'https://raw.githubusercontent.com' . trim(str_replace('/blob/', '/', $href));
        echo "    - {$raw}" . PHP_EOL;
        `curl -s $raw > {$file}`;
        echo "    - captured composer" . PHP_EOL;

    }

    protected function getComposerHref($url)
    {
        $html = file_get_contents($url);

        $doc = new DOMDocument();
        @$doc->loadHtml($html);
        $doc->normalizeDocument();
        $xpath = new DOMXpath($doc);
        $anchors = $xpath->query("//*/a[@title='composer.json']");
        foreach ($anchors as $anchor) {
            return $anchor->getAttribute('href') . PHP_EOL;
        }

        return null;
    }
}
