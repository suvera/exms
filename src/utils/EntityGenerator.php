<?php

namespace dev\suvera\exms\utils;

use Doctrine\DBAL\Types\BigIntType;
use Doctrine\ORM\EntityManager;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\PropertyValueGenerator;
use Laminas\Code\Generator\TypeGenerator;

class EntityGenerator {

    public function __construct(
        private EntityManager $em,
        private string $entitiesPath,
        private string $nameSpace,
    ) {
    }

    public function generate(): void {
        $connection = $this->em->getConnection();
        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTableNames();

        foreach ($tables as $tableName) {
            echo "Table: $tableName\n";
            $clsName = str_replace('_', '', ucwords($tableName, '_'));
            $entityCls = new ClassGenerator();
            $entityCls->setNamespaceName($this->nameSpace);
            $entityCls->setName($clsName);

            $entityCls->addUse('Doctrine\\ORM\\Mapping\\Entity')
                ->addUse('Doctrine\\ORM\\Mapping\\Column')
                ->addUse('Doctrine\\ORM\\Mapping\\Column')
                ->addUse('Doctrine\\ORM\\Mapping\\Table')
                ->addUse('Doctrine\\ORM\\Mapping\\GeneratedValue')
                ->addUse('Doctrine\\ORM\\Mapping\\Id');


            $entityCls->setDocBlock((new AnnotationGenerator())
                    ->addAnnotation('Entity', [])
                    ->addAnnotation('Table', ['name' => $tableName])
            );

            $constructorBody = '';
            foreach ($schemaManager->listTableColumns($tableName) as $columnName => $column) {
                $varName = lcfirst(str_replace('_', '', ucwords($columnName, '_')));
                $p = new PropertyGenerator($varName);
                $p->setFlags(PropertyGenerator::FLAG_PUBLIC);

                $type = $column->getType();

                $props = ['name' => $columnName];
                $nullable = !$column->getNotnull();
                $typePrefix = $nullable ? '?' : '';

                if ($type instanceof BigIntType) {
                    $props['type'] = 'bigint';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'int'));
                } else if ($type instanceof \Doctrine\DBAL\Types\IntegerType) {
                    $props['type'] = 'integer';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'int'));
                } else if ($type instanceof \Doctrine\DBAL\Types\SmallIntType) {
                    $props['type'] = 'smallint';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'int'));
                } else if ($type instanceof \Doctrine\DBAL\Types\BooleanType) {
                    $props['type'] = 'boolean';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'bool'));
                } else if ($type instanceof \Doctrine\DBAL\Types\DateTimeType) {
                    $props['type'] = 'datetime';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . '\DateTime'));
                    if (!$nullable) {
                        $p->setDefaultValue(new PropertyValueGenerator("new \DateTime('now')", PropertyValueGenerator::TYPE_CONSTANT));
                    }
                } else if ($type instanceof \Doctrine\DBAL\Types\DateType) {
                    $props['type'] = 'date';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . '\DateTime'));
                    if (!$nullable) {
                        $p->setDefaultValue(new PropertyValueGenerator("new \DateTime('now')", PropertyValueGenerator::TYPE_CONSTANT));
                    }
                } else if ($type instanceof \Doctrine\DBAL\Types\DateImmutableType) {
                    $props['type'] = 'date_immutable';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . '\DateTimeImmutable'));
                    if (!$nullable) {
                        $p->setDefaultValue(new PropertyValueGenerator("new \DateTimeImmutable('now')", PropertyValueGenerator::TYPE_CONSTANT));
                    }
                } else if ($type instanceof \Doctrine\DBAL\Types\DateIntervalType) {
                    $props['type'] = 'dateinterval';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . '\DateInterval'));
                    if (!$nullable) {
                        $p->setDefaultValue(new PropertyValueGenerator("new \DateInterval('P1D')", PropertyValueGenerator::TYPE_CONSTANT));
                    }
                } else if ($type instanceof \Doctrine\DBAL\Types\DateTimeImmutableType) {
                    $props['type'] = 'datetime_immutable';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . '\DateTimeImmutable'));
                    if (!$nullable) {
                        $p->setDefaultValue(new PropertyValueGenerator("new \DateTimeImmutable('now')", PropertyValueGenerator::TYPE_CONSTANT));
                    }
                } else if ($type instanceof \Doctrine\DBAL\Types\DateTimeTzType) {
                    $props['type'] = 'datetimetz';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . '\DateTime'));
                    if (!$nullable) {
                        $p->setDefaultValue(new PropertyValueGenerator("new \DateTime('now')", PropertyValueGenerator::TYPE_CONSTANT));
                    }
                } else if ($type instanceof \Doctrine\DBAL\Types\DateTimeTzImmutableType) {
                    $props['type'] = 'datetimetz_immutable';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . '\DateTimeImmutable'));
                    if (!$nullable) {
                        $p->setDefaultValue(new PropertyValueGenerator("new \DateTimeImmutable('now')", PropertyValueGenerator::TYPE_CONSTANT));
                    }
                } else if ($type instanceof \Doctrine\DBAL\Types\TimeType) {
                    $props['type'] = 'time';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . '\DateTime'));
                    if (!$nullable) {
                        $p->setDefaultValue(new PropertyValueGenerator("new \DateTime('now')", PropertyValueGenerator::TYPE_CONSTANT));
                    }
                } else if ($type instanceof \Doctrine\DBAL\Types\TimeImmutableType) {
                    $props['type'] = 'time_immutable';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . '\DateTimeImmutable'));
                    if (!$nullable) {
                        $p->setDefaultValue(new PropertyValueGenerator("new \DateTimeImmutable('now')", PropertyValueGenerator::TYPE_CONSTANT));
                    }
                } else if ($type instanceof \Doctrine\DBAL\Types\FloatType) {
                    $props['type'] = 'float';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'double|' . $typePrefix . 'float'));
                } else if ($type instanceof \Doctrine\DBAL\Types\DecimalType) {
                    $props['type'] = 'decimal';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'string'));
                } else if ($type instanceof \Doctrine\DBAL\Types\BinaryType) {
                    $props['type'] = 'binary';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'resource'));
                } else if ($type instanceof \Doctrine\DBAL\Types\BlobType) {
                    $props['type'] = 'blob';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'resource'));
                } else if ($type instanceof \Doctrine\DBAL\Types\JsonType) {
                    $props['type'] = 'json';
                } else if ($type instanceof \Doctrine\DBAL\Types\SimpleArrayType) {
                    $props['type'] = 'simple_array';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'array'));
                    if (!$nullable) {
                        $p->setDefaultValue(new PropertyValueGenerator("[]", PropertyValueGenerator::TYPE_CONSTANT));
                    }
                } else if ($type instanceof \Doctrine\DBAL\Types\AsciiStringType) {
                    $props['type'] = 'ascii_string';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'string'));
                } else if ($type instanceof \Doctrine\DBAL\Types\TextType) {
                    $props['type'] = 'text';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'string'));
                } else if ($type instanceof \Doctrine\DBAL\Types\GuidType) {
                    $props['type'] = 'guid';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'string'));
                } else {
                    $props['type'] = 'string';
                    $p->setType(TypeGenerator::fromTypeString($typePrefix . 'string'));
                }

                $props['nullable'] = $nullable;

                if ($column->getLength() > 0) {
                    $props['length'] = $column->getLength();
                }
                if ($column->getPrecision() > 0) {
                    $props['precision'] = $column->getPrecision();
                }
                if ($column->getScale() > 0) {
                    $props['scale'] = $column->getScale();
                }

                $attrs = new AnnotationGenerator();

                if ($column->getAutoincrement()) {
                    $attrs->addAnnotation('Id', []);
                    $attrs->addAnnotation('GeneratedValue', []);
                }
                $attrs->addAnnotation('Column', $props);

                $p->setDocBlock($attrs);
                $p->omitDefaultValue(true);

                if ($p->getDefaultValue() !== null) {
                    $constructorBody .= '$this->' . $p->getName() . ' = ' . $p->getDefaultValue() . "\n";
                }

                $entityCls->addPropertyFromGenerator($p);
            }

            $constructor = new MethodGenerator('__construct');
            $constructor->setBody($constructorBody);
            $entityCls->addMethodFromGenerator($constructor);

            $code = $entityCls->generate();
            $filePath = $this->entitiesPath . DIRECTORY_SEPARATOR . $clsName . '.php';
            file_put_contents($filePath, "<?php" . PHP_EOL . $code);
            echo "  " . $filePath . "\n";
        }
    }
}
