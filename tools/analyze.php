<?php
$analyze = new Analyze();
$analyze($argv[1]);

class Analyze
{
    public function __invoke(string $file)
    {
        $input = json_decode(file_get_contents($file), true);

        $skip = [
            'pre-install-cmd',
            'post-install-cmd',
            'pre-update-cmd',
            'post-update-cmd',
            'pre-status-cmd',
            'post-status-cmd',
            'pre-archive-cmd',
            'post-archive-cmd',
            'pre-autoload-dump',
            'post-autoload-dump',
            'post-root-package-install',
            'post-create-project-cmd',
            'pre-operations-exec',
            'pre-package-install',
            'post-package-install',
            'pre-package-update',
            'post-package-update',
            'pre-package-uninstall',
            'post-package-uninstall',
            'init',
            'command',
            'pre-file-download',
            'post-file-download',
            'pre-command-run',
            'pre-pool-create',
        ];

        $packages_found = [];
        $name_count = [];
        $name_commands = [];

        foreach ($input as $package => $scripts) {
            foreach ($scripts as $name => $command) {
                if (in_array($name, $skip)) {
                    continue;
                }

                $packages_found[$package] = true;

                if (! isset($name_count[$name])) {
                    $name_count[$name] = 0;
                    $name_commands[$name] = [];
                }

                $command = str_replace("\n", "\\n", $command);

                if (! isset($name_commands[$name][$command])) {
                    $name_commands[$name][$command] = 0;
                }

                $name_commands[$name][$command] ++;
                $name_count[$name] ++;
            }
        }

        arsort($name_count);

        foreach ($name_commands as $name => &$commands) {
            arsort($commands);
        }

        $pct = 0.003; // 99.7% is roughly 3-sigma
        $max = reset($name_count);
        $min = $max * $pct; // this might be better as a % of vendors or packages

        $output = [
            'packages_with_any_scripts' => count($input),
            'packages_with_non_event_scripts' => count($packages_found),
            'non_event_script_summary' => [],
            'non_event_script_details' => [],
        ];

        foreach ($name_count as $name => $count) {

            if ($count < $min) {
                continue;
            }

            $output['non_event_script_summary'][$name] = $count;
            $output['non_event_script_details'][$name]['count'] = $count;


            foreach ($name_commands[$name] as $command => $subcount) {
                $submin = $count * $pct;

                if ($subcount < $submin) {
                    break;
                }

                $output['non_event_script_details'][$name]['commands'][$command] = $subcount;
            }
        }

        echo json_encode($output, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }
}
