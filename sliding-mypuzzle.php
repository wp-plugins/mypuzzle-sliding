<?php
/*
Plugin Name: MyPuzzle - Sliding
Plugin URI: http://mypuzzle.org/sliding/wordpress.html
Description: Include a mypuzzle.org Sliding Puzzle in your blogs with just one shortcode. 
Version: 1.0.0
Author: tom@mypuzzle.org
Author URI: http://mypuzzle.org/
Notes    : Visible Copyrights and Hyperlink to mypuzzle.org required
*/


/*  Copyright 2012  tom@mypuzzle.org  (email : tom@mypuzzle.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include_once("sliding-plugin.php");

/**
 * Default Options
 */
function get_sliding_mp_options ($default = false){
	$shc_default = array(
            'size' => '300',
            'pieces' => '3',
            'showhints' => '0',
            'image' => 'slide-3x3.jpg',
            'bgcolor' => '#ffffff',
            'myimage' => '',
            'showlink' => '0'
            );
	if ($default) {
		update_option('shc_op', $shc_default);
		return $shc_default;
	}
	
	$options = get_option('shc_op');
	if (isset($options))
		return $options;
	update_option('shc_op', $shc_default);
	return $options;
}

/**
 * The Sortcode
 */
 
add_shortcode('sliding-mp', 'sliding_mp');

function sliding_mp($atts) {
	global $post;
	$options = get_sliding_mp_options();	
	
	$size = $options['size'];
	$pieces = $options['pieces'];
        $showhints = $options['showhints'];
        $image = $options['image'];
        $bgcolor = $options['bgcolor'];
        $myimage = $options['myimage'];
        $showlink = $options['showlink'];

	extract(shortcode_atts(array(
                'size' => $size,
                'pieces' => $pieces,
                'showhints' => $showhints,
                'image' => $image,
                'bgcolor' => $bgcolor,
                'myimage' => $myimage,
		'showlink' => $showlink,
	), $atts));
        $flash = plugins_url('sliding-plugin.swf', __FILE__);
        
        if ($pieces == '3') $image = 'slide-3x3.jpg';
        if ($pieces == '4') $image = 'slide-4x4.jpg';
        if ($pieces == '5') $image = 'slide-5x5.jpg';
        
        $mySlider = new slider();
        if ($myimage != '')
        {
            $myPic = $mySlider->getResizedImage($myimage, true);
            $showlink = 1;
        }
        else
            $myPic = $mySlider->getResizedImage($image, false);
    
        $output = "<div style='width:".$size."px'>";
        $output .= "<object id='myFlash' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000'";
	$output .= " codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0'";
	$output .= " width='".$size."' height='".$size."' align='middle'>\r";  
	$output .= "<param name='allowScriptAccess' value='sameDomain' />\r";
	$output .= "<param name='allowFullScreen' value='false' />\r";
	$output .= "<param name='movie' value='".$flash."' />\r";
	$output .= "<param name='flashvars' value='myHint=".$showhints."&myPieces=".$pieces."&myPic=" . $myPic . "' />\r";
	$output .= "<param name='quality' value='high' />\r";
	$output .= "<param name='menu' value='false' />\r";
	$output .= "<param name='bgcolor' value='".$bgcolor."' />\r";
	$output .= "<embed src='".$flash."' flashvars='myHint=".$showhints."&myPieces=".$pieces."&myPic=" . $myPic . "' quality='high' bgcolor='".$bgcolor."'  swLiveConnect='true' ";
	$output .= "    width='".$size."' height='".$size."' name='jigsaw' menu='false' align='middle' allowScriptAccess='sameDomain' ";
	$output .= "    allowFullScreen='false' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' />\r";
	$output .= "</object>\r";
        if ($showlink == 1)
        {
            $output .= "<br/><a style=\"font-size: 10px\" href=\"http://mypuzzle.org/\">Puzzle Games</a>";
        }
        $output .= "</div>";
        
        return($output);

}
/**
 * Settings
 */  

add_action('admin_menu', 'sliding_mp_set');

function sliding_mp_set() {
	$plugin_page = add_options_page('MyPuzzle Sliding', 'MyPuzzle Sliding', 'administrator', 'sudoku-sliding', 'sliding_mp_options_page');		
 }

function sliding_mp_options_page() {

	$options = get_sliding_mp_options();
	
    if(isset($_POST['Restore_Default']))	$options = get_sliding_mp_options(true);	?>

	<div class="wrap">   
	
	<h2><?php _e("MyPuzzle - Sliding Puzzle Settings") ?></h2>
	
	<?php 

	if(isset($_POST['Submit'])){
                $newoptions['showlink'] = isset($_POST['showlink'])?$_POST['showlink']:$options['showlink'];
     		$newoptions['size'] = isset($_POST['size'])?$_POST['size']:$options['size'];
     		$newoptions['pieces'] = isset($_POST['pieces'])?$_POST['pieces']:$options['pieces'];
                $newoptions['showhints'] = isset($_POST['showhints'])?$_POST['showhints']:$options['showhints'];
                $newoptions['image'] = isset($_POST['image'])?$_POST['image']:$options['image'];
                $newoptions['bgcolor'] = isset($_POST['bgcolor'])?$_POST['bgcolor']:$options['bgcolor'];
                $newoptions['myimage'] = isset($_POST['myimage'])?$_POST['myimage']:$options['myimage'];
                
                if ( $options != $newoptions ) {
                        $options = $newoptions;
                        update_option('shc_op', $options);			
                }

 	} 

	if(isset($_POST['Use_Default'])){
        update_option('shc_op', $options);
    }
        $showlink = $options['showlink'];
	$size = $options['size'];
	$pieces = $options['pieces'];
        $showhints = $options['showhints'];
        $image = $options['image'];
        $bgcolor = $options['bgcolor'];
        $myimage = $options['myimage'];
        
	?>
        <form method="POST" name="options" target="_self" enctype="multipart/form-data">
	<h3><?php _e("Sliding Puzzle Parameters") ?></h3>
	
        <table width="" border="0" cellspacing="10" cellpadding="0">
            <tr>
                <td width="100">
                    Insert Link
                </td>
                <td>
                    <select name="showlink" id="showlink" style="width: 100px">
                            <option value="1"<?php echo ($showlink == 1 ? " selected" : "") ?>><?php echo _e("Yes") ?></option>
                            <option value="0"<?php echo ($showlink == 0 ? " selected" : "") ?>><?php echo _e("No") ?></option>
                    </select>
                </td>
                <td width="500">
                    If you somehow like the plugin, we would happy if you enable the link to us. Many Thanks!
                </td>
            </tr>
            <tr>
                <td width="100">
                    Size in px
                </td>
                <td>
                    <input style="width: 100px" type="text" name="size" value="<?php echo ($size); ?>">
                    
                </td>
                <td width="500"></td>
            </tr>
            <tr>
                <td width="50">
                    Pieces count
                </td>
                <td>
                    <select name="pieces" id="pieces" style="width: 100px">
                            <option value="3"<?php echo ($pieces == 3 ? " selected" : "") ?>><?php echo _e("3x3") ?></option>
                            <option value="4"<?php echo ($pieces == 4 ? " selected" : "") ?>><?php echo _e("4x4") ?></option>
                            <option value="5"<?php echo ($pieces == 5 ? " selected" : "") ?>><?php echo _e("5x5") ?></option>
                            
                    </select>
                </td>
                <td width="200"></td>
            </tr>
            <tr>
                <td width="100">
                    Show Hints
                </td>
                <td>
                    <select name="showhints" id="showhint" style="width: 100px">
                            <option value="0"<?php echo ($showhints == 0 ? " selected" : "") ?>><?php echo _e("No") ?></option>
                            <option value="1"<?php echo ($showhints == 1 ? " selected" : "") ?>><?php echo _e("Yes") ?></option>
                    </select>
                </td>
                <td width="200"></td>
            </tr>
            <tr>
                <td width="100">
                    Background Color
                </td>
                <td>
                    <input style="width: 100px" type="text" name="bgcolor" value="<?php echo ($bgcolor); ?>">
                </td>
                <td width="200"></td>
            </tr>
            <tr>
                <td width="100">
                    Image Url
                </td>
                <td>
                    <input style="width: 200px" type="text" name="myimage" value="<?php echo ($myimage); ?>">
                </td>
                <td width="500">
                    Only available with link option.
                </td>
            </tr>
            
        </table>
        
        <p class="submit">
            <input type="submit" name="Submit" value="Update" class="button-primary" />
        </p>
        </form>
    </div>


<?php } 

