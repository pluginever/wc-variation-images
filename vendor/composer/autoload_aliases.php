<?php



namespace PluginEver\VariationImages {

    class AliasAutoloader
    {
        private string $includeFilePath;

        private array $autoloadAliases = array (
  'B8\\App' => 
  array (
    'type' => 'class',
    'classname' => 'App',
    'isabstract' => true,
    'namespace' => 'B8',
    'extends' => 'PluginEver\\VariationImages\\B8\\App',
    'implements' => 
    array (
    ),
  ),
  'B8\\Component' => 
  array (
    'type' => 'class',
    'classname' => 'Component',
    'isabstract' => true,
    'namespace' => 'B8',
    'extends' => 'PluginEver\\VariationImages\\B8\\Component',
    'implements' => 
    array (
    ),
  ),
  'B8\\Container\\Container' => 
  array (
    'type' => 'class',
    'classname' => 'Container',
    'isabstract' => false,
    'namespace' => 'B8\\Container',
    'extends' => 'PluginEver\\VariationImages\\B8\\Container\\Container',
    'implements' => 
    array (
      0 => 'ArrayAccess',
    ),
  ),
  'B8\\Container\\ContainerException' => 
  array (
    'type' => 'class',
    'classname' => 'ContainerException',
    'isabstract' => false,
    'namespace' => 'B8\\Container',
    'extends' => 'PluginEver\\VariationImages\\B8\\Container\\ContainerException',
    'implements' => 
    array (
    ),
  ),
  'B8\\Services\\Cache' => 
  array (
    'type' => 'class',
    'classname' => 'Cache',
    'isabstract' => false,
    'namespace' => 'B8\\Services',
    'extends' => 'PluginEver\\VariationImages\\B8\\Services\\Cache',
    'implements' => 
    array (
    ),
  ),
  'B8\\Services\\Filesystem' => 
  array (
    'type' => 'class',
    'classname' => 'Filesystem',
    'isabstract' => false,
    'namespace' => 'B8\\Services',
    'extends' => 'PluginEver\\VariationImages\\B8\\Services\\Filesystem',
    'implements' => 
    array (
    ),
  ),
  'B8\\Services\\Flash' => 
  array (
    'type' => 'class',
    'classname' => 'Flash',
    'isabstract' => false,
    'namespace' => 'B8\\Services',
    'extends' => 'PluginEver\\VariationImages\\B8\\Services\\Flash',
    'implements' => 
    array (
    ),
  ),
  'B8\\Services\\Logger' => 
  array (
    'type' => 'class',
    'classname' => 'Logger',
    'isabstract' => false,
    'namespace' => 'B8\\Services',
    'extends' => 'PluginEver\\VariationImages\\B8\\Services\\Logger',
    'implements' => 
    array (
    ),
  ),
  'B8\\Services\\Notices' => 
  array (
    'type' => 'class',
    'classname' => 'Notices',
    'isabstract' => false,
    'namespace' => 'B8\\Services',
    'extends' => 'PluginEver\\VariationImages\\B8\\Services\\Notices',
    'implements' => 
    array (
    ),
  ),
  'B8\\Services\\Options' => 
  array (
    'type' => 'class',
    'classname' => 'Options',
    'isabstract' => false,
    'namespace' => 'B8\\Services',
    'extends' => 'PluginEver\\VariationImages\\B8\\Services\\Options',
    'implements' => 
    array (
      0 => 'ArrayAccess',
    ),
  ),
  'B8\\Services\\Queue' => 
  array (
    'type' => 'class',
    'classname' => 'Queue',
    'isabstract' => false,
    'namespace' => 'B8\\Services',
    'extends' => 'PluginEver\\VariationImages\\B8\\Services\\Queue',
    'implements' => 
    array (
    ),
  ),
  'B8\\Services\\Request' => 
  array (
    'type' => 'class',
    'classname' => 'Request',
    'isabstract' => false,
    'namespace' => 'B8\\Services',
    'extends' => 'PluginEver\\VariationImages\\B8\\Services\\Request',
    'implements' => 
    array (
    ),
  ),
  'B8\\Services\\Router' => 
  array (
    'type' => 'class',
    'classname' => 'Router',
    'isabstract' => false,
    'namespace' => 'B8\\Services',
    'extends' => 'PluginEver\\VariationImages\\B8\\Services\\Router',
    'implements' => 
    array (
    ),
  ),
  'B8\\Services\\Scripts' => 
  array (
    'type' => 'class',
    'classname' => 'Scripts',
    'isabstract' => false,
    'namespace' => 'B8\\Services',
    'extends' => 'PluginEver\\VariationImages\\B8\\Services\\Scripts',
    'implements' => 
    array (
    ),
  ),
  'B8\\Services\\Settings' => 
  array (
    'type' => 'class',
    'classname' => 'Settings',
    'isabstract' => false,
    'namespace' => 'B8\\Services',
    'extends' => 'PluginEver\\VariationImages\\B8\\Services\\Settings',
    'implements' => 
    array (
    ),
  ),
  'B8\\Services\\Template' => 
  array (
    'type' => 'class',
    'classname' => 'Template',
    'isabstract' => false,
    'namespace' => 'B8\\Services',
    'extends' => 'PluginEver\\VariationImages\\B8\\Services\\Template',
    'implements' => 
    array (
    ),
  ),
  'B8\\SettingsUI' => 
  array (
    'type' => 'class',
    'classname' => 'SettingsUI',
    'isabstract' => false,
    'namespace' => 'B8',
    'extends' => 'PluginEver\\VariationImages\\B8\\SettingsUI',
    'implements' => 
    array (
    ),
  ),
  'B8\\Traits\\HookableTrait' => 
  array (
    'type' => 'trait',
    'traitname' => 'HookableTrait',
    'namespace' => 'B8\\Traits',
    'use' => 
    array (
      0 => 'PluginEver\\VariationImages\\B8\\Traits\\HookableTrait',
    ),
  ),
  'B8\\Traits\\PathableTrait' => 
  array (
    'type' => 'trait',
    'traitname' => 'PathableTrait',
    'namespace' => 'B8\\Traits',
    'use' => 
    array (
      0 => 'PluginEver\\VariationImages\\B8\\Traits\\PathableTrait',
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
