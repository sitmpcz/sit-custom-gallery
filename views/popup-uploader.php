<div id="sitg-uploader">
    <div tabindex="0" class="sitg-media-modal" role="dialog" aria-labelledby="media-frame-title">
        <div class="sitg-modal-window">
            <button type="button" class="sitg-modal-close media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text">Zavřít okno</span></span></button>
            <div class="sitg-modal-title" id="media-frame-title"><h1>Nahrát fotky</h1></div>
            <div class="sitg-modal-content">
                <div class="sitg-container">
                <?php
                    include( __DIR__ . "/popup-uploader-content.php");
                ?>
                </div>
            </div>
            <div class="sitg-modal-footer">
                <p class="textright"><button type="button" id="sitg-modal-close-btn" class="sitg-modal-close button button-primary button-large">Hotovo</button></p>
            </div>
        </div>
    </div>
    <div class="media-modal-backdrop"></div>
</div>


