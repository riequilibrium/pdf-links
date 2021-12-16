<?php
/*
Plugin Name: PDF Links
Plugin URI: https://github.com/riequilibrium/pdf-links
Description: This plugin adds a section where you easily insert PDF links to the product.
Version: 1.0.0
Text Domain: pdf-links-riequilibrium
Author: Riequilibrium Web Agency
Author URI: https://riequilibrium.com
License: GPLv3
*/

/**
 * Author: Simone Di Paolo
 * Company: Riequilibrium Web Agency
 * Contact: it@riequilibrium.com
 * Date: 2021-12-14
 * Description: Loads existing translations based on installation's language
 */
function pdf_links_init(){
    $current_user = wp_get_current_user();
    if(!($current_user instanceof WP_User))
        return;
    if(function_exists('get_user_locale'))
        $language = get_user_locale($current_user);
    else
        $language = get_locale();
    load_textdomain("pdf-links-riequilibrium", plugin_dir_path(__FILE__) . "/languages/" . $language . ".mo");
}
add_action("plugins_loaded", "pdf_links_init");

/**
 * Author: Simone Di Paolo
 * Company: Riequilibrium Web Agency
 * Contact: it@riequilibrium.com
 * Date: 2021-12-14
 * Description: Creates multiple buttons picked from a certain folder, shown in short description of products
 */
function pdf_links_riequilibrium_box(){
	$screens = ["product"]; // Select types of screens
	foreach($screens as $screen){
		add_meta_box(
			"pdf_links_riequilibrium", // Unique ID
			__("Link PDF", "pdf-links-riequilibrium"), // Box title
			"pdf_links_riequilibrium_html", // Content callback, must be of type callable
			$screen, // Post type
			"normal", // Context
			"default" // Priority
		);
	}
}
add_action("add_meta_boxes", "pdf_links_riequilibrium_box", 1);

add_action('admin_enqueue_scripts', function(){
    if(is_admin())
        wp_enqueue_media();
});

/**
 * Author: Simone Di Paolo
 * Company: Riequilibrium Web Agency
 * Contact: it@riequilibrium.com
 * Date: 2021-12-14
 * Description: HTML callback for the creation of the meta box
 */
function pdf_links_riequilibrium_html($post){
    ?>
    <style>
        .row{
            width: 100%;
        }
        .col-4{
            width: 33.3333333%;
            float: left;
        }
        .row > .col-4 > input{
            margin: 15px 5px;
        }
    </style>
    <script>
        function open_media(col, id){
            if(typeof wp !== 'undefined' && wp.media && wp.media.editor){
                var button = jQuery("#col" + col + "-filebtn-" + id);
                var url = button.prev();
                wp.media.editor.send.attachment = function(props, attachment){
                    url.val(attachment.url);
                };
                wp.media.editor.open(button);
                return false;
            }
        }
        jQuery(document).ready(function(){
            var i = 1;
            var custom_tabs = new Array();
            jQuery("#wc-jetpack-custom-tabs input[id*=wcj_custom_product_tabs_title_local]").each(function(){
                custom_tabs[this.id] = new Array();
                custom_tabs[this.id]["textarea"] = this.id.replace("title", "content");
                jQuery("#select-custom-fields").append(jQuery("<option>", { value: this.id, text: this.value }));
            });
            jQuery("#add-row").on("click", function(e){
                e.preventDefault();
                jQuery("#container-custom-fields").append("<div class='row'><div class='col-4'><input type='text' placeholder='Nome file PDF' id='col1-filename-" + i + "' /><input type='text' id='col1-fileurl-" + i + "' class='process_custom_images' /><input type='button' class='set_custom_images button' onclick='open_media(1, " + i + ");' value='Scegli file PDF' id='col1-filebtn-" + i + "' /></div><div class='col-4'><input type='text' placeholder='Nome file PDF' id='col2-filename-" + i + "' /><input type='text' id='col2-fileurl-" + i + "' class='process_custom_images' /><input type='button' class='set_custom_images button' onclick='open_media(2, " + i + ");' value='Scegli file PDF' id='col2-filebtn-" + i + "' /></div><div class='col-4'><input type='text' placeholder='Nome file PDF' id='col3-filename-" + i + "' /><input type='text' id='col3-fileurl-" + i + "' class='process_custom_images' /><input type='button' class='set_custom_images button' onclick='open_media(3, " + i + ");' value='Scegli file PDF'  id='col3-filebtn-" + i + "' /></div></div>");
                i-=-1;
            });
            jQuery("#insert-custom-fields").on("click", function(e){
                e.preventDefault();
                var inputs = new Array();
                jQuery("#container-custom-fields input[type=text]").each(function(){
                    if(this.id.includes("fileurl") && this.value != "")
                        inputs.push(this.id);
                });
                var section = jQuery("#select-custom-fields option:selected").val();
                var id = section;
                id = id.replace("title", "content");
                jQuery("#" + id + "-html").click();
                var k = 0;
                var inner_text = "";
                inputs.forEach(function(item){
                    if(k % 3 == 0){
                        inner_text = inner_text + '[vc_row equal_height="yes" content_placement="middle" class="custom-row-product-tab"]';
                    }
                    inner_text = inner_text + '[vc_column width="1/3" pofo_enable_responsive_css="1" responsive_css="padding_bottom_mobile:25px"][pofo_feature_box pofo_feature_type="featurebox39" pofo_enable_link="1" pofo_link_target="_blank" pofo_link_on="all" pofo_feature_title="' + jQuery("#" + item.replace("fileurl", "filename")).val() + '" pofo_link_url="' + jQuery("#" + item).val() + '" pofo_icon_list="far fa-file-pdf"]' + jQuery("#" + item.replace("fileurl", "filename")).val() + '[/pofo_feature_box][/vc_column]';
                    if(k % 3 == 2){
                        inner_text = inner_text + '[/vc_row]';
                    }
                    k++;
                });
                if(k - 1 % 3 != 2){
                    if(inner_text != "")
                        inner_text = inner_text + '[/vc_row]';
                }
                jQuery("#" + id).val(inner_text);
                alert("Contenuto inserito con successo!");
                jQuery("#container-custom-fields").empty();
            });
        });
    </script>
    <div>
        <label for="select-custom-fields">Seleziona la sezione dove aggiungere i PDF</label>
        <select id="select-custom-fields"></select>
    </div>
    <div id="row-add-button">
        <button class="button" id="add-row">Aggiungi riga</button>
    </div>
    <div id="container-custom-fields"></div>
    <p>
        <button class="button" id="insert-custom-fields">Inserisci nella sezione</button>
    </p>
    <?php
}