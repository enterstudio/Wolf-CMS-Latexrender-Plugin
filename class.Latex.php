<?php
/**
 * LaTeX Rendering Class - Calling function
 * Copyright (C) 2003  Benjamin Zeiss <zeiss@math.uni-goettingen.de>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * --------------------------------------------------------------------
 * @author Benjamin Zeiss <zeiss@math.uni-goettingen.de>
 * @version v0.8
 * @package latexrender
 * Revised by Steve Mayer
 * This file can be included in many PHP programs by using something like (see example.php to see how it can be used)
 * 		include_once('/full_path_here_to/latexrender/latex.php');
 * 		$text_to_be_converted=latex_content($text_to_be_converted);
 * $text_to_be_converted will then contain the link to the appropriate image
 * or an error code as follows (the 500 values can be altered in class.latexrender.php):
 * 	0 OK
 * 	1 Formula longer than 500 characters
 * 	2 Includes a blacklisted tag
 * 	3 (Not used) Latex rendering failed
 * 	4 Cannot create DVI file
 * 	5 Picture larger than 500 x 500 followed by x x y dimensions
 * 	6 Cannot copy image to pictures directory
 */

class Latex
{

    public static $_picture_path = "/home/webhost/wolfems/wolf/plugins/latexrender/pictures";
    public static $_picture_path_httpd = "http://dragon155.startdedicated.com/wolfems/wolf/plugins/latexrender/pictures";
    public static $_tmp_dir = "/home/webhost/wolfems/wolf/plugins/latexrender/tmp";
    // i was too lazy to write mutator functions for every single program used
    // just access it outside the class or change it here if nescessary
    public static $_latex_path = "/usr/bin/latex";
    public static $_dvips_path = "/usr/bin/dvips";
    public static $_convert_path = "/usr/bin/convert";
    public static $_identify_path = "/usr/bin/identify";
    public static $_formula_density = 120;
    public static $_xsize_limit = 500;
    public static $_ysize_limit = 500;
    public static $_string_length_limit = 500;
    public static $_font_size = 10;
    public static $_tmp_filename;
    public static $_latexclass = "article"; //install extarticle class if you wish to have smaller font sizes
    public static $_image_format = "png"; //change to png if you prefer
    // this most certainly needs to be extended. in the long term it is planned to use
    // a positive list for more security. this is hopefully enough for now. i'd be glad
    // to receive more bad tags !


public static function latex_content($text) {
    // --------------------------------------------------------------------------------------------------
    // adjust this to match your system configuration
    $latexrender_path = CORE_ROOT."/plugins/latexrender";
    $latexrender_path_http = PLUGINS_URI."/latexrender";

    // --------------------------------------------------------------------------------------------------

    include_once($latexrender_path."/class.LatexRender.php");

    preg_match_all("#\[tex\](.*?)\[/tex\]#si",$text,$tex_matches);

    $latex = new LatexRender($latexrender_path."/pictures",$latexrender_path_http."/pictures",$latexrender_path."/tmp");

    // i was too lazy to write mutator functions for every single program used
    // just access it outside the class or change it here if nescessary
    $latex->_latex_path = self::$_latex_path;
    $latex->_dvips_path = self::$_dvips_path;
    $latex->_convert_path = self::$_convert_path;
    $latex->_identify_path = self::$_identify_path;
    $latex->_formula_density = self::$_formula_density;
    $latex->_xsize_limit = self::$_xsize_limit;
    $latex->_ysize_limit = self::$_ysize_limit;
    $latex->_string_length_limit = self::$_string_length_limit;
    $latex->_font_size = self::$_font_size;
    $latex->_latexclass = self::$_latexclass; //install extarticle class if you wish to have smaller font sizes
    $latex->_image_format = self::$_image_format; //change to png if you prefer
    // this most certainly needs to be extended. in the long term it is planned to use
    // a positive list for more security. this is hopefully enough for now. i'd be glad
    // to receive more bad tags !
    $latex->_picture_path = self::$_picture_path;
    $latex->_picture_path_httpd = self::$_picture_path_httpd;
    $latex->_tmp_dir = self::$_tmp_dir;


    for ($i=0; $i < count($tex_matches[0]); $i++) {
        $pos = strpos($text, $tex_matches[0][$i]);
        $latex_formula = $tex_matches[1][$i];

	// if you use htmlArea to input the text then uncomment the next 6 lines
	//	$latex_formula = str_replace("&amp;","&",$latex_formula);
	//	$latex_formula = str_replace("&#38;","&",$latex_formula);
	//	$latex_formula = str_replace("&nbsp;"," ",$latex_formula);
	//	$latex_formula = str_replace("<BR>","",$latex_formula);
	//	$latex_formula = str_replace("<P>","",$latex_formula);
	//	$latex_formula = str_replace("</P>","",$latex_formula);

        $url = $latex->getFormulaURL($latex_formula);

		$alt_latex_formula = htmlentities($latex_formula, ENT_QUOTES);
		$alt_latex_formula = str_replace("\r","&#13;",$alt_latex_formula);
		$alt_latex_formula = str_replace("\n","&#10;",$alt_latex_formula);

        if ($url != false) {
            $text = substr_replace($text, "<img src='".$url."' title='".$alt_latex_formula."' alt='".$alt_latex_formula."' align=absmiddle>",$pos,strlen($tex_matches[0][$i]));
        } else {
            $text = substr_replace($text, "[Unparseable or potentially dangerous latex formula. Error $latex->_errorcode $latex->_errorextra]",$pos,strlen($tex_matches[0][$i]));
        }
    }
    exec("chmod -R 0755 ".$latexrender_path."/pictures/*");
    return $text;
}

}

?>
