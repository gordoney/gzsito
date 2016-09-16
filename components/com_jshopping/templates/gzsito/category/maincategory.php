<?php 
/**
* @version      4.11.0 17.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');

$menu = JSite::getMenu();
$active = $menu->getActive();

print $this->_tmp_category_html_start;
?>
<div class="jshop jshop_list_category" id="comjshop">
    <h1 class="page-name"><?php print $active->title; ?></h1>
    <?php if ($this->category->description) { ?>
        <div class="category_description">
            <?php print $this->category->description?>
        </div>
    <?php } ?>

    <div>
    <?php if (count($this->categories)) : ?>
        <div class = "jshop list_category">
            <?php foreach($this->categories as $k=>$category) : ?>
            
                <?php if ($k % $this->count_category_to_row == 0) : ?>
                    <div class = "categories clearfix">
                <?php endif; ?>
                
                <div class = "sblock<?php echo $this->count_category_to_row;?> jshop_categ">
                    <a href = "<?php print $category->category_link?>">
                        <div class="image-wrapper">
                            <div class="image-overflow">
                                <div class = "image" style="background-image: url(<?php print $this->image_category_path;?>/<?php if ($category->category_image) print $category->category_image; else print $this->noimage;?>);"></div>
                            </div>
                        </div>
                        <div class="name">
                            <span><?php print $category->name?></span>
                        </div>
                    </a>
                </div>
                
                <?php if ($k % $this->count_category_to_row == $this->count_category_to_row - 1) : ?>
                    <div class = "clearfix"></div>
                    </div>
                <?php endif; ?>
                
            <?php endforeach; ?>
            
            <?php if ($k % $this->count_category_to_row != $this->count_category_to_row - 1) : ?>
                <div class = "clearfix"></div>
                </div>
            <?php endif; ?>
            
        </div>
    <?php endif; ?>
    </div>
	
	<?php print $this->_tmp_category_html_before_products;?>
        
    <?php include(dirname(__FILE__)."/products.php");?>
	
	<?php print $this->_tmp_category_html_end;?>
</div>