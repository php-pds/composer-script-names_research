# Research Report for pds/composer-script-names

## Introduction and Summary

Composer defines its own set of event scripts, but allows package authors to
define custom scripts as well. This research analyzes the Composer package
ecosystem to find the names and purposes of those custom scripts, in hopes of
revealing standard conventions within that ecosystem.

This research started with the expectation of finding mostly quality-assurance
and continuous-integration scripts of these types:

- Testing
- Static Analysis
- Style Fixing
- Linting
- "Suites" to style-fix, lint, static-analyze, and test.

This expectation was met; research revealed other less-common custom scripts as
well. It also revealed a common script naming convention.

## Methodology

### Collection

1. Get the list of all packages on Packagist: [vendors/list.json](vendors/list.json).

2. For each of those, fetch the package JSON files from Packagist ...
   `<https://packagist.org/p/{$VENDOR}/{$PACKAGE}.json>` ... and retain them.

3. For each of the fetched package JSON files ...

    - skip the package if it has no `"packages":` entry;
    - skip the package if it has no `"{$VENDOR}/{$PACKAGE}":` entry;
    - skip the package if it has no `"branches":` entry;
    - examine first `"branches":` entry;
    - skip the package if is marked as `abandoned`;
    - skip the package if it is not hosted at Github (this is to minimize
      tooling necessary to analyze repositories without downloading them).

4. For each of the fetched packages, scrape the Github page for the first branch
   source URL, to download and retain the `composer.json` file for that pacakge.

Whereas the Packagist `list.json` file indicates 350642 packages total,
collection netted only 229662 `composer.json` files, for a 34.5% attrition
rate. The attrition is due to the conditions for skipping packages as noted
above:

- Lost 76 packages because they could not be retrieved from Packagist.
- Lost 76 packages because the Packagist "packages" entry was empty.
- Lost 84265 packages because the Packagist "{$VENDOR}/{$PACKAGE}" entry was empty.
- Lost 23061 packages because package was marked as "abandoned".
- Lost 10641 packages because they were not hosted at Github.
- Lost 2861 packages because no composer.json href could be found at Github.

The attrition results are are at [results/attrition.json](results/attrition.json).

### Collation

To aid analysis, collate all `script` definitions in all collected
`composer.json` files by package name and script name.

The collated results indicate that of the 229662 packages collected:

- 41036 packages define at least one script (including Composer event scripts);

- 36521 packages define at least one non-event script.

The results are at [results/collated.json](results/collated.json).

Using the collated results ...

1.  Find how many packages define at least one script, including Composer
    event scripts. (The count is 41036 packages).

2.  Find how many packages define at least one *custom non-event* script.
    (The count is 36251 packages.)

3.  Find the most-common script name that is *not* a Composer event script.
    (That script name is `test`, occurring 26658 times among the 36251 packages
    with at least one non-event script.)

4.  Record all other script names occuring at least 99.7% (roughly 3 sigma) as
    often as the most-common script name. That is, for a script to be recorded
    below, it had to occur at least 80 times.

5.  Record the underlying commands in `composer.json` for each script name. For
    a command to be recorded below, it had to occur at least 99.7% (roughly 3
    sigma) as often as the script name itself. For example, the `test` script
    occurs 26658 times, so a command had to occur at least 80 times for it to be
    recorded as a `test` command.

The script names and counts, with their underlying commands and counts,
are at [results/analyzed.json](results/analyzed.json).

### Analysis

Given the research expectations, it was straightforward to group the script
intentions into one of the following categories:

- Testing
- Static Analysis
- Style Fixing
- Linting
- "Suites" to style-fix, lint, static-analyze, and test.

There were several other unexpected categories, noted below.

#### Testing

These scripts represent some form of testing, typically via `phpunit`:

- `test` -- runs `phpunit`, typically in a default configuration
- `phpunit` -- runs `phpunit`, typically in a default configuration
- `test:unit` -- runs `pest`, `phpunit`, or another testing tool in various configurations
- `infection` -- runs the `infection` mutation testing tool
- `unit` -- runs `phpunit` in various configurations
- `test-unit` -- runs `phpunit` in various configurations
- `test:integration` -- runs `phpunit` for integration (not unit) tests
- `tester` --  runs the Nette `tester` tool
- `unit-test` -- runs `phpunit` in various configurations
- `phpspec` -- runs the `phpspec` BDD tool
- `test:phpunit` -- runs `phpunit` in various configurations
- `behat` -- runs the `behat` BDD testing tool
- `unit-tests` -- runs `phpunit` in various configurations
- `run-tests` -- runs `phpunit` in various configurations
- `test-f` --  runs `phpunit` with a `--filter` flag

Additionally, the following scripts represent some form of testing **with
coverage enabled**, almost always via `phpunit --coverage-*`, in various
configurations:

- `test-coverage`
- `coverage`
- `test-ci`
- `test:coverage`
- `test:ci`
- `cover`
- `test-cover`
- `coverage-html`
- `test-with-coverage`

#### Static Analysis

These scripts run some form of static analysis tool, such as `phpstan`, `psalm`,
etc.

- `phpstan` -- runs `phpstan` in various configurations
- `analyse` -- runs `phpstan` in various configurations
- `psalm` -- runs `psalm` in various configurations
- `stan` -- runs `phpstan` in various configurations
- `analyze` -- runs `phpstan` in various configurations
- `phan` -- runs `phan` in various configurations
- `static-analysis` -- runs one of several static analysis tools
- `test:types` -- runs `phpstan` in various configurations
- `sa` -- runs one of several static analysis tools

#### Style Fixing

These scripts run some form of coding-style fixer, such as `phpcbf`, `phpcs`,
`php-cs-fixer`, and `ecs`.

- `cs-fix` -- runs `phpcbf` in various configurations
- `cs-check` -- runs `phpcs` in various configurations
- `phpcs` -- runs `phpcs`, typically in a default configuration
- `fix-style` --  runs `phpcbf` in various configurations
- `check-style` --  runs `phpcs` in various configurations
- `format` --  runs `php-cs-fixer` in various configurations
- `cs` -- runs `phpcs` or `php-cs-fixer` in various configurations
- `fix` -- runs `php-cs-fixer` in various configurations
- `phpcbf` -- runs `phpcbf`, typically in a default configuration
- `fixer` -- runs `php-cs-fixer`, typically in a default configuration
- `fix-cs` -- runs `ecs` or `php-cs-fixer` in various configurations
- `check-cs` -- runs `ecs` or `php-cs-fixer` in various configurations
- `php-cs-fixer` -- runs `php-cs-fixer` in various configurations
- `sniff` --  runs `phpcs` in various configurations
- `csfix` --  runs one of several style fixers
- `style` --  runs one of several style fixers
- `cs-fixer` --  runs `ecs` or `php-cs-fixer` in various configurations
- `cs:fix` -- runs `php-cs-fixer` in various configurations
- `cbf` -- runs `phpcbf` in various configurations
- `style-check` -- runs `php-cs-fixer` in various configurations
- `cs:check` -- runs `php-cs-fixer` in various configurations
- `ecs` -- runs `ecs` in various configurations
- `style-fix` -- runs one of several style fixers
- `phpcs-fix` -- runs one of several style fixers

#### Linting

Surprisingly, scripts with `lint` in the name only rarely represent syntax
linting proper (a la `php -l`). Instead, the term `lint` occurs mostly as a
synonym for style fixes.

- `lint` -- runs `phpcs`, `php-cs-fixer`, `phplint`, `parallel-lint`, etc.
- `lint-fix` -- runs `phpcbf`, `ecs`, or `php-cs-fixer`
- `phplint` -- runs `php -l`, `parallel-lint`, or `phplint`
- `lint:fix` -- runs `phpcbf`
- `ci:php:lint` -- runs `php -l`
- `lint-php` -- runs `parallel-lint` or `phplint`
- `lint-clean` -- runs `phpcbf`
- `test:lint` --  runs `php-cs-fixer`, `php -l`, or `pint`

#### Suites

These scripts represent some form of a "suite" of checks, combining two or more
other scripts for testing, static analysis, style fixes, and other checks.

- `check`
- `ci`
- `build`
- `all`
- `qa`

There is very little commonality regarding the commands, and their order, from
package to package.

#### Unexpected

These scripts were not expected at the beginning of this research, and are
recorded below for further research if warranted. They occur much less
frequently than the above categories.

##### Metrics

Other metrics tooling:

- `phpmd` -- runs the `phpmd` tood
- `inspect` -- runs one of `deptrac`, `phpstan`, `psalm`, `php-cs-fixer`, etc.
- `metrics` -- runs the `phpmetrics` tool
- `phpcpd` -- runs the `phpcpd` tool

##### Documentation

Documentation generators:

- `docs` -- runs one of `phpdoc`, `sami`, `swagger`, etc
- `doc` -- runs one of `sami`, `phpdox`, `phpdoc`, `doctum`, `apigen`, etc.

##### Built-In Server

Built-in server commands:

- `serve` -- starts the built-in server
- `start` -- starts the built-in server

##### Other

Other or uncategorized:

- `auto-scripts` -- runs `symfony-cmd` or `npm`
- `clean` -- clears one or more caches
- `cghooks` -- runs the `chooks` tool
- `post-merge` -- runs `composer install`
- `rector` -- runs the `rector` tool
- `watch` -- runs the `phpunit-watcher` tool
- `test-watch` -- runs the `phpunit-watcher` tool
- `development-(enable|disable|status)` -- // Laminas-related commands
- `stan-setup` -- a phpstan setup command
- `release` -- runs a release process
- `upload-coverage` -- uploads test coverage files
- `coveralls` -- uploads test coverage files

## Initial Recommendation

### Script Names

This document recommends using the most-commonly occurring script name for each
indicated purpose, independent of any particular tool being used as the script
name. Thus:

| If the `composer.json` file defines a script to ...                       | ... then it MUST be named: |
|-------------------------------------------------------------------------- | -------------------------- |
| Run tests using a default configuration                                   | `test`                     |
| Run tests using a default configuration, with coverage generation         | `test-coverage`            |
| Run tests using alternative configurations or approaches                  | `test-*` (1)               |
| Run a coding style fixer and/or code linter using a default configuration | `cs-fix`                   |
| Run static analysis using a default configuration                         | `analyse` OR `analyze` (2) |
| Run multiple QA scripts or commands in sequence (3)                       | `check`                    |

Notes:

1.  E.g.: `test-integration`, `test-system`, `test-filter`, `test-behavior`, etc.

2.  This allows for both British and American English common usage.

3.  Neither the particular tools to be run, nor the order in which to run them,
    are specified by this document.

### Naming Convention

Finally, one unexpected result is the appearance of a naming convention for
custom Composer scripts.

-   All non-event scripts used lower-case only for their names.

-   For multi-word non-event script names, dashes occurred 35 times, colons
    occurred 12 times, and underscores did not occur at all.

Thus, this document recommends that script names MUST use all lower-case, with
dashes (not colons or underscores) as word separators.

## Review

### Private Review

None of the six private reviewers noted faults with the methodology. There were two observations regarding the data itself:

- One reviewer observed how few packages use non-event scripts. Of 229662 packages, only 36521 (15%) define at least one non-event script. (This reviewer also opined that using `make` is a superior alternative to using a package manager to run scripts.)

- One reviewer observed the increased attrition rate from the previous PDS publication (pds/skeleton).  The prior attrition rate was 8.5%, whereas the rate here is 34.5%.

There were two objections to the initial recommendation:

- One reviewer objected to including `check` as the recommended script name for running two or more QA tools in sequence, due to its low absolute frequency (1899 uses out of 36521 script names, or about 5%).

- One reviewer objected to using `MUST` regarding dashes as word separators in the naming convention. The reviewer felt `SHOULD` was more appropriate, in deference to the Symfony practice of using colons as separators in some cases.

No other objections to the initial recommendation were raised by the private reviewers.

Finally, the reviewers indicated some drafting errors, such as typos.

### Public Review

TBD.

## Final Recommendation

TBD.
