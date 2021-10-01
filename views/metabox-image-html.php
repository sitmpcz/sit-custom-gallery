
<div class="sitg-mb-list-ui-handle<?php echo $featured_class; ?>" data-pid="<?php echo $pid; ?>" data-sort="<?php echo $sort; ?>">
    <div class="sitg-mb-list-item">
        <div class="sitg-mb-list-img" style="background-image: url(<?php echo $img_path; ?>); background-size: cover; width: <?php echo $width; ?>px; height: <?php echo $height; ?>px;"></div>
        <p class="sitg-mb-list-item-title"><strong><?php echo esc_html( stripslashes( $title ) ); ?></strong></p>
        <input type="hidden" name="_sitg_images[<?php echo $pid; ?>]" value="<?php echo $img_path; ?>">
        <div class="sitg-mb-buttons">
            <?php // <span class="sitg-js-btn-delete sitg-mb-buttons-btn sitg-mb-btn-delete"><i class="dashicons dashicons-trash"></i></span> ?>
            <span class="sitg-handler-featured sitg-mb-buttons-btn sitg-mb-btn-featured"><i class="dashicons dashicons-star-filled"></i></span>
            <span class="sitg-handler-edit sitg-mb-buttons-btn sitg-mb-btn-edit"><i class="dashicons dashicons-edit"></i></span>
        </div>
    </div>
    <div class="sitg-mb-edit-layer">
        <span class="sitg-handler-edit-close sitg-mb-btn-close"><i class="dashicons dashicons-no"></i></span>
        <label for="sitg-mb-edit-title-<?php echo $pid; ?>" class="sitg-mb-edit-title-label">Nový titulek obrázku</label>
        <input type="text" name="sitg-mb-edit-title" value="<?php echo esc_html( stripslashes( $title ) ); ?>" id="sitg-mb-edit-title-<?php echo $pid; ?>" data-cache="<?php echo esc_html( stripslashes( $title ) ); ?>" data-lpignore="true" class="sitg-form-control sitg-mb-edit-title">
        <div class="text-center">
            <button type="button" class="sitg-handler-edit-save button button-primary button-large">Uložit</button>
        </div>
    </div>
</div>
