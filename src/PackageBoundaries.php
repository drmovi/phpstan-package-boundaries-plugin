<?php

namespace Drmovi\PackageBoundaries;

use Drmovi\PackageBoundaries\Handlers\HandlerFactory;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

class PackageBoundaries
{

    private static $composerData = null;
    private ?string $packagesPath = null;
    private ?string $sharedPackagesPath = null;
    private ?string $appPath = null;


    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {

        $this->loadSettings();
        if (!$this->packagesPath) {
            return [];
        }

        $isPackageScope = $this->isFileInPackage($scope);
        $isSharedPackageScope = $this->isFileInSharedPackage($scope);
        $isAppScope = $this->isFileInApp($scope);
        $packageScopeName = $this->getPackageFromScope($scope);
        $handler = (new HandlerFactory())->create($node, $scope);
        if (!$handler) {
            return [];
        }
        $classes = $handler->getClassNames();

        if (empty($classes)) {
            return [];
        }

        $classesInPackages = [];
        foreach ($classes as $class) {
            if ($this->isClassInPackage($class) || $this->isClassInSharedPackage($class)) {
                $classesInPackages[] = $class;
            }
        }
        if (empty($classesInPackages)) {
            return [];
        }

        $packages = [];
        foreach ($classesInPackages as $class) {
            $packages[$class] = $this->getPackageNameFromClassName($class);
        }


        $classesViolations = [];
        foreach ($packages as $class => $package) {
            if ($isPackageScope && $packageScopeName != $package) {
                $classesViolations[] = RuleErrorBuilder::message(sprintf('Package %s is not allowed to use %s', $packageScopeName, $class))->build();
            }
            if ($isSharedPackageScope && $this->isClassInPackage($class)) {
                $classesViolations[] = RuleErrorBuilder::message(sprintf('Package %s is not allowed to use %s', $packageScopeName, $class))->build();
            }
            if ($isAppScope && ($this->isClassInPackage($class) || $this->isClassInSharedPackage($class))) {
                $classesViolations[] = RuleErrorBuilder::message(sprintf('The main app is not allowed to use %s', $class))->build();
            }
        }

        if (empty($classesViolations)) {
            return [];
        }

        return $classesViolations;
    }


    private function isFileInPackage(Scope $scope): bool
    {
        return str_starts_with(trim(str_replace(getcwd(), '', $scope->getFile()), '/'), $this->packagesPath);
    }

    private function isFileInSharedPackage(Scope $scope): bool
    {
        return str_starts_with(trim(str_replace(getcwd(), '', $scope->getFile()), '/'), $this->sharedPackagesPath);
    }

    private function getPackageFromScope(Scope $scope): string
    {
        $data = explode('/', trim(str_replace(getcwd(), '', $scope->getFile()), '/'));
        return $data[1];
    }

    private function getPackageNameFromClassName(string $class): string
    {
        $data = explode('/', trim(str_replace(getcwd(), '', (new \ReflectionClass($class))->getFileName()), '/'));
        return $data[1];
    }

    private function isClassInPackage(string $class): bool
    {
        return str_starts_with(trim(str_replace(getcwd(), '', (new \ReflectionClass($class))->getFileName()), '/'), $this->packagesPath);
    }

    private function isClassInApp(string $class): bool
    {
        return str_starts_with(trim(str_replace(getcwd(), '', (new \ReflectionClass($class))->getFileName()), '/'), $this->appPath);
    }


    private function loadSettings(): void
    {
        if (self::$composerData) {
            return;
        }
        self::$composerData = json_decode(file_get_contents(getcwd() . '/composer.json'), true);
        $data = self::$composerData['extra']['phpstan-package-boundaries-plugin'];
        $this->packagesPath = $data['packages_path'] ?? null;
        $this->sharedPackagesPath = $data['shared_packages_path'] ?? null;
        $this->appPath = $data['app_path'] ?? null;
    }

    private function isClassInSharedPackage(mixed $class): bool
    {
        return str_starts_with(trim(str_replace(getcwd(), '', (new \ReflectionClass($class))->getFileName()), '/'), $this->sharedPackagesPath);

    }

    private function isFileInApp(Scope $scope): bool
    {
        return str_starts_with(trim(str_replace(getcwd(), '', $scope->getFile()), '/'), $this->appPath);
    }
}
