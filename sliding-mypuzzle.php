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
            'size' => '460',
            'pieces' => '3',
            'showhints' => '0',
            'image' => 'slide-3x3.jpg',
            'bgcolor' => '#ffffff',
            'myimage' => '',
            'gallery' => 'wp-content/plugins/mypuzzle-sliding/gallery/',
            'showlink' => '0',
            'doresize' => '0'
            );
	if ($default) {
		update_option('sliding_mp_set', $shc_default);
		return $shc_default;
	}
	
	$options = get_option('sliding_mp_set');
	if (isset($options))
		return $options;
	update_option('sliding_mp_set', $shc_default);
	return $options;
}

/**
 * The Sortcode
 */
add_action('wp_enqueue_scripts', 'sliding_mp_jscripts');
add_shortcode('sliding-mp', 'sliding_mp');


function sliding_mp_jscripts() {
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
    wp_enqueue_script( 'jquery' );
    
    //my jscripts
    wp_register_script('mp-sliding-js', plugins_url('/js/sliding_plugin.js', __FILE__));
    wp_enqueue_script('mp-sliding-js');
    wp_register_script('mp-sliding-pop', plugins_url('/js/jquery.bpopup-0.7.0.min.js', __FILE__));
    wp_enqueue_script('mp-sliding-pop');
    
    //my styles
    wp_register_style( 'mp-sliding-style', plugins_url('/css/sliding-plugin.css', __FILE__) );
    wp_enqueue_style( 'mp-sliding-style' );
}    
 
function sliding_mp_testRange($int,$min,$max) {     
    return ($int>=$min && $int<=$max);
}

function sliding_mp($atts) {
	global $post;
	$options = get_sliding_mp_options();	
	
        //get options, or set default
	$size = $options['size'];
        if (!is_numeric($size) || !sliding_mp_testRange(intval($size),100,1500)) {$size=460;}
        $h = $size * 500 / 460;
	$pieces = $options['pieces'];
        if (!is_numeric($pieces) || !sliding_mp_testRange(intval($pieces),3,5)) {$pieces=3;}
        $showhints = $options['showhints'];
        if (!is_numeric($showhints) || !sliding_mp_testRange(intval($showhints),0,1)) {$showhints=1;}
        $image = $options['image'];
        $bgcolor = $options['bgcolor'];
        $bgcolor = str_replace('#', '', $bgcolor);
        if (!preg_match('/^[a-f0-9]{6}$/i', $bgcolor)) $bgcolor = 'FFFFFF';
        $myimage = $options['myimage'];
        $showlink = $options['showlink'];
        if (!is_numeric($showlink) || !sliding_mp_testRange(intval($showlink),0,1)) {$showlink=0;}
        $gallery = $options['gallery'];
        if (!$gallery || $gallery=='') {
            $gallery = 'wp-content/plugins/mypuzzle-sliding/gallery';
        } else {
            if (substr( $gallery, 0, 1 ) === '/') $gallery = substr( $gallery, 1 );
            if (substr( $gallery, strlen($gallery)-1, 1 ) === '/') $gallery = substr( $gallery, 0, strlen($gallery)-1 );
        }
        $doresize = $options['doresize'];
        if (!is_numeric($doresize) || !sliding_mp_testRange(intval($doresize),0,1)) {$doresize=0;}

	extract(shortcode_atts(array(
                'size' => $size,
                'pieces' => $pieces,
                'showhints' => $showhints,
                'image' => $image,
                'bgcolor' => $bgcolor,
                'myimage' => $myimage,
                'gallery' => $gallery,
		'showlink' => $showlink,
                'doresize' => $doresize
	), $atts));
        
        //get flash path
        $flash = plugins_url('sliding-plugin2.swf', __FILE__);
        $closebuton = plugins_url('img/close_button.png', __FILE__);
        $galleryDir = ABSPATH . $gallery;
        $galleryUrl = plugins_url('getGallery.php', __FILE__);
        $resizeUrl = plugins_url('getresizedImage.php', __FILE__);
        $uploadDir = wp_upload_dir();
        $uplDir = 'wp-content/uploads'.$uploadDir['subdir'];
        $uplPath = $uploadDir['path'];
        
        //echo ('$gallery:'.$gallery.'<br/>');
        
        if ($pieces == '3') $image = plugins_url('img/slide-3x3.jpg', __FILE__);
        if ($pieces == '4') $image = plugins_url('img/slide-4x4.jpg', __FILE__);
        if ($pieces == '5') $image = plugins_url('img/slide-5x5.jpg', __FILE__);
        
        $rndfile = sliding_mp_rndfile($galleryDir);
//        echo ('$rndfile:'.$rndfile.'<br/>');
//        echo ('$doresize:'.$doresize.'<br/>');
//        echo ('$myimage:'.$myimage.'<br/>');
        
        
        if (!$rndfile) 
            $rndfile = $image;
        else
            $rndfile = $gallery .'/'. $rndfile;
        
        $mySlider = new sliding_mp_slider();
        if ($myimage != '')
        {
            //check wether we deal with an url or an local-path
            $urlar = parse_url($myimage);
            if ($urlar['host']=='') $myimage = Site_url() .'/' . $myimage;
            if ($doresize==1) {
                $myPic = $mySlider->getResizedImage($myimage, true);
                if (!$myPic) 
                    return("Error: Could not load/resize the image, please check your upload permission or switch off the resize option.");
            }
            else
                $myPic = $myimage;
            $showlink = 1;
        } else {
            if ($doresize==1) {
                $myPic = $mySlider->getResizedImage($rndfile, true);
                if (!$myPic) 
                    return("Error: Could not load/resize the image, please check your upload permission or switch off the resize option.");
            }
            else
                $myPic = $rndfile;
            //$myPic = $image;
        }
        
        
        $output = "<div id='flashObject' style='z-index:0;'>";
        $output .= "<object id='myFlash' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000'";
	$output .= " codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0'";
	$output .= " width='".$size."' height='".$h."' align='middle'>\r";  
	$output .= "<param name='allowScriptAccess' value='sameDomain' />\r";
	$output .= "<param name='allowFullScreen' value='false' />\r";
	$output .= "<param name='movie' value='".$flash."' />\r";
	$output .= "<param name='flashvars' value='myHint=".$showhints."&myPieces=".$pieces."&myPic=" . $myPic . "' />\r";
	$output .= "<param name='quality' value='high' />\r";
	$output .= "<param name='menu' value='false' />\r";
	$output .= "<param name='bgcolor' value='".$bgcolor."' />\r";
        $output .= "<param name='wmode' value='transparent' />\r";
	$output .= "<embed src='".$flash."' flashvars='myHint=".$showhints."&myPieces=".$pieces."&myPic=" . $myPic . "' quality='high' bgcolor='".$bgcolor."'  swLiveConnect='true' ";
	$output .= "    width='".$size."' height='".$h."' name='sliding' menu='false' align='middle' allowScriptAccess='sameDomain' ";
	$output .= "    allowFullScreen='false' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' />\r";
	$output .= "</object>\r";
        $output .= "<div style=\"width:".$size."px;text-align: right;font-size:12px;\"><a href='http://mypuzzle.org/'>Puzzle Games</a></div>";
        $output .= "</div>\r";
        //add diff for the image gallery
        $output .= "<div id='gallery' style='z-index:1;'>\r";
        $output .= "    <span class='button bClose'><img src='".$closebuton."' /></span>\r";
        $output .= "    <div id='image_container' class='scroll-pane'></div>\r";
        $output .= "</div>\r";
        //add diff for the image wrapper template
        $output .= "<div id='imgWrapTemplate' class='imageWrapper' style='visibility:hidden;'>\r"; //
        $output .= "    <img src='' class='pickImage'/>\r";
        $output .= "    <div class='imageTitle'>Turtle</div>\r";
        $output .= "</div>\r";
        //add invisible variables for jquery access
        $output .= "<div id='flashvar_hint' style='visibility:hidden;position:absolute'>".$showhints."</div>\r";
        $output .= "<div id='flashvar_pieces' style='visibility:hidden;position:absolute'>".$pieces."</div>\r";
        $output .= "<div id='flashvar_startPicture' style='visibility:hidden;position:absolute'>".$myPic."</div>\r";
        $output .= "<div id='flashvar_width' style='visibility:hidden;position:absolute'>".$size."</div>\r";
        $output .= "<div id='flashvar_height' style='visibility:hidden;position:absolute'>".$size."</div>\r";
        $output .= "<div id='flashvar_bgcolor' style='visibility:hidden;position:absolute'>".$bgcolor."</div>\r";
        $output .= "<div id='var_uploadDir' style='visibility:hidden;position:absolute'>".$uplDir."</div>\r";
        $output .= "<div id='var_uploadPath' style='visibility:hidden;position:absolute'>".$uplPath."</div>\r";
        $output .= "<div id='var_galleryUrl' style='visibility:hidden;position:absolute'>".$galleryUrl."</div>\r";
        $output .= "<div id='var_resizeUrl' style='visibility:hidden;position:absolute'>".$resizeUrl."</div>\r";
        $output .= "<div id='var_galleryDir' style='visibility:hidden;position:absolute'>".$galleryDir."</div>\r";
        $output .= "<div id='var_plugin' style='visibility:hidden;position:absolute'>".$gallery."/</div>\r";
        $output .= "<div id='var_flash' style='visibility:hidden;position:absolute'>".$flash."</div>\r";
        $output .= "<div id='var_closebutton' style='visibility:hidden;position:absolute'>".$closebuton."</div>\r";
        $output .= "<div id='var_doresize' style='visibility:hidden;position:absolute'>".$doresize."</div>\r";
        //add jscript to start gallery from flash
        $output .= "<script language='javascript'>\r";
        $output .= "function openGallery() {showGallery();}\r";
        $output .= "</script>\r";
        
        return($output);

}

function sliding_mp_rndfile($dir) {
    
    if (!is_dir($dir)) return(null);
    if( $checkDir = opendir($dir) ) {
        $cFile = 0;
        // check all files in $dir, add to array listFile
        $preg = "/.(jpg|gif|png|jpeg)/i";
        while( $file = readdir($checkDir) ) {
            if(preg_match($preg, $file)) {
                if( !is_dir($dir . "/" . $file) ) {
                    $listFile[$cFile] = $file;
                    $cFile++;
                }
            }
        }
    }
    $num = rand(0, count($listFile) );

    return($listFile[$num]); 
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
                $newoptions['gallery'] = isset($_POST['gallery'])?$_POST['gallery']:$options['gallery'];
                $newoptions['doresize'] = isset($_POST['doresize'])?$_POST['doresize']:$options['doresize'];
                
                if ( $options != $newoptions ) {
                        $options = $newoptions;
                        update_option('sliding_mp_set', $options);			
                }

 	} 

	if(isset($_POST['Use_Default'])){
            update_option('sliding_mp_set', $options);
        }
        $showlink = $options['showlink'];
        if (!is_numeric($showlink) || !sliding_mp_testRange(intval($showlink),0,1)) {$showlink=0;}
	$size = $options['size'];
        if (!is_numeric($size) || !sliding_mp_testRange(intval($size),100,1500)) {$size=460;}
	$pieces = $options['pieces'];
        if (!is_numeric($pieces) || !sliding_mp_testRange(intval($pieces),3,5)) {$pieces=3;}
        $showhints = $options['showhints'];
        if (!is_numeric($showhints) || !sliding_mp_testRange(intval($showhints),0,1)) {$showhints=1;}
        $image = $options['image'];
        $bgcolor = $options['bgcolor'];
        $bgcolor = str_replace('#', '', $bgcolor);
        if (!preg_match('/^[a-f0-9]{6}$/i', $bgcolor)) $bgcolor = 'FFFFFF';
        $myimage = $options['myimage'];
        $gallery = $options['gallery'];
        $doresize = $options['doresize'];
        if (!is_numeric($doresize) || !sliding_mp_testRange(intval($doresize),0,1)) {$doresize=0;}
        
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
                <td width="500">
                    460 - equates to 460 pixel width for the flash and gives you the best image quality
                </td>
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
                <td width="200">e.g. "FFFFFF" for white background</td>
            </tr>
            <tr>
                <td width="100">
                    Image Url/Path
                </td>
                <td>
                    <input style="width: 200px" type="text" name="myimage" value="<?php echo ($myimage); ?>">
                </td>
                <td width="700">
                    e.g. "wp-content/uploads/myimage.jpg" or "http://mydomain.com/myimage.jpg" (requires url access)
                </td>
            </tr>
            <tr>
                <td width="100">
                    Path to Gallery
                </td>
                <td>
                    <input style="width: 200px" type="text" name="gallery" value="<?php echo ($gallery); ?>">
                </td>
                <td width="700">
                    e.g. "<?php 
                            $uploadDir = wp_upload_dir();
                            $uplDir = 'wp-content/uploads'.$uploadDir['subdir'];
                            echo ($uplDir.'" - Leave blank for MyPuzzle Images Gallery.'); 
                        ?>
                </td>
            </tr>
            <tr>
                <td width="100">
                    Resize
                </td>
                <td>
                    <select name="doresize" id="doresize" style="width: 200px">
                            <option value="0"<?php echo ($doresize == 0 ? " selected" : "") ?>><?php echo _e("Dont resize images") ?></option>
                            <option value="1"<?php echo ($doresize == 1 ? " selected" : "") ?>><?php echo _e("Resize images to fit") ?></option>
                    </select>
                </td>
                <td width="500">
                    Saves a copy in your current upload folder. Images should be around 400x400 px.
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="Submit" value="Update" class="button-primary" />
        </p>
        </form>
    </div>


<?php } 

