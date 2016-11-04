<?php
namespace dncsoftworks;

/**
 * Class ArrayOfObject
 *
 * Abstract class that allows the creation of "typed" arrays. Based on the name of the
 * class that extends ArrayOfObject, it creates an object that is accessible as an array
 * and accepts only instances of a particular class.
 *
 * The purpose of this class is to add semantical meaning to array typehinting in PHP
 * This is useful when you want to check if a parameter you're receiving is an actual
 * array of a specific class.
 *
 * For example, if you wanted to do the following:
 *
 * class Foo {}
 *
 * function printFooList(Foo[] $fooList) {
 *     // ... code ...
 * }
 *
 * Unfortunately, the type hint "Foo[]" is not allowed in PHP. So, you could extend
 * this class in order to emulate this behavior:
 *
 * class Foo {}
 *
 * class ArrayOfFoo extends ArrayOfObject {}
 *
 * function printFooList(ArrayOfFoo $fooList) {
 *     // ... code ...
 * }
 *
 * @package app\components
 */
abstract class ArrayOfObject extends \ArrayObject
{
    /**
     * @var string The target class name
     */
    private $className;

    /**
     * @var string The current full class name
     */
    private $fullClassName;

    /** @inheritdoc */
    public function __construct($input = array(), $flags = 0, $iterator_class = "ArrayIterator")
    {
        $this->validateClassName();

        $this->isValidArray($input);

        parent::__construct($input, $flags, $iterator_class);
    }

    private function isValidArray($input)
    {
        if (is_array($input)) {
            foreach ($input as $value) {
                $this->isValidInput($value);
            }
        } else {
            throw new \InvalidArgumentException(
                $this->fullClassName . ' accepts only arrays of instances of ' . $this->className
            );
        }
    }

    /**
     * Validates the current class name to determine whether the target class exists
     */
    private function validateClassName()
    {
        $this->fullClassName = $fullClassName = get_class($this);

        if (strpos($fullClassName, '\\') !== false) {
            $fullClassName = substr($fullClassName, (int)strrpos($fullClassName, '\\') + 1);
        }


        if (strpos($fullClassName, 'ArrayOf') !== 0) {
            throw new \InvalidArgumentException('Class name must begin with "ArrayOf"');
        }

        $className = str_replace('ArrayOf', '', $this->fullClassName);

        $isVariableValidClassName = class_exists($className);
        $isReturnValidClassName = class_exists((string)$this->className());

        if ($isVariableValidClassName) {
            $this->className = $className;
        } elseif ($isReturnValidClassName) {
            $this->className = $this->className();
        } else {
            throw new \InvalidArgumentException('Class name must mention a valid class or className() must return a valid class name');
        }

    }

    /**
     * Checks if $value is an instance of the target class
     *
     * @param $value
     *
     * @throws \InvalidArgumentException
     */
    private function isValidInput($value) {
        if (!($value instanceof $this->className)) {
            throw new \InvalidArgumentException($this->fullClassName . ' accepts only instances of ' . $this->className);
        }
    }

    /**
     * If the class name does not contain the target class name, this method
     * should be implemented in order to return the target class name;
     *
     * @return string
     */
    protected function className()
    {
        return '';
    }

    /** @inheritdoc */
    public function offsetSet($index, $newval) {
        $this->isValidInput($newval);
        parent::offsetSet($index, $newval);
    }

    /** @inheritdoc */
    public function append($value)
    {
        $this->isValidInput($value);
        parent::append($value);
    }

    public function exchangeArray($input)
    {
        $this->isValidArray($input);
        return parent::exchangeArray($input);
    }


}
