<?php
require_once 'vendor/autoload.php';

use Ramsey\Uuid\Uuid;

$uuid = Uuid::uuid4();

echo $uuid->toString() . "\n";


class Test {

  #[Tag(name: "configurable", value: "true"), Tag(name: "enumerable", value: "true")]
  readonly public string $name;
  function __construct(string $name) {
    $this->name = $name;
  }
}

$test = new Test("Ahmad Asy Syafiq");

echo $test->name . PHP_EOL;

$obj = $test;
$class = get_class($obj);
$reflect = new ReflectionClass($class);
echo "class: " . $reflect->getShortName() . PHP_EOL;

$properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
foreach ($properties as $property) {

  $roles = ReflectionProperty::IS_READONLY | ReflectionProperty::IS_PUBLIC;
  $modifiers = $property->getModifiers();

  // attributes
  $attributes = $property->getAttributes(flags: ReflectionAttribute::IS_INSTANCEOF);

  foreach ($attributes as $attribute) {

    // each attribute

    $attr_name = $attribute->getName();
    echo "attr: " . $attr_name . ", value: ";
    $args = $attribute->getArguments();

    // attribute tidak punya method untuk mengambil current instance
    // jadi disini dibuat komparasi dari name dan parameter dengan class
    // komparasi terdiri dari nama class dan checkout __construct

    // use case with Tag class

    $tag_class = Tag::class;
    $tag_reflect = new ReflectionClass($tag_class);
    $tag_name = $tag_reflect->getName();
    $tag_short_name = $tag_reflect->getShortName();

    // -- start --

    $tag_attrs = $tag_reflect->getAttributes(Attribute::class, flags: ReflectionAttribute::IS_INSTANCEOF);

    // check tag instance of attribute dengan reflection

    $tag_attrs_size = 0;
    foreach ($tag_attrs as $tag_attr) {
      $tag_attrs_size += 1;
      break;
    }

    $tag_verify = $tag_attrs_size > 0;

    // -- end --

    if ($tag_verify) {
      if ($attr_name === $tag_name || $attr_name === $tag_short_name) {
  
        // must be check on __construct paramaters
    
        // acquisition
        $tag = $tag_reflect->newInstance(...$args);
        echo $tag->getName() . PHP_EOL;
      }
    }

    // -- end --
  }

  if ($modifiers & $roles) {

    echo "property: " . $property->getName() . ", value: ";
    echo $property->getValue($obj) . PHP_EOL;
  }
}

$class = Tag::class;
$reflect = new ReflectionClass($class);
echo "class: ". $reflect->getShortName() . PHP_EOL;

$methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
foreach ($methods as $method) {

  if ($method->isConstructor()) {

    echo "method: ". $method->getName() . PHP_EOL;
    $parameters = $method->getParameters();

    foreach ($parameters as $parameter) {
      echo "name: ". $parameter->getName() . ", type: ";
      $param_dt = $parameter->getType();

      echo "defaut: " . ($parameter->isDefaultValueAvailable() ? "true" : "false") . PHP_EOL;

      // ReflectionNamedType, ReflectionUnionType, ReflectionIntersectionType
      echo debug . phpstr_reflect_type($param_dt) . PHP_EOL;
    }
  }
}

function str_reflect_type(?ReflectionType $type): string {
  // depth case

  if ($type instanceof ReflectionNamedType) {

    return $type->getName();
  }
  
  if ($type instanceof ReflectionUnionType) {

    $obj_types = $type->getTypes();
    $str_types = array_map(fn(?ReflectionType $t): string => str_reflect_type($t), 
                           $obj_types);

    return implode("|", $str_types);
  }
  
  if ($type instanceof ReflectionIntersectionType) {

    $obj_types = $type->getTypes();
    $str_types = array_map(fn(?ReflectionType $t): string => str_reflect_type($t), 
                           $obj_types);

    return implode("&", $str_types);
  }

  return "null";
}

?>