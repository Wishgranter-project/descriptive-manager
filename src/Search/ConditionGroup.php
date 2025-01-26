<?php

namespace WishgranterProject\DescriptiveManager\Search;

class ConditionGroup
{
    /**
     * @var string
     *   The logic operator: "AND" or "OR".
     */
    protected string $operator;

    /**
     * @var array
     *   Array of conditions.
     */
    protected array $conditions = [];

    /**
     * @var WishgranterProject\DescriptiveManager\Search\ConditionGroup[]
     *   Array of condition groups.
     */
    protected array $groups = [];

    /**
     * Constructor.
     *
     * @param string $operator
     *   The logic operator: "AND" or "OR".
     */
    public function __construct($operator = 'AND')
    {
        $this->operator = $operator;
    }

    /**
     * Return read only properties.
     *
     * @param string $propertyName
     *   Property name.
     */
    public function __get($var)
    {
        if (isset($this->{$var})) {
            return $this->{$var};
        }
    }

    /**
     * Add a new condition to this group.
     *
     * @param string[] $propertyPath
     *   A path to extract the actual value during evaluation.
     * @param mixed $valueToCompare
     *   The value for comparison.
     * @param string $operator
     *   The operator.
     *
     * @return WishgranterProject\DescriptiveManager\Search\ConditionGroup
     *   Returns self to chain in other methods.
     */
    public function condition($propertyPath, $valueToCompare, string $operatorId = '=')
    {
        $this->conditions[] = [
            'property'       => $propertyPath,
            'valueToCompare' => $valueToCompare,
            'operatorId'     => $operatorId
        ];

        return $this;
    }

    /**
     * Adds a new condition group ( nested inside this one ).
     *
     * @return WishgranterProject\DescriptiveManager\Search\ConditionGroup
     *   New condition group.
     */
    public function andConditionGroup(): ConditionGroup
    {
        $group = new ConditionGroup('AND');
        $this->groups[] = $group;
        return $group;
    }

    /**
     * Adds a new condition group ( nested inside this one ).
     *
     * @return WishgranterProject\DescriptiveManager\Search\ConditionGroup
     *   New condition group.
     */
    public function orConditionGroup(): ConditionGroup
    {
        $group = new ConditionGroup('OR');
        $this->groups[] = $group;
        return $group;
    }
}
