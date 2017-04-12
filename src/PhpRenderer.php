<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @link      https://github.com/slimphp/PHP-View
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/slimphp/PHP-View/blob/master/LICENSE.md (MIT License)
 */
/**
 * edited to add escaping and includes
 * All rights belong to the original creator
 * John Sayo - unibtc@gmail.com
*/
namespace Slim\Views;
use \InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use \Zend\Escaper\Escaper;
/**
 * Class PhpRenderer
 * @package Slim\Views
 *
 * Render PHP view scripts into a PSR-7 Response object
 */
class PhpRenderer
{
    /**
     * Zend Escaper
     */
    public $escaper;
    /**
     * @var string
     */
    protected $templatePath;
    /**
     * @var array
     */
    protected $attributes;
    /**
     * SlimRenderer constructor.
     *
     * @param string $templatePath
     * @param array $attributes
     */
    public function __construct($templatePath = "", $attributes = [])
    {
        $this->templatePath = rtrim($templatePath, '/\\') . '/';
        $this->attributes = $attributes;
        $this->escaper = new \Zend\Escaper\Escaper('utf-8');
    }
    /**
     * Render a template
     *
     * $data cannot contain template as a key
     *
     * throws RuntimeException if $templatePath . $template does not exist
     *
     * @param ResponseInterface $response
     * @param string             $template
     * @param array              $data
     *
     * @return ResponseInterface
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function render(ResponseInterface $response, $template, $data = [], $escape = null)
    {
       ob_start();
       $output = $this->fetch($template, $data, $escape);
       ob_end_clean();

       $response->getBody()->write($output);

       return $response;
    }
    /**
     * Get the attributes for the renderer
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
    /**
     * Set the attributes for the renderer
     *
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }
    /**
     * Add an attribute
     *
     * @param $key
     * @param $value
     */
    public function addAttribute($key, $value) {
        $this->attributes[$key] = $value;
    }
    /**
     * Retrieve an attribute
     *
     * @param $key
     * @return mixed
     */
    public function getAttribute($key) {
        if (!isset($this->attributes[$key])) {
            return false;
        }
        return $this->attributes[$key];
    }
    /**
     * Get the template path
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }
    /**
     * Set the template path
     *
     * @param string $templatePath
     */
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = rtrim($templatePath, '/\\') . '/';
    }
    public function escape($toescape,$type)
    {
        $arrHolder = array();
        foreach ($toescape as $key => $value) {
            $newarr = array();
            if(is_array($value)){
                $value = $this->escape($value,$type);
                $newarr = $value;
            }else{
                    switch ($type) {
                       case 'html':
                           $newarr = $this->escaper->escapeHtml($value);
                           break;
                       case 'attr':
                           $newarr = $this->escaper->escapeHtmlAttr($value);
                           break;
                        case 'js':
                           $newarr = $this->escaper->escapeJs($value);
                           break;
                        case 'css':
                           $newarr = $this->escaper->escapeCss($value);
                           break;
                        case 'url':
                           $newarr = $this->escaper->escapeUrl($value);
                           break;
                       default:
                          return $toescape;
                           break;
                    }
            }
           $arrHolder[$key] = $newarr;
        }
        return $arrHolder;
    }
    /**
     * Renders a template and returns the result as a string
     *
     * cannot contain template as a key
     *
     * throws RuntimeException if $templatePath . $template does not exist
     *
     * @param $template
     * @param array $data
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function fetch($template, array $data = [], $escape = null) {
        if (isset($data['template'])) {
            throw new \InvalidArgumentException("Duplicate template key found");
        }
        if (!is_file($this->templatePath . $template)) {
            throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        }
        $data = array_merge($this->attributes, $data);
        /**
         * Expose the merge data to $this->attributes
         * so that it is available via getAttribute/s
         */
        $this->attributes = $data;
        /**
         * escape data attributes before sending to page
         * Note: this will escape all values on an associative
         * array only and not their keys,
         * also works for nested arrays.
         */
        if(!is_null($escape))
        {
          $data = $this->escape($data,$escape);
        }
         try {
            $this->protectedIncludeScope($this->templatePath . $template, $data);
            $output = ob_get_contents();
        } catch(\Throwable $e) { // PHP 7+
            ob_end_clean();
            throw $e;
        } catch(\Exception $e) { // PHP < 7
            ob_end_clean();
            throw $e;
        }

        return $output;

    }

    /**
     * @param string $template
     * @param array $data
     */
    protected function protectedIncludeScope ($template, array $data) {
        extract($data);
       include $template;

    }
}