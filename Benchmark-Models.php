<?php

/**
 * This code is a sanitized sample of a plugin made to process excel sheets into usable data for an interactive front-end chart tool.
 *
 *
 *
 * @link              http://example.com
 * @since             2.0.0
 * @package           Model_Performance_New
 *
 * @wordpress-plugin
 * Plugin Name:       Model performance tool New
 * Description:       Plugin includes Model review tool, Correlation matrix calculations and charts.
 * Version:           2.0.0
 * Author:            Zach Stewart
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       model-performance
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'SHORTINIT', true );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );

global $wpdb;
if(!isset($wpdb))
{
    require_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
    require_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');
}

define( 'MODEL_PERFORMANCE_VERSION', '2.0.0' );

function activate_model_performance() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-model-performance-activator.php';
    Model_Performance_Activator::activate();
}

function set_db_model_performance() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/migrations/class-model-performance-db-setup.php';
    $setupDbCustom = new Model_Performance_DBsetup();
    $setupDbCustom->model_performance_install();
}

function deactivate_model_performance() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-model-performance-deactivator.php';
    Model_Performance_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_model_performance' );
register_activation_hook( __FILE__, 'set_db_model_performance' ); // setup for db

register_deactivation_hook( __FILE__, 'deactivate_model_performance' );

function model_performance_update_db_check() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/migrations/class-model-performance-db-setup.php';
    $updateCheckCustom = new Model_Performance_DBsetup();
    $updateCheckCustom->model_performance_update_db_check();
}

// Admin footer modification
function remove_footer_admin ()
{
    echo '<span id="footer-thankyou">Model performance plugin</span>';
}
add_filter('admin_footer_text', 'remove_footer_admin');

// check for db update
add_action('plugins_loaded', 'model_performance_update_db_check');

add_action('wp_head', 'model_performance_ajax_url');

function model_performance_ajax_url() {
    echo '<script type="text/javascript">
           var ajax_url = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

require plugin_dir_path( __FILE__ ) . 'includes/class-model-performance.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_model_performance() {

    $plugin = new Model_Performance();
    $plugin->run();

}
run_model_performance();

function reader($fname){
    $row = 1;
    $arr = array();
    if (($handle = fopen($fname, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
            $num = count($data);
            if($row === 1){
                $titles = array();
                for ($c=0; $c < $num; $c++) {
                    $titles[$c] = $data[$c];
                }
                $t = 0;
            } else {
                for ($c=0; $c < $num; $c++) {
                    if($c === 0){
                        $rtitle = $data[$c];
                        if(array_key_exists($rtitle, $arr)){
                            $t++;
                        } else {
                            $t = 0;
                        }
                        $arr[$rtitle][$t] = array();
                    } else {
                        $arr[$rtitle][$t][$titles[$c]] = $data[$c];
                    }
                }
            }
            $row++;

        }
        fclose($handle);
        return $arr;
    } else {
        die("Error, ".$fname." Not In Place.");
    }
}

function handle_post($morb, $page){
    global $wpdb;
    // First check if the file appears on the _FILES array
    if(isset($_FILES['upload_csv'])){
        echo "Uploaded File Detected <br>";
        $pdf = $_FILES['upload_csv'];

        // Use the wordpress function to upload
        // test_upload_pdf corresponds to the position in the $_FILES array
        // 0 means the content is not associated with any other posts
        $uploaded=wp_handle_upload($pdf, array('test_form' => FALSE));

        // Error checking using WP functions
        if(is_wp_error($uploaded)){
            echo "Error uploading file: " . $uploaded->get_error_message();
        }else{
            echo "File succesfully uploaded to ".$uploaded['url']."<br>";
            $handle = reader($uploaded['url']);
            if($morb === 'm'){
                $bnames = $handle["Model"][0];
            } else {
                $bnames = $handle["Benchmark"][0];
            }
            $i = 0;
            foreach($bnames as $bname => $val){
                if($morb === 'm'){
                    $table = "models";
                } else {
                    $table = "benchmarks";
                }
                $sql = "REPLACE INTO ".$table." (symbol, name) VALUES ('".$bname."','".$val."');";
                if(!isset($wpdb)){
                    die('ERROR: WPDB disconnect');
                }
                $clean = $wpdb->prepare($sql);
                $exe = $wpdb->query($clean);


                if(!$exe){
                    die('Error! Failed to execute Benchmark query '.$i);
                } else {
                    $i++;
                }
            }

            echo "Added $i new $table <br>";
            $i = 0;
            $d = 0;
            $lval = array();
            $lgrow = array();
            foreach($handle as $date => $vals){
                if($date === "Benchmark" || $date === "Model"){ //ignores first 2 lines
                    continue;
                } else {
                    if($morb === 'b'){
                        $itable = 'benchmark';
                        $pages = $page.", gpage";
                        $retpage = "retpage";
                        $gpage = "gpage";
                    } else {
                        $itable = 'model';
                        $pages = $page.", mpage";
                        $retpage = "mreturn";
                        $gpage = "mpage";
                    }
                    $datetime = strtotime($date);
                    $date = date('Y-m-d', $datetime);
                    foreach($vals[0] as $sym => $val){
                        $tsql = "SELECT * FROM ".$itable."_dates WHERE ".$itable."_date='".$date."' AND symbol='".$sym."';";
                        $tclean = $wpdb->prepare($tsql);
                        $texe = $wpdb->query($tclean);

                        if($d === 0 || $val === ''){
                            $moret = $val;
                            $growth = '100.00';
                            $lgrow[$sym] = 100;
                        } else {
                            $ret = $val/100;
                            $moret = number_format($ret,5);
                            $gamt = $lgrow[$sym] * $ret;
                            $gtot = $lgrow[$sym] + $gamt;
                            $lgrow[$sym] = $gtot;
                            $growth = number_format($gtot,2);
                        }

                        $lval[$sym] = $val;


                        if(!$texe){
                            $sql = "INSERT INTO ".$itable."_dates (".$itable."_date, symbol, ".$pages.") VALUES ('".$date."', '".$sym."', '".$moret."', '".$growth."');";
                        }else{
                            $sql = "UPDATE ".$itable."_dates SET ".$page."='".$moret."', ".$gpage."='".$growth."' WHERE ".$itable."_date='".$date."' AND symbol='".$sym."';";
                        }


                        $clean = $wpdb->prepare($sql);
                        $exe = $wpdb->query($clean);

                        if(!$exe){
                            echo "<pre>";
                            var_dump($clean);
                            echo "</pre>";
                            echo'Error! Failed to upsert date query '.$i;
                            $i++;
                        } else {
                            $i++;
                        }
                    }/*foreach($vals[0] as $sym => $val)*/

                    $d++; //iterate date counter
                }
            } /*foreach($handle as $date => $vals)*/
            echo "Added $i new $itable dates. File upload successful! <br>";
        }
    } elseif(isset($_POST['delete-select'])){
        $ds = $_POST['delete-select'];
        if($morb === 'm'){
            $itable = 'models';
            $ttable = 'mdates';
        } else {
            $itable = 'benchmarks';
            $ttable = 'bdates';
        }
        $dsql = "DELETE FROM ".$itable." WHERE symbol='".$ds."'";
        $clean = $wpdb->prepare($dsql);
        $exe = $wpdb->query($clean);
        if(!$exe){
            echo "<pre>";
            var_dump($clean);
            echo "</pre>";
            echo "Failed to delete $itable!";
        } else {
            $dsql2 = "DELETE FROM ".$ttable." WHERE symbol='".$ds."'";
            $dclean = $wpdb->prepare($dsql2);
            $dexe = $wpdb->query($dclean);
            if(!$dexe){
                echo "<pre>";
                var_dump($dclean);
                echo "</pre>";
                echo "Failed to delete $ttable!";
            } else {
                echo "Deleted all entries labeled $ds";
            }
        }
    } elseif(isset($_POST['hide-select'])){
        $action = $_POST['hide-select'];
        $bmark = $_POST['hide-mark'];
        $class = $_POST['user-select'];
        if($morb === 'm'){
            $itable = 'models';
        } else {
            $itable = 'benchmarks';
        }

        $rquery = "SELECT restrictions FROM ".$itable." WHERE symbol='".$bmark."'";
        $rclean = $wpdb->prepare($rquery);
        $query = $wpdb->query($rclean);
        echo "<pre>";
        var_dump($rclean);
        echo "</pre>";
        die();

        if($action === 'hide'){
            if($class === "all"){
                $roles = get_editable_roles();
                $restrictions = '';
                foreach($roles as $role=>$val):
                    $restrictions .= $role.', ';
                endforeach;
            }
            elseif($query){
                $restrictions = $query['restrictions'];
                $restrictions .= ", ".$class;
            } else {
                $restrictions = $class;
            }
        } else {
            if($query){
                if($class === "all"){
                    $restrictions = '';
                } else {
                    $ors = $query['restrictions'];
                    $mrs = str_replace($class, '', $ors);
                    $restrictions = str_replace(', ,', ',', $mrs);
                    //remove bmark from restrictions
                }
            } else {
                echo "Error: This benchmark is already showing for that user category.";
            }
        }

        $sql = "UPDATE ".$itable." SET restrictions='".$restrictions."' WHERE symbol='".$bmark."'";
        $clean = $wpdb->prepare($sql);
        $exe = $wpdb->query($clean);

        if(!$exe){
            echo "<pre>";
            var_dump($clean);
            echo "</pre>";
            echo "Error: Failed to update permissions.";
        } else {
            echo "Successfully $action $bmark for $class.";
        }

    }/*determine which function based on post data*/
}/*handle-post*/
