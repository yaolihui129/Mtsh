<?php
namespace Jira\Controller;
class PublicController extends BaseController
{
    public static function editor($textareaid = 'content', $value = '', $toolbar = 'desc', $height = 200, $color = '', $up = true)
    {
        $str = '<textarea id="' . $textareaid . '" name="' . $textareaid . '">' . $value . '</textarea>';
        if (!defined('EDITOR_INIT')) {
            $str .= '<script type="text/javascript" src="https://xiuliguanggao.com/Public/js/ckeditor/ckeditor.js"></script>';
            define('EDITOR_INIT', 1);
        }

        if ($toolbar == 'basic') {
            $toolbar = "['Bold', 'Italic','Underline','Strike','NumberedList', 'BulletedList', 'TextColor','BGColor', 'Link', 'Unlink', '-', 'Image','Flash','Table','Smiley','SpecialChar'],['RemoveFormat'],
		   \r\n";
        } elseif ($toolbar == 'full') {
            $toolbar = "['Source','-','Templates'],
		    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print'],
		    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],['ShowBlocks'],['Image','Capture','Flash'],['Maximize'],
		    '/',
		    ['Bold','Italic','Underline','Strike','-'],
		    ['Subscript','Superscript','-'],
		    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
		    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		    ['Link','Unlink','Anchor'],
		    ['Table','HorizontalRule','Smiley','SpecialChar'],
		    '/',
		    ['Styles','Format','Font','FontSize'],
		    ['TextColor','BGColor'],
		    ['attachment'],\r\n";

        } elseif ($toolbar == 'desc') {
            $toolbar = "['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'Image', '-','Source'],\r\n";

        } else {
            $toolbar = '';
        }
        $str .= "<script type=\"text/javascript\">\r\n";
        $str .= "CKEDITOR.replace( '$textareaid',{";
        $str .= "height:{$height},";
        if ($color) {
            $str .= "extraPlugins : 'uicolor',uiColor: '$color',";
        }

        if ($up) {
            $str .= "filebrowserImageUploadUrl:'" . B_URL . "&a=upimage',";
            $str .= "filebrowserFlashUploadUrl:'" . B_URL . "&a=upflash',";
        }
        $str .= "toolbar :\r\n";
        $str .= "[\r\n";
        $str .= $toolbar;
        $str .= "]\r\n";
        //$str .= "fullPage : true";
        $str .= "});\r\n";
        $str .= '</script>';
        return $str;
    }

}