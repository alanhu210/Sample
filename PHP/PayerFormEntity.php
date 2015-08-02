<?php
namespace Sample\ORM\Doctrine;

use Sample\ORM\Doctrine\SampleEntity;
use Sample\Utils\PdfMappable;

/**
 * Super class for payer form model classes
 * Also an adapter class providing default implementation of the PdfMappable interface
 */
abstract class PayerFormEntity extends SampleEntity implements PdfMappable
{

    private $transaction;

    protected $pdfPath;

    /**
     * health number not to be saved
     * @var string
     */
    protected $personalHealthNumber;

    public function __construct()
    {
        $this->entityName = get_class($this);
    }

    abstract public function getId();

    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
    }

    public function getPersonalHealthNumber()
    {
        return $this->personalHealthNumber;
    }

    public function setPersonalHealthNumber($personalHealthNumber)
    {
        $this->personalHealthNumber = $personalHealthNumber;
    }

    /**
     * @return \Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    public function setPdfPath($pdfPath)
    {
        $this->pdfPath = $pdfPath;
    }

    /**
     * {@inheritDoc}
     */
    public function getPdfPath()
    {
        if (empty($this->pdfPath)) {
            $this->pdfPath = dirname($GLOBALS['inc_path']) . "/pdf/payer_form";
        }

        return $this->pdfPath;
    }

    /**
     * {@inheritDoc}
     */
    public function getPdf()
    {
        $reflection = new \ReflectionClass(get_class($this));
        $className = $reflection->getShortName();
        $insuranceFormMeta = DoctrineConnection::$entity->getRepository('Sample\ORM\Doctrine\Models\InsuranceFormMeta')
            ->findOneBy(array('entityName' => $className));

        return is_object($insuranceFormMeta) ? $insuranceFormMeta->getEntityName() . '.pdf' : null;
    }

    /**
     * Persists a PayerFormService instance from post data
     * A new instance is created if not existent
     *
     * @param  array $post
     *
     * @return PayerFormService - instance of subclass
     */
    public function saveFromPost(array $post)
    {
        if (empty($this->entityName)) {
            $this->entityName = get_class($this);
        }
        foreach ($post as $key => $value) {
            if ($value == 'on') {
                $post[$key] = 1;
            }
        }
        $methodNames = get_class_methods($this);

        foreach ($methodNames as $method) {
            if (strncmp($method, 'set', 3) == 0) {
                $var = lcfirst(substr($method, 3));

                if (array_key_exists($var, $post)) {
                    $val = $this->getPropertyValue($var, $post[$var]);
                    $this->$method($val);
                }
            }
        }
        DoctrineConnection::$entity->persist($this);

        return $this;
    }

    /**
     * Determine the property type and return a value of correct type
     *
     * @param  string $var
     * @param  string $val
     *
     * @return mixed
     */
    protected function getPropertyValue($var, $val)
    {
        static $propertyTypes = null;
        if ($propertyTypes == null) {
            $propertyTypes = array();
            $refClass = new \ReflectionClass($this->entityName);
            foreach ($refClass->getProperties() as $refProperty) {
                if (preg_match('/@var\s+([^\s]+)/', $refProperty->getDocComment(), $matches)) {
                    list(, $type) = $matches;
                    $propertyTypes[$refProperty->getName()] = $type;
                }
            }
        }
        switch ($propertyTypes[$var]) {
            case 'date':
            case '\DateTime':
            case 'DateTime':
                if ($val) {
                    $val = new \DateTime($val);
                } else {
                    $val = null;
                }
                break;
            default:
                break;
        }

        return $val;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateFormat()
    {
        return 'Y-m-d';
    }

    /**
     * {@inheritDoc}
     */
    public function charsReplacedWithSpace()
    {
        return array();
    }
}
