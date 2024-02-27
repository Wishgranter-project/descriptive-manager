<?php

namespace WishgranterProject\DescriptiveManager\Search;

class ConditionGroup
{
    protected string $operator;

    protected array $conditions = [];

    protected array $groups = [];

    public function __construct($operator = 'AND')
    {
        $this->operator = $operator;
    }

    public function __get($var)
    {
        if (isset($this->{$var})) {
            return $this->{$var};
        }
    }

    public function condition($property, $valueToCompare, string $operatorId = '=')
    {
        $this->conditions[] = [
            'property'       => $property,
            'valueToCompare' => $valueToCompare,
            'operatorId'     => $operatorId
        ];

        return $this;
    }

    public function andConditionGroup()
    {
        $group = new ConditionGroup('AND');
        $this->groups[] = $group;
        return $group;
    }

    public function orConditionGroup()
    {
        $group = new ConditionGroup('OR');
        $this->groups[] = $group;
        return $group;
    }
}
