<?php
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2014 Ron Harwood and the BNT development team
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// File: classes/XmlTemplateSystem.php
//
// Name: XML DOM Template System
// Author: TheMightyDude (Paul Kirby)
// Version: 0.0.9 (0032) Development version.
// Created: March 15 2012
// Updated: August 12 2012
// Copyright: © 2012 Paul Kirby
//
// This file handles all the XML formatting and allows to validate the
// XML Format by using a DTD file, we also use a style sheet that uses
// the XML Data and then outputs as XHTML using a XSLT style sheet file.

namespace Bnt;

define('XMLDOM_PROCESSED', 0x0001);
define('XMLDOM_UNPROCESSED', 0x0002);
define('XMLDOM_XML', 0x0003);

class XmlTemplateSystem
{
    public static $version    = '0.0.9 (0032) [DEV]';
    public static $author     = 'TheMightyDude';
    private $qualifiedName    = 'root';
    private $document         = null;
    private $root             = null;
    private $validation       = false;
    private $initialized      = false;
    private $mode             = XMLDOM_XML;
    private $stylesheet       = null;

    public function __construct($publicId = null, $systemId = null)
    {
        $this->chkLibraries(array('xsl', 'dom'));

        $imp = new \DOMImplementation;
        if (!is_null($publicId) && !is_null($systemId))
        {
            $this->validation = (boolean) true;
            $dtd = $imp->createDocumentType($this->qualifiedName, $publicId, $systemId);
            $this->document = $imp->createDocument('', '', $dtd);
        }
        else
        {
            $this->validation = (boolean) false;
            $this->document = $imp->createDocument('', '');
        }
        $this->initialized = (boolean) false;
    }

    public function __destruct()
    {
        $this->releaseTemplate();
    }

    private function chkLibraries($libraries = null)
    {
        if (is_null($libraries) || !is_array($libraries))
        {
            return (boolean) false;
        }

        foreach ($libraries as $library)
        {
            $library = mb_strtolower($library);
            extension_loaded($library) or trigger_error("Class '{$library}' not found", E_USER_ERROR);
        }
    }

    public function initialize($encoding = 'iso-8859-1', $stylesheet = null, $comment = null)
    {
        $this->encoding($encoding);
        $this->StyleSheet($stylesheet);

        // Add a comment to the document
        if (!is_null($comment))
        {
            $this->document->appendChild(new \DOMComment($comment));
        }

        $this->root = $this->document->appendChild(new \DOMElement($this->qualifiedName));
        $this->standAlone(true);

        // Add Template class info.
        $this->addAttributes($this->root, array('version' => self::$version, 'author' => self::$author));
        $this->initialized = (boolean) true;
    }

    public function releaseTemplate()
    {
        unset($this->document);
        unset($this->error);
    }

    public function encoding($encoding = 'iso-8859-1')
    {
        $this->document->encoding = $encoding;
    }

    public function standAlone($standalone = false)
    {
        $this->document->standAlone = $standalone;
    }

    public function outputMode($mode = XMLDOM_XML)
    {
        switch ($mode)
        {
            case XMLDOM_XML:
                $this->mode = XMLDOM_XML;
                break;
            case XMLDOM_UNPROCESSED:
                $this->mode = XMLDOM_UNPROCESSED;
                break;
            case XMLDOM_PROCESSED:
                $this->mode = XMLDOM_PROCESSED;
                break;
            default:
                $this->mode = XMLDOM_PROCESSED;
                break;
        }
    }

    public function styleSheet($stylesheet = null)
    {
        if (!is_null($stylesheet))
        {
            if (file_exists($stylesheet) === false)
            {
                trigger_error("Style-Sheet: '{$stylesheet}' does not exist.", E_USER_ERROR);
            }

            if ($this->mode == XMLDOM_UNPROCESSED)
            {
                $this->document->appendChild($this->document->createProcessingInstruction('xml-stylesheet', "type=\"text/xsl\" href=\"$stylesheet\""));

            }
            else
            {
                $this->styleSheet = new \DOMDocument();
                $this->styleSheet->preserveWhiteSpace = true;
                $this->styleSheet->formatOutput = true;
                $ret = $this->styleSheet->load($stylesheet);
                if (!$ret)
                {
                    trigger_error("Unable to load style-sheet: '{$stylesheet}'", E_USER_ERROR);
                }
            }
        }
    }

    public function addNode($parent = null, $nodeName = null)
    {
        if (is_null($nodeName))
        {
            return (boolean) false;
        }

        if ($this->initialized === false)
        {
            trigger_error('Template System NOT initialized', E_USER_ERROR);
        }

        if (is_null($parent))
        {
            $parent = $this->root;
        }

        return $parent->appendChild(new \DOMElement($nodeName));
    }

    public function getNode($parent = null, $nodeName = null, &$node = null)
    {
        $node = null;
        if (is_null($nodeName))
        {
            return (boolean) false;
        }

        if ($this->initialized === false)
        {
            trigger_error('Template System NOT initialized', E_USER_ERROR);
        }

        if (is_null($parent))
        {
            $parent = $this->root;
        }

        $nodeList = $parent->childNodes;
        for ($i = 0; $i < $nodeList->length; $i++)
        {
            if ($nodeList->item($i)->nodeName === $nodeName)
            {
                $node = $nodeList->item($i);
                break;
            }
        }

        if (is_null($node) === true)
        {
            return (boolean) false;
        }
        else
        {
            return (boolean) true;
        }
    }

    public function addNodeArray($parent = null, $nodeName = null, $nodeData = null)
    {
        if (is_null($nodeName))
        {
            return (boolean) false;
        }

        if ($this->initialized === false)
        {
            trigger_error('Template System NOT initialized', E_USER_ERROR);
        }

        if (is_null($parent))
        {
            $parent = $this->root;
        }

        $count = (int) count($nodeData);
        for ($i = 0; $i < $count; $i++)
        {
            $node = $parent->appendChild(new \DOMElement($nodeName));
            foreach ($nodeData[$i] as $name => $value)
            {
                $node->setAttributeNode(new \DOMAttr($name, $value));
            }
        }

        return (boolean) true;
    }

    public function addAttribute($node = null, $name = null, $value = null)
    {
        $return = (boolean) false;
        if (!is_null($node) && !is_null($name))
        {
            $node->setAttributeNode(new \DOMAttr($name, $value));
            $return = (boolean) true;
        }

        return $return;
    }

    public function addAttributes($node = null, $array = null)
    {
        $return = (boolean) false;
        if (!is_null($node) && !is_null($array) && is_array($array))
        {
            foreach ($array as $name => $value)
            {
                $node->setAttributeNode(new \DOMAttr($name, $value));
            }
            $return = (boolean) true;
        }

        return $return;
    }

    public function addComment($node = null, $comment = null)
    {
        if (is_null($comment))
        {
            return (boolean) false;
        }

        if ($this->initialized === false)
        {
            trigger_error('Template System NOT initialized', E_USER_ERROR);
        }

        if (is_null($node))
        {
            $node = $this->root;
        }

        $node->appendChild(new \DOMComment($comment));

        return (boolean) true;
    }

    public function addCDATASection($node = null, $cdata = null)
    {
        if (is_null($cdata))
        {
            return (boolean) false;
        }

        if ($this->initialized === false)
        {
            trigger_error('Template System NOT initialized', E_USER_ERROR);
        }

        if (is_null($node))
        {
            $node = $this->root;
        }

        $node->appendChild(new \DOMCdataSection($cdata));

        return (boolean) true;
    }

    public function addTextNode($node = null, $text = null)
    {
        if (is_null($text))
        {
            return (boolean) false;
        }

        if ($this->initialized === false)
        {
            trigger_error('Template System NOT initialized', E_USER_ERROR);
        }

        if (is_null($node))
        {
            $node = $this->root;
        }
        $node->appendChild(new \DOMText($text));

        return (boolean) true;
    }

    public function display()
    {
        if ($this->initialized === false)
        {
            trigger_error('Template System NOT initialized', E_USER_ERROR);
        }

        if (@$this->document->validate() || $this->validation == false)
        {
            header('Cache-Control: no-cache, must-revalidate');

            if ($this->mode == XMLDOM_XML)
            {
                header('Content-Type: application/xml');
                $this->document->preserveWhiteSpace = false;
                $this->document->formatOutput = true;

                // Hash the content, so that we can validate the client end.
                header('Content-Hash: '. sha1($this->document->saveXML()));

                return $this->document->saveXML();
            }
            elseif ($this->mode == XMLDOM_PROCESSED)
            {
                if (is_null($this->stylesheet))
                {
                    trigger_error('styleSheet Not Set.', E_USER_ERROR);
                }

//              header("Content-Type: text/html; charset={$this->document->encoding}");
                header('Content-Type: text/html');
                $proc = new \XSLTProcessor;
                $proc->setProfiling('logs/profiling.txt');
                @$proc->importStyleSheet($this->stylesheet); // attach the xsl rules
                echo $proc->transformToXML($this->document);
            }
            elseif ($this->mode == XMLDOM_UNPROCESSED)
            {
//              header('Content-Type: application/xml; charset={$this->document->encoding}');
                header('Content-Type: application/xml');
                echo $this->document->saveXML();
            }
            else
            {
                trigger_error('Output Mode NOT SUPPORTED', E_USER_ERROR);
            }
        }
        else
        {
            trigger_error('Failed to Validate', E_USER_ERROR);
        }
    }
}
?>
