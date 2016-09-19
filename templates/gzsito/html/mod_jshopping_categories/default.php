<div class="mod_jshopping_categories clearfix">
    <div class="container">
        <?php foreach($categories_arr as $curr) {
              $class = "jshop_menu_level_" . $curr->level;
              if ($categories_id[$curr->level] == $curr->category_id) $class = $class . "_a";
              ?>
              <div class="categories_group">
                  <div class="<?php print $class ?>">
                      <a href="<?php print $curr->category_link ?>"><?php print $curr->name ?>
                          <?php if ($show_image && $curr->category_image) { ?>
                              <img align="absmiddle"
                                   src="<?php print $jshopConfig->image_category_live_path . "/" . $curr->category_image ?>"
                                   alt="<?php print $curr->name ?>"/>
                          <?php } ?>
                      </a>
                  </div>
                  <?php
                  $category->load($curr->category_id);
                  $categories_child_arr = $category->getChildCategories();
                  foreach ($categories_child_arr as $category_child) { ?>
                      <div class="child-cat">
                          <a href="<?php print $category_child->category_link ?>">
                              <?php print $category_child->name ?>
                          </a>
                      </div>
                  <?php } ?>
              </div>
          <?php } ?>
    </div>
</div>
