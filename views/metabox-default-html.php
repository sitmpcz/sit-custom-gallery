    <?php
    // Lista s filtrama a tlacitkama
    include( __DIR__ . "/metabox-filter-bar.php" );
    ?>
<div class="sitg-image-preview-list-wrap">
    <div id="sitg-image-preview-list" class="sitg-image-preview-list" data-post-id="<?php echo $post->ID; ?>">
        <?php
        // Vypis nahranych souboru
        echo $metabox_content; ?>
    </div>
    <p class="sitg-mb-btn-bottom">
        <button class="sitg-btn-delete-all button button-large" title="Smazat vše">Smazat vše</button>
        <button type="button" class="sitg-modal-open button button-primary button-large">Přidat fotky do galerie</button>
    </p>
</div>