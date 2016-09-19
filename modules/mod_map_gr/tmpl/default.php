<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_popular
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="mod-map-gr<?php if (!$params->get('modal-on')) { echo ' modal-off'; } ?>">


    <?php if ($params->get('modal-on')) { ?>
        <div class="modal_form_btn" data-toggle="modal" data-target="#modal_map<?php echo $module->id; ?>">
            <span><?php echo $params->get('modal-btn-text'); ?></span>
        </div>
        
        <div class="modal fade modal_map" id="modal_map<?php echo $module->id; ?>" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">	
                <div type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</div>
    <?php } ?>
    
                <div class="map"> 
                    <div id="page_map_gr<?php echo $module->id; ?>" style="height: 400px;" class="map_window"></div>				
                </div>	
                
    <?php if ($params->get('modal-on')) { ?>                
                </div>	
            </div>
        </div>
    <?php } ?>
    
    <input type="hidden" class="js-map-gr-json" value='<?php echo $json; ?>'>
</div>