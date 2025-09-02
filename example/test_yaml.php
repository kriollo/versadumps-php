<?php

require __DIR__ . '/vendor/autoload.php';

use Versadumps\Versadumps\YamlParser;

echo "=== Pruebas del YamlParser ===\n\n";

// Test 1: Configuración básica
$basicYaml = "host: 127.0.0.1\nport: 9191\n";
$result = YamlParser::parse($basicYaml);
echo "Test 1 - Configuración básica:\n";
var_dump($result);
echo "\n";

// Test 2: Diferentes tipos de datos
$typesYaml = "
string_value: hello world
integer_value: 42
float_value: 3.14
boolean_true: true
boolean_false: false
null_value: null
quoted_string: \"hello with spaces\"
";
$result = YamlParser::parse($typesYaml);
echo "Test 2 - Diferentes tipos:\n";
var_dump($result);
echo "\n";

// Test 3: Arrays
$arrayYaml = "
simple_array:
  - item1
  - item2
  - item3
mixed_array:
  - string item
  - 123
  - true
";
$result = YamlParser::parse($arrayYaml);
echo "Test 3 - Arrays:\n";
var_dump($result);
echo "\n";

// Test 4: Generación de YAML
$data = [
    'host' => '127.0.0.1',
    'port' => 9191,
    'debug' => true,
    'features' => ['logging', 'encryption', 'compression'],
    'timeout' => 30.5
];
$generated = YamlParser::dump($data);
echo "Test 4 - Generación de YAML:\n";
echo $generated;
echo "\n";

// Test 5: Parse del YAML generado
$parsed = YamlParser::parse($generated);
echo "Test 5 - Parse del YAML generado:\n";
var_dump($parsed);

echo "\n=== Todas las pruebas completadas ===\n";
