### PHPSTAN Package Boundaries Plugin

This package provides a simple way to define boundaries between packages phpstan.

#### Installation

```
composer require --dev drmovi/phpstan-package-boundaries-plugin
```


#### Usage

in config file of PHPSTAN, add the following line:

```
rules:
	- Drmovi\PackageBoundaries\PackageBoundaries
```



