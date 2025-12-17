<?php

// Functions and constants

namespace {

}


namespace WooCommerceVariationImages {

    class AliasAutoloader
    {
        private string $includeFilePath;

        private array $autoloadAliases = array (
  'ByteKit\\Admin\\Flash' => 
  array (
    'type' => 'class',
    'classname' => 'Flash',
    'isabstract' => false,
    'namespace' => 'ByteKit\\Admin',
    'extends' => 'WooCommerceVariationImages\\ByteKit\\Admin\\Flash',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Admin\\Notices' => 
  array (
    'type' => 'class',
    'classname' => 'Notices',
    'isabstract' => false,
    'namespace' => 'ByteKit\\Admin',
    'extends' => 'WooCommerceVariationImages\\ByteKit\\Admin\\Notices',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Plugin' => 
  array (
    'type' => 'class',
    'classname' => 'Plugin',
    'isabstract' => true,
    'namespace' => 'ByteKit',
    'extends' => 'WooCommerceVariationImages\\ByteKit\\Plugin',
    'implements' => 
    array (
      0 => 'ByteKit\\Interfaces\\Pluginable',
    ),
  ),
  'ByteKit\\Scripts' => 
  array (
    'type' => 'class',
    'classname' => 'Scripts',
    'isabstract' => false,
    'namespace' => 'ByteKit',
    'extends' => 'WooCommerceVariationImages\\ByteKit\\Scripts',
    'implements' => 
    array (
      0 => 'ByteKit\\Interfaces\\Scriptable',
    ),
  ),
  'ByteKit\\Services' => 
  array (
    'type' => 'class',
    'classname' => 'Services',
    'isabstract' => false,
    'namespace' => 'ByteKit',
    'extends' => 'WooCommerceVariationImages\\ByteKit\\Services',
    'implements' => 
    array (
      0 => 'ArrayAccess',
    ),
  ),
  'ByteKit\\Admin\\Settings' => 
  array (
    'type' => 'class',
    'classname' => 'Settings',
    'isabstract' => true,
    'namespace' => 'ByteKit\\Admin',
    'extends' => 'WooCommerceVariationImages\\ByteKit\\Admin\\Settings',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Traits\\HasPlugin' => 
  array (
    'type' => 'trait',
    'traitname' => 'HasPlugin',
    'namespace' => 'ByteKit\\Traits',
    'use' => 
    array (
      0 => 'WooCommerceVariationImages\\ByteKit\\Traits\\HasPlugin',
    ),
  ),
  'ByteKit\\Interfaces\\Pluginable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Pluginable',
    'namespace' => 'ByteKit\\Interfaces',
    'extends' => 
    array (
      0 => 'WooCommerceVariationImages\\ByteKit\\Interfaces\\Pluginable',
    ),
  ),
  'ByteKit\\Interfaces\\Scriptable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Scriptable',
    'namespace' => 'ByteKit\\Interfaces',
    'extends' => 
    array (
      0 => 'WooCommerceVariationImages\\ByteKit\\Interfaces\\Scriptable',
    ),
  ),
);

        public function __construct()
        {
            $this->includeFilePath = __DIR__ . '/autoload_alias.php';
        }

        public function autoload($class)
        {
            if (!isset($this->autoloadAliases[$class])) {
                return;
            }
            switch ($this->autoloadAliases[$class]['type']) {
                case 'class':
                        $this->load(
                            $this->classTemplate(
                                $this->autoloadAliases[$class]
                            )
                        );
                    break;
                case 'interface':
                    $this->load(
                        $this->interfaceTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                case 'trait':
                    $this->load(
                        $this->traitTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                default:
                    // Never.
                    break;
            }
        }

        private function load(string $includeFile)
        {
            file_put_contents($this->includeFilePath, $includeFile);
            include $this->includeFilePath;
            file_exists($this->includeFilePath) && unlink($this->includeFilePath);
        }

        private function classTemplate(array $class): string
        {
            $abstract = $class['isabstract'] ? 'abstract ' : '';
            $classname = $class['classname'];
            if (isset($class['namespace'])) {
                $namespace = "namespace {$class['namespace']};";
                $extends = '\\' . $class['extends'];
                $implements = empty($class['implements']) ? ''
                : ' implements \\' . implode(', \\', $class['implements']);
            } else {
                $namespace = '';
                $extends = $class['extends'];
                $implements = !empty($class['implements']) ? ''
                : ' implements ' . implode(', ', $class['implements']);
            }
            return <<<EOD
                <?php
                $namespace
                $abstract class $classname extends $extends $implements {}
                EOD;
        }

        private function interfaceTemplate(array $interface): string
        {
            $interfacename = $interface['interfacename'];
            $namespace = isset($interface['namespace'])
            ? "namespace {$interface['namespace']};" : '';
            $extends = isset($interface['namespace'])
            ? '\\' . implode('\\ ,', $interface['extends'])
            : implode(', ', $interface['extends']);
            return <<<EOD
                <?php
                $namespace
                interface $interfacename extends $extends {}
                EOD;
        }
        private function traitTemplate(array $trait): string
        {
            $traitname = $trait['traitname'];
            $namespace = isset($trait['namespace'])
            ? "namespace {$trait['namespace']};" : '';
            $uses = isset($trait['namespace'])
            ? '\\' . implode(';' . PHP_EOL . '    use \\', $trait['use'])
            : implode(';' . PHP_EOL . '    use ', $trait['use']);
            return <<<EOD
                <?php
                $namespace
                trait $traitname { 
                    use $uses; 
                }
                EOD;
        }
    }

    spl_autoload_register([ new AliasAutoloader(), 'autoload' ]);
}
