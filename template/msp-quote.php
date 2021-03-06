<?php
if (!defined("ABSPATH")) {
  exit(); // Exit if accessed directly
}

if (isset($_GET["ids"])): ?>
    <hr/>
    <form id="msp-quote" method="POST" action="<?php echo admin_url(
      "admin-post.php"
    ); ?>">
    <?php foreach ($_GET["ids"] as $product_id):

      $product = wc_get_product($product_id);

      if (!empty($product) && !$product->is_type("simple")) {
        $product_variations = $product->get_children();
      }

      if ($product == false) {
        return;
      }
      ?>
        <div id="product-<?php echo $product_id; ?>" class="row">

            <div class="col-12 col-6">

                <h2><?php echo $product->get_title(); ?></h2>

                <?php $src = msp_get_product_image_src(
                  $product->get_image_id(),
                  "medium"
                ); ?> 

                <img src="<?php echo $src; ?>" class='img-fluid' />

                <br>

            </div>

            <div class="col">
                <?php if (!empty($product_variations)): ?>
                    <h3>Variations:</h3>
                    <table class="table">
                        <?php foreach ($product_variations as $variation_id): ?>
                            <?php $variation = wc_get_product($variation_id); ?>
                            <tr>
                                <td>
                                    <img class="img-mini" src="<?php echo msp_get_product_image_src(
                                      $variation->get_image_id(),
                                      "woocommerce_thumbnail"
                                    ); ?>" />
                                    <span class="text-center"><?php echo $variation->get_sku(); ?> - <?php echo wc_get_formatted_variation(
   $variation->get_variation_attributes(),
   true,
   false,
   true
 ); ?></span>
                                </td>
                                <td class="price"><?php echo $variation->get_price_html(); ?> </td>
                                <td>
                                    <label>How many? </label><br>
                                    <input type="number" id="<?php echo $variation_id .
                                      '-qty"'; ?> name="<?php echo "product[" .
   $variation_id .
   ']"'; ?> />
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                <div class="form-group">
                    <label>How many? </label>
                    <input class="form-control" type="tel" name="product[<?php echo $product->get_id(); ?>]" min="0" />
                </div>
                <?php endif; ?>
            </div>
        </div>
        <hr/>
    <?php
    endforeach; ?>

    <h2>Wheres it going?</h2>

    <div class="form-group">
        <label for="name">What should we call you?</label>
        <input id="name" name="name" class="form-control" type="text" required />
    </div>

    <div class="form-group">
        <label for="email">Your email</label>
        <input id="email" name="email" class="form-control" type="email" required />
    </div>
    
    <div class="form-group">
        <label for="street">Street</label>
        <input id="street" name="street" class="form-control" type="text">
    </div>

    <div class="form-group">
        <label for="zip">Zip</label>
        <input id="zip" name="zip" class="form-control" type="text">
    </div>

    <input type="hidden" name="action" value="msp_submit_bulk_form" />
    <?php do_action("anr_captcha_form_field"); ?>
    <button type="submit" class="btn btn-danger">Submit quote request</button>
</form>

<?php endif;
?>
