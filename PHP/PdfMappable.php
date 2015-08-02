<?php
namespace Sample\Utils;

/**
 * A class implementing this interface should have its properties mapped to the fields of a fillable pdf form
 */
interface PdfMappable
{
    /**
     * @return string path of the directory containing the pdf files which is writable by web server
     */
    public function getPdfPath();

    /**
     * @return string pdf file name
     */
    public function getPdf();

    /**
     * @return array field name to property mapping if they differ
     */
    public function getMappings();

    /**
     * @return string date format
     */
    public function getDateFormat();

    /**
     * @return array list of chars to be replaced by a space. format: [propertyName=>[string1, string2]]
     */
    public function charsReplacedWithSpace();
}
