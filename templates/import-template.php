<div class="wrap">
    <h2><?php echo __("Import game keys", "keys-for-wp-woo-fungies");?></h2>
    <form method="post" enctype="multipart/form-data">
        <label for="csv_file"><?php echo __("Choose CSV:", "keys-for-wp-woo-fungies");?></label>
        <input type="file" name="csv_file" id="csv_file" accept=".csv">
        <?php wp_nonce_field('import_csv', 'import_csv_nonce'); ?>
        <input type="submit" name="submit" value="<?php echo __("Import", "keys-for-wp-woo-fungies");?>">
    </form>
</div>