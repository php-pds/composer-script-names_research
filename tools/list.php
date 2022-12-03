<?php
$dir = dirname(__DIR__);
$file = "{$dir}/vendors/list.json";
$json = json_decode(file_get_contents($file), true);
$before = count($json['packageNames']);
echo "{$before} packages total." . PHP_EOL;
echo PHP_EOL;

$after = $before;
$file = "{$dir}/results/attrition.json";
$json = json_decode(file_get_contents($file), true);
foreach ($json as $reason => $packages) {
    $lost = count($packages);
    echo "Lost {$lost} packages because {$reason}." . PHP_EOL;
    $after -= $lost;
}

echo PHP_EOL;
echo "{$after} packages after attrition." . PHP_EOL;
echo "Attrition rate: " . (1 - ($after / $before)) . PHP_EOL;
