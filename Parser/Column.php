<?php


namespace TheliaStudio\Parser;
use Symfony\Component\DependencyInjection\Container;


/**
 * Class Column
 * @package TheliaStudio\Parser
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class Column
{
    private static $types = array(
        "BOOLEAN" => "bool",
        "TINYINT" => "bool",
        "SMALLINT" => "int",
        "INTEGER" => "int",
        "BIGINT" => "int",
        "DOUBLE" => "double",
        "FLOAT" => "double",
        "REAL" => "double",
        "DECIMAL" => "double",
        "CHAR" => "text",
        "VARCHAR" => "text",
        "LONGVARCHAR" => "textarea",
        "DATE" => "date",
        "TIME" => "time",
        "TIMESTAMP" => "datetime",
        "BLOB" => "binary",
        "CLOB" => "textarea",
        "OBJECT" => "object",
        "ARRAY" => "array",
    );

    private static $formTypes = array(
        "bool" => "checkbox",
        "int" => "integer",
        "double" => "number",
        "text" => "text",
        "textarea" => "textarea",
        "date" => "date",
        "time" => "time",
        "datetime" => "datetime",
    );
    
    protected $name;

    protected $type;

    /**
     * @var bool
     */
    protected $required;

    /**
     * @var bool
     */
    protected $i18n = false;

    public function __construct($name, $type, $required)
    {
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;
    }

    public function getFormType($default = null)
    {
        if (isset(static::$formTypes[$this->getPhpType()])) {
            return static::$formTypes[$this->getPhpType()];
        }

        return $default;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param mixed $required
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isI18n()
    {
        return $this->i18n;
    }

    /**
     * @param boolean $i18n
     * @return $this
     */
    public function setI18n($i18n)
    {
        $this->i18n = $i18n;
        return $this;
    }

    public function getNameAsSQL()
    {
        return strtoupper($this->name);
    }

    public function getCamelizedName()
    {
        return Container::camelize($this->name);
    }

    public function getPhpType()
    {
        if (isset(static::$types[$this->type])) {
            return static::$types[$this->type];
        }

        return false;
    }
}
