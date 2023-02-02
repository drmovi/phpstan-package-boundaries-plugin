<?php

namespace Drmovi\PackageBoundaries;

use Drmovi\PackageBoundaries\Handlers\HandlerFactory;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

class PackageBoundaries
{

    private static $composerData = null;
    private string $packagesFolder = 'packages';
    private array $whitelistPackages = ['shared'];

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {

        $this->loadSettings();

        if (!$this->isFileInPackage($scope)) {
            return [];
        }

        $scopeMicroservice = $this->getPackageFromScope($scope);

        $handler = (new HandlerFactory())->create($node, $scope);
        if (!$handler) {
            return [];
        }
        $classes = $handler->getClassNames();

        if (empty($classes)) {
            return [];
        }

        $classesInMicroservice = [];
        foreach ($classes as $class) {
            if ($this->isClassInPackage($class)) {
                $classesInMicroservice[] = $class;
            }
        }
        if (empty($classesInMicroservice)) {
            return [];
        }

        $nodesMicroservices = [];
        foreach ($classesInMicroservice as $class) {
            $nodesMicroservices[$class] = $this->getPackageNameFromClassName($class);
        }

        if (empty($nodesMicroservices)) {
            return [];
        }


        $nonWhiteListedMicroservices = [];
        foreach ($nodesMicroservices as $class => $microservice) {
            if (!in_array($microservice, $this->whitelistPackages)) {
                $nonWhiteListedMicroservices[$class] = $microservice;
            }
        }
        $classesViolations = [];
        foreach ($nonWhiteListedMicroservices as $class => $nodeMicroservice) {
            if ($scopeMicroservice != $nodeMicroservice) {
                $classesViolations[] = RuleErrorBuilder::message(sprintf('Microservice %s is not allowed to use %s', $scopeMicroservice, $class))->build();
            }
        }
        if (empty($classesViolations)) {
            return [];
        }

        return $classesViolations;
    }


    private function isFileInPackage(Scope $scope): bool
    {
        return str_starts_with(trim(str_replace(getcwd(), '', $scope->getFile()), '/'), $this->packagesFolder);
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
        return str_starts_with(trim(str_replace(getcwd(), '', (new \ReflectionClass($class))->getFileName()), '/'), $this->packagesFolder);
    }


    private function loadSettings(): void
    {
        if (self::$composerData) {
            return;
        }
        self::$composerData =  json_decode(file_get_contents(getcwd() . '/composer.json'), true);
        $data = self::$composerData['extra']['phpstan-package-boundaries-plugin'];
        $this->packagesFolder = $data['packages_path'] ?? $this->packagesFolder;
        $this->whitelistPackages = $data['whitelist_packages'] ?? $this->whitelistPackages;
    }
}
