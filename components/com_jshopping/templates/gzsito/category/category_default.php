<?php 
/**
* @version      4.11.0 17.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');

print $this->_tmp_category_html_start;
?>
<div class="jshop jshop_list_category" id="comjshop">

    <div>
        <?php if (count($this->categories)) { ?>
            <h1 class="page-name"><?php print $this->category->name?></h1>
            <?php if ($this->category->description) { ?>
                <div class="category_description">
                    <?php print $this->category->description?>
                </div>
            <?php } ?>

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
        <?php } else { ?>
            <div class="category-full clearfix">
                <?php //print_r($this->category->short_description);?>
                <div class="left-block">
                    <div class="img" style="background-image: url(<?php print $this->image_category_path;?>/<?php if ($this->category->category_image) print $this->category->category_image; else print $this->noimage;?>);">

                    </div>

                    <div class="form-block-wrapper">
                        <div class="form-block">
                            <div class="buy-text">
                                <?php echo JText :: _('JSHOP_CATEGORY_BUY'); ?> <span><?php print $this->category->name?></span> <?php echo JText :: _('JSHOP_CATEGORY_ORDER'); ?>
                            </div>

                            <div class="prev-text">
                                <?php echo $this->category->short_description; ?>
                            </div>

                            <div class="form-mod">
                                <?php jimport( 'joomla.application.module.helper' );
                                $modules = JModuleHelper::getModules('jshop-category-form');
                                $attribs['style'] = 'xhtml';

                                foreach ($modules as $module) {
                                    echo JModuleHelper::renderModule($module, $attribs);
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="right-block">
                    <div class="name">
                        <?php print $this->category->name?>
                    </div>

                    <?php print $this->_tmp_category_html_before_products;?>
                    <div class="products">
                        <?php foreach ($this->rows as $k=>$product) { ?>
                            <?php if ($k == 0 || $k%2 == 0) { ?>
                                <div>
                            <?php } ?>
                            <div class="product">
                                <a href="<?php echo $product->product_link; ?>"><?php echo $product->name; ?></a>
                            </div>
                            <?php if ($k%2 == 1 || $k == count($this->rows)-1) { ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <div class="description">
                        <?php echo $this->category->description; ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
	
	<?php print $this->_tmp_category_html_end;?>
</div>