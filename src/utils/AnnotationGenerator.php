<?php

namespace dev\suvera\exms\utils;

use Laminas\Code\Generator\DocBlockGenerator;

class AnnotationGenerator extends DocBlockGenerator {

    protected array $annotations = [];

    public function __construct() {
    }

    public function getAnnotations(): array {
        return $this->annotations;
    }

    public function addAnnotation(string $name, array $properties) {
        $this->annotations[$name] = $properties;
        return $this;
    }

    public function addAnnotationProperty(string $name, array $propName, mixed $propValue) {
        if (!isset($this->annotations[$name])) {
            $this->annotations[$name] = [];
        }
        $this->annotations[$name][$propName] = $propValue;
        return $this;
    }

    public function generate() {
        $indent  = $this->getIndentation();

        $output = '';
        foreach ($this->annotations as $name => $properties) {
            $output .= $indent . '#[' . $name;

            if (count($properties) > 0) {
                $output .= '(';
                $idx = 0;
                foreach ($properties as $propName => $propValue) {
                    $idx++;
                    $output .= (($idx == 1) ? '' : ', ') . $propName . ':';
                    switch (gettype($propValue)) {
                        case 'integer':
                        case 'double':
                            $output .= $propValue;
                            break;
                        case 'boolean':
                            $output .= $propValue ? 'true' : 'false';
                            break;
                        case 'array':
                            $output .= var_export($propValue, true);
                            break;
                        case 'NULL':
                            $output .= 'null';
                            break;
                        default:
                            $output .= '"' . $propValue . '"';
                    }
                }
                $output .= ')';
            }

            $output .= ']' . self::LINE_FEED;
        }

        return $output;
    }
}
