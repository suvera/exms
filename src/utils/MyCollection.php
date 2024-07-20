<?php

namespace dev\suvera\exms\utils;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class MyCollection extends ArrayCollection implements \JsonSerializable {

    // constrcutor

    public function __construct(?Collection $elements = null) {
        if ($elements) {
            foreach ($elements as $element) {
                $this->add($element);
            }
        }
    }

    public function jsonSerialize(): mixed {
        return $this->toArray();
    }
}
