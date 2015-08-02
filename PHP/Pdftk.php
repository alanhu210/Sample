<?php
namespace Sample\Utils;

use Sample\Utils\PdfMappable;

class Pdftk
{
    /**
     * @var string console command for pdftk
     */
    protected $pdftk;

    /**
     * @var string pdf file name
     */
    protected $pdfFile;

    /**
     * @var PayerFormService entity to be mapped to pdf
     */
    protected $entity;

    /**
     * @var array field mappings for fillable pdf
     */
    protected $fields;

    /**
     * Constructor
     *
     * @param Sample\Utils\PdfMappable $entity
     * @param string|null $pdftk
     *
     * @throws \Exception
     */
    public function __construct(Sample\Utils\PdfMappable $entity, $pdftk = null)
    {
        $pdfFile = $entity->getPdfPath() . '/' . $entity->getPdf();
        if (!file_exists($pdfFile)) {
            throw new \Exception("File not found: " . $pdfFile, 1);
        }
        $fieldDumpFile = $pdfFile . '.txt';
        if (!file_exists($fieldDumpFile) && !is_writable($entity->getPdfPath())) {
            throw new \Exception("Field dump file missing", 1);
        }
        $this->pdfFile = $pdfFile;
        $this->entity = $entity;
        $this->pdftk = $pdftk == null ? exec('which pdftk') : $pdftk;
        $this->fields = null;
        if (empty($this->pdftk)) {
            throw new \Exception("pdftk is required", 1);
        }
    }

    /**
     * Send filled pdf to browser
     */
    public function outputPdf()
    {
        $pdfContent = $this->fillForm();
        if ($pdfContent == false) {
            echo _('Failed to create a PDF');
        }
        header('Content-type: application/pdf');
        print $pdfContent;
        exit;
    }

    /**
     * Fill the pdf form with FDF data
     *
     * @throws \Exception
     *
     * @return string of filled pdf on success, false on failure
     */
    public function fillForm()
    {
        $fdf = $this->getFdf();
        if (empty($fdf)) {
            throw new \Exception(_('Failed to build fdf'), 1);
        }
        $descriptorSpec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
        );
        $process = proc_open("{$this->pdftk} {$this->pdfFile} fill_form - output -", $descriptorSpec, $pipes);
        if (!is_resource($process)) {
            return false;
        }
        fwrite($pipes[0], $fdf);
        fclose($pipes[0]);
        $content = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        return -1 !== proc_close($process) ? $content : false;
    }

    /**
     * Get data for filling the form
     *
     * @return array
     */
    public function getFormData()
    {
        $mappings = $this->entity->getMappings();
        $fields = $this->getFormFields();
        $data = array();
        foreach ($fields as $name => $field) {
            $property = null;
            if (property_exists($this->entity, $name)) {
                $property = $name;
            } else if (isset($mappings[$name])) {
                $property = $mappings[$name];
            }
            if ($property != null) {
                $method = 'get' . ucfirst($property);
                if (!method_exists($this->entity, $method)) {
                    $method = 'is' . ucfirst($property);
                }
                if (!method_exists($this->entity, $method)) {
                    continue;
                }
                $val = $this->entity->$method();
                $data[$name] = $this->toPdfValue($val, $field, $property);
            }
        }

        return $data;
    }

    /**
     * Get form fields
     *
     * @return array
     */
    public function getFormFields()
    {
        if (empty($this->fields)) {
            $this->fields = array();
            $dumpfile = $this->pdfFile . '.txt';
            if (file_exists($dumpfile)) {
                $result = explode("\n", file_get_contents($dumpfile));
            } else {
                $result = $this->dumpDataFields();
                file_put_contents($dumpfile, implode("\n", $result));
            }

            for ($i = 0, $count = count($result); $i < $count; $i++) {
                $column = array('FieldStateOptions' => array());
                while ($i < $count && '---' !== $result[$i]) {
                    list($name, $value) = explode(':', $result[$i], 2);
                    if ($name == 'FieldStateOption') {
                        $column['FieldStateOptions'][] = trim($value);
                    } else {
                        $column[trim($name)] = trim($value);
                    }
                    $i++;
                }
                if (isset($column['FieldName'])) {
                    $this->fields[$column['FieldName']] = $column;
                }
            }
        }

        return $this->fields;
    }

    /**
     * Get dump of field information of pdf form
     *
     * @return array of strings returned by pdftk dump_data_fields execution.
     */
    protected function dumpDataFields()
    {
        exec("{$this->pdftk} {$this->pdfFile} dump_data_fields", $data, $return);

        return $data;
    }

    /**
     * Get FDF string for filling PDF form
     *
     * @throws \Exception if the field with this name not found in the PDF form
     *
     * @return string a FDF string with data
     */
    public function getFdf()
    {
        $data = $this->getFormData();
        $fields = $this->getFormFields();

        $fdf = "%FDF-1.2\n%âãÏÓ\n1 0 obj\n<</FDF << /Fields [ ";

        foreach ($data as $attribute => $val) {
            if (array_key_exists($attribute, $fields)) {
                $fdf .= '<</V(' . trim(self::escapeValue($val)) . ')/T(' . $attribute . ')>>';
            } else {
                throw new \Exception("Field not found in pdf form: $attribute", 1);
            }
        }
        $fdf .= "]>>>>\nendobj\ntrailer\n<</Root 1 0 R>>\n%%EOF";

        return $fdf;
    }

    /**
     * Escape PDF form value
     *
     * @param string $str
     *
     * @return string
     */
    static function escapeValue($str)
    {
        $str = (string) $str;
        $result = '';
        for ($i = 0, $strLen = strlen($str); $i < $strLen; ++$i) {
            if (ord($str{$i}) == 0x28 || ord($str{$i}) == 0x29 || ord($str{$i}) == 0x5c) {
                $result .= chr(0x5c) . $str{$i};
            } else if (ord($str{$i}) < 32 || 126 < ord($str{$i})) {
                $result .= sprintf("\\%03o", ord($str{$i}));
            } else {
                $result .= $str{$i};
            }
        }

        return $result;
    }

    /**
     * Converts property value to PDF form value
     *
     * @param  mixed $val
     * @param  array $metaData
     * @param string $property
     *
     * @return string
     */
    private function toPdfValue($val, array $metaData, $property)
    {
        if (is_object($val)) {
            $class = get_class($val);
            switch ($class) {
                case 'DateTime':
                    $val = $val->format($this->entity->getDateFormat());
                    break;
                default:
                    $val = 'Unknown';
            }
        } else if ($metaData['FieldType'] == 'Button') {
            if (is_string($val) && intval($val) == 1 || (is_bool($val) && $val == 1)) {
                $checked = array('Yes', 'y', 'Y', 'On');
                foreach ($checked as $key) {
                    if (in_array($key, $metaData['FieldStateOptions'])) {
                        $val = $key;
                        break;
                    }
                }
            } else if (is_string($val) && intval($val) == 0 || (is_bool($val) && $val == 0)) {
                $checked = array('No', 'n', 'N', 'Off');
                foreach ($checked as $key) {
                    if (in_array($key, $metaData['FieldStateOptions'])) {
                        $val = $key;
                        break;
                    }
                }
            }
        }

        $chars = $this->entity->charsReplacedWithSpace();
        if (array_key_exists($property, $chars)) {
            $val = str_replace($chars[$property], ' ', $val);
        }

        return $val;
    }
}
